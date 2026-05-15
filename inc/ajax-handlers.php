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
