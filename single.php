<?php
/**
 * Single article.
 *
 * Layout: navy hero (breadcrumb, category, title, summary, author/meta) with
 * the featured image overlapping its lower edge, then a three-column body on
 * wide screens — sticky share rail, article, sticky sidebar with the table of
 * contents and an enquiry card. Below the article: tags, author card,
 * previous/next, related articles and the enquiry band.
 */
get_header();

while ( have_posts() ) :
	the_post();

	$axa_id        = get_the_ID();
	$axa_cat       = aurix_blog_primary_cat();
	$axa_author_id = (int) get_the_author_meta( 'ID' );
	$axa_author    = get_author_posts_url( $axa_author_id );
	$axa_permalink = get_permalink();
	$axa_title     = get_the_title();

	// Run the content once, give every H2 an id and collect them for the
	// table of contents.
	$axa_toc     = array();
	$axa_used    = array();
	$axa_content = apply_filters( 'the_content', get_the_content() );
	$axa_content = preg_replace_callback( '/<h2([^>]*)>(.*?)<\/h2>/is', function ( $m ) use ( &$axa_toc, &$axa_used ) {
		$text = trim( wp_strip_all_tags( $m[2] ) );
		if ( preg_match( '/\sid=["\']([^"\']+)["\']/', $m[1], $id ) ) {
			$slug = $id[1];
			$attr = $m[1];
		} else {
			$base = sanitize_title( $text );
			$base = $base ? $base : 'section';
			$slug = $base;
			for ( $n = 2; isset( $axa_used[ $slug ] ); $n++ ) {
				$slug = $base . '-' . $n;
			}
			$attr = $m[1] . ' id="' . esc_attr( $slug ) . '"';
		}
		$axa_used[ $slug ] = true;
		$axa_toc[]         = array( 'slug' => $slug, 'text' => $text );
		return '<h2' . $attr . '>' . $m[2] . '</h2>';
	}, $axa_content );

	// Wrap tables so wide ones scroll sideways on phones instead of
	// stretching the page.
	$axa_content = preg_replace( '/<table\b/i', '<div class="axa-table" tabindex="0"><table', $axa_content );
	$axa_content = str_ireplace( '</table>', '</table></div>', $axa_content );

	$axa_share = array(
		'facebook' => array( 'Facebook', 'fab fa-facebook-f', 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $axa_permalink ) ),
		'x'        => array( 'X', 'fab fa-x-twitter', 'https://twitter.com/intent/tweet?url=' . rawurlencode( $axa_permalink ) . '&text=' . rawurlencode( wp_specialchars_decode( $axa_title, ENT_QUOTES ) ) ),
		'linkedin' => array( 'LinkedIn', 'fab fa-linkedin-in', 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $axa_permalink ) ),
		'whatsapp' => array( 'WhatsApp', 'fab fa-whatsapp', 'https://wa.me/?text=' . rawurlencode( wp_specialchars_decode( $axa_title, ENT_QUOTES ) . ' ' . $axa_permalink ) ),
	);
	$axa_share_html = '';
	foreach ( $axa_share as $axa_key => $axa_s ) {
		$axa_share_html .= '<a class="axa-share__btn axa-share__btn--' . esc_attr( $axa_key ) . '" href="' . esc_url( $axa_s[2] ) . '" target="_blank" rel="noopener" aria-label="Share on ' . esc_attr( $axa_s[0] ) . '"><i class="' . esc_attr( $axa_s[1] ) . '" aria-hidden="true"></i></a>';
	}
	$axa_share_html .= '<button type="button" class="axa-share__btn axa-copy" data-url="' . esc_url( $axa_permalink ) . '" aria-label="Copy link"><i class="fas fa-link" aria-hidden="true"></i><span class="axa-copy__tip" role="status"></span></button>';
	?>

	<div class="axa-progress" aria-hidden="true"><span id="axaProgress"></span></div>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'axa' ); ?>>

		<header class="axa-hero<?php echo has_post_thumbnail() ? ' has-media' : ''; ?>">
			<div class="axa-hero__inner">
				<nav class="axb-crumbs" aria-label="Breadcrumb">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
					<i class="fas fa-chevron-right" aria-hidden="true"></i>
					<a href="<?php echo esc_url( aurix_blog_url() ); ?>">Blog</a>
					<?php if ( $axa_cat ) : ?>
						<i class="fas fa-chevron-right" aria-hidden="true"></i>
						<a href="<?php echo esc_url( get_category_link( $axa_cat ) ); ?>"><?php echo esc_html( $axa_cat->name ); ?></a>
					<?php endif; ?>
				</nav>

				<?php if ( $axa_cat ) : ?>
					<a class="axb-chip axb-chip--light" href="<?php echo esc_url( get_category_link( $axa_cat ) ); ?>"><?php echo esc_html( $axa_cat->name ); ?></a>
				<?php endif; ?>

				<h1 class="axa-title"><?php the_title(); ?></h1>

				<?php if ( has_excerpt() ) : ?>
					<p class="axa-dek"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>

				<div class="axa-byline">
					<a class="axa-byline__author" href="<?php echo esc_url( $axa_author ); ?>">
						<?php echo get_avatar( $axa_author_id, 44, 'mystery', '', array( 'class' => 'axa-avatar' ) ); ?>
						<span>
							<small>Written by</small>
							<strong><?php the_author(); ?></strong>
						</span>
					</a>
					<span class="axa-byline__sep" aria-hidden="true"></span>
					<span class="axa-byline__item">
						<small>Published</small>
						<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></time>
					</span>
					<?php if ( get_the_modified_date( 'Ymd' ) > get_the_date( 'Ymd' ) ) : ?>
						<span class="axa-byline__item">
							<small>Updated</small>
							<time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"><?php echo esc_html( get_the_modified_date( 'M j, Y' ) ); ?></time>
						</span>
					<?php endif; ?>
					<span class="axa-byline__item">
						<small>Reading time</small>
						<span><?php echo (int) aurix_blog_read_time(); ?> min</span>
					</span>
				</div>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="axa-media">
				<?php echo aurix_blog_media( get_post(), 'full', true, '(max-width: 1180px) 100vw, 1180px' ); // phpcs:ignore -- escaped in helper ?>
				<?php $axa_caption = get_the_post_thumbnail_caption(); ?>
				<?php if ( $axa_caption ) : ?>
					<figcaption><?php echo esc_html( $axa_caption ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endif; ?>

		<div class="axa-layout<?php echo count( $axa_toc ) >= 2 ? ' has-toc' : ''; ?>">

			<aside class="axa-rail" aria-label="Share this article">
				<span class="axa-rail__label">Share</span>
				<div class="axa-share axa-share--col"><?php echo $axa_share_html; // phpcs:ignore -- escaped above ?></div>
			</aside>

			<div class="axa-main">
				<?php if ( count( $axa_toc ) >= 2 ) : ?>
					<details class="axa-toc axa-toc--inline">
						<summary><span><i class="fas fa-list-ul" aria-hidden="true"></i> In this article</span><i class="fas fa-chevron-down" aria-hidden="true"></i></summary>
						<ol>
							<?php foreach ( $axa_toc as $axa_item ) : ?>
								<li><a href="#<?php echo esc_attr( $axa_item['slug'] ); ?>"><?php echo esc_html( $axa_item['text'] ); ?></a></li>
							<?php endforeach; ?>
						</ol>
					</details>
				<?php endif; ?>

				<div class="axa-content entry-content">
					<?php echo $axa_content; // phpcs:ignore -- already run through the_content filters ?>
				</div>

				<?php
				wp_link_pages( array(
					'before' => '<nav class="axa-pages"><span>Pages:</span>',
					'after'  => '</nav>',
				) );
				?>

				<footer class="axa-foot">
					<?php $axa_tags = get_the_tags(); ?>
					<?php if ( $axa_tags ) : ?>
						<div class="axa-tags">
							<?php foreach ( $axa_tags as $axa_tag ) : ?>
								<a href="<?php echo esc_url( get_tag_link( $axa_tag ) ); ?>">#<?php echo esc_html( $axa_tag->name ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="axa-share-row">
						<span>Found this useful? Share it</span>
						<div class="axa-share"><?php echo $axa_share_html; // phpcs:ignore -- escaped above ?></div>
					</div>

					<?php
					$axa_bio   = get_the_author_meta( 'description' );
					$axa_count = (int) count_user_posts( $axa_author_id, 'post', true );
					?>
					<section class="axa-author" aria-label="About the author">
						<a href="<?php echo esc_url( $axa_author ); ?>" class="axa-author__photo"><?php echo get_avatar( $axa_author_id, 88, 'mystery' ); ?></a>
						<div class="axa-author__body">
							<span class="axb-eyebrow">About the author</span>
							<h2 class="axa-author__name"><a href="<?php echo esc_url( $axa_author ); ?>"><?php the_author(); ?></a></h2>
							<?php if ( $axa_bio ) : ?>
								<p><?php echo esc_html( $axa_bio ); ?></p>
							<?php endif; ?>
							<a class="axa-author__link" href="<?php echo esc_url( $axa_author ); ?>">
								<?php echo $axa_count > 1 ? 'View all ' . (int) $axa_count . ' articles' : 'View profile'; ?>
								<i class="fas fa-arrow-right" aria-hidden="true"></i>
							</a>
						</div>
					</section>
				</footer>
			</div>

			<aside class="axa-side">
				<?php if ( count( $axa_toc ) >= 2 ) : ?>
					<nav class="axa-toc axa-toc--side" aria-label="In this article">
						<div class="axa-toc__head">
							<span>In this article</span>
							<span class="axa-toc__pct" id="axaPct">0%</span>
						</div>
						<ol>
							<?php foreach ( $axa_toc as $axa_item ) : ?>
								<li><a href="#<?php echo esc_attr( $axa_item['slug'] ); ?>"><?php echo esc_html( $axa_item['text'] ); ?></a></li>
							<?php endforeach; ?>
						</ol>
					</nav>
				<?php endif; ?>

				<div class="axa-enquiry">
					<span class="axa-enquiry__icon"><i class="fas fa-tooth" aria-hidden="true"></i></span>
					<h3>Need these instruments in bulk?</h3>
					<p>Wholesale pricing, OEM and private label for clinics and distributors.</p>
					<a class="axb-btn axb-btn--gold axb-btn--block" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Get a quote</a>
					<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
						<a class="axa-enquiry__link" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Browse the catalogue <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
					<?php endif; ?>
				</div>
			</aside>
		</div>
	</article>

	<?php
	$axa_prev = get_previous_post();
	$axa_next = get_next_post();
	if ( $axa_prev || $axa_next ) :
		?>
		<nav class="axa-pager" aria-label="More articles">
			<?php foreach ( array( 'prev' => $axa_prev, 'next' => $axa_next ) as $axa_dir => $axa_p ) : ?>
				<?php if ( ! $axa_p ) : ?>
					<span class="axa-pager__empty"></span>
					<?php continue; ?>
				<?php endif; ?>
				<a class="axa-pager__link axa-pager__link--<?php echo esc_attr( $axa_dir ); ?>" href="<?php echo esc_url( get_permalink( $axa_p ) ); ?>">
					<?php if ( has_post_thumbnail( $axa_p ) ) : ?>
						<span class="axa-pager__thumb"><?php echo aurix_blog_media( $axa_p, 'medium' ); // phpcs:ignore -- escaped in helper ?></span>
					<?php endif; ?>
					<span class="axa-pager__text">
						<small><?php echo 'prev' === $axa_dir ? '<i class="fas fa-arrow-left" aria-hidden="true"></i> Previous article' : 'Next article <i class="fas fa-arrow-right" aria-hidden="true"></i>'; ?></small>
						<strong><?php echo esc_html( get_the_title( $axa_p ) ); ?></strong>
					</span>
				</a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<?php
	// Related: same category first, topped up with the latest posts.
	$axa_related = array();
	if ( $axa_cat ) {
		$axa_related = get_posts( array(
			'posts_per_page' => 3,
			'post__not_in'   => array( $axa_id ),
			'cat'            => $axa_cat->term_id,
		) );
	}
	if ( count( $axa_related ) < 3 ) {
		$axa_related = array_merge( $axa_related, get_posts( array(
			'posts_per_page' => 3 - count( $axa_related ),
			'post__not_in'   => array_merge( array( $axa_id ), wp_list_pluck( $axa_related, 'ID' ) ),
		) ) );
	}
	if ( $axa_related ) :
		?>
		<section class="axa-related">
			<div class="axb-wrap">
				<div class="axb-section-head">
					<h2>Keep reading</h2>
					<a href="<?php echo esc_url( aurix_blog_url() ); ?>">All articles <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
				</div>
				<div class="axb-grid">
					<?php foreach ( $axa_related as $axa_r ) {
						aurix_blog_card( $axa_r, 'card' );
					} ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php aurix_blog_cta(); ?>

<?php endwhile; ?>

<?php get_footer(); ?>
