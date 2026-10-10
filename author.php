<?php
/**
 * Author archive — the author's introduction followed by their articles,
 * linked from the byline and author card on single.php.
 */
get_header();

$axb_author_id = get_queried_object_id();
$axb_name      = get_the_author_meta( 'display_name', $axb_author_id );
$axb_bio       = get_the_author_meta( 'description', $axb_author_id );
$axb_site      = get_the_author_meta( 'user_url', $axb_author_id );

$axb_extra  = '<div class="axb-author-hero">' . get_avatar( $axb_author_id, 96, 'mystery' );
$axb_extra .= '<div><strong>' . (int) $wp_query->found_posts . '</strong> ' . ( 1 === (int) $wp_query->found_posts ? 'article' : 'articles' ) . ' published';
if ( $axb_site ) {
	$axb_extra .= '<br><a href="' . esc_url( $axb_site ) . '" target="_blank" rel="noopener"><i class="fas fa-link" aria-hidden="true"></i> ' . esc_html( preg_replace( '#^https?://#', '', untrailingslashit( $axb_site ) ) ) . '</a>';
}
$axb_extra .= '</div></div>';

get_template_part( 'template-parts/blog-listing', null, array(
	'hero' => array(
		'eyebrow' => 'Author',
		'title'   => esc_html( $axb_name ),
		'intro'   => esc_html( $axb_bio ? $axb_bio : 'Articles written by ' . $axb_name . ' for Aurix International.' ),
		'extra'   => $axb_extra,
		'chips'   => false,
		'search'  => false,
	),
) );

get_footer();
