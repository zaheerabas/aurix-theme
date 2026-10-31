<?php
/**
 * AURIX INBOX — PUSH NOTIFICATIONS
 *
 * Phone / desktop notifications like other apps: when a new message (or an
 * access request) arrives, every signed-in Inbox user who turned on
 * notifications gets a system notification. Tapping it opens the message.
 *
 * Standard Web Push (VAPID), no third-party service or plugin:
 *  - Android / Chrome / Edge / Firefox: works in the browser or installed app.
 *  - iPhone / iPad (iOS 16.4+): works after "Add to Home Screen".
 *
 * Pushes are sent without a payload; the service worker asks the site what
 * to show (/inbox-app/latest), so nothing private travels through push servers.
 *
 * @package Aurix
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── VAPID keys (created once, stored in the database) ─────────────────── */
function aurix_push_b64url( $bin ) {
	return rtrim( strtr( base64_encode( $bin ), '+/', '-_' ), '=' );
}

function aurix_push_supported() {
	return function_exists( 'openssl_pkey_new' ) && function_exists( 'openssl_sign' ) && defined( 'OPENSSL_KEYTYPE_EC' );
}

function aurix_push_vapid() {
	$v = get_option( 'aurix_push_vapid' );
	if ( is_array( $v ) && ! empty( $v['private'] ) && ! empty( $v['public'] ) ) return $v;
	if ( ! aurix_push_supported() ) return null;
	$key = openssl_pkey_new( array( 'curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC ) );
	if ( ! $key ) return null;
	openssl_pkey_export( $key, $pem );
	$d = openssl_pkey_get_details( $key );
	$x = str_pad( $d['ec']['x'], 32, "\0", STR_PAD_LEFT );
	$y = str_pad( $d['ec']['y'], 32, "\0", STR_PAD_LEFT );
	$v = array( 'private' => $pem, 'public' => aurix_push_b64url( "\x04" . $x . $y ) );
	update_option( 'aurix_push_vapid', $v, false );
	return $v;
}

/** ES256 JWT for the push service at $endpoint. */
function aurix_push_jwt( $endpoint, $vapid ) {
	$p      = wp_parse_url( $endpoint );
	$aud    = $p['scheme'] . '://' . $p['host'] . ( isset( $p['port'] ) ? ':' . $p['port'] : '' );
	$header = aurix_push_b64url( wp_json_encode( array( 'typ' => 'JWT', 'alg' => 'ES256' ) ) );
	$claims = aurix_push_b64url( wp_json_encode( array(
		'aud' => $aud,
		'exp' => time() + 12 * HOUR_IN_SECONDS,
		'sub' => 'mailto:' . get_option( 'admin_email' ),
	) ) );
	$data = $header . '.' . $claims;
	if ( ! openssl_sign( $data, $der, $vapid['private'], OPENSSL_ALGO_SHA256 ) ) return '';
	return $data . '.' . aurix_push_b64url( aurix_push_der_to_raw( $der ) );
}

/** Convert an ASN.1 DER ECDSA signature into the raw 64-byte r||s form JWT needs. */
function aurix_push_der_to_raw( $der ) {
	$pos = 2; // skip SEQUENCE tag + length
	if ( ord( $der[1] ) & 0x80 ) $pos += ord( $der[1] ) & 0x7f;
	$out = '';
	for ( $i = 0; $i < 2; $i++ ) {
		$pos++; // INTEGER tag
		$len = ord( $der[ $pos++ ] );
		$int = substr( $der, $pos, $len );
		$pos += $len;
		$int  = ltrim( $int, "\0" );
		$out .= str_pad( $int, 32, "\0", STR_PAD_LEFT );
	}
	return $out;
}

/* ── Subscriptions (one per device) ────────────────────────────────────── */
function aurix_push_subs() {
	$s = get_option( 'aurix_push_subs', array() );
	return is_array( $s ) ? $s : array();
}

function aurix_push_save_subs( $s ) {
	update_option( 'aurix_push_subs', $s, false );
}

function aurix_push_subscribe( $uid, $endpoint, $ua = '' ) {
	$endpoint = esc_url_raw( $endpoint );
	if ( ! $endpoint || 0 !== strpos( $endpoint, 'https://' ) ) return false;
	$subs = aurix_push_subs();
	$subs[ md5( $endpoint ) ] = array( 'uid' => (int) $uid, 'endpoint' => $endpoint, 'ua' => mb_substr( $ua, 0, 200 ), 'time' => time() );
	aurix_push_save_subs( $subs );
	return true;
}

function aurix_push_unsubscribe( $endpoint ) {
	$subs = aurix_push_subs();
	unset( $subs[ md5( $endpoint ) ] );
	aurix_push_save_subs( $subs );
}

function aurix_push_user_devices( $uid ) {
	return count( array_filter( aurix_push_subs(), function ( $s ) use ( $uid ) { return (int) $s['uid'] === (int) $uid; } ) );
}

/* ── What to notify about ──────────────────────────────────────────────── */
/** Per-user choice: 'all' or 'important' (skip newsletter, waitlist, back-in-stock). */
function aurix_push_pref( $uid ) {
	$p = get_user_meta( $uid, '_aurix_push_pref', true );
	return in_array( $p, array( 'all', 'important' ), true ) ? $p : 'important';
}

/** Queue a notification. $item: title, body, url, tag, type, admins_only. Sent after the page has been delivered. */
function aurix_push_queue( $item ) {
	if ( ! aurix_push_supported() || ! aurix_push_subs() ) return;
	$item = wp_parse_args( $item, array( 'title' => 'Aurix Inbox', 'body' => '', 'url' => home_url( '/inbox-app/' ), 'tag' => 'aurix', 'type' => '', 'admins_only' => false ) );
	$item['id']   = wp_generate_password( 10, false, false );
	$item['time'] = time();
	$item['from'] = get_current_user_id(); // don't notify the person who caused it

	$log   = get_option( 'aurix_push_log', array() );
	$log   = is_array( $log ) ? $log : array();
	$log[] = $item;
	update_option( 'aurix_push_log', array_slice( $log, -20 ), false );

	$GLOBALS['aurix_push_outbox'][] = $item;
	if ( empty( $GLOBALS['aurix_push_hooked'] ) ) {
		$GLOBALS['aurix_push_hooked'] = true;
		add_action( 'shutdown', 'aurix_push_flush', 999 );
	}
}

/** Who should get $item? Returns subscriptions keyed by hash. */
function aurix_push_targets( $item ) {
	$out = array();
	foreach ( aurix_push_subs() as $k => $s ) {
		$u = get_userdata( (int) $s['uid'] );
		if ( ! $u || ! user_can( $u, 'aurix_inbox' ) ) continue;
		if ( ! empty( $item['admins_only'] ) && ! user_can( $u, 'manage_options' ) ) continue;
		if ( ! empty( $item['from'] ) && (int) $item['from'] === (int) $u->ID ) continue;
		if ( in_array( $item['type'], array( 'newsletter', 'waitlist', 'stock' ), true ) && 'all' !== aurix_push_pref( $u->ID ) ) continue;
		$out[ $k ] = $s;
	}
	return $out;
}

/** Send queued pushes after the visitor already got their response. */
function aurix_push_flush() {
	$items = $GLOBALS['aurix_push_outbox'] ?? array();
	if ( ! $items ) return;
	$GLOBALS['aurix_push_outbox'] = array();
	if ( function_exists( 'litespeed_finish_request' ) ) litespeed_finish_request();
	elseif ( function_exists( 'fastcgi_finish_request' ) ) fastcgi_finish_request();

	$vapid = aurix_push_vapid();
	if ( ! $vapid ) return;
	$sent  = array();
	$dead  = array();
	foreach ( $items as $item ) {
		foreach ( aurix_push_targets( $item ) as $k => $s ) {
			if ( isset( $sent[ $k ] ) ) continue; // one push per device per request — the device fetches the latest anyway
			$sent[ $k ] = true;
			$code = aurix_push_send( $s['endpoint'], $vapid );
			if ( in_array( $code, array( 404, 410 ), true ) ) $dead[] = $k; // device unsubscribed / app removed
		}
	}
	if ( $dead ) {
		$subs = aurix_push_subs();
		foreach ( $dead as $k ) unset( $subs[ $k ] );
		aurix_push_save_subs( $subs );
	}
}

/** Send one empty (tickle) push. Returns the HTTP status code. */
function aurix_push_send( $endpoint, $vapid = null ) {
	$vapid = $vapid ?: aurix_push_vapid();
	if ( ! $vapid ) return 0;
	$jwt = aurix_push_jwt( $endpoint, $vapid );
	if ( ! $jwt ) return 0;
	$res = wp_remote_post( $endpoint, array(
		'timeout' => 8,
		'headers' => array(
			'Authorization'  => 'vapid t=' . $jwt . ', k=' . $vapid['public'],
			'TTL'            => '86400',
			'Urgency'        => 'high',
			'Content-Length' => '0',
		),
		'body'    => '',
	) );
	return is_wp_error( $res ) ? 0 : (int) wp_remote_retrieve_response_code( $res );
}

/** What a device should show for the latest push (called by the service worker). */
function aurix_push_latest_for( $uid ) {
	$log = array_reverse( (array) get_option( 'aurix_push_log', array() ) );
	$u   = get_userdata( $uid );
	foreach ( $log as $item ) {
		if ( time() - (int) $item['time'] > 30 * MINUTE_IN_SECONDS ) break;
		if ( ! empty( $item['admins_only'] ) && ! user_can( $u, 'manage_options' ) ) continue;
		if ( ! empty( $item['from'] ) && (int) $item['from'] === (int) $uid ) continue;
		if ( ! empty( $item['only'] ) && (int) $item['only'] !== (int) $uid ) continue;
		unset( $item['only'] );
		return $item;
	}
	return array( 'title' => 'Aurix Inbox', 'body' => 'You have new messages', 'url' => home_url( '/inbox-app/' ), 'tag' => 'aurix' );
}

/* ── Triggers ──────────────────────────────────────────────────────────── */
/** New Inbox message → notify. Called from aurix_inbox_add(). */
function aurix_push_new_message( $id ) {
	if ( ! empty( $GLOBALS['aurix_inbox_importing'] ) ) return;
	$r = aurix_inbox_get( $id );
	if ( ! $r || 'spam' === $r['status'] ) return;
	$t   = aurix_inbox_type( $r['type'] );
	$who = $r['name'] ?: ( $r['company'] ?: ( $r['email'] ?: 'Someone' ) );
	aurix_push_queue( array(
		'title' => $t['label'] . ' · ' . $who,
		'body'  => aurix_inbox_summary( $r ) . ( $r['message'] ? "\n" . wp_trim_words( $r['message'], 18 ) : '' ),
		'url'   => home_url( '/inbox-app/?id=' . $id ),
		'tag'   => 'msg-' . $id,
		'type'  => $r['type'],
	) );
}

/* ── Service worker ────────────────────────────────────────────────────── */
function aurix_push_sw_js() {
	$base = home_url( '/inbox-app/' );
	header( 'Content-Type: application/javascript; charset=utf-8' );
	header( 'Service-Worker-Allowed: ' . wp_parse_url( $base, PHP_URL_PATH ) );
	header( 'Cache-Control: no-cache' );
	?>
/* Aurix Inbox service worker — notifications + offline screen */
var BASE = <?php echo wp_json_encode( $base ); ?>;
var ICON = BASE + 'icon-192', BADGE = BASE + 'badge', OFFLINE = 'aurix-offline-v1';

self.addEventListener('install', function (e) {
	e.waitUntil(caches.open(OFFLINE).then(function (c) { return c.addAll([ICON]); }).catch(function () {}));
	self.skipWaiting();
});
self.addEventListener('activate', function (e) { e.waitUntil(self.clients.claim()); });

self.addEventListener('fetch', function (e) {
	if (e.request.mode !== 'navigate') return;
	e.respondWith(fetch(e.request).catch(function () {
		return new Response('<!doctype html><meta name=viewport content="width=device-width,initial-scale=1"><body style="margin:0;display:flex;min-height:100vh;align-items:center;justify-content:center;background:#0d1b2a;color:#fff;font:16px -apple-system,Roboto,Arial,sans-serif;text-align:center;padding:24px"><div><h2 style="color:#b8925a">You are offline</h2><p>Check your internet connection.</p><button onclick="location.reload()" style="margin-top:10px;border:0;border-radius:12px;padding:12px 22px;background:#b8925a;color:#fff;font-weight:700">Try again</button></div>', { headers: { 'Content-Type': 'text/html; charset=utf-8' } });
	}));
});

self.addEventListener('push', function (e) {
	e.waitUntil((async function () {
		var d = null;
		try { if (e.data) d = e.data.json(); } catch (_) {}
		if (!d) {
			try { var r = await fetch(BASE + 'latest', { credentials: 'include', cache: 'no-store' }); if (r.ok) d = await r.json(); } catch (_) {}
		}
		d = d || { title: 'Aurix Inbox', body: 'You have new messages', url: BASE, tag: 'aurix' };
		if (self.navigator && self.navigator.setAppBadge && typeof d.unread === 'number') {
			try { d.unread ? await self.navigator.setAppBadge(d.unread) : await self.navigator.clearAppBadge(); } catch (_) {}
		}
		return self.registration.showNotification(d.title, {
			body: d.body, tag: d.tag || 'aurix', renotify: true, icon: ICON, badge: BADGE,
			data: { url: d.url || BASE }, vibrate: [80, 40, 80], timestamp: Date.now()
		});
	})());
});

self.addEventListener('notificationclick', function (e) {
	e.notification.close();
	var url = (e.notification.data && e.notification.data.url) || BASE;
	e.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
		for (var i = 0; i < list.length; i++) {
			var c = list[i];
			if (c.url.indexOf(BASE) === 0 && 'focus' in c) { return c.focus().then(function (w) { return w.navigate ? w.navigate(url) : w; }); }
		}
		return self.clients.openWindow(url);
	}));
});
	<?php
	exit;
}

/* ── App icons as PNG (iPhone home screen + notifications need PNG) ────── */
function aurix_push_icon_png( $size, $badge = false ) {
	header( 'Cache-Control: public, max-age=604800' );
	if ( ! $badge ) {
		$site = get_site_icon_url( $size );
		if ( $site && ! get_option( 'aurix_app_own_icon' ) ) { wp_safe_redirect( $site ); exit; }
	}
	if ( ! function_exists( 'imagecreatetruecolor' ) ) { wp_safe_redirect( home_url( '/inbox-app/icon' ) ); exit; }
	$im = imagecreatetruecolor( $size, $size );
	imagesavealpha( $im, true );
	if ( $badge ) {
		imagealphablending( $im, false );
		imagefill( $im, 0, 0, imagecolorallocatealpha( $im, 0, 0, 0, 127 ) );
		imagealphablending( $im, true );
		$fg = imagecolorallocate( $im, 255, 255, 255 );
	} else {
		imagefill( $im, 0, 0, imagecolorallocate( $im, 13, 27, 42 ) );
		$fg = imagecolorallocate( $im, 184, 146, 90 );
	}
	$t  = max( 2, (int) round( $size * 0.055 ) );
	imagesetthickness( $im, $t );
	$x1 = (int) ( $size * 0.22 ); $x2 = (int) ( $size * 0.78 );
	$y1 = (int) ( $size * 0.31 ); $y2 = (int) ( $size * 0.69 );
	imagerectangle( $im, $x1, $y1, $x2, $y2, $fg );
	imageline( $im, $x1, $y1, (int) ( $size / 2 ), (int) ( $size * 0.52 ), $fg );
	imageline( $im, (int) ( $size / 2 ), (int) ( $size * 0.52 ), $x2, $y1, $fg );
	header( 'Content-Type: image/png' );
	imagepng( $im );
	imagedestroy( $im );
	exit;
}
