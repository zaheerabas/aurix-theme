<?php
/**
 * AURIX INBOX — one place for every message the site receives.
 *
 * Distributor signups, Pioneer Program / offer code applications, wholesale
 * quotes, Medical Kit Builder RFQs, contact form, product questions,
 * newsletter, VIP waitlist, back-in-stock alerts and supplier listings all
 * land in a single table and a single admin screen (WP Admin → Inbox).
 *
 * Theme forms call aurix_inbox_add() directly. Notification emails sent to
 * the admin by any plugin (Medical Kit Builder, Offer Codes Manager, …) are
 * captured automatically, so those messages show up here too.
 *
 * @package Aurix
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'AURIX_INBOX_DB_VER', '1.0' );

/* ─────────────────────────────────────────────────────────────────────────
   1. MESSAGE TYPES — label, colour and a plain "what they want" line
   ───────────────────────────────────────────────────────────────────────── */
function aurix_inbox_types() {
	return array(
		'distributor' => array( 'label' => 'Distributor Partner', 'color' => '#7c3aed', 'icon' => 'dashicons-groups',        'intent' => 'Wants to become a distribution partner' ),
		'pioneer'     => array( 'label' => 'Pioneer / Offer Code', 'color' => '#b8925a', 'icon' => 'dashicons-tickets-alt',  'intent' => 'Applied for a Pioneer Program offer code' ),
		'quote'       => array( 'label' => 'Wholesale Quote',      'color' => '#0369a1', 'icon' => 'dashicons-cart',         'intent' => 'Wants a wholesale price quote' ),
		'kit'         => array( 'label' => 'Kit Builder RFQ',      'color' => '#0f766e', 'icon' => 'dashicons-portfolio',    'intent' => 'Built a custom instrument kit and wants a quote' ),
		'contact'     => array( 'label' => 'Contact Inquiry',      'color' => '#0d1b2a', 'icon' => 'dashicons-email-alt',    'intent' => 'Sent a message through the contact form' ),
		'question'    => array( 'label' => 'Product Question',     'color' => '#c2410c', 'icon' => 'dashicons-editor-help',  'intent' => 'Has a question about a product' ),
		'newsletter'  => array( 'label' => 'Newsletter',           'color' => '#16a34a', 'icon' => 'dashicons-megaphone',    'intent' => 'Subscribed to the newsletter' ),
		'waitlist'    => array( 'label' => 'VIP Waitlist',         'color' => '#a16207', 'icon' => 'dashicons-star-filled',  'intent' => 'Joined the VIP waitlist for early access' ),
		'stock'       => array( 'label' => 'Back-in-Stock',        'color' => '#be123c', 'icon' => 'dashicons-bell',         'intent' => 'Wants an alert when a product is back in stock' ),
		'supplier'    => array( 'label' => 'Supplier Listing',     'color' => '#4338ca', 'icon' => 'dashicons-building',     'intent' => 'Asked to be listed in the supplier directory' ),
		'other'       => array( 'label' => 'Other',                'color' => '#64748b', 'icon' => 'dashicons-admin-comments', 'intent' => 'Other message' ),
	);
}

function aurix_inbox_type( $key ) {
	$t = aurix_inbox_types();
	return $t[ $key ] ?? $t['other'];
}

function aurix_inbox_statuses() {
	return array(
		'new'      => 'New',
		'read'     => 'Read',
		'replied'  => 'Replied',
		'archived' => 'Archived',
		'spam'     => 'Spam',
	);
}

/* ─────────────────────────────────────────────────────────────────────────
   2. DATABASE TABLE
   ───────────────────────────────────────────────────────────────────────── */
function aurix_inbox_table() {
	global $wpdb;
	return $wpdb->prefix . 'aurix_inbox';
}

function aurix_inbox_install() {
	if ( get_option( 'aurix_inbox_db_ver' ) === AURIX_INBOX_DB_VER ) return;
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table   = aurix_inbox_table();
	$charset = $wpdb->get_charset_collate();
	dbDelta( "CREATE TABLE $table (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		type varchar(32) NOT NULL DEFAULT 'other',
		status varchar(16) NOT NULL DEFAULT 'new',
		name varchar(190) NOT NULL DEFAULT '',
		email varchar(190) NOT NULL DEFAULT '',
		phone varchar(64) NOT NULL DEFAULT '',
		company varchar(190) NOT NULL DEFAULT '',
		country varchar(100) NOT NULL DEFAULT '',
		subject varchar(255) NOT NULL DEFAULT '',
		message longtext NULL,
		data longtext NULL,
		notes longtext NULL,
		source varchar(255) NOT NULL DEFAULT '',
		ref varchar(190) NOT NULL DEFAULT '',
		ip varchar(64) NOT NULL DEFAULT '',
		created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY  (id),
		KEY type_status (type,status),
		KEY email (email),
		KEY ref (ref),
		KEY created_at (created_at)
	) $charset;" );
	update_option( 'aurix_inbox_db_ver', AURIX_INBOX_DB_VER );
}
add_action( 'after_switch_theme', 'aurix_inbox_install' );
add_action( 'init', 'aurix_inbox_install', 5 );

/* ─────────────────────────────────────────────────────────────────────────
   3. ADD A MESSAGE — used by every form in the theme
   ───────────────────────────────────────────────────────────────────────── */
/**
 * @param string $type   One of aurix_inbox_types() keys.
 * @param array  $args   name, email, phone, company, country, subject, message,
 *                       data (array of extra labelled fields), source, ref,
 *                       created_at, status.
 * @return int|false     New row id, existing id (duplicate) or false.
 */
function aurix_inbox_add( $type, $args = array() ) {
	global $wpdb;
	aurix_inbox_install();

	$types = aurix_inbox_types();
	$type  = isset( $types[ $type ] ) ? $type : 'other';
	$now   = current_time( 'mysql' );

	$row = array(
		'type'       => $type,
		'status'     => isset( aurix_inbox_statuses()[ $args['status'] ?? '' ] ) ? $args['status'] : 'new',
		'name'       => mb_substr( sanitize_text_field( $args['name'] ?? '' ), 0, 190 ),
		'email'      => mb_substr( sanitize_email( $args['email'] ?? '' ), 0, 190 ),
		'phone'      => mb_substr( sanitize_text_field( $args['phone'] ?? '' ), 0, 64 ),
		'company'    => mb_substr( sanitize_text_field( $args['company'] ?? '' ), 0, 190 ),
		'country'    => mb_substr( sanitize_text_field( $args['country'] ?? '' ), 0, 100 ),
		'subject'    => mb_substr( sanitize_text_field( $args['subject'] ?? '' ), 0, 255 ),
		'message'    => sanitize_textarea_field( $args['message'] ?? '' ),
		'data'       => wp_json_encode( aurix_inbox_clean_data( $args['data'] ?? array() ) ),
		'notes'      => wp_json_encode( array() ),
		'source'     => mb_substr( esc_url_raw( $args['source'] ?? aurix_inbox_current_source() ), 0, 255 ),
		'ref'        => mb_substr( sanitize_text_field( $args['ref'] ?? '' ), 0, 190 ),
		'ip'         => isset( $args['ip'] ) ? sanitize_text_field( $args['ip'] ) : aurix_inbox_ip(),
		'created_at' => ! empty( $args['created_at'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( $args['created_at'] ) ) : $now,
		'updated_at' => $now,
	);

	// One newsletter / waitlist / stock entry per email (per product for stock).
	if ( in_array( $type, array( 'newsletter', 'waitlist', 'stock' ), true ) && $row['email'] ) {
		$existing = $wpdb->get_var( $wpdb->prepare(
			'SELECT id FROM ' . aurix_inbox_table() . ' WHERE type = %s AND email = %s AND ref = %s LIMIT 1',
			$type, $row['email'], $row['ref']
		) );
		if ( $existing ) return (int) $existing;
	}
	// Imports carry a unique ref — never import the same record twice.
	if ( $row['ref'] && 0 === strpos( $row['ref'], 'import:' ) ) {
		$existing = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . aurix_inbox_table() . ' WHERE ref = %s LIMIT 1', $row['ref'] ) );
		if ( $existing ) return (int) $existing;
	}

	if ( ! $wpdb->insert( aurix_inbox_table(), $row ) ) return false;

	$GLOBALS['aurix_inbox_logged_this_request'] = true;
	delete_transient( 'aurix_inbox_unread' );
	$new_id = (int) $wpdb->insert_id;
	if ( function_exists( 'aurix_push_new_message' ) && 'new' === $row['status'] ) aurix_push_new_message( $new_id );
	return $new_id;
}

function aurix_inbox_clean_data( $data ) {
	$out = array();
	foreach ( (array) $data as $k => $v ) {
		if ( '' === $v || null === $v || array() === $v ) continue;
		if ( is_array( $v ) || is_object( $v ) ) $v = implode( ', ', array_map( 'strval', array_filter( (array) $v, 'is_scalar' ) ) );
		$out[ sanitize_text_field( (string) $k ) ] = sanitize_textarea_field( (string) $v );
	}
	return $out;
}

function aurix_inbox_current_source() {
	$ref = wp_get_referer();
	if ( ! $ref && ! wp_doing_ajax() && isset( $_SERVER['REQUEST_URI'] ) ) $ref = home_url( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	// Never show admin-ajax / REST / wp-admin as the page the visitor was on.
	if ( ! $ref || preg_match( '#/wp-admin/|/wp-json/|admin-ajax\.php#', $ref ) ) return '';
	return $ref;
}

function aurix_inbox_ip() {
	return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
}

function aurix_inbox_get( $id ) {
	global $wpdb;
	$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . aurix_inbox_table() . ' WHERE id = %d', $id ), ARRAY_A );
	if ( ! $row ) return null;
	$row['data']  = json_decode( (string) $row['data'], true ) ?: array();
	$row['notes'] = json_decode( (string) $row['notes'], true ) ?: array();
	return $row;
}

function aurix_inbox_set_status( $ids, $status ) {
	global $wpdb;
	if ( ! isset( aurix_inbox_statuses()[ $status ] ) ) return;
	$ids = array_filter( array_map( 'absint', (array) $ids ) );
	if ( ! $ids ) return;
	$in = implode( ',', $ids );
	$wpdb->query( $wpdb->prepare( 'UPDATE ' . aurix_inbox_table() . " SET status = %s, updated_at = %s WHERE id IN ($in)", $status, current_time( 'mysql' ) ) );
	delete_transient( 'aurix_inbox_unread' );
}

function aurix_inbox_delete( $ids ) {
	global $wpdb;
	$ids = array_filter( array_map( 'absint', (array) $ids ) );
	if ( ! $ids ) return;
	$wpdb->query( 'DELETE FROM ' . aurix_inbox_table() . ' WHERE id IN (' . implode( ',', $ids ) . ')' );
	delete_transient( 'aurix_inbox_unread' );
}

function aurix_inbox_add_note( $id, $kind, $text ) {
	global $wpdb;
	$row = aurix_inbox_get( $id );
	if ( ! $row ) return;
	$user          = wp_get_current_user();
	$row['notes'][] = array(
		'kind' => $kind, // reply | note
		'by'   => $user && $user->exists() ? $user->display_name : 'System',
		'at'   => current_time( 'mysql' ),
		'text' => $text,
	);
	$wpdb->update( aurix_inbox_table(), array( 'notes' => wp_json_encode( $row['notes'] ), 'updated_at' => current_time( 'mysql' ) ), array( 'id' => $id ) );
}

function aurix_inbox_unread_count() {
	$n = get_transient( 'aurix_inbox_unread' );
	if ( false === $n ) {
		global $wpdb;
		$n = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . aurix_inbox_table() . " WHERE status = 'new'" );
		set_transient( 'aurix_inbox_unread', $n, 5 * MINUTE_IN_SECONDS );
	}
	return (int) $n;
}

/* ─────────────────────────────────────────────────────────────────────────
   4. CATCH PLUGIN NOTIFICATIONS
   Any visitor-triggered email to the admin that wasn't already saved by a
   theme form (Medical Kit Builder, Offer Codes Manager, …) is saved here.
   ───────────────────────────────────────────────────────────────────────── */
add_filter( 'wp_mail', 'aurix_inbox_capture_mail', 1 );
function aurix_inbox_capture_mail( $atts ) {
	if ( ! get_option( 'aurix_inbox_capture', 1 ) ) return $atts;
	if ( ! empty( $GLOBALS['aurix_inbox_logged_this_request'] ) ) return $atts; // theme form already saved it
	if ( ! empty( $GLOBALS['aurix_inbox_sending'] ) ) return $atts;             // our own reply
	if ( wp_doing_cron() ) return $atts;
	// Only visitor actions: front-end requests or AJAX/REST calls, not admin screens.
	$is_rest = defined( 'REST_REQUEST' ) && REST_REQUEST;
	if ( is_admin() && ! wp_doing_ajax() ) return $atts;
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) && ! $is_rest ) return $atts;

	$to = is_array( $atts['to'] ) ? $atts['to'] : explode( ',', (string) $atts['to'] );
	$to = array_map( 'strtolower', array_map( 'trim', $to ) );
	$admins = array_filter( array_map( 'strtolower', array(
		get_option( 'admin_email' ),
		get_option( 'aurix_inbox_notify_email' ),
		get_theme_mod( 'aurix_cf_admin_email' ),
		get_option( 'woocommerce_email_from_address' ),
	) ) );
	$to_admin = false;
	foreach ( $to as $addr ) {
		if ( preg_match( '/<([^>]+)>/', $addr, $m ) ) $addr = $m[1];
		if ( in_array( trim( $addr ), $admins, true ) ) { $to_admin = true; break; }
	}
	if ( ! $to_admin ) return $atts;

	$subject = wp_strip_all_tags( (string) $atts['subject'] );
	// Skip WordPress / WooCommerce system mail.
	if ( preg_match( '/new order|order #|password|new user registration|login|backup|update|cancel+ed order|failed order|refund|low stock|out of stock|security|plugin|theme/i', $subject ) ) {
		return $atts;
	}

	$body    = (string) $atts['message'];
	$is_html = false !== stripos( $body, '<html' ) || false !== stripos( $body, '<table' ) || false !== stripos( $body, '<br' ) || false !== stripos( $body, '<p' );
	if ( $is_html ) {
		$body = preg_replace( '#<(style|script|head)[^>]*>.*?</\1>#is', '', $body );
		$body = preg_replace( '#<(br|/p|/tr|/div|/h\d|/li)[^>]*>#i', "\n", $body );
		$body = preg_replace( '#</t[dh]>\s*<t[dh][^>]*>#i', ': ', $body );
		$body = html_entity_decode( wp_strip_all_tags( $body ), ENT_QUOTES, 'UTF-8' );
	}
	$body = trim( preg_replace( "/\n\s*\n+/", "\n\n", preg_replace( "/[ \t]+/", ' ', $body ) ) );

	// Sender email: Reply-To header first, then first non-admin address in the body.
	$email   = '';
	$headers = is_array( $atts['headers'] ) ? implode( "\n", $atts['headers'] ) : (string) $atts['headers'];
	if ( preg_match( '/^reply-to:\s*(.+)$/im', $headers, $m ) && preg_match( '/[\w.+\-]+@[\w\-]+\.[\w.\-]+/', $m[1], $e ) ) {
		$email = $e[0];
	}
	if ( ! $email && preg_match_all( '/[\w.+\-]+@[\w\-]+\.[\w.\-]+/', $body, $all ) ) {
		foreach ( $all[0] as $cand ) {
			if ( ! in_array( strtolower( $cand ), $admins, true ) ) { $email = $cand; break; }
		}
	}
	$name = '';
	if ( preg_match( '/^\s*(?:full\s*)?name\s*[:\-]\s*(.+)$/im', $body, $m ) ) $name = trim( $m[1] );
	// Logged-in visitor (e.g. Pioneer Program applications): use their account details.
	if ( is_user_logged_in() ) {
		$u = wp_get_current_user();
		if ( ! $email ) $email = $u->user_email;
		if ( ! $name && strtolower( $email ) === strtolower( $u->user_email ) ) $name = trim( $u->first_name . ' ' . $u->last_name ) ?: $u->display_name;
	}
	$phone = '';
	if ( preg_match( '/^\s*(?:phone|tel|mobile|whatsapp)\s*[:\-]\s*(.+)$/im', $body, $m ) ) $phone = trim( $m[1] );
	$company = '';
	if ( preg_match( '/^\s*(?:company|business|organi[sz]ation|clinic)(?:\s*name)?\s*[:\-]\s*(.+)$/im', $body, $m ) ) $company = trim( $m[1] );
	$country = '';
	if ( preg_match( '/^\s*country\s*[:\-]\s*(.+)$/im', $body, $m ) ) $country = trim( $m[1] );

	aurix_inbox_add( aurix_inbox_guess_type( $subject . ' ' . mb_substr( $body, 0, 400 ) ), array(
		'name'    => $name,
		'email'   => $email,
		'phone'   => $phone,
		'company' => $company,
		'country' => $country,
		'subject' => $subject,
		'message' => $body,
		'data'    => array( 'Captured from' => 'Plugin notification email' ),
	) );
	return $atts;
}

function aurix_inbox_guess_type( $text ) {
	$t = strtolower( $text );
	if ( preg_match( '/pioneer|offer.?code|offer_?codes?|\bocm|promo|coupon|discount code/', $t ) ) return 'pioneer';
	if ( preg_match( '/kit.?builder|medical.?kit|custom kit|\bmkb|\bkit\b|_kit/', $t ) ) return 'kit';
	if ( preg_match( '/distribut|partner|reseller|dealer/', $t ) )                      return 'distributor';
	if ( preg_match( '/newsletter|subscrib/', $t ) )                                    return 'newsletter';
	if ( preg_match( '/waitlist|wait list/', $t ) )                                     return 'waitlist';
	if ( preg_match( '/back in stock|back-in-stock|restock/', $t ) )                    return 'stock';
	if ( preg_match( '/rfq|quote|quotation|wholesale|bulk/', $t ) )                     return 'quote';
	if ( preg_match( '/question|faq/', $t ) )                                           return 'question';
	if ( preg_match( '/supplier|listing|directory/', $t ) )                             return 'supplier';
	if ( preg_match( '/contact|inquiry|enquiry|message/', $t ) )                        return 'contact';
	return 'other';
}

/* ─────────────────────────────────────────────────────────────────────────
   5. GENERIC FRONT-END FORM ENDPOINT
   Lets any Elementor HTML widget / newsletter box post straight to the Inbox:
   action=aurix_inbox_submit, nonce=aurixData.inboxNonce, type, name, email…
   ───────────────────────────────────────────────────────────────────────── */
function aurix_inbox_submit() {
	if ( ! check_ajax_referer( 'aurix_inbox_submit', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'Please reload the page and try again.' ) );
	}
	if ( ! empty( $_POST['website_url'] ) ) { // honeypot
		wp_send_json_success( array( 'message' => 'Thank you!' ) );
	}
	$rl_key = 'aurix_inbox_rl_' . md5( aurix_inbox_ip() );
	$count  = (int) get_transient( $rl_key );
	if ( $count >= 8 ) {
		wp_send_json_error( array( 'message' => 'Too many submissions. Please try again later.' ) );
	}

	$allowed = array( 'newsletter', 'distributor', 'contact', 'quote', 'question', 'other' );
	$type    = sanitize_key( wp_unslash( $_POST['type'] ?? 'other' ) );
	if ( ! in_array( $type, $allowed, true ) ) $type = 'other';

	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => 'Please enter a valid email address.' ) );
	}

	$core  = array( 'action', 'nonce', 'type', 'name', 'email', 'phone', 'company', 'country', 'subject', 'message', 'website_url' );
	$extra = array();
	foreach ( $_POST as $k => $v ) {
		if ( in_array( $k, $core, true ) ) continue;
		$label           = ucwords( str_replace( array( '_', '-' ), ' ', sanitize_key( $k ) ) );
		$extra[ $label ] = is_array( $v ) ? implode( ', ', array_map( 'sanitize_text_field', wp_unslash( $v ) ) ) : sanitize_textarea_field( wp_unslash( $v ) );
		if ( count( $extra ) >= 25 ) break;
	}

	$id = aurix_inbox_add( $type, array(
		'name'    => wp_unslash( $_POST['name'] ?? '' ),
		'email'   => $email,
		'phone'   => wp_unslash( $_POST['phone'] ?? '' ),
		'company' => wp_unslash( $_POST['company'] ?? '' ),
		'country' => wp_unslash( $_POST['country'] ?? '' ),
		'subject' => wp_unslash( $_POST['subject'] ?? '' ),
		'message' => wp_unslash( $_POST['message'] ?? '' ),
		'data'    => $extra,
	) );
	set_transient( $rl_key, $count + 1, HOUR_IN_SECONDS );

	if ( $id && 'newsletter' !== $type ) {
		aurix_inbox_notify_admin( $id );
	}
	wp_send_json_success( array( 'message' => 'newsletter' === $type ? 'You are subscribed. Thank you!' : 'Thank you! We will get back to you shortly.' ) );
}
add_action( 'wp_ajax_aurix_inbox_submit', 'aurix_inbox_submit' );
add_action( 'wp_ajax_nopriv_aurix_inbox_submit', 'aurix_inbox_submit' );

/** Short admin alert linking straight to the message in the Inbox. */
function aurix_inbox_notify_admin( $id ) {
	$row = aurix_inbox_get( $id );
	if ( ! $row ) return;
	$t  = aurix_inbox_type( $row['type'] );
	$to = get_option( 'aurix_inbox_notify_email' ) ?: get_option( 'admin_email' );
	$GLOBALS['aurix_inbox_sending'] = true;
	wp_mail(
		$to,
		'[' . $t['label'] . '] ' . ( $row['name'] ?: $row['email'] ),
		$t['intent'] . ".\n\nFrom: " . trim( $row['name'] . ' <' . $row['email'] . '>' ) . "\n"
		. ( $row['company'] ? 'Company: ' . $row['company'] . "\n" : '' )
		. ( $row['message'] ? "\n" . $row['message'] . "\n" : '' )
		. "\nOpen in Inbox: " . admin_url( 'admin.php?page=aurix-inbox&view=' . $id ),
		array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $row['email'] )
	);
	$GLOBALS['aurix_inbox_sending'] = false;
}

/** Nonce for the generic endpoint, available to theme JS as aurixData.inboxNonce. */
add_action( 'wp_enqueue_scripts', function () {
	wp_add_inline_script( 'aurix-main', 'window.aurixData=window.aurixData||{};aurixData.inboxNonce=' . wp_json_encode( wp_create_nonce( 'aurix_inbox_submit' ) ) . ';aurixData.ajaxUrl=aurixData.ajaxUrl||' . wp_json_encode( admin_url( 'admin-ajax.php' ) ) . ';', 'after' );
}, 100 );

/**
 * [aurix_newsletter] — compact newsletter box that saves to the Inbox.
 * Attributes: title, button, placeholder.
 */
add_shortcode( 'aurix_newsletter', function ( $atts ) {
	$a   = shortcode_atts( array( 'title' => '', 'button' => 'Subscribe', 'placeholder' => 'Your email address' ), $atts );
	$uid = 'axnl-' . wp_rand( 1000, 99999 );
	ob_start(); ?>
	<form class="axnl-form" id="<?php echo esc_attr( $uid ); ?>" novalidate>
		<?php if ( $a['title'] ) : ?><p class="axnl-title"><?php echo esc_html( $a['title'] ); ?></p><?php endif; ?>
		<div class="axnl-row">
			<input type="email" name="email" required placeholder="<?php echo esc_attr( $a['placeholder'] ); ?>" aria-label="Email address" autocomplete="email">
			<input type="text" name="website_url" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
			<button type="submit"><?php echo esc_html( $a['button'] ); ?></button>
		</div>
		<p class="axnl-msg" role="status" aria-live="polite"></p>
	</form>
	<style>
	.axnl-form{max-width:460px;position:relative}.axnl-title{font-weight:600;margin:0 0 8px}
	.axnl-row{display:flex;gap:8px}.axnl-row input[type=email]{flex:1;min-width:0;padding:12px 14px;border:1px solid #d6dbe3;border-radius:8px;font-size:15px}
	.axnl-row button{padding:12px 20px;border:0;border-radius:8px;background:#0d1b2a;color:#fff;font-weight:600;cursor:pointer}
	.axnl-row button:hover{background:#734608}.axnl-msg{font-size:13px;margin:8px 0 0;min-height:1em}
	@media(max-width:480px){.axnl-row{flex-direction:column}}
	</style>
	<script>
	(function(){var f=document.getElementById(<?php echo wp_json_encode( $uid ); ?>);if(!f)return;
	f.addEventListener('submit',function(e){e.preventDefault();var m=f.querySelector('.axnl-msg'),b=f.querySelector('button'),em=f.email.value.trim();
	if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)){m.textContent='Please enter a valid email address.';return;}
	b.disabled=true;var d=new URLSearchParams({action:'aurix_inbox_submit',type:'newsletter',email:em,website_url:f.website_url.value,nonce:<?php echo wp_json_encode( wp_create_nonce( 'aurix_inbox_submit' ) ); ?>});
	fetch(<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,{method:'POST',body:d}).then(function(r){return r.json();}).then(function(r){
	m.textContent=(r&&r.data&&r.data.message)||'Something went wrong.';if(r&&r.success){f.email.value='';}b.disabled=false;}).catch(function(){m.textContent='Something went wrong. Please try again.';b.disabled=false;});});})();
	</script>
	<?php
	return ob_get_clean();
} );

/* ─────────────────────────────────────────────────────────────────────────
   6. ADMIN MENU, BADGE, ADMIN BAR, DASHBOARD WIDGET
   ───────────────────────────────────────────────────────────────────────── */
add_action( 'admin_menu', function () {
	$n     = aurix_inbox_unread_count() + ( function_exists( 'aurix_team_pending' ) && current_user_can( 'manage_options' ) ? count( aurix_team_pending() ) : 0 );
	$badge = $n ? ' <span class="awaiting-mod count-' . $n . '"><span class="pending-count">' . number_format_i18n( $n ) . '</span></span>' : '';
	add_menu_page( 'Inbox', 'Inbox' . $badge, 'aurix_inbox', 'aurix-inbox', 'aurix_inbox_page', 'dashicons-email-alt', 3 );
	add_submenu_page( 'aurix-inbox', 'All Messages', 'All Messages', 'aurix_inbox', 'aurix-inbox', 'aurix_inbox_page' );
	add_submenu_page( 'aurix-inbox', 'Inbox Settings & Import', 'Settings & Import', 'manage_options', 'aurix-inbox-settings', 'aurix_inbox_settings_page' );

	// The old VIP Waitlist screen is replaced by Inbox → VIP Waitlist.
	remove_menu_page( 'aurix-waitlist' );
}, 99 );

add_action( 'admin_bar_menu', function ( $bar ) {
	if ( ! current_user_can( 'aurix_inbox' ) ) return;
	$n = aurix_inbox_unread_count();
	$bar->add_node( array(
		'id'    => 'aurix-inbox',
		'title' => '<span class="ab-icon dashicons dashicons-email-alt" style="top:2px"></span><span class="ab-label">' . ( $n ? $n . ' new' : 'Inbox' ) . '</span>',
		'href'  => wp_is_mobile() ? home_url( '/inbox-app/' ) : admin_url( 'admin.php?page=aurix-inbox' ),
	) );
}, 80 );

add_action( 'wp_dashboard_setup', function () {
	if ( ! current_user_can( 'aurix_inbox' ) ) return;
	wp_add_dashboard_widget( 'aurix_inbox_widget', 'Inbox — latest messages', function () {
		global $wpdb;
		$rows = $wpdb->get_results( 'SELECT id,type,status,name,email,subject,created_at FROM ' . aurix_inbox_table() . " WHERE status NOT IN ('spam','archived') ORDER BY created_at DESC LIMIT 6", ARRAY_A );
		if ( ! $rows ) { echo '<p>No messages yet.</p>'; return; }
		echo '<ul style="margin:0">';
		foreach ( $rows as $r ) {
			$t = aurix_inbox_type( $r['type'] );
			printf(
				'<li style="display:flex;gap:8px;align-items:center;padding:6px 0;border-bottom:1px solid #f0f0f1"><span style="background:%1$s;color:#fff;font-size:10px;font-weight:600;padding:2px 7px;border-radius:10px;white-space:nowrap">%2$s</span><a href="%3$s" style="flex:1;%4$s">%5$s</a><span style="color:#8c8f94;font-size:11px">%6$s</span></li>',
				esc_attr( $t['color'] ), esc_html( $t['label'] ),
				esc_url( admin_url( 'admin.php?page=aurix-inbox&view=' . $r['id'] ) ),
				'new' === $r['status'] ? 'font-weight:700' : '',
				esc_html( $r['name'] ?: $r['email'] ?: '(no name)' ),
				esc_html( human_time_diff( strtotime( $r['created_at'] ), current_time( 'timestamp' ) ) )
			);
		}
		echo '</ul><p style="margin:10px 0 0"><a class="button" href="' . esc_url( admin_url( 'admin.php?page=aurix-inbox' ) ) . '">Open Inbox</a></p>';
	} );
} );

/* ─────────────────────────────────────────────────────────────────────────
   7. ADMIN ACTIONS (bulk, single, reply, note, export)
   ───────────────────────────────────────────────────────────────────────── */
add_action( 'admin_init', function () {
	if ( empty( $_REQUEST['page'] ) || 'aurix-inbox' !== $_REQUEST['page'] || ! current_user_can( 'aurix_inbox' ) ) return;

	// CSV export of the current filter.
	if ( isset( $_GET['export'] ) ) {
		check_admin_referer( 'aurix_inbox_export' );
		aurix_inbox_export_csv( aurix_inbox_query_args() );
		exit;
	}

	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['aurix_inbox_action'] ) ) return;
	check_admin_referer( 'aurix_inbox_action' );

	$action = sanitize_key( $_POST['aurix_inbox_action'] );
	$ids    = array_map( 'absint', (array) ( $_POST['ids'] ?? array() ) );
	$back   = wp_validate_redirect( (string) wp_get_raw_referer(), admin_url( 'admin.php?page=aurix-inbox' ) );
	$notice = '';

	switch ( $action ) {
		case 'bulk':
			$do = sanitize_key( $_POST['bulk'] ?? '' );
			if ( 'delete' === $do ) { if ( current_user_can( 'manage_options' ) ) { aurix_inbox_delete( $ids ); $notice = 'deleted'; } }
			elseif ( isset( aurix_inbox_statuses()[ $do ] ) ) { aurix_inbox_set_status( $ids, $do ); $notice = 'updated'; }
			break;

		case 'status':
			aurix_inbox_set_status( $ids, sanitize_key( $_POST['status'] ?? '' ) );
			$notice = 'updated';
			break;

		case 'delete':
			if ( ! current_user_can( 'manage_options' ) ) break;
			aurix_inbox_delete( $ids );
			$back   = admin_url( 'admin.php?page=aurix-inbox' );
			$notice = 'deleted';
			break;

		case 'note':
			$text = sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) );
			if ( $ids && $text ) { aurix_inbox_add_note( $ids[0], 'note', $text ); $notice = 'noted'; }
			break;

		case 'reply':
			$row  = $ids ? aurix_inbox_get( $ids[0] ) : null;
			$text = sanitize_textarea_field( wp_unslash( $_POST['reply'] ?? '' ) );
			$subj = sanitize_text_field( wp_unslash( $_POST['reply_subject'] ?? '' ) );
			if ( $row && $text ) {
				$notice = aurix_inbox_send_reply( $row, $subj, $text ) ? 'replied' : 'mailfail';
			}
			break;
	}
	wp_safe_redirect( add_query_arg( 'ax_notice', $notice, remove_query_arg( 'ax_notice', $back ) ) );
	exit;
} );

/** Email a reply to the sender, log it in the conversation and mark the message replied. */
function aurix_inbox_send_reply( $row, $subj, $text ) {
	if ( ! is_email( $row['email'] ) || '' === trim( $text ) ) return false;
	$from_name  = get_theme_mod( 'aurix_cf_from_name', get_bloginfo( 'name' ) );
	$from_email = get_option( 'aurix_inbox_from_email' ) ?: get_option( 'admin_email' );
	$GLOBALS['aurix_inbox_sending'] = true;
	$sent = wp_mail(
		$row['email'],
		$subj ?: 'Re: ' . ( $row['subject'] ?: aurix_inbox_type( $row['type'] )['label'] ),
		aurix_inbox_reply_html( $text, $row ),
		array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . $from_name . ' <' . $from_email . '>', 'Reply-To: ' . $from_email )
	);
	$GLOBALS['aurix_inbox_sending'] = false;
	if ( $sent ) {
		aurix_inbox_add_note( $row['id'], 'reply', $text );
		aurix_inbox_set_status( $row['id'], 'replied' );
	}
	return (bool) $sent;
}

/** One plain-English line: what does this person want? */
function aurix_inbox_summary( $r ) {
	$t = aurix_inbox_type( $r['type'] );
	$d = is_array( $r['data'] ) ? $r['data'] : ( json_decode( (string) $r['data'], true ) ?: array() );
	$summary = $t['intent'];
	if ( 'quote' === $r['type'] && ! empty( $d['Product'] ) )   $summary = 'Wants a wholesale quote for ' . $d['Product'] . ( ! empty( $d['Quantity'] ) ? ' (qty ' . $d['Quantity'] . ')' : '' );
	if ( 'question' === $r['type'] && ! empty( $d['Product'] ) ) $summary = 'Has a question about ' . $d['Product'];
	if ( 'stock' === $r['type'] && ! empty( $d['Product'] ) )    $summary = 'Wants an alert when ' . $d['Product'] . ' is back in stock';
	if ( 'contact' === $r['type'] && ! empty( $d['Inquiry type'] ) ) $summary = ucfirst( $d['Inquiry type'] ) . ' inquiry: ' . $r['subject'];
	if ( 'supplier' === $r['type'] && ! empty( $d['Wants to be an Aurix distributor'] ) && 'Yes' === $d['Wants to be an Aurix distributor'] ) $summary .= ' — and wants to become an Aurix distributor';
	return $summary;
}

function aurix_inbox_reply_html( $text, $row ) {
	$site = get_bloginfo( 'name' );
	return '<!DOCTYPE html><html><body style="margin:0;background:#f6f7f9;font-family:Arial,sans-serif">'
		. '<table width="100%" cellpadding="0" cellspacing="0" style="padding:32px 16px"><tr><td align="center">'
		. '<table width="580" cellpadding="0" cellspacing="0" style="max-width:580px;background:#fff;border-radius:12px;overflow:hidden">'
		. '<tr><td style="background:#0d1b2a;padding:22px 32px;font-family:Georgia,serif;font-size:20px;font-weight:700;color:#b8925a">' . esc_html( $site ) . '</td></tr>'
		. '<tr><td style="padding:30px 32px;font-size:15px;line-height:1.7;color:#1e293b">'
		. ( $row['name'] ? '<p style="margin:0 0 14px">Dear ' . esc_html( $row['name'] ) . ',</p>' : '' )
		. wpautop( esc_html( $text ) )
		. '</td></tr><tr><td style="background:#f8fafc;border-top:1px solid #e8edf5;padding:14px 32px;font-size:12px;color:#94a3b8">'
		. esc_html( $site ) . ' &middot; ' . esc_html( preg_replace( '#^https?://#', '', home_url() ) ) . '</td></tr></table></td></tr></table></body></html>';
}

function aurix_inbox_query_args() {
	return array(
		'type'   => sanitize_key( $_GET['type'] ?? '' ),
		'status' => sanitize_key( $_GET['status'] ?? '' ),
		's'      => sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) ),
		'paged'  => max( 1, absint( $_GET['paged'] ?? 1 ) ),
	);
}

function aurix_inbox_where( $q ) {
	global $wpdb;
	$w = array( '1=1' );
	if ( $q['type'] && isset( aurix_inbox_types()[ $q['type'] ] ) ) $w[] = $wpdb->prepare( 'type = %s', $q['type'] );
	if ( $q['status'] && isset( aurix_inbox_statuses()[ $q['status'] ] ) ) {
		$w[] = $wpdb->prepare( 'status = %s', $q['status'] );
	} else {
		$w[] = "status NOT IN ('archived','spam')"; // default view hides archived + spam
	}
	if ( $q['s'] ) {
		$like = '%' . $wpdb->esc_like( $q['s'] ) . '%';
		$w[]  = $wpdb->prepare( '(name LIKE %s OR email LIKE %s OR company LIKE %s OR subject LIKE %s OR message LIKE %s OR phone LIKE %s)', $like, $like, $like, $like, $like, $like );
	}
	return implode( ' AND ', $w );
}

function aurix_inbox_export_csv( $q ) {
	global $wpdb;
	$rows = $wpdb->get_results( 'SELECT * FROM ' . aurix_inbox_table() . ' WHERE ' . aurix_inbox_where( $q ) . ' ORDER BY created_at DESC', ARRAY_A );
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=aurix-inbox-' . ( $q['type'] ?: 'all' ) . '-' . gmdate( 'Y-m-d' ) . '.csv' );
	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" );
	fputcsv( $out, array( 'ID', 'Type', 'Status', 'Date', 'Name', 'Email', 'Phone', 'Company', 'Country', 'Subject', 'Message', 'Details', 'Source' ) );
	foreach ( $rows as $r ) {
		$d = json_decode( (string) $r['data'], true ) ?: array();
		$details = array();
		foreach ( $d as $k => $v ) $details[] = "$k: $v";
		fputcsv( $out, array( $r['id'], aurix_inbox_type( $r['type'] )['label'], $r['status'], $r['created_at'], $r['name'], $r['email'], $r['phone'], $r['company'], $r['country'], $r['subject'], $r['message'], implode( ' | ', $details ), $r['source'] ) );
	}
	fclose( $out );
}

/* ─────────────────────────────────────────────────────────────────────────
   8. ADMIN SCREEN
   ───────────────────────────────────────────────────────────────────────── */
function aurix_inbox_admin_css() { ?>
	<style>
	.axi{--navy:#0d1b2a;--gold:#b8925a;--line:#e5e7eb;--muted:#64748b;margin:18px 20px 0 0;font-size:14px}
	.axi *{box-sizing:border-box}
	.axi-head{display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:16px}
	.axi-head h1{margin:0;font-size:24px;font-weight:700;color:var(--navy)}
	.axi-head .axi-sub{color:var(--muted)}
	.axi-head .axi-actions{margin-left:auto;display:flex;gap:8px;flex-wrap:wrap}
	.axi-grid{display:grid;grid-template-columns:240px 1fr;gap:18px;align-items:start}
	.axi-side{background:#fff;border:1px solid var(--line);border-radius:12px;padding:8px;position:sticky;top:46px}
	.axi-side a{display:flex;align-items:center;gap:9px;padding:9px 10px;border-radius:8px;color:#1f2937;text-decoration:none}
	.axi-side a:hover{background:#f6f7f9}.axi-side a.on{background:var(--navy);color:#fff}
	.axi-side a .dashicons{font-size:17px;width:17px;height:17px}
	.axi-side .cnt{margin-left:auto;font-size:12px;color:var(--muted)}.axi-side a.on .cnt{color:#cbd5e1}
	.axi-side .new{background:#d63638;color:#fff;font-size:11px;font-weight:700;border-radius:10px;padding:1px 7px;margin-left:4px}
	.axi-side hr{border:0;border-top:1px solid var(--line);margin:6px 4px}
	.axi-card{background:#fff;border:1px solid var(--line);border-radius:12px;overflow:hidden}
	.axi-bar{display:flex;gap:8px;align-items:center;flex-wrap:wrap;padding:12px;border-bottom:1px solid var(--line);background:#fafbfc}
	.axi-bar input[type=search]{min-width:240px}
	.axi-bar .axi-pages{margin-left:auto;color:var(--muted);display:flex;gap:6px;align-items:center}
	.axi-row{display:grid;grid-template-columns:28px 150px 1fr 110px 90px;gap:12px;align-items:center;padding:12px 14px;border-bottom:1px solid #f1f2f4;cursor:pointer}
	.axi-row:hover{background:#f8fafc}.axi-row.is-new{background:#fffdf6}.axi-row.is-new .axi-who{font-weight:700}
	.axi-row:last-child{border-bottom:0}
	.axi-pill{display:inline-flex;align-items:center;gap:4px;color:#fff;font-size:11px;font-weight:600;padding:3px 9px;border-radius:12px;white-space:nowrap}
	.axi-who{color:var(--navy);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
	.axi-what{color:var(--muted);font-size:13px;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
	.axi-date{color:var(--muted);font-size:12px;text-align:right}
	.axi-st{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--muted)}
	.axi-st.new{color:#d63638}.axi-st.replied{color:#16a34a}
	.axi-empty{padding:50px 20px;text-align:center;color:var(--muted)}
	.axi-detail{display:grid;grid-template-columns:1fr 320px;gap:18px;align-items:start}
	.axi-intent{padding:22px 24px;border-bottom:1px solid var(--line)}
	.axi-intent h2{margin:10px 0 4px;font-size:21px;color:var(--navy)}
	.axi-intent p{margin:0;color:var(--muted)}
	.axi-sec{padding:18px 24px;border-bottom:1px solid var(--line)}.axi-sec:last-child{border-bottom:0}
	.axi-sec h3{margin:0 0 10px;font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:var(--muted)}
	.axi-msg{white-space:pre-wrap;line-height:1.65;color:#1f2937;background:#f8fafc;border-radius:8px;padding:14px 16px;margin:0}
	.axi-fields{width:100%;border-collapse:collapse}.axi-fields td{padding:7px 0;border-bottom:1px solid #f1f2f4;vertical-align:top}
	.axi-fields td:first-child{color:var(--muted);width:38%;padding-right:10px}
	.axi-contact a{display:flex;gap:8px;align-items:center;padding:8px 0;text-decoration:none;word-break:break-all}
	.axi-thread .it{border-left:3px solid var(--gold);padding:8px 12px;margin-bottom:10px;background:#fdfaf4;border-radius:0 8px 8px 0}
	.axi-thread .it.note{border-color:#94a3b8;background:#f8fafc}
	.axi-thread .meta{font-size:12px;color:var(--muted);margin-bottom:4px}
	.axi textarea{width:100%;min-height:120px}
	.axi-tabs{display:flex;gap:6px;margin-bottom:8px}.axi-tabs button{border:1px solid var(--line);background:#fff;border-radius:6px;padding:5px 12px;cursor:pointer}
	.axi-tabs button.on{background:var(--navy);color:#fff;border-color:var(--navy)}
	.axi-stack>*{margin-bottom:8px}
	@media(max-width:1100px){.axi-grid,.axi-detail{grid-template-columns:1fr}.axi-side{position:static;display:flex;flex-wrap:wrap}.axi-side a{flex:1 1 45%}.axi-side hr{display:none}}
	@media(max-width:782px){
		.axi-row{grid-template-columns:24px 1fr 64px;row-gap:4px}
		.axi-row>input{grid-row:1/span 2}.axi-row .axi-type{grid-column:2;grid-row:1}
		.axi-row>span:nth-of-type(2){grid-column:2;grid-row:2}
		.axi-row .axi-stc{display:none}.axi-row .axi-date{grid-column:3;grid-row:1/span 2}
		.axi-bar input[type=search]{min-width:0;flex:1 1 100%;order:3}
	}
	</style>
<?php }

function aurix_inbox_notice() {
	$map = array(
		'updated'  => array( 'success', 'Messages updated.' ),
		'deleted'  => array( 'success', 'Deleted.' ),
		'noted'    => array( 'success', 'Note saved.' ),
		'replied'  => array( 'success', 'Reply sent and saved to this conversation.' ),
		'mailfail' => array( 'error', 'The reply could not be sent. Check GoSMTP settings.' ),

		'saved'    => array( 'success', 'Settings saved.' ),
	);
	$k = sanitize_key( $_GET['ax_notice'] ?? '' );
	if ( isset( $map[ $k ] ) ) {
		printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr( $map[ $k ][0] ), esc_html( $map[ $k ][1] ) );
	}
	if ( isset( $_GET['ax_count'] ) ) {
		$c = absint( $_GET['ax_count'] );
		printf( '<div class="notice notice-info is-dismissible"><p>%s</p></div>', esc_html( $c ? $c . ' new records added to the Inbox.' : 'Nothing new to import — everything is already in the Inbox.' ) );
	}
}

function aurix_inbox_page() {
	if ( ! current_user_can( 'aurix_inbox' ) ) return;
	aurix_inbox_admin_css();
	echo '<div class="axi">';
	aurix_inbox_notice();
	if ( ! empty( $_GET['view'] ) ) {
		aurix_inbox_detail( absint( $_GET['view'] ) );
	} else {
		aurix_inbox_list();
	}
	echo '</div>';
}

function aurix_inbox_list() {
	global $wpdb;
	$table    = aurix_inbox_table();
	$q        = aurix_inbox_query_args();
	$per_page = 30;
	$where    = aurix_inbox_where( $q );
	$total    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE $where" );
	$offset   = ( $q['paged'] - 1 ) * $per_page;
	$rows     = $wpdb->get_results( "SELECT * FROM $table WHERE $where ORDER BY created_at DESC, id DESC LIMIT $offset, $per_page", ARRAY_A );
	$pages    = max( 1, (int) ceil( $total / $per_page ) );

	$counts = array();
	foreach ( $wpdb->get_results( "SELECT type, COUNT(*) c, SUM(status='new') n FROM $table WHERE status NOT IN ('archived','spam') GROUP BY type", ARRAY_A ) as $c ) {
		$counts[ $c['type'] ] = $c;
	}
	$all_c = array_sum( wp_list_pluck( $counts, 'c' ) );
	$all_n = array_sum( wp_list_pluck( $counts, 'n' ) );
	$st_c  = $wpdb->get_results( "SELECT status, COUNT(*) c FROM $table GROUP BY status", OBJECT_K );
	$base  = admin_url( 'admin.php?page=aurix-inbox' );
	$types = aurix_inbox_types();
	?>
	<div class="axi-head">
		<h1>Inbox</h1>
		<span class="axi-sub"><?php echo $all_n ? esc_html( $all_n . ' unread' ) : 'All caught up'; ?></span>
		<div class="axi-actions">
			<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array_filter( array( 'type' => $q['type'], 'status' => $q['status'], 's' => $q['s'], 'export' => 1 ) ), $base ), 'aurix_inbox_export' ) ); ?>"><span class="dashicons dashicons-download" style="margin-top:4px"></span> Export CSV</a>
			<?php if ( current_user_can( 'manage_options' ) ) : ?><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=aurix-inbox-settings' ) ); ?>">Settings &amp; Import</a><?php endif; ?>
			<button type="button" class="button button-primary" id="axi-phone-btn" aria-expanded="false" aria-controls="axi-phone"><span class="dashicons dashicons-smartphone" style="margin-top:4px"></span> Open on phone</button>
		</div>
	</div>

	<div id="axi-phone" class="axi-card" hidden style="margin-bottom:16px"><div class="axi-sec" style="display:flex;gap:20px;align-items:center;flex-wrap:wrap">
		<div id="axi-phone-qr" role="img" aria-label="QR code to open the Inbox on a phone" style="width:156px;height:156px;padding:4px;background:#fff;border:1px solid #e5e7eb;border-radius:8px"></div>
		<div style="flex:1;min-width:240px">
			<h3>Scan to open the Inbox on a phone</h3>
			<p style="margin:0 0 6px">Point the phone camera at this code. New people fill in their name and ask for access — you'll see the request at the top of this page to <strong>Accept</strong> or <strong>Decline</strong>.</p>
			<p style="margin:0 0 10px;color:#64748b">Accepted people can only use the Inbox — not WordPress admin, orders, products or settings. Nobody sees a message before you accept.</p>
			<?php if ( current_user_can( 'manage_options' ) ) : ?>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=aurix-inbox-settings#team' ) ); ?>">Give someone access</a>
				<span class="description" style="margin-left:6px">— they get a personal QR code that signs them in without a password.</span>
			<?php endif; ?>
		</div>
	</div></div>
	<script src="<?php echo esc_url( get_template_directory_uri() . '/js/qrcode.min.js' ); ?>"></script>
	<script>(function(){var b=document.getElementById('axi-phone-btn'),p=document.getElementById('axi-phone'),done=false;b.addEventListener('click',function(){p.hidden=!p.hidden;b.setAttribute('aria-expanded',String(!p.hidden));if(!p.hidden&&!done&&window.aurixQR){done=true;aurixQR(document.getElementById('axi-phone-qr'),<?php echo wp_json_encode( home_url( '/inbox-app/' ) ); ?>,148);}});})();</script>

	<?php
	if ( function_exists( 'aurix_team_requests_panel' ) ) {
		$tm = get_transient( 'aurix_team_msg_' . get_current_user_id() );
		if ( $tm ) { delete_transient( 'aurix_team_msg_' . get_current_user_id() ); echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $tm ) . '</p></div>'; }
		aurix_team_requests_panel();
	}
	?>
	<div class="axi-grid">
		<nav class="axi-side" aria-label="Message types">
			<a class="<?php echo ! $q['type'] && ! $q['status'] ? 'on' : ''; ?>" href="<?php echo esc_url( $base ); ?>">
				<span class="dashicons dashicons-email-alt"></span>All messages
				<span class="cnt"><?php echo (int) $all_c; ?><?php if ( $all_n ) echo '<span class="new">' . (int) $all_n . '</span>'; ?></span>
			</a>
			<hr>
			<?php foreach ( $types as $key => $t ) :
				$c = $counts[ $key ]['c'] ?? 0;
				$n = $counts[ $key ]['n'] ?? 0;
				if ( ! $c && 'other' === $key ) continue; ?>
				<a class="<?php echo $q['type'] === $key ? 'on' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'type', $key, $base ) ); ?>">
					<span class="dashicons <?php echo esc_attr( $t['icon'] ); ?>" style="color:<?php echo $q['type'] === $key ? '#fff' : esc_attr( $t['color'] ); ?>"></span>
					<?php echo esc_html( $t['label'] ); ?>
					<span class="cnt"><?php echo (int) $c; ?><?php if ( $n ) echo '<span class="new">' . (int) $n . '</span>'; ?></span>
				</a>
			<?php endforeach; ?>
			<hr>
			<?php foreach ( array( 'replied' => 'dashicons-yes-alt', 'archived' => 'dashicons-archive', 'spam' => 'dashicons-shield' ) as $sk => $ic ) : ?>
				<a class="<?php echo $q['status'] === $sk ? 'on' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'status', $sk, $base ) ); ?>">
					<span class="dashicons <?php echo esc_attr( $ic ); ?>"></span><?php echo esc_html( aurix_inbox_statuses()[ $sk ] ); ?>
					<span class="cnt"><?php echo (int) ( $st_c[ $sk ]->c ?? 0 ); ?></span>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="axi-card">
			<form method="get" class="axi-bar">
				<input type="hidden" name="page" value="aurix-inbox">
				<?php if ( $q['type'] ) : ?><input type="hidden" name="type" value="<?php echo esc_attr( $q['type'] ); ?>"><?php endif; ?>
				<select name="status">
					<option value="">Open (not archived)</option>
					<?php foreach ( aurix_inbox_statuses() as $sk => $sl ) : ?>
						<option value="<?php echo esc_attr( $sk ); ?>" <?php selected( $q['status'], $sk ); ?>><?php echo esc_html( $sl ); ?></option>
					<?php endforeach; ?>
				</select>
				<input type="search" name="s" value="<?php echo esc_attr( $q['s'] ); ?>" placeholder="Search name, email, company, message…">
				<button class="button">Filter</button>
				<span class="axi-pages">
					<?php echo (int) $total; ?> messages
					<?php if ( $pages > 1 ) : ?>
						&middot; page <?php echo (int) $q['paged']; ?> of <?php echo (int) $pages; ?>
						<?php if ( $q['paged'] > 1 ) : ?><a class="button button-small" href="<?php echo esc_url( add_query_arg( 'paged', $q['paged'] - 1 ) ); ?>">&lsaquo;</a><?php endif; ?>
						<?php if ( $q['paged'] < $pages ) : ?><a class="button button-small" href="<?php echo esc_url( add_query_arg( 'paged', $q['paged'] + 1 ) ); ?>">&rsaquo;</a><?php endif; ?>
					<?php endif; ?>
				</span>
			</form>

			<form method="post" id="axi-bulk">
				<?php wp_nonce_field( 'aurix_inbox_action' ); ?>
				<input type="hidden" name="aurix_inbox_action" value="bulk">
				<div class="axi-bar" style="background:#fff">
					<label><input type="checkbox" id="axi-all"> Select all</label>
					<select name="bulk">
						<option value="read">Mark as read</option>
						<option value="new">Mark as unread</option>
						<option value="replied">Mark as replied</option>
						<option value="archived">Archive</option>
						<option value="spam">Mark as spam</option>
						<?php if ( current_user_can( 'manage_options' ) ) : ?><option value="delete">Delete permanently</option><?php endif; ?>
					</select>
					<button class="button" onclick="var s=this.form.bulk.value;if(!this.form.querySelector('input[name=&quot;ids[]&quot;]:checked')){alert('Select at least one message.');return false;}return s!=='delete'||confirm('Delete the selected messages permanently?');">Apply</button>
				</div>

				<?php if ( ! $rows ) : ?>
					<div class="axi-empty"><span class="dashicons dashicons-email-alt" style="font-size:40px;width:40px;height:40px;color:#cbd5e1"></span><p>No messages here.</p></div>
				<?php endif; ?>

				<?php foreach ( $rows as $r ) :
					$t    = aurix_inbox_type( $r['type'] );
					$who  = $r['name'] ?: ( $r['company'] ?: ( $r['email'] ?: '(no name)' ) );
					$what = $r['subject'] ?: wp_trim_words( (string) $r['message'], 16 );
					if ( ! $what ) $what = $t['intent'];
					$url  = add_query_arg( 'view', $r['id'], $base ); ?>
					<div class="axi-row <?php echo 'new' === $r['status'] ? 'is-new' : ''; ?>" data-href="<?php echo esc_url( $url ); ?>">
						<input type="checkbox" name="ids[]" value="<?php echo (int) $r['id']; ?>" onclick="event.stopPropagation()" aria-label="Select message">
						<span class="axi-type"><span class="axi-pill" style="background:<?php echo esc_attr( $t['color'] ); ?>"><?php echo esc_html( $t['label'] ); ?></span></span>
						<span style="min-width:0">
							<a class="axi-who" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $who ); ?><?php if ( $r['company'] && $r['name'] ) echo ' <span style="font-weight:400;color:#64748b">· ' . esc_html( $r['company'] ) . '</span>'; ?></a>
							<span class="axi-what"><?php echo esc_html( $what ); ?></span>
						</span>
						<span class="axi-stc"><span class="axi-st <?php echo esc_attr( $r['status'] ); ?>"><?php echo esc_html( aurix_inbox_statuses()[ $r['status'] ] ?? $r['status'] ); ?></span></span>
						<span class="axi-date" title="<?php echo esc_attr( $r['created_at'] ); ?>"><?php echo esc_html( aurix_inbox_when( $r['created_at'] ) ); ?></span>
					</div>
				<?php endforeach; ?>
			</form>
		</div>
	</div>
	<script>
	document.querySelectorAll('.axi-row').forEach(function(r){r.addEventListener('click',function(e){if(e.target.closest('a,input'))return;location.href=r.dataset.href;});});
	var all=document.getElementById('axi-all');if(all)all.addEventListener('change',function(){document.querySelectorAll('#axi-bulk input[name="ids[]"]').forEach(function(c){c.checked=all.checked;});});
	</script>
	<?php
}

function aurix_inbox_when( $mysql ) {
	$ts  = strtotime( $mysql );
	$now = current_time( 'timestamp' );
	if ( $now - $ts < DAY_IN_SECONDS ) return human_time_diff( $ts, $now ) . ' ago';
	if ( gmdate( 'Y', $ts ) === gmdate( 'Y', $now ) ) return date_i18n( 'M j', $ts );
	return date_i18n( 'M j, Y', $ts );
}

function aurix_inbox_detail( $id ) {
	global $wpdb;
	$r = aurix_inbox_get( $id );
	if ( ! $r ) { echo '<p>Message not found. <a href="' . esc_url( admin_url( 'admin.php?page=aurix-inbox' ) ) . '">Back to Inbox</a></p>'; return; }
	if ( 'new' === $r['status'] ) { aurix_inbox_set_status( $id, 'read' ); $r['status'] = 'read'; }

	$t     = aurix_inbox_type( $r['type'] );
	$table = aurix_inbox_table();
	$prev  = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE created_at > %s AND status NOT IN ('archived','spam') ORDER BY created_at ASC LIMIT 1", $r['created_at'] ) );
	$next  = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE created_at < %s AND status NOT IN ('archived','spam') ORDER BY created_at DESC LIMIT 1", $r['created_at'] ) );
	$other = $r['email'] ? $wpdb->get_results( $wpdb->prepare( "SELECT id,type,subject,created_at FROM $table WHERE email = %s AND id <> %d ORDER BY created_at DESC LIMIT 8", $r['email'], $id ), ARRAY_A ) : array();
	$base  = admin_url( 'admin.php?page=aurix-inbox' );
	$wa    = preg_replace( '/\D+/', '', $r['phone'] );
	$who   = $r['name'] ?: ( $r['company'] ?: $r['email'] );

	$summary = aurix_inbox_summary( $r );
	$d       = $r['data'];
	?>
	<div class="axi-head">
		<a class="button" href="<?php echo esc_url( add_query_arg( 'type', $r['type'], $base ) ); ?>">&larr; <?php echo esc_html( $t['label'] ); ?></a>
		<a class="button" href="<?php echo esc_url( $base ); ?>">All messages</a>
		<div class="axi-actions">
			<?php if ( $prev ) : ?><a class="button" href="<?php echo esc_url( add_query_arg( 'view', $prev, $base ) ); ?>">&lsaquo; Newer</a><?php endif; ?>
			<?php if ( $next ) : ?><a class="button" href="<?php echo esc_url( add_query_arg( 'view', $next, $base ) ); ?>">Older &rsaquo;</a><?php endif; ?>
		</div>
	</div>

	<div class="axi-detail">
		<div class="axi-card">
			<div class="axi-intent">
				<span class="axi-pill" style="background:<?php echo esc_attr( $t['color'] ); ?>"><span class="dashicons <?php echo esc_attr( $t['icon'] ); ?>" style="font-size:13px;width:13px;height:13px"></span><?php echo esc_html( $t['label'] ); ?></span>
				<span class="axi-st <?php echo esc_attr( $r['status'] ); ?>" style="margin-left:8px"><?php echo esc_html( aurix_inbox_statuses()[ $r['status'] ] ?? '' ); ?></span>
				<h2><?php echo esc_html( $summary ); ?></h2>
				<p><?php echo esc_html( $who ); ?> &middot; <?php echo esc_html( date_i18n( 'M j, Y \a\t g:i a', strtotime( $r['created_at'] ) ) ); ?></p>
			</div>

			<?php if ( $r['subject'] || $r['message'] ) : ?>
			<div class="axi-sec">
				<h3>Message</h3>
				<?php if ( $r['subject'] ) : ?><p style="font-weight:600;margin:0 0 8px"><?php echo esc_html( $r['subject'] ); ?></p><?php endif; ?>
				<?php if ( $r['message'] ) : ?><p class="axi-msg"><?php echo esc_html( $r['message'] ); ?></p><?php endif; ?>
			</div>
			<?php endif; ?>

			<?php if ( $d ) : ?>
			<div class="axi-sec">
				<h3>Details</h3>
				<table class="axi-fields">
					<?php foreach ( $d as $k => $v ) : ?>
						<tr><td><?php echo esc_html( $k ); ?></td><td><?php echo preg_match( '#^https?://#', $v ) ? '<a href="' . esc_url( $v ) . '" target="_blank" rel="noopener">' . esc_html( $v ) . '</a>' : nl2br( esc_html( $v ) ); ?></td></tr>
					<?php endforeach; ?>
				</table>
			</div>
			<?php endif; ?>

			<?php if ( $r['notes'] ) : ?>
			<div class="axi-sec axi-thread">
				<h3>Conversation &amp; notes</h3>
				<?php foreach ( $r['notes'] as $n ) : ?>
					<div class="it <?php echo 'note' === ( $n['kind'] ?? '' ) ? 'note' : ''; ?>">
						<div class="meta"><strong><?php echo 'reply' === ( $n['kind'] ?? '' ) ? 'Reply sent' : 'Internal note'; ?></strong> &middot; <?php echo esc_html( ( $n['by'] ?? '' ) . ' · ' . ( $n['at'] ?? '' ) ); ?></div>
						<?php echo nl2br( esc_html( $n['text'] ?? '' ) ); ?>
					</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<div class="axi-sec">
				<div class="axi-tabs" role="tablist">
					<?php if ( is_email( $r['email'] ) ) : ?><button type="button" class="on" data-tab="reply">Reply by email</button><?php endif; ?>
					<button type="button" class="<?php echo is_email( $r['email'] ) ? '' : 'on'; ?>" data-tab="note">Internal note</button>
				</div>
				<?php if ( is_email( $r['email'] ) ) : ?>
				<form method="post" data-pane="reply">
					<?php wp_nonce_field( 'aurix_inbox_action' ); ?>
					<input type="hidden" name="aurix_inbox_action" value="reply">
					<input type="hidden" name="ids[]" value="<?php echo (int) $id; ?>">
					<p style="margin:0 0 6px;color:#64748b">To: <?php echo esc_html( $r['email'] ); ?></p>
					<input type="text" name="reply_subject" class="widefat" style="margin-bottom:8px" value="<?php echo esc_attr( 'Re: ' . ( $r['subject'] ?: $t['label'] . ' — Aurix International' ) ); ?>">
					<textarea name="reply" required placeholder="Write your reply… (sent from your site email with the Aurix header)"></textarea>
					<p><button class="button button-primary">Send reply</button></p>
				</form>
				<?php endif; ?>
				<form method="post" data-pane="note" style="<?php echo is_email( $r['email'] ) ? 'display:none' : ''; ?>">
					<?php wp_nonce_field( 'aurix_inbox_action' ); ?>
					<input type="hidden" name="aurix_inbox_action" value="note">
					<input type="hidden" name="ids[]" value="<?php echo (int) $id; ?>">
					<textarea name="note" required placeholder="Only your team sees this note."></textarea>
					<p><button class="button">Save note</button></p>
				</form>
			</div>
		</div>

		<div class="axi-stack">
			<div class="axi-card"><div class="axi-sec axi-contact">
				<h3>Contact</h3>
				<p style="font-size:16px;font-weight:600;margin:0 0 4px;color:#0d1b2a"><?php echo esc_html( $r['name'] ?: '—' ); ?></p>
				<?php if ( $r['company'] ) : ?><p style="margin:0 0 6px;color:#64748b"><?php echo esc_html( $r['company'] ); ?><?php echo $r['country'] ? ' · ' . esc_html( $r['country'] ) : ''; ?></p><?php elseif ( $r['country'] ) : ?><p style="margin:0 0 6px;color:#64748b"><?php echo esc_html( $r['country'] ); ?></p><?php endif; ?>
				<?php if ( $r['email'] ) : ?><a href="mailto:<?php echo esc_attr( $r['email'] ); ?>"><span class="dashicons dashicons-email"></span><?php echo esc_html( $r['email'] ); ?></a><?php endif; ?>
				<?php if ( $r['phone'] ) : ?>
					<a href="tel:<?php echo esc_attr( $r['phone'] ); ?>"><span class="dashicons dashicons-phone"></span><?php echo esc_html( $r['phone'] ); ?></a>
					<?php if ( strlen( $wa ) >= 8 ) : ?><a href="https://wa.me/<?php echo esc_attr( $wa ); ?>" target="_blank" rel="noopener"><span class="dashicons dashicons-whatsapp"></span>WhatsApp</a><?php endif; ?>
				<?php endif; ?>
				<?php if ( $r['source'] ) : ?><a href="<?php echo esc_url( $r['source'] ); ?>" target="_blank" rel="noopener"><span class="dashicons dashicons-admin-links"></span>Sent from: <?php echo esc_html( wp_parse_url( $r['source'], PHP_URL_PATH ) ?: '/' ); ?></a><?php endif; ?>
			</div></div>

			<div class="axi-card"><div class="axi-sec">
				<h3>Status</h3>
				<form method="post" style="display:flex;gap:6px;flex-wrap:wrap">
					<?php wp_nonce_field( 'aurix_inbox_action' ); ?>
					<input type="hidden" name="aurix_inbox_action" value="status">
					<input type="hidden" name="ids[]" value="<?php echo (int) $id; ?>">
					<?php foreach ( array( 'new' => 'Mark unread', 'replied' => 'Replied', 'archived' => 'Archive', 'spam' => 'Spam' ) as $sk => $sl ) :
						if ( $sk === $r['status'] ) continue; ?>
						<button class="button" name="status" value="<?php echo esc_attr( $sk ); ?>"><?php echo esc_html( $sl ); ?></button>
					<?php endforeach; ?>
				</form>
<?php if ( current_user_can( 'manage_options' ) ) : ?>
				<form method="post" style="margin-top:10px" onsubmit="return confirm('Delete this message permanently?');">
					<?php wp_nonce_field( 'aurix_inbox_action' ); ?>
					<input type="hidden" name="aurix_inbox_action" value="delete">
					<input type="hidden" name="ids[]" value="<?php echo (int) $id; ?>">
					<button class="button-link" style="color:#b32d2e">Delete permanently</button>
				</form>
				<?php endif; ?>
			</div></div>

			<?php if ( 'pioneer' === $r['type'] ) : ?>
			<div class="axi-card"><div class="axi-sec">
				<h3>Pioneer Program</h3>
				<p style="margin:0 0 8px;color:#64748b">Approve or reject the code application in the Pioneer Program screen.</p>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . aurix_inbox_find_admin_page( array( 'offer', 'pioneer' ) ) ) ); ?>">Open Pioneer Program</a>
			</div></div>
			<?php elseif ( ! empty( $d['Listing ID'] ) ) : ?>
			<div class="axi-card"><div class="axi-sec">
				<h3>Directory listing</h3>
				<a class="button" href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $d['Listing ID'] ) . '&action=edit' ) ); ?>">Review &amp; publish listing</a>
			</div></div>
			<?php endif; ?>

			<?php if ( $other ) : ?>
			<div class="axi-card"><div class="axi-sec">
				<h3>Other messages from this person</h3>
				<?php foreach ( $other as $o ) : $ot = aurix_inbox_type( $o['type'] ); ?>
					<a href="<?php echo esc_url( add_query_arg( 'view', $o['id'], $base ) ); ?>" style="display:flex;gap:8px;align-items:center;padding:6px 0;text-decoration:none">
						<span class="axi-pill" style="background:<?php echo esc_attr( $ot['color'] ); ?>"><?php echo esc_html( $ot['label'] ); ?></span>
						<span style="color:#64748b;font-size:12px;margin-left:auto"><?php echo esc_html( aurix_inbox_when( $o['created_at'] ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div></div>
			<?php endif; ?>
		</div>
	</div>
	<script>
	document.querySelectorAll('.axi-tabs button').forEach(function(b){b.addEventListener('click',function(){
		document.querySelectorAll('.axi-tabs button').forEach(function(x){x.classList.toggle('on',x===b);});
		document.querySelectorAll('[data-pane]').forEach(function(p){p.style.display=p.dataset.pane===b.dataset.tab?'':'none';});
	});});
	</script>
	<?php
}

/** Find a registered admin page slug containing one of the keywords (for plugin deep links). */
function aurix_inbox_find_admin_page( $keywords ) {
	global $menu, $submenu;
	$slugs = array();
	foreach ( (array) $menu as $m ) $slugs[] = $m[2] ?? '';
	foreach ( (array) $submenu as $items ) foreach ( $items as $s ) $slugs[] = $s[2] ?? '';
	foreach ( $slugs as $slug ) {
		foreach ( $keywords as $k ) {
			if ( $slug && false !== stripos( $slug, $k ) && false === strpos( $slug, '.php' ) ) return $slug;
		}
	}
	return 'aurix-inbox';
}

/* ─────────────────────────────────────────────────────────────────────────
   9. SETTINGS & IMPORT
   ───────────────────────────────────────────────────────────────────────── */
add_action( 'admin_init', function () {
	if ( ( $_REQUEST['page'] ?? '' ) !== 'aurix-inbox-settings' || 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! current_user_can( 'manage_options' ) ) return;
	if ( ! empty( $_POST['ax_team'] ) ) return; // handled in inbox-team.php
	check_admin_referer( 'aurix_inbox_settings' );
	$do   = sanitize_key( $_POST['ax_do'] ?? '' );
	$back = admin_url( 'admin.php?page=aurix-inbox-settings' );

	if ( 'save' === $do ) {
		update_option( 'aurix_inbox_capture', empty( $_POST['capture'] ) ? 0 : 1 );
		update_option( 'aurix_inbox_notify_email', sanitize_email( wp_unslash( $_POST['notify_email'] ?? '' ) ) );
		update_option( 'aurix_inbox_from_email', sanitize_email( wp_unslash( $_POST['from_email'] ?? '' ) ) );
		wp_safe_redirect( add_query_arg( 'ax_notice', 'saved', $back ) );
		exit;
	}
	global $wpdb;
	$GLOBALS['aurix_inbox_importing'] = true; // no phone notifications for imported history
	$before = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . aurix_inbox_table() );
	if ( 'import_theme' === $do ) {
		aurix_inbox_import_theme_data();
		$n = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . aurix_inbox_table() ) - $before;
		wp_safe_redirect( add_query_arg( array( 'ax_notice' => 'imported', 'ax_count' => $n ), $back ) );
		exit;
	}
	if ( 'import_oc' === $do ) {
		aurix_inbox_import_oc_codes();
		$n = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . aurix_inbox_table() ) - $before;
		wp_safe_redirect( add_query_arg( array( 'ax_notice' => 'imported', 'ax_count' => $n ), $back ) );
		exit;
	}
	if ( 'import_table' === $do ) {
		aurix_inbox_import_table( sanitize_text_field( wp_unslash( $_POST['table'] ?? '' ) ), sanitize_key( $_POST['as_type'] ?? 'other' ) );
		$n = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . aurix_inbox_table() ) - $before;
		wp_safe_redirect( add_query_arg( array( 'ax_notice' => 'imported', 'ax_count' => $n ), $back ) );
		exit;
	}
	if ( 'import_cpt' === $do ) {
		aurix_inbox_import_cpt( sanitize_key( $_POST['cpt'] ?? '' ), sanitize_key( $_POST['as_type'] ?? 'other' ) );
		$n = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . aurix_inbox_table() ) - $before;
		wp_safe_redirect( add_query_arg( array( 'ax_notice' => 'imported', 'ax_count' => $n ), $back ) );
		exit;
	}
} );

/** Import data the theme already stored: VIP waitlist, back-in-stock lists, submitted supplier listings. */
function aurix_inbox_import_theme_data() {
	global $wpdb;
	$n = 0;
	foreach ( (array) get_option( 'aurix_waitlist_emails', array() ) as $i => $e ) {
		if ( empty( $e['email'] ) ) continue;
		if ( aurix_inbox_add( 'waitlist', array( 'email' => $e['email'], 'created_at' => $e['date'] ?? '', 'status' => 'read', 'ref' => 'import:waitlist:' . md5( $e['email'] ), 'source' => home_url( '/' ), 'ip' => '' ) ) ) $n++;
	}
	$opts = $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'aurix\\_oos\\_waitlist\\_%'" );
	foreach ( $opts as $o ) {
		$pid  = (int) substr( $o->option_name, strlen( 'aurix_oos_waitlist_' ) );
		$name = $pid ? get_the_title( $pid ) : '';
		foreach ( (array) maybe_unserialize( $o->option_value ) as $e ) {
			if ( empty( $e['email'] ) ) continue;
			if ( aurix_inbox_add( 'stock', array(
				'email' => $e['email'], 'created_at' => $e['date'] ?? '', 'status' => 'read', 'ip' => '',
				'ref'   => 'import:stock:' . $pid . ':' . md5( $e['email'] ),
				'source'=> $pid ? get_permalink( $pid ) : '',
				'data'  => array( 'Product' => $name, 'Product ID' => $pid ),
			) ) ) $n++;
		}
	}
	if ( post_type_exists( 'aurix_supplier' ) ) {
		$posts = get_posts( array( 'post_type' => 'aurix_supplier', 'post_status' => array( 'pending', 'draft', 'publish' ), 'numberposts' => -1, 'meta_key' => '_axd_source', 'meta_value' => 'submitted' ) );
		foreach ( $posts as $p ) {
			$m = function ( $k ) use ( $p ) { return get_post_meta( $p->ID, '_axd_' . $k, true ); };
			if ( aurix_inbox_add( 'supplier', array(
				'name' => $m( 'contact' ), 'email' => $m( 'email' ), 'phone' => $m( 'phone' ), 'company' => $p->post_title, 'country' => $m( 'country' ),
				'message' => $p->post_excerpt, 'created_at' => $p->post_date, 'status' => 'pending' === $p->post_status ? 'new' : 'read',
				'ref' => 'import:supplier:' . $p->ID, 'ip' => '', 'source' => home_url( '/' ),
				'data' => array( 'Website' => $m( 'website' ), 'City / State' => trim( $m( 'city' ) . ', ' . $m( 'state' ), ', ' ), 'Company type' => $m( 'type' ), 'Focus' => str_replace( ';', ', ', $m( 'focus' ) ), 'Listing ID' => $p->ID, 'Listing status' => $p->post_status ),
			) ) ) $n++;
		}
	}
	return $n;
}

/** Tables from other plugins that look like they hold submissions. */
function aurix_inbox_candidate_tables() {
	global $wpdb;
	$out = array();
	foreach ( (array) $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $wpdb->prefix ) . '%' ) ) as $tbl ) {
		$short = substr( $tbl, strlen( $wpdb->prefix ) );
		if ( 'aurix_inbox' === $short ) continue;
		if ( preg_match( '/^(wc_|woocommerce_|actionscheduler_|yoast|wpforms_|litespeed|e_)/', $short ) ) continue;
		if ( ! preg_match( '/offer|pioneer|mkb|kit|rfq|quote|newsletter|subscri|distribut|lead|inquir|enquir|message|submission|application|waitlist/i', $short ) ) continue;
		$cols = $wpdb->get_col( "SHOW COLUMNS FROM `$tbl`" );
		if ( ! preg_grep( '/mail/i', $cols ) ) continue; // must have an email column to be useful
		$out[ $tbl ] = array( 'rows' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM `$tbl`" ), 'cols' => $cols, 'guess' => aurix_inbox_guess_type( $short ) );
	}
	return $out;
}

/** Post types from other plugins that look like submissions. */
/** Pioneer Program (Offer Codes Manager) keeps applications in {prefix}oc_codes, one row per WP user. */
function aurix_inbox_oc_table() {
	global $wpdb;
	$t = $wpdb->prefix . 'oc_codes';
	return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) === $t ? $t : '';
}

function aurix_inbox_import_oc_codes() {
	global $wpdb;
	$t = aurix_inbox_oc_table();
	if ( ! $t ) return 0;
	$n = 0;
	foreach ( (array) $wpdb->get_results( "SELECT * FROM `$t` ORDER BY id ASC LIMIT 5000", ARRAY_A ) as $r ) {
		$u    = ! empty( $r['user_id'] ) ? get_userdata( (int) $r['user_id'] ) : null;
		$meta = function ( $k ) use ( $u ) { return $u ? (string) get_user_meta( $u->ID, $k, true ) : ''; };
		$name = $u ? ( trim( $meta( 'first_name' ) . ' ' . $meta( 'last_name' ) ) ?: $u->display_name ) : '';
		$st   = (string) ( $r['status'] ?? '' );
		$data = array(
			'Application status' => ucfirst( $st ),
			'Code'               => $r['code'] ?? '',
			'Offer'              => ! empty( $r['offer_id'] ) ? get_the_title( (int) $r['offer_id'] ) : '',
			'Uses'               => isset( $r['usage_count'] ) ? $r['usage_count'] . ( ! empty( $r['max_uses'] ) ? ' of ' . $r['max_uses'] : '' ) : '',
			'Expires'            => $r['expiry_date'] ?? '',
			'Note'               => $r['note'] ?? '',
			'Customer account'   => $u ? admin_url( 'user-edit.php?user_id=' . $u->ID ) : '',
		);
		if ( aurix_inbox_add( 'pioneer', array(
			'name'       => $name,
			'email'      => $u ? $u->user_email : '',
			'phone'      => $meta( 'billing_phone' ),
			'company'    => $meta( 'billing_company' ),
			'country'    => $meta( 'billing_country' ),
			'subject'    => 'Pioneer Program application',
			'created_at' => $r['created_at'] ?? '',
			'status'     => 'pending' === $st ? 'new' : 'read',
			'ref'        => 'import:oc_codes:' . ( $r['id'] ?? md5( wp_json_encode( $r ) ) ),
			'ip'         => '',
			'source'     => '',
			'data'       => $data,
		) ) ) $n++;
	}
	return $n;
}

function aurix_inbox_candidate_cpts() {
	$out = array();
	foreach ( get_post_types( array(), 'objects' ) as $pt ) {
		if ( in_array( $pt->name, array( 'aurix_supplier', 'product', 'shop_order', 'shop_coupon', 'post', 'page', 'oc_offer' ), true ) ) continue;
		if ( ! preg_match( '/offer|pioneer|mkb|kit|rfq|quote|newsletter|subscri|distribut|lead|inquir|enquir|application|submission/i', $pt->name ) ) continue;
		$c = wp_count_posts( $pt->name );
		$total = 0;
		foreach ( (array) $c as $v ) $total += (int) $v;
		if ( $total ) $out[ $pt->name ] = array( 'label' => $pt->labels->name ?? $pt->name, 'rows' => $total, 'guess' => aurix_inbox_guess_type( $pt->name ) );
	}
	return $out;
}

/** Map a plugin row (column => value) onto Inbox fields. */
function aurix_inbox_map_row( $row ) {
	$pick = function ( $patterns ) use ( &$row ) {
		foreach ( $patterns as $p ) {
			foreach ( $row as $k => $v ) {
				if ( '' !== (string) $v && preg_match( $p, $k ) ) { unset( $row[ $k ] ); return (string) $v; }
			}
		}
		return '';
	};
	$id      = $pick( array( '/^id$/i' ) );
	$email   = $pick( array( '/^(customer_|user_|contact_)?e-?mail$/i', '/mail/i' ) );
	$first   = $pick( array( '/first_?name/i' ) );
	$last    = $pick( array( '/last_?name/i' ) );
	$name    = $pick( array( '/^(customer_|full_|contact_|user_)?name$/i', '/name$/i' ) );
	$phone   = $pick( array( '/phone|mobile|whatsapp|tel/i' ) );
	$company = $pick( array( '/company|business|clinic|organi[sz]ation/i' ) );
	$country = $pick( array( '/country/i' ) );
	$subject = $pick( array( '/subject|title/i' ) );
	$message = $pick( array( '/^message$/i', '/message|note|comment|description|details|reason|requirement/i' ) );
	$created = $pick( array( '/created|submitted|date_?added|applied|^date$|_at$|timestamp|time/i' ) );
	$status  = $pick( array( '/^status$/i' ) );
	$data    = array();
	foreach ( $row as $k => $v ) {
		if ( '' === (string) $v || null === $v ) continue;
		$val = maybe_unserialize( $v );
		if ( is_string( $val ) && preg_match( '/^\s*[\[{]/', $val ) ) {
			$j = json_decode( $val, true );
			if ( is_array( $j ) ) $val = $j;
		}
		if ( is_array( $val ) ) $val = aurix_inbox_pretty( $val );
		$data[ ucwords( str_replace( array( '_', '-' ), ' ', $k ) ) ] = mb_substr( (string) $val, 0, 2000 );
	}
	if ( $status ) $data = array( 'Original status' => $status ) + $data;
	return array(
		'id' => $id, 'email' => $email, 'name' => $name ?: trim( "$first $last" ), 'phone' => $phone, 'company' => $company,
		'country' => $country, 'subject' => $subject, 'message' => $message,
		'created_at' => ( $created && strtotime( is_numeric( $created ) ? '@' . $created : $created ) ) ? gmdate( 'Y-m-d H:i:s', is_numeric( $created ) ? (int) $created : strtotime( $created ) ) : '',
		'data' => $data,
	);
}

/** Turn nested plugin data (kit items, settings arrays) into readable lines. */
function aurix_inbox_pretty( $arr, $depth = 0 ) {
	$lines = array();
	$is_list = array_keys( $arr ) === range( 0, count( $arr ) - 1 );
	foreach ( $arr as $k => $v ) {
		if ( is_array( $v ) ) {
			$name = $v['name'] ?? $v['title'] ?? $v['product'] ?? $v['product_name'] ?? null;
			$qty  = $v['qty'] ?? $v['quantity'] ?? null;
			if ( $name && $depth < 3 ) {
				$lines[] = str_repeat( '  ', $depth ) . '- ' . $name . ( null !== $qty ? ' × ' . $qty : '' );
			} else {
				$lines[] = str_repeat( '  ', $depth ) . ( $is_list ? '-' : ucfirst( str_replace( '_', ' ', $k ) ) . ':' );
				$lines[] = aurix_inbox_pretty( $v, $depth + 1 );
			}
		} elseif ( '' !== (string) $v ) {
			$lines[] = str_repeat( '  ', $depth ) . ( $is_list ? '- ' : ucfirst( str_replace( '_', ' ', $k ) ) . ': ' ) . $v;
		}
	}
	return implode( "\n", array_filter( $lines, 'strlen' ) );
}

function aurix_inbox_import_table( $table, $type ) {
	global $wpdb;
	$cands = aurix_inbox_candidate_tables();
	if ( ! isset( $cands[ $table ] ) ) return 0;
	if ( ! isset( aurix_inbox_types()[ $type ] ) ) $type = $cands[ $table ]['guess'];
	$n = 0;
	$rows = $wpdb->get_results( "SELECT * FROM `$table` LIMIT 5000", ARRAY_A );
	foreach ( $rows as $i => $row ) {
		$m = aurix_inbox_map_row( $row );
		$m['data'] = array( 'Imported from' => $table ) + $m['data'];
		$m['ref']    = 'import:' . $table . ':' . ( $m['id'] ?: md5( wp_json_encode( $row ) ) );
		$m['status'] = 'read';
		$m['ip']     = '';
		$m['source'] = '';
		unset( $m['id'] );
		if ( aurix_inbox_add( $type, $m ) ) $n++;
	}
	return $n;
}

function aurix_inbox_import_cpt( $cpt, $type ) {
	$cands = aurix_inbox_candidate_cpts();
	if ( ! isset( $cands[ $cpt ] ) ) return 0;
	if ( ! isset( aurix_inbox_types()[ $type ] ) ) $type = $cands[ $cpt ]['guess'];
	$n = 0;
	foreach ( get_posts( array( 'post_type' => $cpt, 'post_status' => 'any', 'numberposts' => 5000 ) ) as $p ) {
		$row = array( 'id' => $p->ID, 'title' => $p->post_title, 'message' => wp_strip_all_tags( $p->post_content ), 'created' => $p->post_date, 'status' => $p->post_status );
		foreach ( get_post_meta( $p->ID ) as $k => $v ) {
			if ( 0 === strpos( $k, '_edit_' ) || '_wp_old_slug' === $k ) continue;
			$row[ ltrim( $k, '_' ) ] = $v[0] ?? '';
		}
		$m = aurix_inbox_map_row( $row );
		$m['data']   = array( 'Imported from' => $cpt ) + $m['data'];
		$m['ref']    = 'import:' . $cpt . ':' . $p->ID;
		$m['status'] = 'read';
		$m['ip']     = '';
		$m['source'] = '';
		unset( $m['id'] );
		if ( aurix_inbox_add( $type, $m ) ) $n++;
	}
	return $n;
}

function aurix_inbox_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	aurix_inbox_admin_css();
	$tables = aurix_inbox_candidate_tables();
	$cpts   = aurix_inbox_candidate_cpts();
	$types  = aurix_inbox_types();
	$type_select = function ( $guess, $form_id ) use ( $types ) {
		$h = '<select name="as_type" form="' . esc_attr( $form_id ) . '">';
		foreach ( $types as $k => $t ) $h .= '<option value="' . esc_attr( $k ) . '"' . selected( $guess, $k, false ) . '>' . esc_html( $t['label'] ) . '</option>';
		return $h . '</select>';
	};
	?>
	<div class="axi">
		<?php aurix_inbox_notice(); ?>
		<div class="axi-head"><h1>Inbox — Settings &amp; Import</h1><div class="axi-actions"><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=aurix-inbox' ) ); ?>">&larr; Back to Inbox</a></div></div>

		<div class="axi-card" style="max-width:900px;margin-bottom:18px"><div class="axi-sec">
			<h3>Settings</h3>
			<form method="post">
				<?php wp_nonce_field( 'aurix_inbox_settings' ); ?>
				<input type="hidden" name="ax_do" value="save">
				<table class="form-table" role="presentation">
					<tr><th>Capture plugin emails</th><td><label><input type="checkbox" name="capture" value="1" <?php checked( get_option( 'aurix_inbox_capture', 1 ) ); ?>> Save notifications that plugins (Medical Kit Builder, Offer Codes Manager, …) send to the admin as Inbox messages</label></td></tr>
					<tr><th><label for="ax-ne">Alert email</label></th><td><input id="ax-ne" type="email" class="regular-text" name="notify_email" value="<?php echo esc_attr( get_option( 'aurix_inbox_notify_email' ) ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"><p class="description">Where new-message alerts from Inbox-only forms (e.g. [aurix_newsletter], custom Elementor forms) are sent.</p></td></tr>
					<tr><th><label for="ax-fe">Reply-from email</label></th><td><input id="ax-fe" type="email" class="regular-text" name="from_email" value="<?php echo esc_attr( get_option( 'aurix_inbox_from_email' ) ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"><p class="description">Replies you send from the Inbox come from this address (should match your GoSMTP sender).</p></td></tr>
				</table>
				<p><button class="button button-primary">Save settings</button></p>
			</form>
		</div></div>

		<div class="axi-card" style="max-width:900px;margin-bottom:18px"><div class="axi-sec" style="display:flex;gap:20px;align-items:center;flex-wrap:wrap">
			<div style="flex:1;min-width:260px">
				<h3>Inbox on your phone</h3>
				<p style="margin:0 0 8px">Open <a href="<?php echo esc_url( home_url( '/inbox-app/' ) ); ?>" target="_blank"><strong><?php echo esc_html( preg_replace( '#^https?://#', '', home_url( '/inbox-app/' ) ) ); ?></strong></a> on your phone and log in (tick <em>Remember Me</em>).</p>
				<p style="margin:0;color:#64748b">Then add it to your home screen — iPhone: Share → <em>Add to Home Screen</em>. Android: tap <em>Add</em> on the banner or ⋮ → <em>Install app</em>. It opens like an app with Call, WhatsApp and Reply buttons on every message.</p>
			</div>
			<div id="axi-qr" role="img" aria-label="QR code to open the Inbox app on your phone" style="width:148px;height:148px;padding:4px;border:1px solid #e5e7eb;border-radius:8px;background:#fff"></div>
			<script src="<?php echo esc_url( get_template_directory_uri() . '/js/qrcode.min.js' ); ?>"></script>
			<script>if(window.aurixQR){aurixQR(document.getElementById('axi-qr'),<?php echo wp_json_encode( home_url( '/inbox-app/' ) ); ?>,138);}</script>
		</div></div>

		<?php aurix_team_card(); ?>

		<div class="axi-card" style="max-width:900px;margin-bottom:18px"><div class="axi-sec">
			<h3>Import existing messages</h3>
			<p>Safe to run more than once — records already imported are skipped and nothing is deleted from the original source.</p>
			<form method="post" style="margin:12px 0 18px">
				<?php wp_nonce_field( 'aurix_inbox_settings' ); ?>
				<input type="hidden" name="ax_do" value="import_theme">
				<button class="button button-primary">Import theme data</button>
				<span class="description" style="margin-left:8px">VIP waitlist (<?php echo count( (array) get_option( 'aurix_waitlist_emails', array() ) ); ?>), back-in-stock alerts, submitted supplier listings</span>
			</form>

			<?php $oc_t = aurix_inbox_oc_table(); if ( $oc_t ) : global $wpdb; ?>
			<form method="post" style="margin:0 0 18px">
				<?php wp_nonce_field( 'aurix_inbox_settings' ); ?>
				<input type="hidden" name="ax_do" value="import_oc">
				<button class="button button-primary">Import Pioneer Program applications</button>
				<span class="description" style="margin-left:8px"><?php echo (int) $wpdb->get_var( "SELECT COUNT(*) FROM `$oc_t`" ); ?> applications / codes from Offer Codes Manager</span>
			</form>
			<?php endif; ?>

			<h3 style="margin-top:20px">Plugin data found on this site</h3>
			<?php if ( ! $tables && ! $cpts ) : ?>
				<p class="description">No plugin submission tables found (looked for Kit Builder, offer code, RFQ, newsletter and similar tables that contain an email column).</p>
			<?php endif; ?>
			<?php if ( $tables || $cpts ) : ?>
			<table class="widefat striped">
				<thead><tr><th>Source</th><th>Records</th><th>Import as</th><th></th></tr></thead>
				<tbody>
				<?php foreach ( $tables as $tbl => $info ) : ?>
					<?php $fid = 'axf-' . md5( $tbl ); ?>
					<tr>
						<td><code><?php echo esc_html( $tbl ); ?></code><br><span class="description"><?php echo esc_html( implode( ', ', array_slice( $info['cols'], 0, 8 ) ) . ( count( $info['cols'] ) > 8 ? '…' : '' ) ); ?></span></td>
						<td><?php echo (int) $info['rows']; ?></td>
						<td><?php echo $type_select( $info['guess'], $fid ); // phpcs:ignore ?></td>
						<td><form method="post" id="<?php echo esc_attr( $fid ); ?>">
							<?php wp_nonce_field( 'aurix_inbox_settings' ); ?>
							<input type="hidden" name="ax_do" value="import_table"><input type="hidden" name="table" value="<?php echo esc_attr( $tbl ); ?>">
							<button class="button">Import</button>
						</form></td>
					</tr>
				<?php endforeach; ?>
				<?php foreach ( $cpts as $cpt => $info ) : ?>
					<?php $fid = 'axf-' . md5( $cpt ); ?>
					<tr>
						<td><?php echo esc_html( $info['label'] ); ?> <span class="description">(post type <code><?php echo esc_html( $cpt ); ?></code>)</span></td>
						<td><?php echo (int) $info['rows']; ?></td>
						<td><?php echo $type_select( $info['guess'], $fid ); // phpcs:ignore ?></td>
						<td><form method="post" id="<?php echo esc_attr( $fid ); ?>">
							<?php wp_nonce_field( 'aurix_inbox_settings' ); ?>
							<input type="hidden" name="ax_do" value="import_cpt"><input type="hidden" name="cpt" value="<?php echo esc_attr( $cpt ); ?>">
							<button class="button">Import</button>
						</form></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>
		</div></div>

		<div class="axi-card" style="max-width:900px"><div class="axi-sec">
			<h3>Send any form to the Inbox</h3>
			<p>Newsletter box anywhere (page, footer, Elementor Shortcode widget): <code>[aurix_newsletter title="Get industry updates" button="Subscribe"]</code></p>
			<p>Custom Elementor HTML form — post to <code>admin-ajax.php</code> with <code>action=aurix_inbox_submit</code>, <code>nonce=aurixData.inboxNonce</code>, <code>type</code> (newsletter, distributor, contact, quote, question, other), <code>email</code> and optional <code>name, phone, company, country, subject, message</code>. Any extra fields are saved as details.</p>
		</div></div>
	</div>
	<?php
}
