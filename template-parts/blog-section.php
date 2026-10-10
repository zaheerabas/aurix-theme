<?php
/**
 * One category section on the main blog page.
 *
 * Expects $args:
 *   section  array     from aurix_blog_sections(): term, layout, title, intro, show
 *   posts    WP_Post[] the section's posts (already limited to 'show')
 *   index    int       position on the page (for the "01" numbering)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$axs        = $args['section'];
$axs_posts  = $args['posts'];
$axs_layout = $axs['layout'];
$axs_term   = $axs['term'];
$axs_id     = 'axs-' . $axs_term->slug;
// A magazine needs a lead story plus at least two headlines; with fewer
// posts show them side by side instead.
if ( 'magazine' === $axs_layout && count( $axs_posts ) < 3 ) {
	$axs_layout = 'mosaic';
}
if ( ! $axs_posts ) {
	return;
}
?>
<section class="axs axs--<?php echo esc_attr( $axs_layout ); ?>" id="<?php echo esc_attr( $axs_id ); ?>" aria-labelledby="<?php echo esc_attr( $axs_id ); ?>-title">
	<div class="axb-wrap">
		<header class="axs-head">
			<div class="axs-head__text">
				<span class="axs-num"><?php echo esc_html( str_pad( (string) ( (int) $args['index'] + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				<div>
					<h2 class="axs-title" id="<?php echo esc_attr( $axs_id ); ?>-title"><?php echo esc_html( $axs['title'] ); ?></h2>
					<?php if ( $axs['intro'] ) : ?>
						<p class="axs-intro"><?php echo esc_html( $axs['intro'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
			<div class="axs-head__actions">
				<?php if ( 'carousel' === $axs_layout && count( $axs_posts ) > 3 ) : ?>
					<button type="button" class="axs-arrow" data-dir="-1" aria-label="Scroll back" aria-controls="<?php echo esc_attr( $axs_id ); ?>-track"><i class="fas fa-arrow-left" aria-hidden="true"></i></button>
					<button type="button" class="axs-arrow" data-dir="1" aria-label="Scroll forward" aria-controls="<?php echo esc_attr( $axs_id ); ?>-track"><i class="fas fa-arrow-right" aria-hidden="true"></i></button>
				<?php endif; ?>
				<a class="axs-all" href="<?php echo esc_url( get_category_link( $axs_term ) ); ?>">View all <?php echo (int) $axs_term->count; ?> <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
			</div>
		</header>

		<?php if ( 'magazine' === $axs_layout ) :
			// Big story on the left, a list of headlines on the right.
			$axs_lead = array_shift( $axs_posts ); ?>
			<div class="axs-mag">
				<?php aurix_blog_card( $axs_lead, 'card', array( 'size' => 'large', 'words' => 32 ) ); ?>
				<?php if ( $axs_posts ) : ?>
					<div class="axs-mag__list">
						<?php foreach ( $axs_posts as $axs_p ) {
							aurix_blog_card( $axs_p, 'row' );
						} ?>
					</div>
				<?php endif; ?>
			</div>

		<?php elseif ( 'carousel' === $axs_layout ) : ?>
			<div class="axs-track" id="<?php echo esc_attr( $axs_id ); ?>-track" tabindex="0" aria-label="<?php echo esc_attr( $axs['title'] ); ?> articles">
				<?php foreach ( $axs_posts as $axs_p ) {
					aurix_blog_card( $axs_p, 'card', array( 'words' => 16 ) );
				} ?>
			</div>

		<?php elseif ( 'timeline' === $axs_layout ) : ?>
			<ol class="axs-tl">
				<?php foreach ( $axs_posts as $axs_p ) : ?>
					<li class="axs-tl__item">
						<div class="axs-tl__date" aria-hidden="true">
							<span class="axs-tl__day"><?php echo esc_html( get_the_date( 'd', $axs_p ) ); ?></span>
							<span class="axs-tl__mon"><?php echo esc_html( get_the_date( 'M', $axs_p ) ); ?></span>
							<span class="axs-tl__yr"><?php echo esc_html( get_the_date( 'Y', $axs_p ) ); ?></span>
						</div>
						<article class="axb-card axb-card--tl">
							<div class="axb-card__body">
								<?php
								echo aurix_blog_title( $axs_p, 'h3' ); // phpcs:ignore
								echo aurix_blog_excerpt( $axs_p, 28 ); // phpcs:ignore
								echo aurix_blog_meta( $axs_p ); // phpcs:ignore
								?>
							</div>
							<?php echo aurix_blog_media( $axs_p, 'medium_large' ); // phpcs:ignore ?>
						</article>
					</li>
				<?php endforeach; ?>
			</ol>

		<?php elseif ( 'feature' === $axs_layout ) : ?>
			<div class="axs-feat">
				<?php foreach ( $axs_posts as $axs_i => $axs_p ) {
					aurix_blog_card( $axs_p, 'panel', array( 'flip' => 1 === $axs_i % 2, 'words' => 40 ) );
				} ?>
			</div>

		<?php elseif ( 'mosaic' === $axs_layout ) : ?>
			<div class="axs-mosaic<?php echo count( $axs_posts ) < 3 ? ' axs-mosaic--2' : ''; ?>">
				<?php foreach ( $axs_posts as $axs_p ) {
					aurix_blog_card( $axs_p, 'card', array( 'words' => 18 ) );
				} ?>
			</div>

		<?php else : // list ?>
			<ol class="axs-list">
				<?php foreach ( $axs_posts as $axs_p ) : ?>
					<li><?php aurix_blog_card( $axs_p, 'row', array( 'words' => 14 ) ); ?></li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</div>
</section>
