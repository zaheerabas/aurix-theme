<?php
/**
 * Aurix International — ajax-handlers.php v3.0
 * Author: ZaheerAbbas
 */
if ( !defined('ABSPATH') ) exit;

// Cart count
function aurix_ajax_cart_count() {
    check_ajax_referer('aurix_nonce','nonce');
    $count = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0;
    wp_send_json_success(['count' => $count]);
}
add_action('wp_ajax_aurix_get_cart_count',        'aurix_ajax_cart_count');
add_action('wp_ajax_nopriv_aurix_get_cart_count', 'aurix_ajax_cart_count');

// Cart total
function aurix_ajax_cart_total() {
    check_ajax_referer('aurix_nonce','nonce');
    $total = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_subtotal() : '';
    wp_send_json_success(['total' => $total]);
}
add_action('wp_ajax_aurix_get_cart_total',        'aurix_ajax_cart_total');
add_action('wp_ajax_nopriv_aurix_get_cart_total', 'aurix_ajax_cart_total');

// Full cart sidebar refresh — minicart HTML + count + subtotal
function aurix_ajax_cart_sidebar() {
    check_ajax_referer('aurix_nonce','nonce');
    if ( !function_exists('WC') || !WC()->cart ) {
        wp_send_json_error();
    }
    WC()->cart->calculate_totals();

    ob_start();
    woocommerce_mini_cart();
    $minicart = ob_get_clean();

    wp_send_json_success([
        'minicart'  => $minicart,
        'count'     => WC()->cart->get_cart_contents_count(),
        'subtotal'  => WC()->cart->get_cart_subtotal(),
    ]);
}
add_action('wp_ajax_aurix_get_cart_sidebar',        'aurix_ajax_cart_sidebar');
add_action('wp_ajax_nopriv_aurix_get_cart_sidebar', 'aurix_ajax_cart_sidebar');

// Update cart item qty (also handles remove when qty=0)
function aurix_ajax_update_cart_qty() {
    check_ajax_referer('aurix_nonce','nonce');
    if ( !function_exists('WC') || !WC()->cart ) { wp_send_json_error(); }

    $key = sanitize_text_field( wp_unslash( $_POST['key'] ?? '' ) );
    $qty = max(0, (int) wp_unslash( $_POST['qty'] ?? 0 ));

    if ( !isset( WC()->cart->get_cart()[$key] ) ) { wp_send_json_error(['msg'=>'Item not found']); }

    if ( $qty === 0 ) {
        WC()->cart->remove_cart_item($key);
    } else {
        WC()->cart->set_quantity($key, $qty, true);
    }

    WC()->cart->calculate_totals();
    if ( WC()->session ) WC()->session->save_data();

    ob_start(); woocommerce_mini_cart(); $minicart = ob_get_clean();

    $free_threshold = 500;
    $cart_total     = (float) WC()->cart->get_cart_contents_total();
    $remaining      = max(0, $free_threshold - $cart_total);
    $progress       = min(100, ($cart_total / $free_threshold) * 100);

    wp_send_json_success([
        'minicart'  => $minicart,
        'count'     => WC()->cart->get_cart_contents_count(),
        'subtotal'  => WC()->cart->get_cart_subtotal(),
        'cart_hash' => WC()->cart->get_cart_hash(),
    ]);
}
add_action('wp_ajax_aurix_update_cart_qty',        'aurix_ajax_update_cart_qty');
add_action('wp_ajax_nopriv_aurix_update_cart_qty', 'aurix_ajax_update_cart_qty');

// ─────────────────────────────────────────────────
// Contact Form — Send emails to user & admin
// ─────────────────────────────────────────────────
function aurix_contact_submit() {

    // 1. Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'aurix_contact' ) ) {
        wp_send_json_error( [ 'msg' => 'Security check failed.' ] );
    }

    // 2. Sanitize fields
    $first_name = sanitize_text_field( wp_unslash( $_POST['firstName'] ?? '' ) );
    $last_name  = sanitize_text_field( wp_unslash( $_POST['lastName']  ?? '' ) );
    $email      = sanitize_email(      wp_unslash( $_POST['email']     ?? '' ) );
    $phone      = sanitize_text_field( wp_unslash( $_POST['phone']     ?? '' ) );
    $company    = sanitize_text_field( wp_unslash( $_POST['company']   ?? '' ) );
    $role       = sanitize_text_field( wp_unslash( $_POST['role']      ?? '' ) );
    $inq_type   = sanitize_text_field( wp_unslash( $_POST['type']      ?? '' ) );
    $budget     = sanitize_text_field( wp_unslash( $_POST['budget']    ?? '' ) );
    $subject    = sanitize_text_field( wp_unslash( $_POST['subject']   ?? '' ) );
    $message    = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
    $newsletter = ! empty( $_POST['newsletter'] ) ? 'Yes' : 'No';

    $full_name  = trim( "$first_name $last_name" );

    // 3. Validate required
    if ( ! $first_name || ! is_email( $email ) || ! $inq_type || ! $subject || strlen( $message ) < 20 ) {
        wp_send_json_error( [ 'msg' => 'Missing or invalid required fields.' ] );
    }

    // 4. Budget label map
    $budget_map = [ 'small' => 'Under $10K', 'mid' => '$10K – $50K', 'large' => '$50K+' ];
    $budget_label = $budget_map[ $budget ] ?? 'Not specified';

    // ── Save to the Inbox ──
    $saved = false;
    if ( function_exists( 'aurix_inbox_add' ) ) {
        $saved = aurix_inbox_add( 'contact', [
            'name' => $full_name, 'email' => $email, 'phone' => $phone, 'company' => $company,
            'subject' => $subject, 'message' => $message,
            'data' => [ 'Inquiry type' => $inq_type, 'Role' => $role, 'Budget' => $budget_label, 'Newsletter' => $newsletter ],
        ] );
        if ( 'Yes' === $newsletter ) {
            aurix_inbox_add( 'newsletter', [ 'name' => $full_name, 'email' => $email, 'company' => $company, 'status' => 'read', 'data' => [ 'Signed up via' => 'Contact form' ] ] );
        }
    }

    // ── Pull customizer email settings ──
    $admin_to      = get_theme_mod( 'aurix_cf_admin_email',   get_option( 'admin_email' ) );
    $admin_cc      = get_theme_mod( 'aurix_cf_admin_cc',      '' );
    $from_name     = get_theme_mod( 'aurix_cf_from_name',     get_bloginfo('name') );
    $from_email    = get_theme_mod( 'aurix_cf_from_email',    'noreply@' . wp_parse_url( home_url(), PHP_URL_HOST ) );
    $admin_subject = get_theme_mod( 'aurix_cf_admin_subject', 'New Contact Inquiry: {subject}' );
    $user_subject  = get_theme_mod( 'aurix_cf_user_subject',  'We received your message — Aurix International' );

    // Replace placeholders in subjects
    $placeholders  = [ '{name}', '{subject}', '{type}', '{company}' ];
    $replacements  = [ $full_name, $subject, $inq_type, $company ?: 'N/A' ];
    $admin_subject = str_replace( $placeholders, $replacements, $admin_subject );
    $user_subject  = str_replace( $placeholders, $replacements, $user_subject );

    // ── Shared email header/footer ──
    $site_name = esc_html( get_bloginfo('name') );
    $site_url  = esc_url( home_url() );
    $year      = date('Y');

    $email_header = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  body{margin:0;padding:0;background:#F7F4EF;font-family:"Helvetica Neue",Arial,sans-serif;color:#1A1714}
  .wrap{max-width:600px;margin:32px auto;background:#fff;border:1px solid #E4DFD6;border-radius:12px;overflow:hidden}
  .header{background:#1A1714;padding:28px 36px;text-align:center}
  .header h1{margin:0;font-size:22px;font-weight:600;color:#B5814A;letter-spacing:0.04em;font-family:Georgia,serif}
  .header p{margin:4px 0 0;font-size:12px;color:#8C8880;letter-spacing:0.1em;text-transform:uppercase}
  .body{padding:32px 36px}
  .body h2{font-size:20px;font-family:Georgia,serif;font-weight:normal;color:#1A1714;margin:0 0 6px}
  .body .sub{font-size:14px;color:#8C8880;margin:0 0 24px;line-height:1.6}
  .field-table{width:100%;border-collapse:collapse;margin:20px 0}
  .field-table tr{border-bottom:1px solid #E4DFD6}
  .field-table tr:last-child{border-bottom:none}
  .field-table td{padding:10px 0;font-size:13px;vertical-align:top}
  .field-table td:first-child{font-weight:600;color:#3D3A35;width:120px;padding-right:12px}
  .field-table td:last-child{color:#1A1714}
  .msg-box{background:#F7F4EF;border:1px solid #E4DFD6;border-radius:8px;padding:16px;font-size:13px;color:#1A1714;line-height:1.7;margin:20px 0}
  .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;background:#FBF5EC;color:#B5814A;border:1px solid rgba(181,129,74,0.25)}
  .cta{text-align:center;margin:28px 0 8px}
  .cta a{display:inline-block;background:#B5814A;color:#fff;text-decoration:none;padding:13px 28px;border-radius:8px;font-size:14px;font-weight:600;letter-spacing:0.02em}
  .footer{background:#F7F4EF;border-top:1px solid #E4DFD6;padding:18px 36px;text-align:center;font-size:11px;color:#8C8880;line-height:1.7}
  .footer a{color:#B5814A;text-decoration:none}
  .divider{height:1px;background:#E4DFD6;margin:20px 0}
</style></head><body><div class="wrap">
<div class="header"><h1>' . $site_name . '</h1><p>Surgical &amp; Dental Instruments</p></div>';

    $email_footer = '<div class="footer">
  &copy; ' . $year . ' ' . $site_name . ' &nbsp;·&nbsp; <a href="' . $site_url . '">' . $site_url . '</a><br>
  4024 Avenue S, Brooklyn, New York 11234 &nbsp;·&nbsp; <a href="mailto:info@aurixinternational.com">info@aurixinternational.com</a>
</div></div></body></html>';

    // ──────────────────────────────────────────────
    // A. ADMIN EMAIL
    // ──────────────────────────────────────────────
    $admin_body = $email_header . '
<div class="body">
  <h2>New Inquiry Received</h2>
  <p class="sub">A new contact form submission has arrived. Details are below.</p>
  <span class="badge">' . esc_html($inq_type) . '</span>
  <table class="field-table">
    <tr><td>Name</td><td>' . esc_html($full_name) . '</td></tr>
    <tr><td>Email</td><td><a href="mailto:' . esc_attr($email) . '" style="color:#B5814A">' . esc_html($email) . '</a></td></tr>
    <tr><td>Phone</td><td>' . ( $phone ? esc_html($phone) : '—' ) . '</td></tr>
    <tr><td>Company</td><td>' . ( $company ? esc_html($company) : '—' ) . '</td></tr>
    <tr><td>Role</td><td>' . ( $role ? esc_html($role) : '—' ) . '</td></tr>
    <tr><td>Budget</td><td>' . esc_html($budget_label) . '</td></tr>
    <tr><td>Newsletter</td><td>' . esc_html($newsletter) . '</td></tr>
    <tr><td>Subject</td><td>' . esc_html($subject) . '</td></tr>
  </table>
  <div class="divider"></div>
  <strong style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:#8C8880">Message</strong>
  <div class="msg-box">' . nl2br( esc_html($message) ) . '</div>
  <div class="cta"><a href="mailto:' . esc_attr($email) . '">Reply to ' . esc_html($first_name) . '</a></div>
</div>' . $email_footer;

    // ──────────────────────────────────────────────
    // B. USER CONFIRMATION EMAIL
    // ──────────────────────────────────────────────
    $user_greeting = get_theme_mod( 'aurix_cf_user_greeting',
        'Thank you for reaching out, {name}. We have received your inquiry and our team will review it shortly.' );
    $user_greeting = str_replace( $placeholders, $replacements, $user_greeting );

    $user_body = $email_header . '
<div class="body">
  <h2>Your message is on its way.</h2>
  <p class="sub">' . esc_html($user_greeting) . '</p>
  <table class="field-table">
    <tr><td>Name</td><td>' . esc_html($full_name) . '</td></tr>
    <tr><td>Inquiry</td><td><span class="badge">' . esc_html($inq_type) . '</span></td></tr>
    <tr><td>Subject</td><td>' . esc_html($subject) . '</td></tr>
    <tr><td>Budget</td><td>' . esc_html($budget_label) . '</td></tr>
    <tr><td>Submitted</td><td>' . date('F j, Y \a\t g:i A T') . '</td></tr>
  </table>
  <div class="divider"></div>
  <strong style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:#8C8880">Your Message</strong>
  <div class="msg-box">' . nl2br( esc_html($message) ) . '</div>
  <p style="font-size:13px;color:#8C8880;line-height:1.7;margin:16px 0 0">Expected reply time: <strong style="color:#1A1714">within 24 business hours</strong>.<br>
  For urgent matters, call us at <a href="tel:+13477273384" style="color:#B5814A">+1 (347) 727-3384</a>.</p>
</div>' . $email_footer;

    // ── Send headers ──
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $from_name . ' <' . $from_email . '>',
    ];
    if ( $admin_cc ) {
        $headers[] = 'Cc: ' . sanitize_email( $admin_cc );
    }

    // ── Dispatch ──
    $admin_sent = wp_mail( sanitize_email($admin_to), $admin_subject, $admin_body, $headers );

    $user_headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $from_name . ' <' . $from_email . '>',
        'Reply-To: ' . sanitize_email($admin_to),
    ];
    $user_sent = wp_mail( $email, $user_subject, $user_body, $user_headers );

    if ( $admin_sent || $user_sent || $saved ) {
        wp_send_json_success( [ 'msg' => 'Sent' ] );
    } else {
        wp_send_json_error( [ 'msg' => 'Mail delivery failed. Please contact us directly.' ] );
    }
}
add_action( 'wp_ajax_aurix_contact_submit',        'aurix_contact_submit' );
add_action( 'wp_ajax_nopriv_aurix_contact_submit', 'aurix_contact_submit' );
