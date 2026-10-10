<?php
/**
 * Fallback template: the posts page (Settings → Reading) and the category,
 * tag, date and search archives all render the shared blog listing.
 */
get_header();

$axb_hero = array(
	'intro' => 'Product guides, company news, events and industry insights from Aurix International — makers of precision dental and surgical instruments.',
);

if ( is_category() ) {
	$axb_term = get_queried_object();
	$axb_hero = array(
		'eyebrow'    => 'Category',
		'title'      => single_cat_title( '', false ),
		'intro'      => category_description() ? wp_strip_all_tags( category_description() ) : 'All articles filed under ' . single_cat_title( '', false ) . '.',
		'active_cat' => $axb_term->term_id,
	);
} elseif ( is_tag() ) {
	$axb_hero = array(
		'eyebrow' => 'Topic',
		'title'   => '#' . single_tag_title( '', false ),
		'intro'   => 'All articles tagged “' . single_tag_title( '', false ) . '”.',
	);
} elseif ( is_search() ) {
	$axb_hero = array(
		'eyebrow' => 'Search',
		'title'   => 'Results for “' . get_search_query() . '”',
		'intro'   => (int) $wp_query->found_posts . ' ' . ( 1 === (int) $wp_query->found_posts ? 'result' : 'results' ) . ' found.',
		'chips'   => false,
	);
} elseif ( is_date() ) {
	$axb_hero = array(
		'eyebrow' => 'Archive',
		'title'   => wp_strip_all_tags( get_the_archive_title() ),
		'chips'   => false,
	);
} elseif ( is_archive() ) {
	$axb_hero = array(
		'eyebrow' => 'Archive',
		'title'   => wp_strip_all_tags( get_the_archive_title() ),
		'chips'   => false,
		'search'  => false,
	);
}

get_template_part( 'template-parts/blog-listing', null, array(
	'hero'     => $axb_hero,
	'featured' => is_home(),
) );

get_footer();
