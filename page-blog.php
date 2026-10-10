<?php
/**
 * Blog page (a Page with slug "blog"). Lists every published post with the
 * shared blog listing; categories appear as filter chips in the header.
 * If /blog is instead set as the "Posts page" under Settings → Reading,
 * index.php renders the same listing.
 */
get_header();

$axb_paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$axb_query = new WP_Query( array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => AURIX_BLOG_PER_PAGE,
	'paged'               => $axb_paged,
	'ignore_sticky_posts' => false,
) );

$axb_intro = has_excerpt() ? get_the_excerpt() : 'Product guides, company news, events and industry insights from Aurix International — makers of precision dental and surgical instruments.';

get_template_part( 'template-parts/blog-listing', null, array(
	'query'    => $axb_query,
	'featured' => true,
	'hero'     => array( 'intro' => $axb_intro ),
) );

get_footer();
