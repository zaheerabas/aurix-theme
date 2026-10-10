<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * Renders wp_nav_menu() items using the theme's existing icon+arrow markup
 * (previously hardcoded for the mobile panel and header pills), so both are
 * now fully editable from Appearance -> Menus instead of requiring a code
 * change every time a link needs to be added, removed, reordered, or renamed.
 *
 * HOW TO ADD AN ICON TO A MENU ITEM
 * In Appearance -> Menus, click the item to expand it. If you don't see a
 * "CSS Classes" field, click "Screen Options" (top-right of the page) and
 * enable it. Then enter a class like:
 *   icon-fa-star     -> Font Awesome solid star icon
 *   icon-far-heart   -> Font Awesome regular (outline) heart icon
 *   icon-fab-google  -> Font Awesome brand icon
 * Leave it blank for no icon. Browse available icon names at fontawesome.com
 * (this theme includes Font Awesome 5 Free).
 *
 * HOW TO ADD A DIVIDER (mobile menu only)
 * Add a "Custom Link" item (URL can just be #), then give it the CSS class
 * `menu-divider`. It'll render as a plain divider line instead of a link,
 * wherever you drag it in the menu order.
 *
 * PILL-SPECIFIC STYLE CLASSES (Header Quick Links menu only)
 * `anav-track`     -> plainer/transparent style, no pill background (used by
 *                     the original "Track Order" link)
 * `anav-pill--hot` -> red "hot deal" accent (used by "Today's Deals")
 */
class Aurix_Icon_Menu_Walker extends Walker_Nav_Menu {

	private $mode; // 'mobile' | 'pill'

	public function __construct( $mode = 'mobile' ) {
		$this->mode = $mode;
	}

	private function extract_icon( $classes ) {
		foreach ( $classes as $class ) {
			if ( strpos( $class, 'icon-fa-' ) === 0 )  return 'fas fa-' . substr( $class, strlen( 'icon-fa-' ) );
			if ( strpos( $class, 'icon-far-' ) === 0 ) return 'far fa-' . substr( $class, strlen( 'icon-far-' ) );
			if ( strpos( $class, 'icon-fab-' ) === 0 ) return 'fab fa-' . substr( $class, strlen( 'icon-fab-' ) );
		}
		return '';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;

		// Divider marker -- only meaningful for the mobile panel, but harmless
		// (just does nothing extra) if someone adds it to the pills menu too.
		if ( in_array( 'menu-divider', $classes, true ) ) {
			$output .= '<div class="apanel__divider"></div>';
			return;
		}

		$icon = $this->extract_icon( $classes );

		// Strip our marker classes so they don't leak onto the rendered link
		// as literal (meaningless) CSS classes.
		$visible_classes = array_filter( $classes, function( $c ) {
			return strpos( $c, 'icon-fa' ) !== 0 && $c !== 'menu-divider';
		} );

		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$url   = ! empty( $item->url ) ? $item->url : '';
		$attrs = $item->target ? ' target="' . esc_attr( $item->target ) . '"' : '';

		if ( $this->mode === 'pill' ) {
			// "Track Order" traditionally used a plainer style (no pill background)
			// than the other pills -- let an item opt into that via this class.
			$base = in_array( 'anav-track', $visible_classes, true ) ? 'anav-track' : 'anav-pill';
			$visible_classes = array_diff( $visible_classes, array( 'anav-track' ) );
			$extra = $visible_classes ? ' ' . esc_attr( implode( ' ', $visible_classes ) ) : '';
			$output .= '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $base ) . $extra . '"' . $attrs . '>';
			if ( $icon ) $output .= '<i class="' . esc_attr( $icon ) . '"></i> ';
			$output .= esc_html( $title ) . '</a>';
		} else {
			$extra = $visible_classes ? ' class="' . esc_attr( implode( ' ', $visible_classes ) ) . '"' : '';
			$output .= '<a href="' . esc_url( $url ) . '"' . $extra . $attrs . '><span class="albl">';
			if ( $icon ) $output .= '<i class="' . esc_attr( $icon ) . '"></i> ';
			$output .= esc_html( $title ) . '</span><i class="fas fa-chevron-right albl-arrow"></i></a>';
		}
	}

	// start_el() above already outputs the complete <a>/divider for each item,
	// so there's nothing left to close here.
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}
