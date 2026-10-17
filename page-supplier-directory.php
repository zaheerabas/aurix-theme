<?php
/**
 * Template Name: Dental Supplier Directory
 * Template Post Type: page
 *
 * Full directory page (template-parts/supplier-directory.php). Any page
 * content is shown above the FAQ. The same sections can also be placed in
 * any page with the [aurix_supplier_directory] shortcode.
 */
defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/supplier-directory', null, array( 'embed' => false ) );
get_footer();
