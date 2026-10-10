<?php get_header();

while ( have_posts() ) : the_post();

	// Reading time (avg 200 wpm), computed from plain-text content.
	$word_count  = str_word_count( wp_strip_all_tags( get_the_content() ) );
	$reading_min = max( 1, (int) ceil( $word_count / 200 ) );

	$categories  = get_the_category();
	$primary_cat = ! empty( $categories ) ? $categories[0] : null;
	$has_image   = has_post_thumbnail();

	// Build the content once, then extract H2s into a table of contents,
	// inject matching #ids so the TOC links jump correctly, and -- for
	$content = apply_filters( 'the_content', get_the_content() );
	$toc     = array();
	$content = preg_replace_callback( '/<h2([^>]*)>(.*?)<\/h2>/is', function ( $m ) use ( &$toc ) {
		$text  = wp_strip_all_tags( $m[2] );
		$slug  = sanitize_title( $text );
		$toc[] = array( 'slug' => $slug, 'text' => $text );
		return '<h2' . $m[1] . ' id="' . esc_attr( $slug ) . '">' . $m[2] . '</h2>';
	}, $content );

	$blog_url = home_url( '/blog' );
	?>

	<!-- Header -- clean, light treatment (breadcrumb/category/title/meta on
	     the page's normal background) rather than a dark full-bleed overlay
	     band. The featured image, if there is one, displays as a normal
	     contained image below the meta row instead of as a background. -->
	<div class="aurix-page-wrap abp-header-wrap" style="max-width:1400px;">
		<nav class="abp-breadcrumb" aria-label="Breadcrumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
			<span>/</span>
			<a href="<?php echo esc_url( $blog_url ); ?>">Blog</a>
			<span>/</span>
			<span class="abp-breadcrumb-current"><?php the_title(); ?></span>
		</nav>

		<h1 class="page-title abp-title"><?php the_title(); ?></h1>

		<div class="abp-meta">
			<?php if ( $primary_cat ) : ?>
				<span><a href="<?php echo esc_url( get_category_link( $primary_cat->term_id ) ); ?>" class="abp-cat-badge abp-cat-badge--inline"><?php echo esc_html( $primary_cat->name ); ?></a></span>
			<?php endif; ?>
			<span><i class="fas fa-calendar"></i> <?php echo esc_html( get_the_date() ); ?></span>
			<span class="abp-meta-author"><?php echo get_avatar( get_the_author_meta( 'ID' ), 22, '', '', array( 'class' => 'abp-meta-avatar' ) ); ?> <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" class="abp-meta-author-link"><?php the_author(); ?></a></span>
			<span><i class="fas fa-clock"></i> <?php echo esc_html( $reading_min ); ?> min read</span>
		</div>

		<?php if ( $has_image ) : ?>
			<div class="abp-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
		<?php endif; ?>
	</div>

	<!-- Reading progress bar -->
	<div class="abp-progress-track"><div class="abp-progress-fill" id="abpProgressFill"></div></div>

	<div class="aurix-page-wrap aurix-blog-post" style="max-width:1400px;">

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'abp-article' ); ?>>

			<div class="abp-sidebar">
				<?php if ( count( $toc ) >= 2 ) : ?>
					<details class="abp-toc" id="abpToc" open>
						<summary class="abp-toc-title"><i class="fas fa-list-ul"></i> In this article <i class="fas fa-chevron-down abp-toc-chevron"></i></summary>
						<ul>
							<?php foreach ( $toc as $item ) : ?>
								<li><a href="#<?php echo esc_attr( $item['slug'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</details>
				<?php endif; ?>

				<!-- Desktop-only: compact author card + recent posts (the full
				     author bio and related-posts grid at the bottom of the
				     article already cover this on mobile/tablet, so these
				     stay hidden there rather than showing the same thing twice). -->
				<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" class="abp-sidebar-author">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 56 ); ?>
					<div>
						<span class="abp-sidebar-author-label">Author</span>
						<div class="abp-sidebar-author-name"><?php the_author(); ?></div>
						<?php $short_bio = get_the_author_meta( 'description' ); ?>
						<?php if ( $short_bio ) : ?>
							<p><?php echo esc_html( wp_trim_words( $short_bio, 14 ) ); ?></p>
						<?php else : ?>
							<span class="abp-sidebar-author-view">View profile <i class="fas fa-arrow-right"></i></span>
						<?php endif; ?>
					</div>
				</a>

				<?php
				$recent_posts = get_posts( array(
					'posts_per_page' => 4,
					'post__not_in'   => array( get_the_ID() ),
					'orderby'        => 'date',
				) );
				if ( $recent_posts ) :
					?>
					<div class="abp-sidebar-recent">
						<div class="abp-sidebar-recent-title">Recent Posts</div>
						<?php foreach ( $recent_posts as $rp ) : ?>
							<a href="<?php echo esc_url( get_permalink( $rp ) ); ?>" class="abp-sidebar-recent-item">
								<?php if ( has_post_thumbnail( $rp->ID ) ) : ?>
									<div class="abp-sidebar-recent-thumb"><?php echo get_the_post_thumbnail( $rp->ID, 'thumbnail' ); ?></div>
								<?php endif; ?>
								<div>
									<h4><?php echo esc_html( get_the_title( $rp ) ); ?></h4>
									<span><?php echo esc_html( get_the_date( '', $rp ) ); ?></span>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="entry-content"><?php echo $content; // phpcs:ignore -- already run through the_content filters above ?></div>

			<?php
			$tags = get_the_tags();
			if ( $tags ) :
				?>
				<div class="abp-tags">
					<?php foreach ( $tags as $tag ) : ?>
						<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="abp-tag"><?php echo esc_html( $tag->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- Share -->
			<div class="abp-share">
				<span class="abp-share-label">Share this article</span>
				<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode( get_permalink() ); ?>" target="_blank" rel="noopener" aria-label="Share on Facebook"><i class="fab fa-facebook-f"></i></a>
				<a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( get_permalink() ); ?>&text=<?php echo urlencode( get_the_title() ); ?>" target="_blank" rel="noopener" aria-label="Share on X"><i class="fab fa-x-twitter"></i></a>
				<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode( get_permalink() ); ?>" target="_blank" rel="noopener" aria-label="Share on LinkedIn"><i class="fab fa-linkedin-in"></i></a>
				<button type="button" class="abp-copy-link" data-url="<?php echo esc_url( get_permalink() ); ?>" aria-label="Copy link"><i class="fas fa-link"></i></button>
			</div>

			<!-- Author bio -->
			<div class="abp-author">
				<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 64 ); ?>
				</a>
				<div class="abp-author-body">
					<span class="abp-author-label">Author</span>
					<div class="abp-author-name">
						<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php the_author(); ?></a>
					</div>
					<?php
					$bio          = get_the_author_meta( 'description' );
					$author_id    = get_the_author_meta( 'ID' );
					$author_count = (int) count_user_posts( $author_id, 'post' );
					?>
					<?php if ( $bio ) : ?>
						<p class="abp-author-bio"><?php echo esc_html( $bio ); ?></p>
					<?php endif; ?>
					<div class="abp-author-actions">
						<?php if ( $author_count > 1 ) : ?>
							<span class="abp-author-count"><?php echo esc_html( $author_count ); ?> articles published</span>
						<?php endif; ?>
						<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>" class="abp-author-btn">View all articles <i class="fas fa-arrow-right"></i></a>
					</div>
				</div>
			</div>

		</article>

		<!-- Prev / Next -->
		<?php
		$prev = get_previous_post();
		$next = get_next_post();
		if ( $prev || $next ) :
			?>
			<div class="abp-pagenav">
				<?php if ( $prev ) : ?>
					<a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" class="abp-pagenav-link abp-pagenav-prev">
						<span class="abp-pagenav-dir"><i class="fas fa-arrow-left"></i> Previous</span>
						<span class="abp-pagenav-title"><?php echo esc_html( get_the_title( $prev ) ); ?></span>
					</a>
				<?php else : ?><span></span><?php endif; ?>
				<?php if ( $next ) : ?>
					<a href="<?php echo esc_url( get_permalink( $next ) ); ?>" class="abp-pagenav-link abp-pagenav-next">
						<span class="abp-pagenav-dir">Next <i class="fas fa-arrow-right"></i></span>
						<span class="abp-pagenav-title"><?php echo esc_html( get_the_title( $next ) ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<!-- Related posts -->
		<?php
		$related_args = array(
			'posts_per_page' => 3,
			'post__not_in'   => array( get_the_ID() ),
			'orderby'        => 'rand',
		);
		if ( $primary_cat ) {
			$related_args['category__in'] = array( $primary_cat->term_id );
		}
		$related = get_posts( $related_args );
		if ( $related ) :
			?>
			<div class="abp-related">
				<h2 class="abp-related-title">Related Articles</h2>
				<div class="abp-related-grid">
					<?php foreach ( $related as $rp ) : ?>
						<a href="<?php echo esc_url( get_permalink( $rp ) ); ?>" class="abp-related-card<?php echo has_post_thumbnail( $rp->ID ) ? '' : ' abp-related-card--text-only'; ?>">
							<?php if ( has_post_thumbnail( $rp->ID ) ) : ?>
								<div class="abp-related-thumb"><?php echo get_the_post_thumbnail( $rp->ID, 'medium' ); ?></div>
							<?php endif; ?>
							<div class="abp-related-body">
								<span class="abp-related-date"><?php echo esc_html( get_the_date( '', $rp ) ); ?></span>
								<h3><?php echo esc_html( get_the_title( $rp ) ); ?></h3>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

	</div>

<?php endwhile; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
	// Different default per device: collapsed on mobile (saves scroll space),
	// expanded on tablet/desktop (matches the existing inline-box behavior).
	var toc = document.getElementById('abpToc');
	if (toc && window.innerWidth < 768) {
		toc.removeAttribute('open');
	}

	var copyBtn = document.querySelector('.abp-copy-link');
	if (copyBtn) {
		copyBtn.addEventListener('click', function () {
			navigator.clipboard.writeText(copyBtn.dataset.url).then(function () {
				var icon = copyBtn.querySelector('i');
				icon.className = 'fas fa-check';
				setTimeout(function () { icon.className = 'fas fa-link'; }, 1500);
			});
		});
	}

	var article = document.querySelector('.abp-article');
	var fill = document.getElementById('abpProgressFill');
	if (article && fill) {
		window.addEventListener('scroll', function () {
			var rect = article.getBoundingClientRect();
			var articleHeight = article.offsetHeight;
			var viewportH = window.innerHeight;
			var scrolled = Math.min(Math.max((-rect.top) / (articleHeight - viewportH), 0), 1);
			fill.style.width = (scrolled * 100) + '%';
		}, { passive: true });
	}
});
</script>

<?php get_footer(); ?>
