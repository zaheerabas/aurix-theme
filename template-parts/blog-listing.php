<?php
/**
 * Blog listing — shared by index.php (posts page and category, tag, date
 * and search archives) and page-blog.php (a Page with slug "blog").
 *
 * Expects $args:
 *   query    WP_Query  the posts to list (defaults to the main query)
 *   hero     array     arguments for aurix_blog_hero()
 *   featured bool      show the lead story + two side stories on page 1
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wp_query;
$axb_query    = isset( $args['query'] ) ? $args['query'] : $wp_query;
$axb_hero     = isset( $args['hero'] ) ? $args['hero'] : array();
$axb_paged    = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$axb_featured = ! empty( $args['featured'] ) && 1 === $axb_paged;

$axb_posts = $axb_query->posts;
$axb_lead  = array();
if ( $axb_featured && count( $axb_posts ) >= 4 ) {
	$axb_lead  = array_slice( $axb_posts, 0, 3 );
	$axb_posts = array_slice( $axb_posts, 3 );
}
?>
<div class="axb">
	<?php aurix_blog_hero( $axb_hero ); ?>

	<div class="axb-wrap">
		<?php if ( ! $axb_query->have_posts() ) : ?>

			<div class="axb-empty">
				<span class="axb-empty__icon"><i class="fas fa-feather-alt" aria-hidden="true"></i></span>
				<h2><?php echo is_search() ? 'No articles match your search' : 'No articles yet'; ?></h2>
				<p><?php echo is_search() ? 'Try a different word, or browse all articles.' : 'New articles are on the way. Please check back soon.'; ?></p>
				<a class="axb-btn axb-btn--navy" href="<?php echo esc_url( aurix_blog_url() ); ?>">Browse all articles</a>
			</div>

		<?php else : ?>

			<?php if ( $axb_lead ) : ?>
				<section class="axb-featured" aria-label="Featured articles">
					<?php aurix_blog_card( $axb_lead[0], 'lead' ); ?>
					<div class="axb-featured__side">
						<?php aurix_blog_card( $axb_lead[1], 'side' ); ?>
						<?php aurix_blog_card( $axb_lead[2], 'side' ); ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $axb_posts ) : ?>
				<section class="axb-latest">
					<div class="axb-section-head">
						<h2><?php echo $axb_lead ? 'Latest articles' : ( 1 < $axb_paged ? 'Page ' . (int) $axb_paged : 'Articles' ); ?></h2>
						<span><?php echo (int) $axb_query->found_posts; ?> <?php echo 1 === (int) $axb_query->found_posts ? 'article' : 'articles'; ?></span>
					</div>
					<div class="axb-grid">
						<?php foreach ( $axb_posts as $axb_post ) {
							aurix_blog_card( $axb_post, 'card' );
						} ?>
					</div>
				</section>
			<?php endif; ?>

			<?php aurix_blog_pagination( $axb_query ); ?>

		<?php endif; ?>
	</div>

	<?php aurix_blog_cta(); ?>
</div>
<?php wp_reset_postdata(); ?>
