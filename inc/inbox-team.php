<?php
/**
 * AURIX INBOX — TEAM ACCESS
 *
 * Admins decide who else can use the Inbox. Each team member gets the
 * "Inbox Team" role, which can ONLY read and answer Inbox messages — no
 * access to products, orders, pages or settings.
 *
 * Admin adds a person (name + email) → a personal QR code / link appears.
 * The person scans it with their phone → they are signed in to the Inbox app
 * straight away (no password needed). Links work once and expire after 48 h.
 * Removing access signs the person out on every device immediately.
 *
 * @package Aurix
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'AURIX_TEAM_ROLE', 'aurix_inbox_team' );
define( 'AURIX_TEAM_LINK_TTL', 2 * DAY_IN_SECONDS );

/* ── Role + capability ──────────────────────────────────────────────────── */
add_action( 'init', function () {
	if ( ! get_role( AURIX_TEAM_ROLE ) ) {
		add_role( AURIX_TEAM_ROLE, 'Inbox Team', array( 'read' => true, 'aurix_inbox' => true ) );
	}
}, 2 );

/** Site admins always have Inbox access. */
add_filter( 'user_has_cap', function ( $allcaps ) {
	if ( ! empty( $allcaps['manage_options'] ) ) $allcaps['aurix_inbox'] = true;
	return $allcaps;
} );

/** True for Inbox-only team members (not site admins). */
function aurix_team_is_member( $user = null ) {
	$user = $user ? $user : wp_get_current_user();
	return $user && $user->exists() && in_array( AURIX_TEAM_ROLE, (array) $user->roles, true ) && ! user_can( $user, 'manage_options' );
}

/* Team members get the Inbox and nothing else: after login they land in the
   Inbox app, wp-admin is closed to them, and there's no admin bar. */
add_filter( 'login_redirect', function ( $to, $requested, $user ) {
	return ( $user instanceof WP_User && aurix_team_is_member( $user ) ) ? home_url( '/inbox-app/' ) : $to;
}, 20, 3 );
add_filter( 'woocommerce_login_redirect', function ( $to, $user ) {
	return aurix_team_is_member( $user ) ? home_url( '/inbox-app/' ) : $to;
}, 20, 2 );
add_filter( 'show_admin_bar', function ( $show ) {
	return aurix_team_is_member() ? false : $show;
} );
add_action( 'admin_init', function () {
	if ( wp_doing_ajax() || ! aurix_team_is_member() ) return;
	wp_safe_redirect( home_url( '/inbox-app/' ) );
	exit;
}, 1 );
/* Accepted phones stay signed in for 60 days. */
add_filter( 'auth_cookie_expiration', function ( $ttl, $user_id, $remember ) {
	$u = get_userdata( $user_id );
	return ( $remember && $u && aurix_team_is_member( $u ) ) ? 60 * DAY_IN_SECONDS : $ttl;
}, 10, 3 );

/* ── Personal sign-in links (QR) ────────────────────────────────────────── */
function aurix_team_make_link( $user_id ) {
	$token = wp_generate_password( 40, false, false );
	update_user_meta( $user_id, '_aurix_inbox_key', hash( 'sha256', $token ) );
	update_user_meta( $user_id, '_aurix_inbox_key_exp', time() + AURIX_TEAM_LINK_TTL );
	return add_query_arg( 'key', $user_id . '.' . $token, home_url( '/inbox-app/' ) );
}

/** Called by the app before the login check. Signs the person in if the link is valid. */
function aurix_team_maybe_login_with_key() {
	if ( empty( $_GET['key'] ) ) return;
	$raw = sanitize_text_field( wp_unslash( $_GET['key'] ) );
	list( $uid, $token ) = array_pad( explode( '.', $raw, 2 ), 2, '' );
	$uid  = absint( $uid );
	$user = $uid ? get_userdata( $uid ) : null;
	$hash = $user ? (string) get_user_meta( $uid, '_aurix_inbox_key', true ) : '';
	$exp  = $user ? (int) get_user_meta( $uid, '_aurix_inbox_key_exp', true ) : 0;

	$ok = $user && $hash && $token && hash_equals( $hash, hash( 'sha256', $token ) ) && $exp > time() && aurix_team_is_member( $user );
	if ( ! $ok ) {
		aurix_team_link_error_page();
	}

	// One-time link: burn it, then sign in for 14 days ("remember me").
	delete_user_meta( $uid, '_aurix_inbox_key' );
	delete_user_meta( $uid, '_aurix_inbox_key_exp' );
	wp_set_current_user( $uid );
	wp_set_auth_cookie( $uid, true, is_ssl() );
	update_user_meta( $uid, '_aurix_inbox_joined', current_time( 'mysql' ) );
	wp_safe_redirect( add_query_arg( 'm', rawurlencode( 'Welcome, ' . $user->display_name . '! Add this app to your home screen.' ), home_url( '/inbox-app/' ) ) );
	exit;
}

function aurix_team_link_error_page() {
	status_header( 403 );
	nocache_headers();
	?><!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Link expired — Aurix Inbox</title>
	<style>body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#0d1b2a;font:16px/1.5 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;color:#0d1b2a;padding:20px;box-sizing:border-box}
	.b{background:#fff;border-radius:18px;padding:28px 24px;max-width:380px;text-align:center}h1{font-size:21px;margin:0 0 8px}p{color:#6b7280;margin:0 0 18px}a{display:block;background:#0d1b2a;color:#fff;text-decoration:none;padding:13px;border-radius:12px;font-weight:700}</style></head>
	<body><div class="b"><h1>This link has expired</h1><p>Sign-in QR codes work once and expire after 48 hours. Ask your admin for a new one, or log in with your email and password.</p>
	<a href="<?php echo esc_url( wp_login_url( home_url( '/inbox-app/' ) ) ); ?>">Log in</a></div></body></html>
	<?php
	exit;
}

/** Track when a member last opened the Inbox (at most once every 10 minutes). */
function aurix_team_touch() {
	$uid  = get_current_user_id();
	$last = (int) get_user_meta( $uid, '_aurix_inbox_seen', true );
	if ( time() - $last > 600 ) update_user_meta( $uid, '_aurix_inbox_seen', time() );
}

/* ── Admin: add / remove / new link ─────────────────────────────────────── */
function aurix_team_members() {
	return get_users( array( 'role' => AURIX_TEAM_ROLE, 'orderby' => 'display_name' ) );
}

add_action( 'admin_init', function () {
	if ( ! in_array( $_REQUEST['page'] ?? '', array( 'aurix-inbox-settings', 'aurix-inbox' ), true ) || 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['ax_team'] ) ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	check_admin_referer( 'aurix_team' );

	$do   = sanitize_key( $_POST['ax_team'] );
	$back = 'aurix-inbox' === $_REQUEST['page'] ? admin_url( 'admin.php?page=aurix-inbox' ) : admin_url( 'admin.php?page=aurix-inbox-settings#team' );
	$msg  = '';

	if ( 'accept' === $do || 'decline' === $do ) {
		$msg = aurix_team_decide( sanitize_key( $_POST['req'] ?? '' ), 'accept' === $do );
	}

	if ( 'add' === $do ) {
		$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		if ( ! is_email( $email ) ) {
			$msg = 'Please enter a valid email address.';
		} else {
			$user = aurix_team_grant( $email, $name );
			if ( is_wp_error( $user ) ) {
				$msg = $user->get_error_message();
			} else {
				aurix_team_stash_link( $user->ID );
				$msg = ( $user->display_name ?: $email ) . ' now has Inbox access. Let them scan the QR code below.';
			}
		}
	}

	$uid  = absint( $_POST['user_id'] ?? 0 );
	$user = $uid ? get_userdata( $uid ) : null;
	if ( $user && in_array( AURIX_TEAM_ROLE, (array) $user->roles, true ) ) {
		if ( 'link' === $do ) {
			aurix_team_stash_link( $uid );
			$msg = 'New sign-in QR code created for ' . $user->display_name . '. Older codes no longer work.';
		} elseif ( 'email' === $do ) {
			$url  = aurix_team_make_link( $uid );
			$site = get_bloginfo( 'name' );
			$sent = wp_mail(
				$user->user_email,
				'Your access to the ' . $site . ' Inbox',
				'Hello ' . $user->display_name . ",\n\nYou now have access to the " . $site . " Inbox, where you can read and answer customer messages.\n\nOpen this link on your phone to sign in (works once, valid for 48 hours):\n" . $url
				. "\n\nAfter it opens, add it to your home screen:\n• iPhone: Share → Add to Home Screen\n• Android: ⋮ → Install app\n\n" . $site,
				array( 'Content-Type: text/plain; charset=UTF-8' )
			);
			$msg = $sent ? 'Sign-in link emailed to ' . $user->user_email . '.' : 'Email could not be sent — check GoSMTP.';
		} elseif ( 'remove' === $do ) {
			$user->remove_role( AURIX_TEAM_ROLE );
			if ( ! $user->roles ) $user->set_role( get_role( 'customer' ) ? 'customer' : 'subscriber' );
			delete_user_meta( $uid, '_aurix_inbox_key' );
			delete_user_meta( $uid, '_aurix_inbox_key_exp' );
			WP_Session_Tokens::get_instance( $uid )->destroy_all(); // signed out everywhere
			$msg = $user->display_name . ' no longer has Inbox access and has been signed out on all devices.';
		}
	}

	set_transient( 'aurix_team_msg_' . get_current_user_id(), $msg, 300 );
	wp_safe_redirect( $back );
	exit;
} );

/**
 * Give a person Inbox-only access: creates an "Inbox Team" account, or adds the
 * role to an existing (customer) account. Returns WP_User or WP_Error.
 */
function aurix_team_grant( $email, $name = '', $phone = '' ) {
	$user = get_user_by( 'email', $email );
	if ( $user && user_can( $user, 'manage_options' ) ) {
		return new WP_Error( 'admin', $email . ' is a site admin and already has full Inbox access.' );
	}
	if ( ! $user ) {
		$login = sanitize_user( strtok( $email, '@' ), true );
		$base  = $login ?: 'inbox';
		$i     = 1;
		while ( username_exists( $login ) ) $login = $base . ( ++$i );
		$parts = explode( ' ', $name, 2 );
		$uid   = wp_insert_user( array(
			'user_login'   => $login,
			'user_email'   => $email,
			'user_pass'    => wp_generate_password( 24 ),
			'display_name' => $name ?: $login,
			'first_name'   => $parts[0] ?? '',
			'last_name'    => $parts[1] ?? '',
			'role'         => AURIX_TEAM_ROLE,
		) );
		if ( is_wp_error( $uid ) ) return new WP_Error( 'create', 'Could not add this person: ' . $uid->get_error_message() );
		$user = get_userdata( $uid );
	} elseif ( ! in_array( AURIX_TEAM_ROLE, (array) $user->roles, true ) ) {
		$user->add_role( AURIX_TEAM_ROLE ); // existing customer account keeps its other role
	}
	if ( $phone && ! get_user_meta( $user->ID, 'billing_phone', true ) ) update_user_meta( $user->ID, 'billing_phone', $phone );
	if ( ! get_user_meta( $user->ID, '_aurix_inbox_added', true ) ) update_user_meta( $user->ID, '_aurix_inbox_added', current_time( 'mysql' ) );
	return $user;
}

/* ── Access requests (someone scans the Inbox QR) ───────────────────────── */
function aurix_team_requests() {
	$r = get_option( 'aurix_team_requests', array() );
	return is_array( $r ) ? $r : array();
}
function aurix_team_save_requests( $r ) {
	// Forget decided/old requests after 7 days.
	foreach ( $r as $id => $q ) {
		if ( 'pending' !== $q['state'] && time() - (int) ( $q['decided'] ?? $q['time'] ) > 7 * DAY_IN_SECONDS ) unset( $r[ $id ] );
	}
	update_option( 'aurix_team_requests', $r, false );
}
function aurix_team_pending() {
	return array_filter( aurix_team_requests(), function ( $q ) { return 'pending' === $q['state']; } );
}

/** Visitor submits the request form on /inbox-app/. Returns error text or ''. */
function aurix_team_create_request() {
	if ( ! empty( $_POST['website_url'] ) ) return ''; // honeypot
	$ip_key = 'aurix_req_rl_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$count  = (int) get_transient( $ip_key );
	if ( $count >= 5 ) return 'Too many requests. Please try again later.';

	$name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$note  = sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) );
	if ( ! $name || ! is_email( $email ) ) return 'Please enter your name and a valid email address.';

	$reqs = aurix_team_requests();
	// One open request per email — replace the older one.
	foreach ( $reqs as $id => $q ) {
		if ( 'pending' === $q['state'] && strtolower( $q['email'] ) === strtolower( $email ) ) unset( $reqs[ $id ] );
	}
	$id    = strtolower( wp_generate_password( 12, false, false ) );
	$token = wp_generate_password( 40, false, false );
	$reqs[ $id ] = array(
		'id' => $id, 'name' => mb_substr( $name, 0, 100 ), 'email' => $email, 'phone' => mb_substr( $phone, 0, 40 ), 'note' => mb_substr( $note, 0, 300 ),
		'time' => time(), 'ip' => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
		'device' => mb_substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ), 0, 200 ),
		'token' => hash( 'sha256', $token ), 'state' => 'pending',
	);
	aurix_team_save_requests( $reqs );
	set_transient( $ip_key, $count + 1, HOUR_IN_SECONDS );
	setcookie( 'aurix_inbox_req', $id . '.' . $token, array( 'expires' => time() + 7 * DAY_IN_SECONDS, 'path' => '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax' ) );
	$_COOKIE['aurix_inbox_req'] = $id . '.' . $token;

	// Tell the admins (phone notification + email).
	if ( function_exists( 'aurix_push_queue' ) ) {
		aurix_push_queue( array(
			'title'       => 'Access request · ' . $name,
			'body'        => $name . ' scanned the Inbox QR code and asks for access. Tap to accept or decline.',
			'url'         => home_url( '/inbox-app/?view=team' ),
			'tag'         => 'req-' . $id,
			'admins_only' => true,
		) );
	}
	$to = get_option( 'aurix_inbox_notify_email' ) ?: get_option( 'admin_email' );
	$GLOBALS['aurix_inbox_sending'] = true;
	wp_mail( $to, 'Inbox access request: ' . $name,
		$name . ' (' . $email . ( $phone ? ', ' . $phone : '' ) . ") scanned the Inbox QR code and is asking for access.
"
		. ( $note ? "
Note: " . $note . "
" : '' )
		. "
Accept or decline: " . admin_url( 'admin.php?page=aurix-inbox' ),
		array( 'Content-Type: text/plain; charset=UTF-8' ) );
	$GLOBALS['aurix_inbox_sending'] = false;
	return '';
}

/** The request belonging to this phone (from its cookie), if the token matches. */
function aurix_team_device_request() {
	if ( empty( $_COOKIE['aurix_inbox_req'] ) ) return null;
	list( $id, $token ) = array_pad( explode( '.', sanitize_text_field( wp_unslash( $_COOKIE['aurix_inbox_req'] ) ), 2 ), 2, '' );
	$reqs = aurix_team_requests();
	$q    = $reqs[ $id ] ?? null;
	return ( $q && $token && hash_equals( $q['token'], hash( 'sha256', $token ) ) ) ? $q : null;
}

/** Admin accepts / declines a request. Returns a message for the admin. */
function aurix_team_decide( $id, $accept ) {
	$reqs = aurix_team_requests();
	$q    = $reqs[ $id ] ?? null;
	if ( ! $q || 'pending' !== $q['state'] ) return 'This request was already handled.';
	if ( $accept ) {
		$user = aurix_team_grant( $q['email'], $q['name'], $q['phone'] );
		if ( is_wp_error( $user ) ) return $user->get_error_message();
		$reqs[ $id ]['state'] = 'approved';
		$reqs[ $id ]['uid']   = $user->ID;
		$msg = $q['name'] . ' now has Inbox access. Their phone opens the Inbox automatically.';
		// Back-up: email a sign-in link in case they closed the page.
		$url = aurix_team_make_link( $user->ID );
		wp_mail( $q['email'], 'Your Inbox access was approved — ' . get_bloginfo( 'name' ),
			'Hello ' . $q['name'] . ",

Your request for access to the " . get_bloginfo( 'name' ) . " Inbox was approved.

If the Inbox didn't open on your phone automatically, open this link on your phone (works once, valid for 48 hours):
" . $url . "

Then add it to your home screen:
• iPhone: Share → Add to Home Screen
• Android: ⋮ → Install app",
			array( 'Content-Type: text/plain; charset=UTF-8' ) );
	} else {
		$reqs[ $id ]['state'] = 'declined';
		$msg = 'Request from ' . $q['name'] . ' declined.';
	}
	$reqs[ $id ]['decided'] = time();
	$reqs[ $id ]['by']      = get_current_user_id();
	aurix_team_save_requests( $reqs );
	return $msg;
}

/** Called on /inbox-app/ for visitors without access: sign in an approved phone, else return its request (or null). */
function aurix_team_device_flow() {
	$q = aurix_team_device_request();
	if ( $q && 'approved' === $q['state'] && ! empty( $q['uid'] ) ) {
		$user = get_userdata( (int) $q['uid'] );
		if ( $user && aurix_team_is_member( $user ) ) {
			$reqs = aurix_team_requests();
			$reqs[ $q['id'] ]['state'] = 'claimed';
			aurix_team_save_requests( $reqs );
			setcookie( 'aurix_inbox_req', '', array( 'expires' => time() - 3600, 'path' => '/' ) );
			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID, true, is_ssl() );
			wp_safe_redirect( add_query_arg( 'm', rawurlencode( 'Welcome, ' . $user->display_name . '! Add this app to your home screen.' ), home_url( '/inbox-app/' ) ) );
			exit;
		}
	}
	return $q;
}

/** "N people asked for access" panel — Inbox dashboard and Team card. */
function aurix_team_requests_panel() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	$pending = aurix_team_pending();
	if ( ! $pending ) return;
	?>
	<div class="axi-card" style="margin-bottom:16px;border-color:#e8d5a3;background:#fffdf6"><div class="axi-sec">
		<h3 style="color:#734608"><span class="dashicons dashicons-admin-users"></span> <?php echo count( $pending ) === 1 ? '1 person is' : count( $pending ) . ' people are'; ?> asking for Inbox access</h3>
		<?php foreach ( $pending as $q ) : ?>
			<div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;padding:10px 0;border-top:1px solid #f1e7cf">
				<div style="flex:1;min-width:220px">
					<strong><?php echo esc_html( $q['name'] ); ?></strong> · <?php echo esc_html( $q['email'] ); ?><?php echo $q['phone'] ? ' · ' . esc_html( $q['phone'] ) : ''; ?>
					<?php if ( $q['note'] ) : ?><br><span style="color:#475569">“<?php echo esc_html( $q['note'] ); ?>”</span><?php endif; ?>
					<br><span class="description"><?php echo esc_html( human_time_diff( $q['time'] ) . ' ago' ); ?> · <?php echo esc_html( aurix_team_device_label( $q['device'] ) ); ?><?php echo get_user_by( 'email', $q['email'] ) ? ' · has an account on this site' : ''; ?></span>
				</div>
				<form method="post" style="display:flex;gap:6px">
					<?php wp_nonce_field( 'aurix_team' ); ?>
					<input type="hidden" name="req" value="<?php echo esc_attr( $q['id'] ); ?>">
					<button class="button button-primary" name="ax_team" value="accept">Accept</button>
					<button class="button" name="ax_team" value="decline">Decline</button>
				</form>
			</div>
		<?php endforeach; ?>
		<p class="description" style="margin:8px 0 0">Accepted people can only use the Inbox — they can't open WordPress admin, orders, products, pages or settings.</p>
	</div></div>
	<?php
}

function aurix_team_device_label( $ua ) {
	foreach ( array( 'iPhone' => 'iPhone', 'iPad' => 'iPad', 'Android' => 'Android phone', 'Windows' => 'Windows PC', 'Macintosh' => 'Mac' ) as $k => $v ) {
		if ( false !== stripos( (string) $ua, $k ) ) return $v;
	}
	return 'Unknown device';
}

/** Keep the freshly made link for this admin's next page view only (never in the URL). */
function aurix_team_stash_link( $uid ) {
	set_transient( 'aurix_team_link_' . get_current_user_id(), array( 'uid' => $uid, 'url' => aurix_team_make_link( $uid ) ), 15 * MINUTE_IN_SECONDS );
}

/** "Team access" card on Inbox → Settings & Import. */
function aurix_team_card() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	$me    = get_current_user_id();
	$msg   = get_transient( 'aurix_team_msg_' . $me );
	$fresh = get_transient( 'aurix_team_link_' . $me );
	delete_transient( 'aurix_team_msg_' . $me );
	delete_transient( 'aurix_team_link_' . $me );
	$members = aurix_team_members();
	$fresh_user = $fresh ? get_userdata( $fresh['uid'] ) : null;
	?>
	<div class="axi-card" id="team" style="max-width:900px;margin-bottom:18px"><div class="axi-sec">
		<h3>Team access</h3>
		<p style="margin:0 0 12px">Staff can only use the Inbox — read, reply, call and WhatsApp customers. They can't open WordPress admin, orders, products, pages or settings. Two ways to add someone: they scan the Inbox QR code and you <strong>Accept</strong> their request, or you add them here and they scan their personal QR code.</p>

		<?php if ( $msg ) : ?><div class="notice notice-info inline" style="margin:0 0 14px"><p><?php echo esc_html( $msg ); ?></p></div><?php endif; ?>
		<?php aurix_team_requests_panel(); ?>

		<?php if ( $fresh && $fresh_user ) : ?>
		<div style="display:flex;gap:18px;align-items:center;flex-wrap:wrap;background:#fdf8ed;border:1px solid #e8d5a3;border-radius:12px;padding:16px;margin-bottom:16px">
			<div id="axi-team-qr" role="img" aria-label="Sign-in QR code for <?php echo esc_attr( $fresh_user->display_name ); ?>" style="width:168px;height:168px;padding:4px;background:#fff;border-radius:8px"></div>
			<div style="flex:1;min-width:240px">
				<p style="font-size:16px;font-weight:700;margin:0 0 4px;color:#0d1b2a">Sign-in code for <?php echo esc_html( $fresh_user->display_name ); ?></p>
				<p style="margin:0 0 10px;color:#64748b">Ask them to scan this with their phone camera. It signs them in to the Inbox app and works <strong>once</strong>, for 48 hours. This code is shown only now — create a new one any time.</p>
				<input type="text" readonly class="large-text" id="axi-team-url" value="<?php echo esc_attr( $fresh['url'] ); ?>" onclick="this.select()" style="margin-bottom:8px">
				<button type="button" class="button" onclick="navigator.clipboard&&navigator.clipboard.writeText(document.getElementById('axi-team-url').value).then(function(){this.textContent='Copied';}.bind(this))">Copy link</button>
				<a class="button" target="_blank" rel="noopener" href="https://wa.me/?text=<?php echo rawurlencode( 'Your Aurix Inbox sign-in link (open on your phone, works once): ' . $fresh['url'] ); ?>">Send via WhatsApp</a>
			</div>
		</div>
		<script src="<?php echo esc_url( get_template_directory_uri() . '/js/qrcode.min.js' ); ?>"></script>
		<script>if(window.aurixQR){aurixQR(document.getElementById('axi-team-qr'),<?php echo wp_json_encode( $fresh['url'] ); ?>,160);}</script>
		<?php endif; ?>

		<form method="post" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:16px">
			<?php wp_nonce_field( 'aurix_team' ); ?>
			<input type="hidden" name="ax_team" value="add">
			<input type="text" name="name" placeholder="Full name" class="regular-text" style="max-width:220px">
			<input type="email" name="email" required placeholder="Email address" class="regular-text" style="max-width:260px">
			<button class="button button-primary">Give Inbox access</button>
		</form>

		<?php if ( $members ) : ?>
		<table class="widefat striped">
			<thead><tr><th>Team member</th><th>Added</th><th>Last opened Inbox</th><th style="text-align:right">Actions</th></tr></thead>
			<tbody>
			<?php foreach ( $members as $u ) :
				$seen = (int) get_user_meta( $u->ID, '_aurix_inbox_seen', true );
				$add  = (string) get_user_meta( $u->ID, '_aurix_inbox_added', true ); ?>
				<tr>
					<td><strong><?php echo esc_html( $u->display_name ); ?></strong><br><span class="description"><?php echo esc_html( $u->user_email ); ?></span></td>
					<td><?php echo $add ? esc_html( aurix_inbox_when( $add ) ) : '—'; ?></td>
					<td><?php echo $seen ? esc_html( human_time_diff( $seen ) . ' ago' ) : 'Not yet'; ?></td>
					<td style="text-align:right;white-space:nowrap">
						<form method="post" style="display:inline">
							<?php wp_nonce_field( 'aurix_team' ); ?>
							<input type="hidden" name="user_id" value="<?php echo (int) $u->ID; ?>">
							<button class="button button-small" name="ax_team" value="link">New QR code</button>
							<button class="button button-small" name="ax_team" value="email">Email link</button>
							<button class="button button-small" name="ax_team" value="remove" style="color:#b32d2e" onclick="return confirm('Remove Inbox access for <?php echo esc_js( $u->display_name ); ?>? They will be signed out on all devices.')">Remove</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php else : ?>
			<p class="description">No team members yet. Site admins always have access.</p>
		<?php endif; ?>
	</div></div>
	<?php
}
