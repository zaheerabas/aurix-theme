<?php
/**
 * Blog listing — shared by index.php (posts page and category, tag, date
 * and search archives), page-blog.php (a Page with slug "blog") and
 * author.php.
 *
 * Expects $args:
 *   query    WP_Query  the posts to list (defaults to the main query)
 *   hero     array     arguments for aurix_blog_hero()
 *   featured bool      main blog page: on page 1 show the featured stories
 *                      and one section per category before "All articles"
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wp_query;
$axb_query = isset( $args['query'] ) ? $args['query'] : $wp_query;
$axb_hero  = isset( $args['hero'] ) ? $args['hero'] : array();
$axb_paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$axb_home  = ! empty( $args['featured'] ) && 1 === $axb_paged;

// Featured: the four newest posts (sticky posts first).
$axb_featured = array();
if ( $axb_home && $axb_query->post_count >= 4 ) {
	$axb_featured = array_slice( $axb_query->posts, 0, 4 );
}
$axb_featured_ids = wp_list_pluck( $axb_featured, 'ID' );
?>
<div class="axb">
	<?php aurix_blog_hero( $axb_hero ); ?>

	<?php if ( ! $axb_query->have_posts() ) : ?>

		<div class="axb-wrap">
			<div class="axb-empty">
				<span class="axb-empty__icon"><i class="fas fa-feather-alt" aria-hidden="true"></i></span>
				<h2><?php echo is_search() ? 'No articles match your search' : 'No articles yet'; ?></h2>
				<p><?php echo is_search() ? 'Try a different word, or browse all articles.' : 'New articles are on the way. Please check back soon.'; ?></p>
				<a class="axb-btn axb-btn--navy" href="<?php echo esc_url( aurix_blog_url() ); ?>">Browse all articles</a>
			</div>
		</div>

	<?php else : ?>

		<?php if ( $axb_featured ) : ?>
			<section class="axb-featured" aria-label="Featured articles">
				<div class="axb-wrap">
					<?php aurix_blog_card( $axb_featured[0], 'split', array( 'eager' => true, 'words' => 36 ) ); ?>
					<div class="axb-featured__row">
						<?php foreach ( array_slice( $axb_featured, 1 ) as $axb_p ) {
							aurix_blog_card( $axb_p, 'card', array( 'words' => 16 ) );
						} ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php
		if ( $axb_home ) :
			$axb_index = 0;
			foreach ( aurix_blog_sections() as $axb_section ) :
				$axb_args = array(
					'posts_per_page'      => $axb_section['show'],
					'cat'                 => $axb_section['term']->term_id,
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
				);
				// Prefer posts not already featured above; fall back if that empties the section.
				$axb_posts = get_posts( $axb_args + array( 'post__not_in' => $axb_featured_ids ) );
				if ( ! $axb_posts ) {
					$axb_posts = get_posts( $axb_args );
				}
				if ( ! $axb_posts ) {
					continue;
				}
				get_template_part( 'template-parts/blog-section', null, array(
					'section' => $axb_section,
					'posts'   => $axb_posts,
					'index'   => $axb_index++,
				) );
			endforeach;
		endif;
		?>

		<section class="axb-all" id="all-articles">
			<div class="axb-wrap">
				<header class="axs-head">
					<div class="axs-head__text">
						<div>
							<h2 class="axs-title"><?php
								if ( $axb_home ) {
									echo 'All articles';
								} elseif ( 1 < $axb_paged ) {
									echo 'Page ' . (int) $axb_paged;
								} else {
									echo 'Articles';
								}
							?></h2>
							<p class="axs-intro"><?php echo (int) $axb_query->found_posts; ?> <?php echo 1 === (int) $axb_query->found_posts ? 'article' : 'articles'; ?>, newest first.</p>
						</div>
					</div>
				</header>
				<div class="axb-grid">
					<?php foreach ( $axb_query->posts as $axb_p ) {
						aurix_blog_card( $axb_p, 'card' );
					} ?>
				</div>
				<?php aurix_blog_pagination( $axb_query ); ?>
			</div>
		</section>

	<?php endif; ?>

	<?php aurix_blog_cta(); ?>
</div>
<?php wp_reset_postdata(); ?>
