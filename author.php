<?php
/**
 * Author archive — "admin introduction and details" page linked from the
 * author name/photo in single.php. No author.php existed before, so this
 * URL fell back to the bare index.php template with no bio shown at all.
 */
get_header();

$author_id   = get_queried_object_id();
$author_name = get_the_author_meta( 'display_name', $author_id );
$author_bio  = get_the_author_meta( 'description', $author_id );
$author_url  = get_the_author_meta( 'user_url', $author_id );

$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 );
$author_posts = new WP_Query( array(
	'author'         => $author_id,
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 9,
	'paged'          => $paged,
) );
?>

<div class="aurix-page-wrap aurix-blog-index" style="max-width:1400px;">

	<!-- Author intro -->
	<div class="aauth-intro">
		<?php echo get_avatar( $author_id, 96 ); ?>
		<div class="aauth-intro-body">
			<span class="aauth-intro-label">Author</span>
			<h1 class="aauth-intro-name"><?php echo esc_html( $author_name ); ?></h1>
			<?php if ( $author_bio ) : ?>
				<p class="aauth-intro-bio"><?php echo esc_html( $author_bio ); ?></p>
			<?php endif; ?>
			<?php if ( $author_url ) : ?>
				<a href="<?php echo esc_url( $author_url ); ?>" class="aauth-intro-link" target="_blank" rel="noopener"><i class="fas fa-link"></i> <?php echo esc_html( $author_url ); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<h2 class="abi-section-title">Articles by <?php echo esc_html( $author_name ); ?></h2>

	<?php if ( $author_posts->have_posts() ) : ?>
		<div class="abi-grid">
			<?php while ( $author_posts->have_posts() ) : $author_posts->the_post();
				$cats = get_the_category();
				$cat  = ! empty( $cats ) ? $cats[0] : null;
				$word_count  = str_word_count( wp_strip_all_tags( get_the_content() ) );
				$reading_min = max( 1, (int) ceil( $word_count / 200 ) );
				?>
				<a href="<?php the_permalink(); ?>" class="abi-card">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="abi-card-thumb">
							<?php the_post_thumbnail( 'medium_large' ); ?>
							<?php if ( $cat ) : ?><span class="abi-card-cat"><?php echo esc_html( $cat->name ); ?></span><?php endif; ?>
						</div>
					<?php endif; ?>
					<div class="abi-card-body">
						<h2><?php the_title(); ?></h2>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
						<div class="abi-card-meta">
							<span><i class="fas fa-calendar"></i> <?php echo esc_html( get_the_date() ); ?></span>
							<span><i class="fas fa-clock"></i> <?php echo esc_html( $reading_min ); ?> min read</span>
						</div>
					</div>
				</a>
			<?php endwhile; ?>
		</div>

		<?php
		$total = $author_posts->max_num_pages;
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
			<h2>No articles yet</h2>
			<p><?php echo esc_html( $author_name ); ?> hasn't published anything yet.</p>
		</div>
	<?php endif; wp_reset_postdata(); ?>

</div>

<?php get_footer(); ?>
