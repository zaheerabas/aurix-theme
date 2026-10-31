<?php
/**
 * AURIX INBOX — MOBILE APP  (https://your-site.com/inbox-app/)
 *
 * Phone-first Inbox that installs to the home screen like a normal app:
 *  - Message list with date groups, filters, search, swipe to archive / done,
 *    pull to refresh, bottom tab bar.
 *  - Message screen with a sticky Call / WhatsApp / Reply / Done bar and a
 *    reply sheet with quick replies.
 *  - "Install app" (hidden once installed) and push notifications for new
 *    messages and access requests (see inc/inbox-push.php).
 *  - People without access see "Request access" (see inc/inbox-team.php).
 *
 * @package Aurix
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Which app route (if any) this request is for. */
function aurix_app_route() {
	$path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );
	$base = rtrim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' ) . '/inbox-app';
	if ( 0 !== strpos( $path, $base ) ) return '';
	$rest = trim( substr( $path, strlen( $base ) ), '/' );
	if ( '' === $rest ) return 'app';
	$map = array(
		'manifest' => 'manifest', 'icon' => 'icon', 'icon-192' => 'icon192', 'icon-512' => 'icon512',
		'icon-180' => 'icon180', 'badge' => 'badge', 'sw' => 'sw', 'count' => 'count', 'status' => 'status',
		'latest' => 'latest', 'act' => 'act', 'push' => 'push',
	);
	return $map[ $rest ] ?? '';
}

function aurix_app_url( $args = array() ) {
	return add_query_arg( $args, home_url( '/inbox-app/' ) );
}

add_action( 'init', function () {
	$route = aurix_app_route();
	if ( ! $route ) return;

	if ( ! defined( 'DONOTCACHEPAGE' ) ) define( 'DONOTCACHEPAGE', true ); // SpeedyCache / LiteSpeed: never cache
	nocache_headers();

	/* Public assets (no login needed). */
	if ( 'icon' === $route ) {
		header( 'Content-Type: image/svg+xml' );
		header( 'Cache-Control: public, max-age=604800' );
		echo aurix_app_icon_svg(); // phpcs:ignore
		exit;
	}
	if ( in_array( $route, array( 'icon180', 'icon192', 'icon512', 'badge' ), true ) ) {
		$sizes = array( 'icon180' => 180, 'icon192' => 192, 'icon512' => 512, 'badge' => 96 );
		aurix_push_icon_png( $sizes[ $route ], 'badge' === $route );
	}
	if ( 'sw' === $route ) {
		aurix_push_sw_js();
	}
	if ( 'manifest' === $route ) {
		header( 'Content-Type: application/manifest+json' );
		echo wp_json_encode( array(
			'id'               => home_url( '/inbox-app/' ),
			'name'             => 'Aurix Inbox',
			'short_name'       => 'Inbox',
			'description'      => 'All Aurix International messages in one place',
			'start_url'        => home_url( '/inbox-app/' ),
			'scope'            => home_url( '/inbox-app/' ),
			'display'          => 'standalone',
			'orientation'      => 'portrait',
			'background_color' => '#0d1b2a',
			'theme_color'      => '#0d1b2a',
			'icons'            => array(
				array( 'src' => home_url( '/inbox-app/icon-192' ), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any' ),
				array( 'src' => home_url( '/inbox-app/icon-512' ), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any' ),
				array( 'src' => home_url( '/inbox-app/icon' ), 'sizes' => 'any', 'type' => 'image/svg+xml', 'purpose' => 'maskable' ),
			),
			'shortcuts'        => array(
				array( 'name' => 'Unread messages', 'url' => home_url( '/inbox-app/?f=unread' ) ),
			),
		) );
		exit;
	}

	// Personal QR / link from an admin signs a team member straight in.
	if ( function_exists( 'aurix_team_maybe_login_with_key' ) ) aurix_team_maybe_login_with_key();

	// No Inbox access yet: the QR code leads to an "ask for access" screen.
	if ( ! current_user_can( 'aurix_inbox' ) ) {
		if ( 'status' === $route ) {
			$q = aurix_team_device_request();
			wp_send_json( array( 'state' => $q ? $q['state'] : 'none' ) );
		}
		if ( 'app' !== $route ) {
			wp_send_json( array( 'error' => 'signed_out' ), 401 );
		}
		$error = '';
		if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && 'request' === ( $_POST['do'] ?? '' ) ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'aurix_req' ) ) {
				$error = 'Please try again.';
			} else {
				$error = aurix_team_create_request();
				if ( ! $error ) {
					wp_safe_redirect( home_url( '/inbox-app/' ) );
					exit;
				}
			}
		}
		if ( ! empty( $_GET['new'] ) ) { // start over on this phone
			setcookie( 'aurix_inbox_req', '', array( 'expires' => time() - 3600, 'path' => '/' ) );
			unset( $_COOKIE['aurix_inbox_req'] );
		}
		$q = aurix_team_device_flow(); // signs the phone in and exits if the request was accepted
		aurix_app_request_page( $q, $error );
		exit;
	}
	if ( function_exists( 'aurix_team_touch' ) ) aurix_team_touch();

	global $wpdb;
	if ( 'count' === $route ) {
		delete_transient( 'aurix_inbox_unread' );
		wp_send_json( array(
			'unread'   => aurix_inbox_unread_count(),
			'latest'   => (int) $wpdb->get_var( 'SELECT MAX(id) FROM ' . aurix_inbox_table() ),
			'requests' => current_user_can( 'manage_options' ) && function_exists( 'aurix_team_pending' ) ? count( aurix_team_pending() ) : 0,
		) );
	}
	if ( 'latest' === $route ) {
		$item = function_exists( 'aurix_push_latest_for' ) ? aurix_push_latest_for( get_current_user_id() ) : array();
		delete_transient( 'aurix_inbox_unread' );
		$item['unread'] = aurix_inbox_unread_count();
		unset( $item['from'], $item['admins_only'] );
		wp_send_json( $item );
	}
	if ( 'act' === $route ) {
		aurix_app_ajax_action();
	}
	if ( 'push' === $route ) {
		aurix_app_ajax_push();
	}

	if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		aurix_app_handle_post();
	}
	aurix_app_render();
	exit;
}, 1 );

/* ── Form posts (reply, note, status) ─────────────────────────────────── */
function aurix_app_handle_post() {
	check_admin_referer( 'aurix_app' );
	$id   = absint( $_POST['id'] ?? 0 );
	$do   = sanitize_key( $_POST['do'] ?? '' );
	$row  = $id ? aurix_inbox_get( $id ) : null;
	$back = aurix_app_url( array( 'id' => $id ) );
	$msg  = '';

	if ( $row ) {
		if ( 'reply' === $do ) {
			$text = sanitize_textarea_field( wp_unslash( $_POST['reply'] ?? '' ) );
			$msg  = aurix_inbox_send_reply( $row, sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) ), $text ) ? 'Reply sent' : 'Reply could not be sent — check GoSMTP';
		} elseif ( 'note' === $do ) {
			$text = sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) );
			if ( $text ) { aurix_inbox_add_note( $id, 'note', $text ); $msg = 'Note saved'; }
		} elseif ( isset( aurix_inbox_statuses()[ $do ] ) ) {
			aurix_inbox_set_status( $id, $do );
			$labels = array( 'new' => 'Marked unread', 'read' => 'Moved back to Inbox', 'replied' => 'Marked as done', 'archived' => 'Archived', 'spam' => 'Moved to spam' );
			$msg    = $labels[ $do ];
			if ( in_array( $do, array( 'archived', 'spam', 'new' ), true ) ) $back = aurix_app_url();
		} elseif ( 'delete' === $do && current_user_can( 'manage_options' ) ) {
			aurix_inbox_delete( $id );
			$msg  = 'Deleted';
			$back = aurix_app_url();
		}
	}
	wp_safe_redirect( add_query_arg( 'm', rawurlencode( $msg ), $back ) );
	exit;
}

/* ── JSON actions (swipe, access requests) ────────────────────────────── */
function aurix_app_ajax_action() {
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'aurix_app' ) ) {
		wp_send_json( array( 'ok' => false, 'msg' => 'Please reload the app.' ), 403 );
	}
	$do  = sanitize_key( $_POST['do'] ?? '' );
	$msg = '';
	if ( in_array( $do, array( 'accept', 'decline' ), true ) ) {
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json( array( 'ok' => false, 'msg' => 'Only admins can do this.' ), 403 );
		$msg = aurix_team_decide( sanitize_key( $_POST['req'] ?? '' ), 'accept' === $do );
	} else {
		$id = absint( $_POST['id'] ?? 0 );
		if ( ! $id || ! isset( aurix_inbox_statuses()[ $do ] ) ) wp_send_json( array( 'ok' => false, 'msg' => 'Unknown action' ), 400 );
		aurix_inbox_set_status( $id, $do );
		$labels = array( 'new' => 'Marked unread', 'read' => 'Marked read', 'replied' => 'Marked as done', 'archived' => 'Archived', 'spam' => 'Moved to spam' );
		$msg    = $labels[ $do ];
	}
	delete_transient( 'aurix_inbox_unread' );
	wp_send_json( array( 'ok' => true, 'msg' => $msg, 'unread' => aurix_inbox_unread_count() ) );
}

/* ── Push: subscribe / unsubscribe / test / preference ────────────────── */
function aurix_app_ajax_push() {
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'aurix_app' ) ) {
		wp_send_json( array( 'ok' => false, 'msg' => 'Please reload the app.' ), 403 );
	}
	$uid      = get_current_user_id();
	$do       = sanitize_key( $_POST['do'] ?? '' );
	$endpoint = esc_url_raw( wp_unslash( $_POST['endpoint'] ?? '' ) );
	switch ( $do ) {
		case 'subscribe':
			$ok = aurix_push_subscribe( $uid, $endpoint, sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ) );
			wp_send_json( array( 'ok' => $ok ) );
		case 'unsubscribe':
			aurix_push_unsubscribe( $endpoint );
			wp_send_json( array( 'ok' => true ) );
		case 'pref':
			$p = sanitize_key( $_POST['pref'] ?? '' );
			if ( in_array( $p, array( 'all', 'important' ), true ) ) update_user_meta( $uid, '_aurix_push_pref', $p );
			wp_send_json( array( 'ok' => true ) );
		case 'test':
			$item = array( 'title' => 'Notifications are on ✓', 'body' => 'You will get a notification like this when a new message arrives.', 'url' => home_url( '/inbox-app/' ), 'tag' => 'test', 'time' => time(), 'id' => 'test' );
			$log  = (array) get_option( 'aurix_push_log', array() );
			$log[] = $item + array( 'only' => $uid );
			update_option( 'aurix_push_log', array_slice( $log, -20 ), false );
			$code = $endpoint ? aurix_push_send( $endpoint ) : 0;
			wp_send_json( array( 'ok' => $code >= 200 && $code < 300, 'code' => $code ) );
	}
	wp_send_json( array( 'ok' => false ), 400 );
}

/* ── Page shell ────────────────────────────────────────────────────────── */
function aurix_app_render() {
	$id   = absint( $_GET['id'] ?? 0 );
	$view = sanitize_key( $_GET['view'] ?? '' );
	$r    = $id ? aurix_inbox_get( $id ) : null;
	if ( $r && 'new' === $r['status'] ) { aurix_inbox_set_status( $id, 'read' ); $r['status'] = 'read'; }
	if ( 'team' === $view && ! current_user_can( 'manage_options' ) ) $view = '';
	$unread = aurix_inbox_unread_count();
	$title  = ( $unread ? '(' . $unread . ') ' : '' ) . 'Aurix Inbox';
	$screen = $r ? 'detail' : ( in_array( $view, array( 'more', 'team' ), true ) ? $view : 'list' );
	?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<meta name="theme-color" content="#0d1b2a">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Inbox">
<meta name="format-detection" content="telephone=no">
<link rel="manifest" href="<?php echo esc_url( home_url( '/inbox-app/manifest' ) ); ?>">
<link rel="icon" href="<?php echo esc_url( home_url( '/inbox-app/icon' ) ); ?>" type="image/svg+xml">
<link rel="apple-touch-icon" href="<?php echo esc_url( home_url( '/inbox-app/icon-180' ) ); ?>">
<title><?php echo esc_html( $title ); ?></title>
<style><?php aurix_app_css(); ?></style>
</head>
<body class="s-<?php echo esc_attr( $screen ); ?>">
<?php
	if ( 'detail' === $screen ) aurix_app_detail( $r );
	elseif ( 'more' === $screen ) aurix_app_more();
	elseif ( 'team' === $screen ) aurix_app_team();
	else aurix_app_list();
	if ( 'detail' !== $screen ) aurix_app_tabbar( $screen, $unread );
	if ( ! empty( $_GET['m'] ) ) echo '<div class="toast show" role="status">' . esc_html( sanitize_text_field( wp_unslash( $_GET['m'] ) ) ) . '</div>';
	else echo '<div class="toast" role="status" aria-live="polite"></div>';
	aurix_app_install_sheet();
	aurix_app_js( $unread, $screen );
	?>
</body>
</html>
<?php
}

function aurix_app_tabbar( $screen, $unread ) {
	$f    = sanitize_key( $_GET['f'] ?? '' );
	$reqs = current_user_can( 'manage_options' ) && function_exists( 'aurix_team_pending' ) ? count( aurix_team_pending() ) : 0;
	$tabs = array(
		array( 'inbox', 'Inbox', aurix_app_url(), 'list' === $screen && 'unread' !== $f, 0 ),
		array( 'dot', 'Unread', aurix_app_url( array( 'f' => 'unread' ) ), 'list' === $screen && 'unread' === $f, $unread ),
	);
	if ( current_user_can( 'manage_options' ) ) $tabs[] = array( 'users', 'Team', aurix_app_url( array( 'view' => 'team' ) ), 'team' === $screen, $reqs );
	$tabs[] = array( 'gear', 'Settings', aurix_app_url( array( 'view' => 'more' ) ), 'more' === $screen, 0 );
	echo '<nav class="tabbar" aria-label="App sections">';
	foreach ( $tabs as $t ) {
		printf(
			'<a href="%s" class="%s"%s>%s<span>%s</span>%s</a>',
			esc_url( $t[2] ), $t[3] ? 'on' : '', $t[3] ? ' aria-current="page"' : '',
			aurix_app_svg( $t[0] ), esc_html( $t[1] ),
			$t[4] ? '<b class="tb-badge" data-badge="' . esc_attr( $t[0] ) . '">' . ( $t[4] > 99 ? '99+' : (int) $t[4] ) . '</b>' : ( 'dot' === $t[0] ? '<b class="tb-badge" data-badge="dot" hidden></b>' : '' )
		); // phpcs:ignore
	}
	echo '</nav>';
}

/* ── Message list ─────────────────────────────────────────────────────── */
function aurix_app_list() {
	global $wpdb;
	$table = aurix_inbox_table();
	$types = aurix_inbox_types();
	$f     = sanitize_key( $_GET['f'] ?? '' );
	$s     = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
	$limit = 30 * max( 1, absint( $_GET['p'] ?? 1 ) );

	$where = array( '1=1' );
	if ( 'unread' === $f )                                                   $where[] = "status = 'new'";
	elseif ( in_array( $f, array( 'replied', 'archived', 'spam' ), true ) ) $where[] = $wpdb->prepare( 'status = %s', $f );
	else                                                                      $where[] = "status NOT IN ('archived','spam')";
	if ( isset( $types[ $f ] ) ) $where[] = $wpdb->prepare( 'type = %s', $f );
	if ( $s ) {
		$like    = '%' . $wpdb->esc_like( $s ) . '%';
		$where[] = $wpdb->prepare( '(name LIKE %s OR email LIKE %s OR company LIKE %s OR phone LIKE %s OR subject LIKE %s OR message LIKE %s)', $like, $like, $like, $like, $like, $like );
	}
	$w     = implode( ' AND ', $where );
	$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE $w" );
	$rows  = $wpdb->get_results( "SELECT * FROM $table WHERE $w ORDER BY created_at DESC, id DESC LIMIT $limit", ARRAY_A );

	$counts = array();
	foreach ( $wpdb->get_results( "SELECT type, COUNT(*) c, SUM(status='new') n FROM $table WHERE status NOT IN ('archived','spam') GROUP BY type", ARRAY_A ) as $c ) {
		$counts[ $c['type'] ] = $c;
	}
	$unread = aurix_inbox_unread_count();
	$user   = wp_get_current_user();
	$titles = array( 'unread' => 'Unread', 'replied' => 'Done', 'archived' => 'Archived', 'spam' => 'Spam' );
	$title  = $titles[ $f ] ?? ( isset( $types[ $f ] ) ? $types[ $f ]['label'] : 'Inbox' );
	$hour   = (int) current_time( 'G' );
	$hello  = $hour < 12 ? 'Good morning' : ( $hour < 18 ? 'Good afternoon' : 'Good evening' );
	?>
	<div id="ptr" class="ptr" aria-hidden="true"><span></span></div>
	<header class="top">
		<p class="hello"><?php echo esc_html( $hello . ', ' . ( $user->first_name ?: $user->display_name ) ); ?></p>
		<div class="top-row">
			<h1><?php echo esc_html( $title ); ?></h1>
			<button type="button" class="icon-btn" id="bell" aria-label="Notifications" hidden><?php echo aurix_app_svg( 'bell' ); // phpcs:ignore ?></button>
		</div>
		<p class="sub" id="sub"><?php echo $unread ? esc_html( $unread . ' unread' ) : 'You\'re all caught up'; ?></p>
		<form class="search" method="get" action="<?php echo esc_url( aurix_app_url() ); ?>" role="search">
			<?php if ( $f ) : ?><input type="hidden" name="f" value="<?php echo esc_attr( $f ); ?>"><?php endif; ?>
			<?php echo aurix_app_svg( 'search' ); // phpcs:ignore ?>
			<input type="search" name="s" value="<?php echo esc_attr( $s ); ?>" placeholder="Search people, companies, messages" aria-label="Search messages" enterkeyhint="search">
			<?php if ( $s ) : ?><a class="clear" href="<?php echo esc_url( aurix_app_url( $f ? array( 'f' => $f ) : array() ) ); ?>" aria-label="Clear search">×</a><?php endif; ?>
		</form>
	</header>

	<nav class="chips" aria-label="Filter by type">
		<a class="chip <?php echo '' === $f || 'unread' === $f ? 'on' : ''; ?>" href="<?php echo esc_url( aurix_app_url( 'unread' === $f ? array( 'f' => 'unread' ) : array() ) ); ?>">All types</a>
		<?php foreach ( $types as $k => $t ) :
			$c = (int) ( $counts[ $k ]['c'] ?? 0 );
			$n = (int) ( $counts[ $k ]['n'] ?? 0 );
			if ( ! $c && $f !== $k ) continue; ?>
			<a class="chip <?php echo $f === $k ? 'on' : ''; ?>" style="--c:<?php echo esc_attr( $t['color'] ); ?>" href="<?php echo esc_url( aurix_app_url( array( 'f' => $k ) ) ); ?>"><i></i><?php echo esc_html( $t['label'] ); ?><?php if ( $n ) echo ' <b>' . (int) $n . '</b>'; ?></a>
		<?php endforeach; ?>
		<a class="chip <?php echo 'replied' === $f ? 'on' : ''; ?>" href="<?php echo esc_url( aurix_app_url( array( 'f' => 'replied' ) ) ); ?>">✓ Done</a>
		<a class="chip <?php echo 'archived' === $f ? 'on' : ''; ?>" href="<?php echo esc_url( aurix_app_url( array( 'f' => 'archived' ) ) ); ?>">Archived</a>
	</nav>

	<div id="newbar" class="newbar" hidden><a href="<?php echo esc_url( aurix_app_url( $f ? array( 'f' => $f ) : array() ) ); ?>">↑ New messages</a></div>

	<main class="list" id="list">
		<?php aurix_app_prompts(); ?>
		<?php if ( current_user_can( 'manage_options' ) && function_exists( 'aurix_team_pending' ) && ( $pending = aurix_team_pending() ) && '' === $f && ! $s ) : ?>
			<a class="req-banner" href="<?php echo esc_url( aurix_app_url( array( 'view' => 'team' ) ) ); ?>">
				<?php echo aurix_app_svg( 'users' ); // phpcs:ignore ?>
				<span><strong><?php echo count( $pending ) === 1 ? esc_html( reset( $pending )['name'] ) . ' wants access' : count( $pending ) . ' people want access'; ?></strong><em>Tap to accept or decline</em></span>
				<?php echo aurix_app_svg( 'chev' ); // phpcs:ignore ?>
			</a>
		<?php endif; ?>

		<?php if ( ! $rows ) : ?>
			<div class="empty">
				<div class="empty-ic"><?php echo aurix_app_svg( $s ? 'search' : 'check' ); // phpcs:ignore ?></div>
				<h2><?php echo $s ? 'No results' : ( 'unread' === $f ? 'No unread messages' : 'Nothing here' ); ?></h2>
				<p><?php echo $s ? 'Try a different name, email or word.' : 'New messages appear here as soon as they arrive.'; ?></p>
			</div>
		<?php endif; ?>

		<?php
		$group = '';
		$swipe = ! in_array( $f, array( 'archived', 'spam' ), true );
		foreach ( $rows as $row ) :
			$g = aurix_app_day_group( $row['created_at'] );
			if ( $g !== $group ) { $group = $g; echo '<h3 class="day">' . esc_html( $g ) . '</h3>'; }
			$t   = aurix_inbox_type( $row['type'] );
			$who = $row['name'] ?: ( $row['company'] ?: ( $row['email'] ?: 'Unknown sender' ) );
			$sub = $row['company'] && $row['name'] ? $row['company'] : ( $row['country'] ?: '' ); ?>
			<div class="swipe" data-id="<?php echo (int) $row['id']; ?>" data-swipe="<?php echo $swipe ? '1' : '0'; ?>">
				<div class="swipe-bg"><span class="l"><?php echo aurix_app_svg( 'check' ); // phpcs:ignore ?>Done</span><span class="r">Archive<?php echo aurix_app_svg( 'archive' ); // phpcs:ignore ?></span></div>
				<a class="item <?php echo 'new' === $row['status'] ? 'unread' : ''; ?>" href="<?php echo esc_url( aurix_app_url( array( 'id' => $row['id'] ) ) ); ?>">
					<span class="avatar" style="--c:<?php echo esc_attr( $t['color'] ); ?>"><?php echo esc_html( aurix_app_initials( $who ) ); ?></span>
					<span class="body">
						<span class="line1"><span class="who"><?php echo esc_html( $who ); ?></span><span class="when"><?php echo esc_html( aurix_app_time( $row['created_at'] ) ); ?></span></span>
						<span class="tag"><i style="background:<?php echo esc_attr( $t['color'] ); ?>"></i><?php echo esc_html( $t['label'] ); ?><?php if ( $sub ) echo '<em> · ' . esc_html( $sub ) . '</em>'; ?></span>
						<span class="what"><?php echo esc_html( aurix_inbox_summary( $row ) ); ?></span>
					</span>
					<?php if ( 'replied' === $row['status'] ) : ?><span class="done" aria-label="Done"><?php echo aurix_app_svg( 'check' ); // phpcs:ignore ?></span><?php endif; ?>
				</a>
			</div>
		<?php endforeach; ?>
		<?php if ( $total > count( $rows ) ) : ?>
			<a class="more" href="<?php echo esc_url( add_query_arg( 'p', absint( $_GET['p'] ?? 1 ) + 1 ) ); ?>">Show more · <?php echo (int) ( $total - count( $rows ) ); ?> older</a>
		<?php endif; ?>
		<?php if ( $rows && $swipe ) : ?><p class="hint">Tip: swipe a message right for <b>Done</b>, left to <b>Archive</b>.</p><?php endif; ?>
	</main>
	<?php
}

/** Install + notifications prompt cards (shown by JS only when useful). */
function aurix_app_prompts() { ?>
	<div class="prompt" id="p-install" hidden>
		<div class="p-ic"><?php echo aurix_app_svg( 'phone-add' ); // phpcs:ignore ?></div>
		<div class="p-tx"><strong>Install the Inbox app</strong><span>One tap from your home screen, full screen, with notifications.</span></div>
		<button type="button" class="p-btn" data-install>Install</button>
		<button type="button" class="p-x" data-dismiss="install" aria-label="Not now">×</button>
	</div>
	<div class="prompt" id="p-notify" hidden>
		<div class="p-ic gold"><?php echo aurix_app_svg( 'bell' ); // phpcs:ignore ?></div>
		<div class="p-tx"><strong>Get notified of new messages</strong><span>Like WhatsApp — even when the app is closed.</span></div>
		<button type="button" class="p-btn" data-notify-on>Turn on</button>
		<button type="button" class="p-x" data-dismiss="notify" aria-label="Not now">×</button>
	</div>
<?php }

function aurix_app_day_group( $mysql ) {
	$d     = gmdate( 'Y-m-d', strtotime( $mysql ) );
	$today = current_time( 'Y-m-d' );
	if ( $d === $today ) return 'Today';
	if ( $d === gmdate( 'Y-m-d', strtotime( $today . ' -1 day' ) ) ) return 'Yesterday';
	if ( strtotime( $d ) > strtotime( $today . ' -7 days' ) ) return 'This week';
	if ( gmdate( 'Y-m', strtotime( $d ) ) === current_time( 'Y-m' ) ) return 'This month';
	return date_i18n( 'F Y', strtotime( $d ) );
}

function aurix_app_time( $mysql ) {
	$ts  = strtotime( $mysql );
	$now = current_time( 'timestamp' );
	if ( $now - $ts < 60 ) return 'now';
	if ( $now - $ts < HOUR_IN_SECONDS ) return floor( ( $now - $ts ) / 60 ) . 'm';
	if ( gmdate( 'Y-m-d', $ts ) === gmdate( 'Y-m-d', $now ) ) return date_i18n( 'g:i a', $ts );
	if ( $now - $ts < 6 * DAY_IN_SECONDS ) return date_i18n( 'D', $ts );
	return date_i18n( gmdate( 'Y', $ts ) === gmdate( 'Y', $now ) ? 'M j' : 'M j, Y', $ts );
}

/* ── Message screen ───────────────────────────────────────────────────── */
function aurix_app_detail( $r ) {
	$t     = aurix_inbox_type( $r['type'] );
	$d     = $r['data'];
	$who   = $r['name'] ?: ( $r['company'] ?: ( $r['email'] ?: 'Unknown sender' ) );
	$tel   = preg_replace( '/[^\d+]/', '', $r['phone'] );
	$wa    = preg_replace( '/\D+/', '', $r['phone'] );
	$can_m = is_email( $r['email'] );
	$first = $r['name'] ? strtok( $r['name'], ' ' ) : '';
	$greet = 'Hello' . ( $first ? ' ' . $first : '' ) . ', thank you for contacting Aurix International. ';
	$ref   = wp_get_raw_referer();
	$back  = ( $ref && false !== strpos( $ref, '/inbox-app' ) && false === strpos( $ref, 'id=' ) ) ? $ref : aurix_app_url();
	?>
	<header class="bar">
		<a class="icon-btn ghost" href="<?php echo esc_url( $back ); ?>" aria-label="Back"><?php echo aurix_app_svg( 'back' ); // phpcs:ignore ?></a>
		<span class="bar-title"><?php echo esc_html( $t['label'] ); ?></span>
		<button type="button" class="icon-btn ghost" data-sheet="actions" aria-label="More actions"><?php echo aurix_app_svg( 'dots' ); // phpcs:ignore ?></button>
	</header>

	<main class="detail">
		<section class="hero">
			<span class="avatar xl" style="--c:<?php echo esc_attr( $t['color'] ); ?>"><?php echo esc_html( aurix_app_initials( $who ) ); ?></span>
			<h1><?php echo esc_html( $who ); ?></h1>
			<p class="from"><?php echo esc_html( implode( ' · ', array_filter( array( $r['name'] ? $r['company'] : '', $r['country'] ) ) ) ); ?></p>
			<span class="pill" style="--c:<?php echo esc_attr( $t['color'] ); ?>"><?php echo esc_html( $t['label'] ); ?></span>
			<?php if ( 'replied' === $r['status'] ) : ?><span class="pill ok">✓ Done</span><?php elseif ( 'archived' === $r['status'] ) : ?><span class="pill muted">Archived</span><?php endif; ?>
		</section>

		<section class="card want">
			<h2>What they want</h2>
			<p class="big"><?php echo esc_html( aurix_inbox_summary( $r ) ); ?></p>
			<p class="date"><?php echo esc_html( date_i18n( 'l, M j · g:i a', strtotime( $r['created_at'] ) ) ); ?></p>
		</section>

		<?php if ( $r['subject'] || $r['message'] ) : ?>
		<section class="card">
			<h2>Message</h2>
			<?php if ( $r['subject'] ) : ?><p class="subj"><?php echo esc_html( $r['subject'] ); ?></p><?php endif; ?>
			<?php if ( $r['message'] ) : ?><p class="msg"><?php echo esc_html( $r['message'] ); ?></p><?php endif; ?>
		</section>
		<?php endif; ?>

		<section class="card">
			<h2>Contact</h2>
			<ul class="rows">
				<?php if ( $r['email'] ) : ?><li><a href="mailto:<?php echo esc_attr( $r['email'] ); ?>"><?php echo aurix_app_svg( 'mail' ); // phpcs:ignore ?><span><small>Email</small><?php echo esc_html( $r['email'] ); ?></span></a><button type="button" class="copy" data-copy="<?php echo esc_attr( $r['email'] ); ?>" aria-label="Copy email"><?php echo aurix_app_svg( 'copy' ); // phpcs:ignore ?></button></li><?php endif; ?>
				<?php if ( $r['phone'] ) : ?><li><a href="tel:<?php echo esc_attr( $tel ); ?>"><?php echo aurix_app_svg( 'phone' ); // phpcs:ignore ?><span><small>Phone</small><?php echo esc_html( $r['phone'] ); ?></span></a><button type="button" class="copy" data-copy="<?php echo esc_attr( $r['phone'] ); ?>" aria-label="Copy phone"><?php echo aurix_app_svg( 'copy' ); // phpcs:ignore ?></button></li><?php endif; ?>
				<?php if ( $r['company'] ) : ?><li><span class="nolink"><?php echo aurix_app_svg( 'building' ); // phpcs:ignore ?><span><small>Company</small><?php echo esc_html( $r['company'] ); ?></span></span></li><?php endif; ?>
				<?php if ( $r['country'] ) : ?><li><span class="nolink"><?php echo aurix_app_svg( 'globe' ); // phpcs:ignore ?><span><small>Country</small><?php echo esc_html( $r['country'] ); ?></span></span></li><?php endif; ?>
				<?php if ( $r['source'] ) : ?><li><a href="<?php echo esc_url( $r['source'] ); ?>" target="_blank" rel="noopener"><?php echo aurix_app_svg( 'link' ); // phpcs:ignore ?><span><small>Sent from page</small><?php echo esc_html( wp_parse_url( $r['source'], PHP_URL_PATH ) ?: '/' ); ?></span></a></li><?php endif; ?>
			</ul>
		</section>

		<?php if ( $d ) : ?>
		<section class="card">
			<h2>Details</h2>
			<dl class="kv">
				<?php foreach ( $d as $k => $v ) : ?>
					<dt><?php echo esc_html( $k ); ?></dt>
					<dd><?php echo preg_match( '#^https?://#', $v ) ? '<a href="' . esc_url( $v ) . '" target="_blank" rel="noopener">Open link ↗</a>' : nl2br( esc_html( $v ) ); ?></dd>
				<?php endforeach; ?>
			</dl>
		</section>
		<?php endif; ?>

		<?php if ( $r['notes'] ) : ?>
		<section class="card">
			<h2>Conversation</h2>
			<div class="thread">
			<?php foreach ( $r['notes'] as $n ) : $is_reply = 'reply' === ( $n['kind'] ?? '' ); ?>
				<div class="bubble <?php echo $is_reply ? 'out' : 'note'; ?>">
					<?php echo nl2br( esc_html( $n['text'] ?? '' ) ); ?>
					<div class="meta"><?php echo $is_reply ? '✉ Emailed' : '🔒 Team note'; ?> · <?php echo esc_html( ( $n['by'] ?? '' ) . ' · ' . aurix_app_time( $n['at'] ?? '' ) ); ?></div>
				</div>
			<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>
	</main>

	<nav class="dock" aria-label="Quick actions">
		<?php if ( $tel ) : ?><a href="tel:<?php echo esc_attr( $tel ); ?>"><?php echo aurix_app_svg( 'phone' ); // phpcs:ignore ?><span>Call</span></a><?php endif; ?>
		<?php if ( strlen( $wa ) >= 8 ) : ?><a class="wa" href="https://wa.me/<?php echo esc_attr( $wa ); ?>?text=<?php echo rawurlencode( $greet ); ?>" target="_blank" rel="noopener"><?php echo aurix_app_svg( 'whatsapp' ); // phpcs:ignore ?><span>WhatsApp</span></a><?php endif; ?>
		<?php if ( $can_m ) : ?><button type="button" class="primary-dock" data-sheet="reply"><?php echo aurix_app_svg( 'reply' ); // phpcs:ignore ?><span>Reply</span></button><?php endif; ?>
		<button type="button" data-sheet="note"><?php echo aurix_app_svg( 'note' ); // phpcs:ignore ?><span>Note</span></button>
		<?php if ( 'replied' !== $r['status'] ) : ?>
		<form method="post"><?php wp_nonce_field( 'aurix_app' ); ?><input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>"><button name="do" value="replied" class="ok"><?php echo aurix_app_svg( 'check' ); // phpcs:ignore ?><span>Done</span></button></form>
		<?php endif; ?>
	</nav>

	<?php if ( $can_m ) : ?>
	<div class="sheet" id="sheet-reply" role="dialog" aria-modal="true" aria-label="Reply" hidden>
		<div class="sheet-panel">
			<div class="grab"></div>
			<div class="sheet-head"><strong>Reply to <?php echo esc_html( $first ?: $r['email'] ); ?></strong><button type="button" class="p-x" data-close aria-label="Close">×</button></div>
			<form method="post">
				<?php wp_nonce_field( 'aurix_app' ); ?>
				<input type="hidden" name="do" value="reply"><input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
				<input type="hidden" name="subject" value="<?php echo esc_attr( 'Re: ' . ( $r['subject'] ?: $t['label'] . ' — Aurix International' ) ); ?>">
				<p class="to">To <?php echo esc_html( $r['email'] ); ?> · sent with the Aurix email design</p>
				<div class="quick">
					<button type="button" data-text="Thank you for your interest. Our team is preparing the details and will send them to you within 24 hours.">⏱ Reply in 24h</button>
					<button type="button" data-text="Thank you! Could you please share your phone or WhatsApp number so our team can call you to discuss your requirements?">📞 Ask for phone</button>
					<button type="button" data-text="Thank you for your request. Please find our quotation below:&#10;&#10;">💲 Send quote</button>
					<button type="button" data-text="Thank you for reaching out. Could you share more details — quantities, sizes and delivery country — so we can help you faster?">❓ Ask details</button>
				</div>
				<textarea name="reply" rows="6" required placeholder="Write your reply…"></textarea>
				<button class="primary" type="submit">Send reply</button>
			</form>
		</div>
	</div>
	<?php endif; ?>

	<div class="sheet" id="sheet-note" role="dialog" aria-modal="true" aria-label="Team note" hidden>
		<div class="sheet-panel">
			<div class="grab"></div>
			<div class="sheet-head"><strong>Team note</strong><button type="button" class="p-x" data-close aria-label="Close">×</button></div>
			<form method="post">
				<?php wp_nonce_field( 'aurix_app' ); ?>
				<input type="hidden" name="do" value="note"><input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
				<p class="to">Only your team sees notes.</p>
				<textarea name="note" rows="4" required placeholder="e.g. Called them, sending samples Monday"></textarea>
				<button class="primary" type="submit">Save note</button>
			</form>
		</div>
	</div>

	<div class="sheet" id="sheet-actions" role="dialog" aria-modal="true" aria-label="More actions" hidden>
		<div class="sheet-panel">
			<div class="grab"></div>
			<form method="post" class="menu">
				<?php wp_nonce_field( 'aurix_app' ); ?>
				<input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
				<?php if ( $can_m ) : ?><a href="mailto:<?php echo esc_attr( $r['email'] ); ?>?subject=<?php echo rawurlencode( 'Re: ' . ( $r['subject'] ?: $t['label'] ) ); ?>"><?php echo aurix_app_svg( 'mail' ); // phpcs:ignore ?>Reply from my email app</a><?php endif; ?>
				<button name="do" value="new"><?php echo aurix_app_svg( 'dot' ); // phpcs:ignore ?>Mark as unread</button>
				<?php if ( 'replied' === $r['status'] ) : ?><button name="do" value="read"><?php echo aurix_app_svg( 'inbox' ); // phpcs:ignore ?>Not done — move back to Inbox</button><?php endif; ?>
				<?php if ( 'archived' !== $r['status'] ) : ?><button name="do" value="archived"><?php echo aurix_app_svg( 'archive' ); // phpcs:ignore ?>Archive</button><?php else : ?><button name="do" value="read"><?php echo aurix_app_svg( 'inbox' ); // phpcs:ignore ?>Move back to Inbox</button><?php endif; ?>
				<?php if ( 'spam' !== $r['status'] ) : ?><button name="do" value="spam"><?php echo aurix_app_svg( 'shield' ); // phpcs:ignore ?>Mark as spam</button><?php endif; ?>
				<?php if ( current_user_can( 'manage_options' ) ) : ?><button name="do" value="delete" class="danger" onclick="return confirm('Delete this message permanently?')"><?php echo aurix_app_svg( 'trash' ); // phpcs:ignore ?>Delete permanently</button><?php endif; ?>
			</form>
			<button type="button" class="cancel" data-close>Cancel</button>
		</div>
	</div>
	<?php
}

/* ── Settings ─────────────────────────────────────────────────────────── */
function aurix_app_more() {
	$u    = wp_get_current_user();
	$pref = function_exists( 'aurix_push_pref' ) ? aurix_push_pref( $u->ID ) : 'important';
	?>
	<header class="top compact"><div class="top-row"><h1>Settings</h1></div></header>
	<main class="detail settings">
		<section class="card me">
			<span class="avatar xl" style="--c:#b8925a"><?php echo esc_html( aurix_app_initials( $u->display_name ) ); ?></span>
			<div><strong><?php echo esc_html( $u->display_name ); ?></strong><span><?php echo esc_html( $u->user_email ); ?></span><em><?php echo current_user_can( 'manage_options' ) ? 'Admin' : 'Inbox team'; ?></em></div>
		</section>

		<h2 class="sec">Notifications</h2>
		<section class="card list-card">
			<div class="row-set">
				<span class="rs-ic gold"><?php echo aurix_app_svg( 'bell' ); // phpcs:ignore ?></span>
				<span class="rs-tx"><strong>New message alerts</strong><small id="n-state">Checking…</small></span>
				<label class="switch"><input type="checkbox" id="n-toggle" aria-label="New message alerts"><span></span></label>
			</div>
			<div class="row-set" id="n-pref-row">
				<span class="rs-ic"><?php echo aurix_app_svg( 'filter' ); // phpcs:ignore ?></span>
				<span class="rs-tx"><strong>Alert me about</strong><small>Newsletter, waitlist and stock alerts are quieter</small></span>
				<select id="n-pref" aria-label="Alert me about">
					<option value="important" <?php selected( $pref, 'important' ); ?>>Important</option>
					<option value="all" <?php selected( $pref, 'all' ); ?>>Everything</option>
				</select>
			</div>
			<button type="button" class="row-set btn-row" id="n-test" hidden>
				<span class="rs-ic"><?php echo aurix_app_svg( 'send' ); // phpcs:ignore ?></span>
				<span class="rs-tx"><strong>Send a test notification</strong><small>Check it arrives on this device</small></span>
				<?php echo aurix_app_svg( 'chev' ); // phpcs:ignore ?>
			</button>
		</section>
		<p class="foot" id="n-help"></p>

		<h2 class="sec" id="install-sec-h">App</h2>
		<section class="card list-card" id="install-sec">
			<button type="button" class="row-set btn-row" data-install id="install-row">
				<span class="rs-ic"><?php echo aurix_app_svg( 'phone-add' ); // phpcs:ignore ?></span>
				<span class="rs-tx"><strong>Add to Home Screen</strong><small>Open the Inbox like an app</small></span>
				<?php echo aurix_app_svg( 'chev' ); // phpcs:ignore ?>
			</button>
			<div class="row-set" id="installed-row" hidden>
				<span class="rs-ic ok"><?php echo aurix_app_svg( 'check' ); // phpcs:ignore ?></span>
				<span class="rs-tx"><strong>Installed on this device</strong><small>You're using the app version</small></span>
			</div>
			<?php if ( current_user_can( 'manage_options' ) ) : ?>
			<a class="row-set btn-row" href="<?php echo esc_url( admin_url( 'admin.php?page=aurix-inbox' ) ); ?>">
				<span class="rs-ic"><?php echo aurix_app_svg( 'desktop' ); // phpcs:ignore ?></span>
				<span class="rs-tx"><strong>Desktop Inbox</strong><small>Export, import and full settings</small></span>
				<?php echo aurix_app_svg( 'chev' ); // phpcs:ignore ?>
			</a>
			<?php endif; ?>
		</section>

		<section class="card list-card">
			<a class="row-set btn-row danger" href="<?php echo esc_url( wp_logout_url( home_url( '/inbox-app/' ) ) ); ?>" onclick="return confirm('Sign out of the Inbox on this device?')">
				<span class="rs-ic"><?php echo aurix_app_svg( 'logout' ); // phpcs:ignore ?></span>
				<span class="rs-tx"><strong>Sign out</strong><small>Notifications stop on this device</small></span>
			</a>
		</section>
		<p class="foot">Aurix Inbox · <?php echo esc_html( preg_replace( '#^https?://#', '', home_url() ) ); ?></p>
	</main>
	<?php
}

/* ── Team (admins): access requests + members ─────────────────────────── */
function aurix_app_team() {
	$pending = function_exists( 'aurix_team_pending' ) ? aurix_team_pending() : array();
	$members = function_exists( 'aurix_team_members' ) ? aurix_team_members() : array();
	?>
	<header class="top compact"><div class="top-row"><h1>Team</h1></div><p class="sub">Who can use the Inbox</p></header>
	<main class="detail settings">
		<h2 class="sec">Access requests</h2>
		<?php if ( ! $pending ) : ?>
			<section class="card"><p class="muted-p">No one is waiting. When someone scans the Inbox QR code, their request appears here and you get a notification.</p></section>
		<?php endif; ?>
		<?php foreach ( $pending as $q ) : ?>
			<section class="card req" data-req="<?php echo esc_attr( $q['id'] ); ?>">
				<div class="req-top">
					<span class="avatar" style="--c:#7c3aed"><?php echo esc_html( aurix_app_initials( $q['name'] ) ); ?></span>
					<div><strong><?php echo esc_html( $q['name'] ); ?></strong><span><?php echo esc_html( $q['email'] ); ?><?php echo $q['phone'] ? ' · ' . esc_html( $q['phone'] ) : ''; ?></span>
					<small><?php echo esc_html( human_time_diff( $q['time'] ) . ' ago · ' . aurix_team_device_label( $q['device'] ) ); ?></small></div>
				</div>
				<?php if ( $q['note'] ) : ?><p class="note-q">“<?php echo esc_html( $q['note'] ); ?>”</p><?php endif; ?>
				<div class="req-btns">
					<button type="button" class="btn-ghost" data-decide="decline">Decline</button>
					<button type="button" class="btn-solid" data-decide="accept">Accept</button>
				</div>
			</section>
		<?php endforeach; ?>

		<h2 class="sec">Team members</h2>
		<section class="card list-card">
			<?php if ( ! $members ) : ?><p class="muted-p" style="padding:14px 16px;margin:0">No team members yet. Admins always have access.</p><?php endif; ?>
			<?php foreach ( $members as $m ) :
				$seen = (int) get_user_meta( $m->ID, '_aurix_inbox_seen', true ); ?>
				<div class="row-set">
					<span class="avatar sm" style="--c:#0f766e"><?php echo esc_html( aurix_app_initials( $m->display_name ) ); ?></span>
					<span class="rs-tx"><strong><?php echo esc_html( $m->display_name ); ?></strong><small><?php echo esc_html( $m->user_email ); ?> · <?php echo $seen ? esc_html( 'active ' . human_time_diff( $seen ) . ' ago' ) : 'not opened yet'; ?></small></span>
				</div>
			<?php endforeach; ?>
		</section>
		<p class="foot">To remove someone or create a personal QR code, use Inbox → Settings &amp; Import → Team access on a computer.</p>
	</main>
	<?php
}

/** "How to install" sheet (iPhone needs manual steps). */
function aurix_app_install_sheet() { ?>
	<div class="sheet" id="sheet-install" role="dialog" aria-modal="true" aria-label="Install the app" hidden>
		<div class="sheet-panel">
			<div class="grab"></div>
			<div class="sheet-head"><strong>Add Inbox to your Home Screen</strong><button type="button" class="p-x" data-close aria-label="Close">×</button></div>
			<ol class="steps" data-os="ios">
				<li><span class="n">1</span><span>Tap the <b>Share</b> button <span class="ios-share"><?php echo aurix_app_svg( 'share' ); // phpcs:ignore ?></span> at the bottom of Safari.</span></li>
				<li><span class="n">2</span><span>Scroll and tap <b>Add to Home Screen</b> <span class="ios-share"><?php echo aurix_app_svg( 'plus-square' ); // phpcs:ignore ?></span></span></li>
				<li><span class="n">3</span><span>Tap <b>Add</b>. Then open <b>Inbox</b> from your Home Screen and turn on notifications.</span></li>
			</ol>
			<ol class="steps" data-os="other">
				<li><span class="n">1</span><span>Open your browser menu <b>⋮</b> (top right).</span></li>
				<li><span class="n">2</span><span>Tap <b>Install app</b> or <b>Add to Home screen</b>.</span></li>
				<li><span class="n">3</span><span>Open <b>Inbox</b> from your Home Screen.</span></li>
			</ol>
			<p class="to" data-os="ios-notsafari" hidden>On iPhone, please open this page in <b>Safari</b> first — other browsers can't add apps to the Home Screen.</p>
			<button type="button" class="primary" data-close>Got it</button>
		</div>
	</div>
<?php }

/** Screen for people without access: request form → waiting → (auto sign-in) / declined. */
function aurix_app_request_page( $q, $error = '' ) {
	nocache_headers();
	$u     = wp_get_current_user();
	$state = $q ? $q['state'] : 'none';
	?><!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><meta name="robots" content="noindex,nofollow"><meta name="theme-color" content="#0d1b2a">
<link rel="icon" href="<?php echo esc_url( home_url( '/inbox-app/icon' ) ); ?>" type="image/svg+xml">
<title>Aurix Inbox — Request access</title>
<style><?php aurix_app_css(); ?>
.gate{min-height:100vh;display:flex;flex-direction:column;justify-content:center;padding:calc(24px + var(--safe-t)) 18px calc(24px + var(--safe-b));background:var(--navy)}
.gate .card{max-width:420px;width:100%;margin:0 auto;padding:24px 20px}
.gate .logo{width:56px;height:56px;border-radius:16px;background:var(--navy);color:var(--gold);display:flex;align-items:center;justify-content:center;margin-bottom:14px}
.gate .logo svg{width:30px;height:30px}
.gate h1{margin:0 0 6px;font-size:22px;color:var(--navy)}.gate p{margin:0 0 16px;color:var(--muted)}
.gate label{display:block;font-size:14px;font-weight:600;margin:12px 0 6px}
.gate input,.gate textarea{width:100%;border:1px solid #d1d5db;border-radius:12px;padding:12px;font:inherit}
.gate .err{background:#fef2f2;color:#b91c1c;border-radius:10px;padding:10px 12px;margin-bottom:10px;font-size:14px}
.gate .alt{display:block;text-align:center;margin-top:16px;color:var(--muted);font-size:14px}
.gate .spin{width:28px;height:28px;border:3px solid #e5e7eb;border-top-color:var(--gold);border-radius:50%;animation:sp 1s linear infinite;margin:6px 0 14px}
@keyframes sp{to{transform:rotate(360deg)}}
.gate .info{background:#f8fafc;border-radius:12px;padding:12px;font-size:14px;color:#334155}
</style></head>
<body><div class="gate"><div class="card">
	<?php if ( 'pending' === $state ) : ?>
		<div class="logo"><?php echo aurix_app_svg( 'clock' ); // phpcs:ignore ?></div>
		<h1>Waiting for approval</h1>
		<p>Your request was sent to the Aurix admin. Keep this page open — the Inbox opens here by itself as soon as they accept.</p>
		<div class="spin" role="status" aria-label="Waiting"></div>
		<div class="info"><strong><?php echo esc_html( $q['name'] ); ?></strong><br><?php echo esc_html( $q['email'] ); ?></div>
		<a class="alt" href="<?php echo esc_url( home_url( '/inbox-app/?new=1' ) ); ?>">Not you? Send a new request</a>
		<script>
		(function(){function poll(){fetch(<?php echo wp_json_encode( home_url( '/inbox-app/status' ) ); ?>,{credentials:'same-origin',cache:'no-store'}).then(function(r){return r.json();}).then(function(d){if(d.state!=='pending'){location.reload();}}).catch(function(){});}
		setInterval(function(){if(!document.hidden)poll();},5000);document.addEventListener('visibilitychange',function(){if(!document.hidden)poll();});})();
		</script>
	<?php elseif ( 'declined' === $state ) : ?>
		<div class="logo"><?php echo aurix_app_svg( 'x' ); // phpcs:ignore ?></div>
		<h1>Request not approved</h1>
		<p>The admin declined this request. If you think this is a mistake, please contact them directly.</p>
		<a class="primary" style="display:block;text-align:center;text-decoration:none" href="<?php echo esc_url( home_url( '/inbox-app/?new=1' ) ); ?>">Send a new request</a>
	<?php else : ?>
		<div class="logo"><?php echo aurix_app_svg( 'lock' ); // phpcs:ignore ?></div>
		<h1>Aurix Inbox</h1>
		<p>Ask for access to answer customer messages from this phone. An admin will accept or decline your request.</p>
		<?php if ( $error ) : ?><div class="err" role="alert"><?php echo esc_html( $error ); ?></div><?php endif; ?>
		<form method="post">
			<?php wp_nonce_field( 'aurix_req' ); ?>
			<input type="hidden" name="do" value="request">
			<input type="text" name="website_url" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
			<label for="rq-name">Your name</label>
			<input id="rq-name" name="name" required autocomplete="name" value="<?php echo esc_attr( $u->exists() ? $u->display_name : sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ) ); ?>">
			<label for="rq-email">Email</label>
			<input id="rq-email" name="email" type="email" required autocomplete="email" value="<?php echo esc_attr( $u->exists() ? $u->user_email : sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ) ); ?>">
			<label for="rq-phone">Phone / WhatsApp <span style="font-weight:400;color:#6b7280">(optional)</span></label>
			<input id="rq-phone" name="phone" type="tel" autocomplete="tel" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ) ); ?>">
			<label for="rq-note">Note to the admin <span style="font-weight:400;color:#6b7280">(optional)</span></label>
			<textarea id="rq-note" name="note" rows="2" placeholder="e.g. Sales team, Sialkot office"><?php echo esc_textarea( sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) ) ); ?></textarea>
			<button class="primary" type="submit">Request access</button>
		</form>
		<a class="alt" href="<?php echo esc_url( wp_login_url( home_url( '/inbox-app/' ) ) ); ?>">Admin? Log in instead</a>
	<?php endif; ?>
</div></div></body></html>
<?php
}

function aurix_app_initials( $s ) {
	$s     = preg_replace( '/@.*/', '', trim( $s ) );
	$parts = preg_split( '/[\s._\-]+/', $s, -1, PREG_SPLIT_NO_EMPTY );
	$i     = mb_substr( $parts[0] ?? '?', 0, 1 ) . ( isset( $parts[1] ) ? mb_substr( $parts[1], 0, 1 ) : '' );
	return mb_strtoupper( $i );
}


function aurix_app_svg( $name ) {
	$p = array(
		'search'      => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'back'        => '<path d="M15 18l-6-6 6-6"/>',
		'chev'        => '<path d="M9 18l6-6-6-6"/>',
		'phone'       => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
		'whatsapp'    => '<path d="M3 21l1.7-5A8.5 8.5 0 1 1 8 19.4z"/><path d="M9 10c.5 2 2 3.5 4 4l1.2-1.2 2 1-.4 1.5c-3.6.4-7.7-3.7-7.3-7.3L10 7.6l1 2z"/>',
		'reply'       => '<path d="M9 17l-5-5 5-5"/><path d="M4 12h11a5 5 0 0 1 5 5v2"/>',
		'mail'        => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'check'       => '<path d="M20 6 9 17l-5-5"/>',
		'inbox'       => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5h13L22 12v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6z"/>',
		'desktop'     => '<rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>',
		'logout'      => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/>',
		'lock'        => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
		'clock'       => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'x'           => '<circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/>',
		'bell'        => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
		'users'       => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
		'dot'         => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.5" fill="currentColor"/>',
		'gear'        => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
		'archive'     => '<rect x="2" y="3" width="20" height="5" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8M10 12h4"/>',
		'phone-add'   => '<rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M12 8v6M9 11h6M11 18h2"/>',
		'copy'        => '<rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
		'building'    => '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M8 10h.01M16 10h.01M12 10h.01M8 14h.01M16 14h.01M12 14h.01"/>',
		'globe'       => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/>',
		'link'        => '<path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/>',
		'dots'        => '<circle cx="12" cy="5" r="1.4" fill="currentColor"/><circle cx="12" cy="12" r="1.4" fill="currentColor"/><circle cx="12" cy="19" r="1.4" fill="currentColor"/>',
		'note'        => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h5"/>',
		'shield'      => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/>',
		'trash'       => '<path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>',
		'filter'      => '<path d="M22 3H2l8 9.5V19l4 2v-8.5z"/>',
		'send'        => '<path d="M22 2 11 13M22 2l-7 20-4-9-9-4z"/>',
		'share'       => '<path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8M16 6l-4-4-4 4M12 2v13"/>',
		'plus-square' => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M12 8v8M8 12h8"/>',
	);
	return '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $p[ $name ] ?? '' ) . '</svg>';
}

function aurix_app_icon_svg() {
	return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><rect width="512" height="512" fill="#0d1b2a"/>'
		. '<rect x="112" y="156" width="288" height="200" rx="28" fill="none" stroke="#b8925a" stroke-width="28"/>'
		. '<path d="M126 176l130 96 130-96" fill="none" stroke="#b8925a" stroke-width="28" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

function aurix_app_css() { ?>
:root{--navy:#0d1b2a;--navy2:#1b2a3d;--gold:#b8925a;--gold2:#734608;--bg:#f2f3f6;--card:#fff;--line:#e8eaee;--muted:#6b7280;--ink:#0f172a;--ok:#16a34a;--red:#e11d48;--safe-t:env(safe-area-inset-top,0px);--safe-b:env(safe-area-inset-bottom,0px);--r:18px}
*{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html,body{margin:0;background:var(--bg);color:var(--ink);font:16px/1.45 -apple-system,BlinkMacSystemFont,"SF Pro Text","Segoe UI",Roboto,Inter,Arial,sans-serif;-webkit-font-smoothing:antialiased;overscroll-behavior-y:contain}
a{color:inherit}button{font:inherit;color:inherit;cursor:pointer}
:focus-visible{outline:2px solid var(--gold);outline-offset:2px}
@media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}

/* header */
.top{background:linear-gradient(180deg,var(--navy) 0%,var(--navy2) 100%);color:#fff;padding:calc(16px + var(--safe-t)) 18px 16px}
.top.compact{padding-bottom:18px}
.hello{margin:0;color:#94a3b8;font-size:13px;font-weight:500}
.top-row{display:flex;align-items:center;gap:10px}
.top h1{margin:0;font-size:30px;font-weight:800;letter-spacing:-.02em;flex:1}
.top .sub{margin:2px 0 0;color:#cbd5e1;font-size:14px}
.icon-btn{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:14px;border:0;background:rgba(255,255,255,.1);color:#fff;text-decoration:none;position:relative}
.icon-btn.ghost{background:transparent;color:var(--navy)}
.icon-btn.alert::after{content:"";position:absolute;top:9px;right:10px;width:9px;height:9px;border-radius:50%;background:var(--gold);box-shadow:0 0 0 2px var(--navy)}
.search{margin-top:14px;display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border-radius:14px;padding:0 12px;color:#94a3b8;border:1px solid rgba(255,255,255,.06)}
.search:focus-within{background:rgba(255,255,255,.16);border-color:rgba(184,146,90,.6)}
.search input{flex:1;min-width:0;border:0;background:transparent;color:#fff;font-size:16px;padding:12px 0;outline:none}
.search input::placeholder{color:#94a3b8}
.search input::-webkit-search-cancel-button{display:none}
.search .clear{color:#cbd5e1;text-decoration:none;font-size:22px;line-height:1;padding:4px}
.chips{display:flex;gap:8px;overflow-x:auto;padding:0 18px 14px;scrollbar-width:none;background:var(--navy2);scroll-snap-type:x proximity}
.chips::-webkit-scrollbar{display:none}
.chip{flex:0 0 auto;display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:999px;background:rgba(255,255,255,.08);color:#e2e8f0;text-decoration:none;font-size:14px;font-weight:600;white-space:nowrap;scroll-snap-align:start}
.chip i{width:8px;height:8px;border-radius:50%;background:var(--c,#94a3b8)}
.chip b{background:var(--red);color:#fff;border-radius:999px;font-size:11px;padding:1px 7px}
.chip.on{background:#fff;color:var(--navy)}

/* list */
.list{padding:6px 0 calc(96px + var(--safe-b))}
.day{margin:16px 18px 6px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted)}
.swipe{position:relative;overflow:hidden;margin:0 10px 8px;border-radius:var(--r)}
.swipe-bg{position:absolute;inset:0;display:flex;justify-content:space-between;align-items:center;padding:0 22px;font-weight:700;font-size:14px;color:#fff;background:var(--ok)}
.swipe-bg span{display:flex;align-items:center;gap:6px;opacity:0;transition:opacity .15s}
.swipe-bg.go-l{background:var(--ok)}.swipe-bg.go-r{background:#475569}
.swipe-bg.go-l .l,.swipe-bg.go-r .r{opacity:1}
.item{position:relative;display:flex;gap:12px;align-items:flex-start;padding:14px;background:var(--card);text-decoration:none;border-radius:var(--r);box-shadow:0 1px 2px rgba(15,23,42,.04);transition:transform .25s cubic-bezier(.2,.8,.2,1),background .15s;touch-action:pan-y;will-change:transform}
.item:active{background:#fafafa}
.item.unread{box-shadow:inset 3px 0 0 var(--gold),0 1px 2px rgba(15,23,42,.05)}
.avatar{flex:0 0 46px;width:46px;height:46px;border-radius:50%;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;background:var(--c,#64748b);background-image:linear-gradient(135deg,rgba(255,255,255,.18),rgba(0,0,0,.08))}
.avatar.xl{width:72px;height:72px;flex-basis:72px;font-size:24px}
.avatar.sm{width:36px;height:36px;flex-basis:36px;font-size:13px}
.body{flex:1;min-width:0;display:flex;flex-direction:column;gap:2px}
.line1{display:flex;gap:8px;align-items:baseline}
.who{flex:1;min-width:0;font-weight:600;font-size:16px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.item.unread .who{font-weight:800}
.when{color:var(--muted);font-size:12px;white-space:nowrap}
.item.unread .when{color:var(--gold2);font-weight:700}
.tag{display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:#475569;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tag i{flex:0 0 7px;height:7px;border-radius:50%}
.tag em{font-style:normal;color:var(--muted);font-weight:500;overflow:hidden;text-overflow:ellipsis}
.what{color:#334155;font-size:14px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-top:2px}
.done{color:var(--ok);align-self:center}
.more{display:block;margin:14px 10px;text-align:center;padding:14px;border-radius:var(--r);background:var(--card);text-decoration:none;font-weight:700;color:var(--navy)}
.hint{text-align:center;color:#9ca3af;font-size:12px;margin:14px 20px}
.empty{text-align:center;color:var(--muted);padding:56px 28px}
.empty-ic{width:72px;height:72px;margin:0 auto 14px;border-radius:50%;background:#e9f7ef;color:var(--ok);display:flex;align-items:center;justify-content:center}
.empty-ic svg{width:34px;height:34px}
.empty h2{margin:0 0 4px;color:var(--ink);font-size:19px}.empty p{margin:0}
.newbar{position:fixed;left:0;right:0;top:calc(12px + var(--safe-t));z-index:30;text-align:center;pointer-events:none}
.newbar a{pointer-events:auto;display:inline-block;background:var(--gold);color:#fff;text-decoration:none;padding:9px 18px;border-radius:999px;font-size:14px;font-weight:700;box-shadow:0 8px 24px rgba(0,0,0,.2);animation:drop .35s ease}
@keyframes drop{from{transform:translateY(-30px);opacity:0}}
.ptr{position:fixed;left:50%;top:calc(10px + var(--safe-t));width:38px;height:38px;margin-left:-19px;border-radius:50%;background:#fff;box-shadow:0 4px 14px rgba(0,0,0,.18);z-index:40;display:flex;align-items:center;justify-content:center;transform:translateY(-70px);transition:transform .2s}
.ptr span{width:18px;height:18px;border:2.5px solid #e5e7eb;border-top-color:var(--gold);border-radius:50%}
.ptr.spin span{animation:sp .8s linear infinite}
@keyframes sp{to{transform:rotate(360deg)}}

/* prompts + banners */
.prompt{display:flex;align-items:center;gap:12px;margin:8px 10px;padding:14px;border-radius:var(--r);background:var(--card);border:1px solid var(--line);position:relative}
.prompt[hidden]{display:none}
.p-ic{flex:0 0 44px;height:44px;border-radius:14px;background:#eef2ff;color:#4338ca;display:flex;align-items:center;justify-content:center}
.p-ic.gold{background:#fdf6ea;color:var(--gold2)}
.p-tx{flex:1;min-width:0;display:flex;flex-direction:column;padding-right:16px}
.p-tx strong{font-size:15px}.p-tx span{font-size:13px;color:var(--muted)}
.p-btn{border:0;background:var(--navy);color:#fff;border-radius:12px;padding:10px 14px;font-weight:700;font-size:14px;white-space:nowrap}
.p-x{position:absolute;top:4px;right:6px;border:0;background:none;color:#9ca3af;font-size:22px;line-height:1;padding:4px 6px}
.sheet-head .p-x{position:static}
.req-banner{display:flex;align-items:center;gap:12px;margin:8px 10px;padding:14px;border-radius:var(--r);background:linear-gradient(135deg,#fdf6ea,#fbecd2);text-decoration:none;color:var(--gold2)}
.req-banner span{flex:1;display:flex;flex-direction:column}.req-banner strong{color:var(--ink)}.req-banner em{font-style:normal;font-size:13px}

/* tab bar */
.tabbar{position:fixed;left:0;right:0;bottom:0;z-index:20;display:flex;background:rgba(255,255,255,.92);backdrop-filter:saturate(180%) blur(18px);-webkit-backdrop-filter:saturate(180%) blur(18px);border-top:1px solid var(--line);padding:6px 6px calc(6px + var(--safe-b))}
.tabbar a{flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;padding:6px 0;text-decoration:none;color:#94a3b8;font-size:11px;font-weight:600;position:relative;border-radius:12px}
.tabbar a.on{color:var(--navy)}
.tabbar a.on svg{color:var(--gold)}
.tb-badge{position:absolute;top:1px;left:calc(50% + 6px);min-width:18px;height:18px;padding:0 5px;border-radius:9px;background:var(--red);color:#fff;font-size:11px;line-height:18px;text-align:center;box-shadow:0 0 0 2px #fff}
.tb-badge[hidden]{display:none}

/* detail */
.bar{position:sticky;top:0;z-index:10;display:flex;align-items:center;gap:8px;padding:calc(8px + var(--safe-t)) 8px 8px;background:rgba(242,243,246,.9);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px)}
.bar-title{flex:1;text-align:center;font-weight:700;font-size:16px}
.detail{padding:4px 12px calc(110px + var(--safe-b));display:flex;flex-direction:column;gap:12px;max-width:680px;margin:0 auto}
.hero{text-align:center;padding:8px 0 4px}
.hero h1{margin:10px 0 2px;font-size:24px;letter-spacing:-.01em}
.hero .avatar{margin:0 auto}
.from{margin:0 0 10px;color:var(--muted)}
.pill{display:inline-block;padding:5px 12px;border-radius:999px;font-size:12px;font-weight:700;color:#fff;background:var(--c,#64748b);margin:0 2px}
.pill.ok{background:#dcfce7;color:#15803d}.pill.muted{background:#e5e7eb;color:#475569}
.card{background:var(--card);border-radius:var(--r);padding:16px;box-shadow:0 1px 2px rgba(15,23,42,.04)}
.card h2{margin:0 0 8px;font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted)}
.want{background:linear-gradient(135deg,#fffdf8,#fdf6ea);border:1px solid #f1e3c6}
.want .big{margin:0;font-size:19px;font-weight:700;line-height:1.35;color:var(--navy)}
.date{margin:6px 0 0;color:var(--muted);font-size:13px}
.subj{font-weight:700;margin:0 0 6px}
.msg{white-space:pre-wrap;margin:0;line-height:1.6;font-size:16px}
.rows{list-style:none;margin:0;padding:0}
.rows li{display:flex;align-items:center;border-top:1px solid var(--line)}
.rows li:first-child{border-top:0}
.rows a,.rows .nolink{flex:1;min-width:0;display:flex;align-items:center;gap:12px;padding:10px 0;text-decoration:none;color:var(--ink)}
.rows a svg,.rows .nolink svg{flex:0 0 22px;color:var(--muted)}
.rows small{display:block;font-size:12px;color:var(--muted)}
.rows span span{min-width:0;word-break:break-word}
.rows a span span{color:#1d4ed8}
.copy{border:0;background:#f1f5f9;border-radius:10px;width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#475569}
.copy svg{width:18px;height:18px}
.kv{margin:0;display:grid;grid-template-columns:minmax(90px,38%) 1fr;gap:10px 14px;font-size:15px}
.kv dt{color:var(--muted)}.kv dd{margin:0;word-break:break-word}.kv a{color:#1d4ed8}
.thread{display:flex;flex-direction:column;gap:8px}
.bubble{padding:10px 14px;border-radius:16px;font-size:15px;max-width:88%}
.bubble.out{align-self:flex-end;background:var(--navy);color:#fff;border-bottom-right-radius:4px}
.bubble.note{align-self:flex-start;background:#fef9c3;border-bottom-left-radius:4px}
.bubble .meta{font-size:11px;opacity:.7;margin-top:4px}
.dock{position:fixed;left:0;right:0;bottom:0;z-index:20;display:flex;gap:6px;padding:8px 10px calc(8px + var(--safe-b));background:rgba(255,255,255,.94);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);border-top:1px solid var(--line)}
.dock>a,.dock>button,.dock form{flex:1;display:flex}
.dock a,.dock button{flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;padding:8px 2px;border:0;border-radius:14px;background:#f1f5f9;color:var(--navy);text-decoration:none;font-size:12px;font-weight:700}
.dock a:active,.dock button:active{transform:scale(.96)}
.dock .wa{background:#dcfce7;color:#15803d}
.dock .primary-dock{background:var(--navy);color:#fff}
.dock .ok{background:#ecfdf5;color:var(--ok)}

/* sheets */
.sheet{position:fixed;inset:0;z-index:50;background:rgba(15,23,42,.45);display:flex;align-items:flex-end;animation:fade .2s}
.sheet[hidden]{display:none}
@keyframes fade{from{opacity:0}}
.sheet-panel{width:100%;max-width:680px;margin:0 auto;background:#fff;border-radius:22px 22px 0 0;padding:8px 16px calc(16px + var(--safe-b));max-height:92vh;overflow:auto;animation:up .28s cubic-bezier(.2,.8,.2,1)}
@keyframes up{from{transform:translateY(100%)}}
.grab{width:40px;height:5px;border-radius:3px;background:#d1d5db;margin:4px auto 10px}
.sheet-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;font-size:17px}
.to{margin:0 0 10px;color:var(--muted);font-size:13px}
.quick{display:flex;gap:6px;overflow-x:auto;margin-bottom:10px;scrollbar-width:none}
.quick::-webkit-scrollbar{display:none}
.quick button{flex:0 0 auto;border:1px solid var(--line);background:#fff;border-radius:999px;padding:8px 12px;font-size:13px;font-weight:600}
textarea{width:100%;border:1px solid #d1d5db;border-radius:14px;padding:12px;font:inherit;resize:vertical;background:#fafafa}
textarea:focus{outline:2px solid var(--gold);border-color:transparent;background:#fff}
.primary{width:100%;margin-top:10px;border:0;border-radius:14px;background:var(--navy);color:#fff;font-size:16px;font-weight:700;padding:15px}
.primary:disabled{opacity:.6}
.menu{display:flex;flex-direction:column}
.menu a,.menu button{display:flex;align-items:center;gap:14px;padding:15px 6px;border:0;border-bottom:1px solid var(--line);background:none;text-align:left;text-decoration:none;font-size:16px;font-weight:500}
.menu svg{color:var(--muted)}
.menu .danger{color:var(--red)}.menu .danger svg{color:var(--red)}
.cancel{width:100%;margin-top:10px;border:0;background:#f1f5f9;border-radius:14px;padding:14px;font-weight:700}
.steps{list-style:none;margin:6px 0 10px;padding:0;display:flex;flex-direction:column;gap:12px}
.steps li{display:flex;gap:12px;align-items:center;font-size:15px}
.steps .n{flex:0 0 28px;height:28px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px}
.ios-share{display:inline-flex;vertical-align:middle;color:#0a84ff}.ios-share svg{width:20px;height:20px}

/* settings */
.settings{padding-top:14px}
.sec{margin:10px 6px 0;font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted)}
.me{display:flex;align-items:center;gap:14px}
.me div{display:flex;flex-direction:column}.me div span{color:var(--muted);font-size:14px}.me em{font-style:normal;font-size:12px;font-weight:700;color:var(--gold2);margin-top:2px}
.list-card{padding:0;overflow:hidden}
.row-set{display:flex;align-items:center;gap:12px;padding:12px 16px;border-top:1px solid var(--line);width:100%;background:none;border-left:0;border-right:0;border-bottom:0;text-align:left;text-decoration:none;color:var(--ink)}
.list-card>.row-set:first-child{border-top:0}
.row-set[hidden]{display:none}
.rs-ic{flex:0 0 34px;height:34px;border-radius:10px;background:#f1f5f9;color:#475569;display:flex;align-items:center;justify-content:center}
.rs-ic svg{width:19px;height:19px}
.rs-ic.gold{background:#fdf6ea;color:var(--gold2)}.rs-ic.ok{background:#dcfce7;color:var(--ok)}
.rs-tx{flex:1;min-width:0;display:flex;flex-direction:column}
.rs-tx small{color:var(--muted);font-size:13px}
.btn-row>svg:last-child{color:#cbd5e1}
.row-set.danger{color:var(--red)}.row-set.danger .rs-ic{background:#fff1f2;color:var(--red)}
.row-set select{border:1px solid var(--line);border-radius:10px;padding:7px 8px;font:inherit;font-size:14px;background:#fff}
.switch{position:relative;width:52px;height:32px;flex:0 0 52px}
.switch input{position:absolute;opacity:0;width:100%;height:100%;margin:0;cursor:pointer;z-index:1}
.switch span{position:absolute;inset:0;border-radius:16px;background:#d1d5db;transition:background .2s}
.switch span::after{content:"";position:absolute;top:3px;left:3px;width:26px;height:26px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.25);transition:transform .2s}
.switch input:checked+span{background:var(--ok)}
.switch input:checked+span::after{transform:translateX(20px)}
.switch input:disabled+span{opacity:.5}
.foot{text-align:center;color:#9ca3af;font-size:12px;margin:4px 16px 10px}
.foot:empty{display:none}
.muted-p{margin:0;color:var(--muted)}
.req-top{display:flex;gap:12px;align-items:center}
.req-top div{display:flex;flex-direction:column;min-width:0}.req-top span{color:var(--muted);font-size:14px;word-break:break-word}.req-top small{color:#9ca3af}
.note-q{margin:10px 0 0;padding:10px 12px;border-radius:12px;background:#f8fafc;color:#334155}
.req-btns{display:flex;gap:8px;margin-top:12px}
.btn-ghost,.btn-solid{flex:1;border-radius:12px;padding:12px;font-weight:700;border:1px solid var(--line);background:#fff}
.btn-solid{background:var(--navy);color:#fff;border-color:var(--navy)}

/* toast */
.toast{position:fixed;left:50%;bottom:calc(92px + var(--safe-b));transform:translate(-50%,20px);background:#111827;color:#fff;padding:12px 18px;border-radius:14px;font-size:14px;font-weight:600;z-index:60;opacity:0;pointer-events:none;transition:opacity .2s,transform .2s;box-shadow:0 10px 30px rgba(0,0,0,.25);max-width:90vw;text-align:center}
.toast.show{opacity:1;transform:translate(-50%,0)}
.toast .undo{margin-left:12px;color:var(--gold);font-weight:800;pointer-events:auto;background:none;border:0}
@media(min-width:720px){.list,.chips,.top{padding-left:max(18px,calc(50vw - 340px));padding-right:max(18px,calc(50vw - 340px))}.swipe,.prompt,.req-banner,.more{max-width:680px;margin-left:auto;margin-right:auto}.day{max-width:660px;margin-left:auto;margin-right:auto}.tabbar{justify-content:center}.tabbar a{max-width:140px}}
<?php }

function aurix_app_js( $unread, $screen ) {
	$vapid = function_exists( 'aurix_push_vapid' ) ? aurix_push_vapid() : null;
	$cfg   = array(
		'base'     => home_url( '/inbox-app/' ),
		'nonce'    => wp_create_nonce( 'aurix_app' ),
		'unread'   => (int) $unread,
		'screen'   => $screen,
		'vapid'    => $vapid ? $vapid['public'] : '',
		'admin'    => current_user_can( 'manage_options' ),
	);
	?>
<script>
(function () {
	var C = <?php echo wp_json_encode( $cfg ); ?>;
	var $ = function (s, r) { return (r || document).querySelector(s); };
	var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
	var LS = { get: function (k) { try { return localStorage.getItem(k); } catch (e) { return null; } }, set: function (k, v) { try { localStorage.setItem(k, v); } catch (e) {} } };
	var ua = navigator.userAgent, isIOS = /iphone|ipad|ipod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
	var isSafari = isIOS && !/crios|fxios|edgios|opios/i.test(ua);
	var standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
	var installed = standalone || LS.get('axInstalled') === '1';
	var pushOK = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window && !!C.vapid;
	var haptic = function () { if (navigator.vibrate) navigator.vibrate(12); };

	/* toast */
	var toastEl = $('.toast'), toastT;
	function toast(msg, undo) {
		toastEl.innerHTML = ''; toastEl.textContent = msg;
		if (undo) { var b = document.createElement('button'); b.className = 'undo'; b.textContent = 'Undo'; b.onclick = undo; toastEl.appendChild(b); }
		toastEl.classList.add('show'); clearTimeout(toastT); toastT = setTimeout(function () { toastEl.classList.remove('show'); }, undo ? 4500 : 2600);
	}
	if (toastEl.classList.contains('show')) { toastT = setTimeout(function () { toastEl.classList.remove('show'); }, 2800); history.replaceState(null, '', location.href.replace(/([?&])m=[^&]*&?/, '$1').replace(/[?&]$/, '')); }

	function post(url, data) {
		var body = new URLSearchParams(data); body.append('nonce', C.nonce);
		return fetch(url, { method: 'POST', body: body, credentials: 'same-origin' }).then(function (r) { return r.json(); });
	}

	/* sheets */
	function openSheet(id) { var s = $('#sheet-' + id); if (!s) return; s.hidden = false; document.body.style.overflow = 'hidden'; var t = $('textarea', s); if (t) setTimeout(function () { t.focus(); }, 280); }
	function closeSheets() { $$('.sheet').forEach(function (s) { s.hidden = true; }); document.body.style.overflow = ''; }
	$$('[data-sheet]').forEach(function (b) { b.addEventListener('click', function () { openSheet(b.dataset.sheet); }); });
	$$('.sheet').forEach(function (s) { s.addEventListener('click', function (e) { if (e.target === s || e.target.closest('[data-close]')) closeSheets(); }); });
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeSheets(); });
	$$('.quick button').forEach(function (b) { b.addEventListener('click', function () { var t = b.closest('form').querySelector('textarea'); t.value = b.dataset.text + (t.value ? '\n\n' + t.value : ''); t.focus(); t.setSelectionRange(t.value.length, t.value.length); }); });
	$$('form').forEach(function (f) { f.addEventListener('submit', function () { var b = f.querySelector('.primary'); if (b) setTimeout(function () { b.disabled = true; b.textContent = 'Sending…'; }, 0); }); });
	$$('[data-copy]').forEach(function (b) { b.addEventListener('click', function () { if (navigator.clipboard) navigator.clipboard.writeText(b.dataset.copy).then(function () { haptic(); toast('Copied'); }); }); });

	/* app badge + title */
	function setBadge(n) {
		document.title = (n ? '(' + n + ') ' : '') + 'Aurix Inbox';
		if (navigator.setAppBadge) { (n ? navigator.setAppBadge(n) : navigator.clearAppBadge()).catch(function () {}); }
		var tb = $('[data-badge="dot"]'); if (tb) { tb.hidden = !n; tb.textContent = n > 99 ? '99+' : n; }
		var sub = $('#sub'); if (sub) sub.textContent = n ? n + ' unread' : 'You\'re all caught up';
	}
	setBadge(C.unread);

	/* live check for new messages */
	var latest = null;
	function check() {
		fetch(C.base + 'count', { credentials: 'same-origin', cache: 'no-store' }).then(function (r) { return r.json(); }).then(function (d) {
			if (latest !== null && d.latest > latest) { var nb = $('#newbar'); if (nb) nb.hidden = false; haptic(); }
			latest = d.latest; setBadge(d.unread);
		}).catch(function () {});
	}
	check(); setInterval(function () { if (!document.hidden) check(); }, 30000);
	document.addEventListener('visibilitychange', function () { if (!document.hidden) check(); });

	/* pull to refresh (list) */
	var ptr = $('#ptr');
	if (ptr) {
		var y0 = null, dy = 0;
		window.addEventListener('touchstart', function (e) { if (window.scrollY <= 0 && !$('.sheet:not([hidden])')) { y0 = e.touches[0].clientY; dy = 0; } }, { passive: true });
		window.addEventListener('touchmove', function (e) { if (y0 === null) return; dy = e.touches[0].clientY - y0; if (dy > 0) ptr.style.transform = 'translateY(' + Math.min(dy * 0.5, 70) + 'px) rotate(' + dy * 2 + 'deg)'; }, { passive: true });
		window.addEventListener('touchend', function () { if (y0 === null) return; y0 = null; if (dy > 110) { ptr.classList.add('spin'); haptic(); location.reload(); } else { ptr.style.transform = ''; } });
	}

	/* swipe: right = Done, left = Archive (with Undo) */
	$$('.swipe[data-swipe="1"]').forEach(function (w) {
		var it = $('.item', w), bg = $('.swipe-bg', w), x0 = null, y0 = null, dx = 0, locked = null;
		it.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; y0 = e.touches[0].clientY; dx = 0; locked = null; it.style.transition = 'none'; }, { passive: true });
		it.addEventListener('touchmove', function (e) {
			if (x0 === null) return;
			var mx = e.touches[0].clientX - x0, my = e.touches[0].clientY - y0;
			if (locked === null && (Math.abs(mx) > 8 || Math.abs(my) > 8)) locked = Math.abs(mx) > Math.abs(my) ? 'x' : 'y';
			if (locked !== 'x') return;
			dx = mx; it.style.transform = 'translateX(' + dx + 'px)';
			bg.className = 'swipe-bg ' + (dx > 0 ? 'go-l' : 'go-r');
		}, { passive: true });
		it.addEventListener('touchend', function () {
			it.style.transition = '';
			if (locked === 'x' && Math.abs(dx) > 90) {
				var act = dx > 0 ? 'replied' : 'archived'; haptic();
				it.style.transform = 'translateX(' + (dx > 0 ? '110%' : '-110%') + ')';
				setTimeout(function () { w.style.transition = 'max-height .25s, margin .25s, opacity .25s'; w.style.maxHeight = w.offsetHeight + 'px'; requestAnimationFrame(function () { w.style.maxHeight = '0'; w.style.margin = '0 10px'; w.style.opacity = '0'; }); }, 180);
				post(C.base + 'act', { id: w.dataset.id, do: act }).then(function (d) {
					if (d.unread !== undefined) setBadge(d.unread);
					toast(d.msg || 'Done', function () { post(C.base + 'act', { id: w.dataset.id, do: 'read' }).then(function () { location.reload(); }); });
				}).catch(function () { toast('No connection — try again'); location.reload(); });
			} else { it.style.transform = ''; }
			if (locked === 'x') { it.addEventListener('click', function stop(e) { e.preventDefault(); it.removeEventListener('click', stop, true); }, true); }
			x0 = null;
		});
	});

	/* access requests (admins) */
	$$('[data-decide]').forEach(function (b) {
		b.addEventListener('click', function () {
			var card = b.closest('[data-req]'); $$('button', card).forEach(function (x) { x.disabled = true; });
			post(C.base + 'act', { do: b.dataset.decide, req: card.dataset.req }).then(function (d) { haptic(); toast(d.msg); card.style.opacity = '.4'; setTimeout(function () { location.reload(); }, 1400); });
		});
	});

	/* ── Install ── */
	var deferred = null;
	window.addEventListener('beforeinstallprompt', function (e) { e.preventDefault(); deferred = e; refreshInstall(); });
	window.addEventListener('appinstalled', function () { LS.set('axInstalled', '1'); installed = true; refreshInstall(); toast('Installed — open Inbox from your Home Screen'); });
	function canOfferInstall() { return !installed && (deferred || isIOS || /android/i.test(ua)); }
	function refreshInstall() {
		var show = canOfferInstall();
		var card = $('#p-install'); if (card) card.hidden = !show || LS.get('axHideInstall') === '1';
		var row = $('#install-row'), done = $('#installed-row');
		if (row) row.hidden = installed; if (done) done.hidden = !standalone;
		if (row && !installed && !show) row.hidden = false;
	}
	refreshInstall();
	$$('[data-install]').forEach(function (b) {
		b.addEventListener('click', function () {
			if (deferred) { deferred.prompt(); deferred.userChoice.then(function (c) { if (c.outcome === 'accepted') { LS.set('axInstalled', '1'); installed = true; } deferred = null; refreshInstall(); }); return; }
			var s = $('#sheet-install');
			$$('[data-os]', s).forEach(function (el) { el.hidden = true; });
			if (isIOS) { $('[data-os="ios"]', s).hidden = false; if (!isSafari) $('[data-os="ios-notsafari"]', s).hidden = false; }
			else { $('[data-os="other"]', s).hidden = false; }
			openSheet('install');
		});
	});

	/* ── Notifications ── */
	var reg = null;
	function b64ToU8(s) { s = s.replace(/-/g, '+').replace(/_/g, '/'); s += '==='.slice((s.length + 3) % 4); var r = atob(s), a = new Uint8Array(r.length); for (var i = 0; i < r.length; i++) a[i] = r.charCodeAt(i); return a; }
	function getSub() { return reg ? reg.pushManager.getSubscription() : Promise.resolve(null); }
	function pushState() {
		if (!pushOK) return Promise.resolve(isIOS && !standalone ? 'needs-install' : 'unsupported');
		if (Notification.permission === 'denied') return Promise.resolve('denied');
		return getSub().then(function (s) { return s ? 'on' : 'off'; });
	}
	function enablePush() {
		if (isIOS && !standalone) { toast('First add the app to your Home Screen'); $('[data-install]') && $('[data-install]').click(); return Promise.resolve('needs-install'); }
		if (!pushOK) { toast('This browser can\'t show notifications'); return Promise.resolve('unsupported'); }
		return Notification.requestPermission().then(function (p) {
			if (p !== 'granted') { toast(p === 'denied' ? 'Notifications are blocked in your phone settings' : 'Notifications not turned on'); return p; }
			return reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: b64ToU8(C.vapid) }).then(function (s) {
				return post(C.base + 'push', { do: 'subscribe', endpoint: s.endpoint }).then(function () { haptic(); toast('Notifications are on'); LS.set('axNotifyAsked', '1'); return 'on'; });
			});
		}).catch(function () { toast('Could not turn on notifications'); return 'off'; });
	}
	function disablePush() {
		return getSub().then(function (s) { if (!s) return 'off'; var ep = s.endpoint; return s.unsubscribe().then(function () { return post(C.base + 'push', { do: 'unsubscribe', endpoint: ep }); }).then(function () { toast('Notifications are off'); return 'off'; }); });
	}
	function paint(state) {
		var bell = $('#bell'); if (bell) { bell.hidden = !pushOK && !isIOS; bell.classList.toggle('alert', state === 'off'); bell.setAttribute('aria-label', state === 'on' ? 'Notifications are on' : 'Turn on notifications'); }
		var card = $('#p-notify'); if (card) card.hidden = !(state === 'off' || state === 'needs-install') || LS.get('axHideNotify') === '1' || ($('#p-install') && !$('#p-install').hidden && state === 'needs-install');
		var tg = $('#n-toggle'), st = $('#n-state'), help = $('#n-help'), test = $('#n-test'), pref = $('#n-pref-row');
		if (tg) {
			tg.checked = state === 'on'; tg.disabled = state === 'unsupported' || state === 'denied';
			var txt = { on: 'On for this device', off: 'Off', denied: 'Blocked in phone settings', unsupported: 'Not supported in this browser', 'needs-install': 'Add the app to your Home Screen first' };
			st.textContent = txt[state] || '';
			help.textContent = state === 'denied' ? 'To allow notifications: open your phone Settings → ' + (isIOS ? 'Notifications → Inbox' : 'Apps → your browser → Notifications') + ', then come back.' : (state === 'needs-install' ? 'On iPhone, notifications work in the Home Screen app (iOS 16.4 or newer).' : '');
			if (test) test.hidden = state !== 'on'; if (pref) pref.hidden = state !== 'on';
		}
	}
	function refreshPush() { return pushState().then(function (s) { paint(s); return s; }); }

	if ('serviceWorker' in navigator) {
		navigator.serviceWorker.register(C.base + 'sw', { scope: C.base }).then(function (r) { reg = r; return navigator.serviceWorker.ready; }).then(function (r) { reg = r; refreshPush(); }).catch(function () { paint('unsupported'); });
	} else { paint(isIOS && !standalone ? 'needs-install' : 'unsupported'); }

	$$('[data-notify-on]').forEach(function (b) { b.addEventListener('click', function () { enablePush().then(refreshPush); }); });
	var bellBtn = $('#bell'); if (bellBtn) bellBtn.addEventListener('click', function () { pushState().then(function (s) { if (s === 'on') location.href = C.base + '?view=more'; else enablePush().then(refreshPush); }); });
	var tgl = $('#n-toggle'); if (tgl) tgl.addEventListener('change', function () { (tgl.checked ? enablePush() : disablePush()).then(refreshPush); });
	var prefSel = $('#n-pref'); if (prefSel) prefSel.addEventListener('change', function () { post(C.base + 'push', { do: 'pref', pref: prefSel.value }).then(function () { toast('Saved'); }); });
	var testBtn = $('#n-test'); if (testBtn) testBtn.addEventListener('click', function () { getSub().then(function (s) { if (!s) return; toast('Sending…'); post(C.base + 'push', { do: 'test', endpoint: s.endpoint }).then(function (d) { toast(d.ok ? 'Sent — it should arrive in a few seconds' : 'Push service did not accept it (' + d.code + ')'); }); }); });
	$$('[data-dismiss]').forEach(function (b) { b.addEventListener('click', function () { var k = b.dataset.dismiss; LS.set(k === 'install' ? 'axHideInstall' : 'axHideNotify', '1'); b.closest('.prompt').hidden = true; }); });
})();
</script>
<?php }
