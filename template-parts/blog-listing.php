<?php
/**
 * Shared blog listing renderer -- included by index.php (used when /blog is
 * set as the site's "Posts page" under Settings -> Reading) and page-blog.php
 * (used if /blog is instead a literal Page with that slug).
 *
 * Deliberately built as several visually distinct sections rather than one
 * repeating grid:
 *   1. Featured spotlight  -- the single most recent post, large treatment
 *   2. Browse by category  -- pill row (only shown if more than one category is in use)
 *   3. Latest Articles     -- image-card grid, posts WITH a featured image only
 *   4. Quick Reads         -- compact list rows, posts WITHOUT a featured image
 *      (this avoids the empty gray placeholder box a text-only post used to get
 *      when forced into the same image-card layout as everything else)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 );

$blog_query = new WP_Query( array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 12,
	'paged'          => $paged,
) );

function abi_reading_time( $post_id ) {
	$words = str_word_count( wp_strip_all_tags( get_the_content( null, false, $post_id ) ) );
	return max( 1, (int) ceil( $words / 200 ) );
}
?>

<div class="abi-header">
	<h1 class="abi-title">Blog</h1>
	<p class="abi-subtitle">Guides, product updates, and industry insights from Aurix International.</p>
</div>

<?php if ( $blog_query->have_posts() ) :

	// Split into "has an image" vs "text only" for the two section layouts below,
	// and pull out the single newest post to spotlight separately so it isn't
	// duplicated in the grid underneath it.
	$all_posts     = $blog_query->posts;
	$featured_post = $all_posts[0];
	$rest          = array_slice( $all_posts, 1 );
	$with_image    = array_filter( $rest, function ( $p ) { return has_post_thumbnail( $p->ID ); } );
	$text_only     = array_filter( $rest, function ( $p ) { return ! has_post_thumbnail( $p->ID ); } );

	$categories = get_categories( array( 'hide_empty' => true ) );
	?>

	<!-- 1. Featured spotlight -->
	<a href="<?php echo esc_url( get_permalink( $featured_post ) ); ?>" class="abi-feature<?php echo has_post_thumbnail( $featured_post->ID ) ? '' : ' abi-feature--text-only'; ?>">
		<?php if ( has_post_thumbnail( $featured_post->ID ) ) : ?>
			<div class="abi-feature-media">
				<?php echo get_the_post_thumbnail( $featured_post->ID, 'large' ); ?>
			</div>
		<?php endif; ?>
		<div class="abi-feature-body">
			<span class="abi-feature-label"><i class="fas fa-star"></i> Latest</span>
			<h2><?php echo esc_html( get_the_title( $featured_post ) ); ?></h2>
			<p><?php echo esc_html( wp_trim_words( get_the_excerpt( $featured_post ), 32 ) ); ?></p>
			<div class="abi-card-meta">
				<span><i class="fas fa-calendar"></i> <?php echo esc_html( get_the_date( '', $featured_post ) ); ?></span>
				<span><i class="fas fa-clock"></i> <?php echo esc_html( abi_reading_time( $featured_post->ID ) ); ?> min read</span>
			</div>
		</div>
	</a>

	<!-- 2. Browse by category -->
	<?php if ( count( $categories ) > 1 ) : ?>
		<div class="abi-cat-row">
			<?php foreach ( $categories as $cat ) : ?>
				<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="abi-cat-pill"><?php echo esc_html( $cat->name ); ?> <span><?php echo esc_html( $cat->count ); ?></span></a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<!-- 3. Latest Articles -- image-card grid (posts with a featured image only) -->
	<?php if ( ! empty( $with_image ) ) : ?>
		<h2 class="abi-section-title">Latest Articles</h2>
		<div class="abi-grid">
			<?php foreach ( $with_image as $p ) :
				$cats = get_the_category( $p->ID );
				$cat  = ! empty( $cats ) ? $cats[0] : null;
				?>
				<a href="<?php echo esc_url( get_permalink( $p ) ); ?>" class="abi-card">
					<div class="abi-card-thumb">
						<?php echo get_the_post_thumbnail( $p->ID, 'medium_large' ); ?>
						<?php if ( $cat ) : ?><span class="abi-card-cat"><?php echo esc_html( $cat->name ); ?></span><?php endif; ?>
					</div>
					<div class="abi-card-body">
						<h3><?php echo esc_html( get_the_title( $p ) ); ?></h3>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt( $p ), 20 ) ); ?></p>
						<div class="abi-card-meta">
							<span><i class="fas fa-calendar"></i> <?php echo esc_html( get_the_date( '', $p ) ); ?></span>
							<span><i class="fas fa-clock"></i> <?php echo esc_html( abi_reading_time( $p->ID ) ); ?> min read</span>
						</div>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<!-- 4. Quick Reads -- compact list rows (posts with no featured image) -->
	<?php if ( ! empty( $text_only ) ) : ?>
		<h2 class="abi-section-title">Quick Reads</h2>
		<div class="abi-list">
			<?php foreach ( $text_only as $p ) :
				$cats = get_the_category( $p->ID );
				$cat  = ! empty( $cats ) ? $cats[0] : null;
				?>
				<a href="<?php echo esc_url( get_permalink( $p ) ); ?>" class="abi-list-row">
					<i class="fas fa-file-alt abi-list-icon"></i>
					<div class="abi-list-body">
						<h3><?php echo esc_html( get_the_title( $p ) ); ?></h3>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt( $p ), 24 ) ); ?></p>
					</div>
					<div class="abi-list-meta">
						<?php if ( $cat ) : ?><span class="abi-list-cat"><?php echo esc_html( $cat->name ); ?></span><?php endif; ?>
						<span><?php echo esc_html( get_the_date( '', $p ) ); ?></span>
						<span><?php echo esc_html( abi_reading_time( $p->ID ) ); ?> min</span>
					</div>
					<i class="fas fa-chevron-right abi-list-arrow"></i>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php
	$total = $blog_query->max_num_pages;
	if ( $total > 1 ) :
		?>
		<nav class="abi-pagination">
			<?php
			echo paginate_links( array(
				'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
				'format'    => '?paged=%#%',
				'current'   => max( 1, $paged ),
				'total'     => $total,
				'prev_text' => '<i class="fas fa-arrow-left"></i>',
				'next_text' => '<i class="fas fa-arrow-right"></i>',
			) );
			?>
		</nav>
	<?php endif; ?>

<?php else : ?>
	<div class="abi-empty">
		<i class="fas fa-newspaper"></i>
		<h2>No posts yet</h2>
		<p>Check back soon for guides and updates.</p>
	</div>
<?php endif; wp_reset_postdata(); ?>
