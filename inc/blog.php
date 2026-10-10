<?php
/**
 * Blog helpers shared by the blog listing (index.php, page-blog.php,
 * category/tag/date/search archives), the article page (single.php) and the
 * author archive (author.php).
 *
 * Markup classes use the "axb-" prefix; styles live in css/blog.css and
 * behaviour in js/blog.js, both loaded only on blog screens.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Number of posts per listing page (1 lead + 2 side stories + 3x3 grid). */
define( 'AURIX_BLOG_PER_PAGE', 12 );

/**
 * True on every screen that shows blog content.
 */
function aurix_is_blog_screen() {
	if ( is_admin() ) {
		return false;
	}
	return is_home() || is_singular( 'post' ) || is_category() || is_tag() || is_author()
		|| is_date() || ( is_search() && 'product' !== get_query_var( 'post_type' ) )
		|| is_page( 'blog' );
}

/** Show 12 posts per page on the posts page and blog archives. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_home() || $q->is_category() || $q->is_tag() || $q->is_author() || $q->is_date() ) {
		$q->set( 'posts_per_page', AURIX_BLOG_PER_PAGE );
	}
} );

add_action( 'wp_enqueue_scripts', function () {
	if ( ! aurix_is_blog_screen() ) {
		return;
	}
	$ver = wp_get_theme( get_template() )->get( 'Version' );
	wp_enqueue_style( 'aurix-blog', get_template_directory_uri() . '/css/blog.css', array( 'aurix-style' ), $ver );
	wp_enqueue_script( 'aurix-blog', get_template_directory_uri() . '/js/blog.js', array(), $ver, true );
}, 20 );

add_filter( 'body_class', function ( $classes ) {
	if ( aurix_is_blog_screen() ) {
		$classes[] = 'aurix-blog-screen';
	}
	return $classes;
} );

/**
 * Estimated reading time in minutes (200 words per minute).
 */
function aurix_blog_read_time( $post = null ) {
	$post  = get_post( $post );
	$words = str_word_count( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * The first category of a post that isn't "Uncategorized", or null.
 */
function aurix_blog_primary_cat( $post = null ) {
	$post = get_post( $post );
	foreach ( (array) get_the_category( $post->ID ) as $cat ) {
		if ( 'uncategorized' !== $cat->slug ) {
			return $cat;
		}
	}
	return null;
}

/**
 * Categories shown as filter chips on the blog listing: every category that
 * has posts, except "Uncategorized".
 */
function aurix_blog_filter_cats() {
	$cats = get_categories( array(
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'exclude'    => array( (int) get_option( 'default_category' ) ),
	) );
	return array_filter( $cats, function ( $c ) {
		return 'uncategorized' !== $c->slug;
	} );
}

/**
 * URL of the main blog listing: the "Posts page" if one is set under
 * Settings → Reading, otherwise the Page with slug "blog", otherwise /blog.
 */
function aurix_blog_url() {
	$posts_page = (int) get_option( 'page_for_posts' );
	if ( $posts_page ) {
		return get_permalink( $posts_page );
	}
	$page = get_page_by_path( 'blog' );
	return $page ? get_permalink( $page ) : home_url( '/blog/' );
}

/**
 * Article card used by every listing.
 *
 * @param WP_Post|int $post
 * @param string      $variant 'card' (grid), 'lead' (large overlay story) or 'side' (compact horizontal).
 */
function aurix_blog_card( $post, $variant = 'card' ) {
	$post  = get_post( $post );
	$url   = get_permalink( $post );
	$cat   = aurix_blog_primary_cat( $post );
	$thumb = has_post_thumbnail( $post );
	$size  = 'lead' === $variant ? 'large' : ( 'side' === $variant ? 'medium' : 'medium_large' );
	$words = 'lead' === $variant ? 28 : 20;
	$classes = 'axb-card axb-card--' . $variant . ( $thumb ? '' : ' axb-card--noimg' );
	?>
	<article class="<?php echo esc_attr( $classes ); ?>">
		<div class="axb-card__media">
			<?php if ( $thumb ) : ?>
				<?php echo get_the_post_thumbnail( $post, $size, array( 'loading' => 'lead' === $variant ? 'eager' : 'lazy' ) ); ?>
			<?php else : ?>
				<span class="axb-card__placeholder" aria-hidden="true"><span>AURIX</span></span>
			<?php endif; ?>
		</div>
		<div class="axb-card__body">
			<?php if ( $cat ) : ?>
				<span class="axb-chip"><?php echo esc_html( $cat->name ); ?></span>
			<?php endif; ?>
			<h3 class="axb-card__title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3>
			<?php if ( 'side' !== $variant ) : ?>
				<p class="axb-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post ), $words, '…' ) ); ?></p>
			<?php endif; ?>
			<div class="axb-card__meta">
				<time datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>"><?php echo esc_html( get_the_date( 'M j, Y', $post ) ); ?></time>
				<span aria-hidden="true">·</span>
				<span><?php echo (int) aurix_blog_read_time( $post ); ?> min read</span>
			</div>
		</div>
	</article>
	<?php
}

/**
 * Navy hero band used at the top of every listing.
 *
 * @param array $args eyebrow, title, intro, search (bool), active_cat (term ID), extra (HTML after intro).
 */
function aurix_blog_hero( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'eyebrow'    => 'The Aurix Journal',
		'title'      => 'Insights & News',
		'intro'      => '',
		'search'     => true,
		'chips'      => true,
		'active_cat' => 0,
		'extra'      => '',
	) );
	$cats = $args['chips'] ? aurix_blog_filter_cats() : array();
	?>
	<header class="axb-hero">
		<div class="axb-hero__inner">
			<nav class="axb-crumbs" aria-label="Breadcrumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
				<i class="fas fa-chevron-right" aria-hidden="true"></i>
				<?php if ( is_home() || is_page( 'blog' ) ) : ?>
					<span aria-current="page">Blog</span>
				<?php else : ?>
					<a href="<?php echo esc_url( aurix_blog_url() ); ?>">Blog</a>
					<i class="fas fa-chevron-right" aria-hidden="true"></i>
					<span aria-current="page"><?php echo esc_html( wp_specialchars_decode( wp_strip_all_tags( $args['title'] ), ENT_QUOTES ) ); ?></span>
				<?php endif; ?>
			</nav>
			<span class="axb-eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></span>
			<h1 class="axb-hero__title"><?php echo wp_kses_post( $args['title'] ); ?></h1>
			<?php if ( $args['intro'] ) : ?>
				<p class="axb-hero__intro"><?php echo wp_kses_post( $args['intro'] ); ?></p>
			<?php endif; ?>
			<?php echo $args['extra']; // phpcs:ignore -- built by the calling template ?>

			<?php if ( $args['search'] ) : ?>
				<form class="axb-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<i class="fas fa-search" aria-hidden="true"></i>
					<input type="search" name="s" value="<?php echo esc_attr( is_search() ? get_search_query() : '' ); ?>" placeholder="Search articles…" aria-label="Search articles">
					<input type="hidden" name="post_type" value="post">
					<button type="submit">Search</button>
				</form>
			<?php endif; ?>
		</div>

		<?php if ( count( $cats ) > 1 ) : ?>
			<nav class="axb-filters" aria-label="Blog categories">
				<div class="axb-filters__track">
					<a href="<?php echo esc_url( aurix_blog_url() ); ?>" class="axb-filter<?php echo $args['active_cat'] ? '' : ' is-active'; ?>"<?php echo $args['active_cat'] ? '' : ' aria-current="page"'; ?>>All articles</a>
					<?php foreach ( $cats as $cat ) :
						$active = (int) $args['active_cat'] === (int) $cat->term_id; ?>
						<a href="<?php echo esc_url( get_category_link( $cat ) ); ?>" class="axb-filter<?php echo $active ? ' is-active' : ''; ?>"<?php echo $active ? ' aria-current="page"' : ''; ?>>
							<?php echo esc_html( $cat->name ); ?> <span><?php echo (int) $cat->count; ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</nav>
		<?php endif; ?>
	</header>
	<?php
}

/**
 * Numbered pagination for a listing query.
 */
function aurix_blog_pagination( $query ) {
	$total = (int) $query->max_num_pages;
	if ( $total < 2 ) {
		return;
	}
	$current = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	$links   = paginate_links( array(
		'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
		'format'    => '?paged=%#%',
		'current'   => $current,
		'total'     => $total,
		'mid_size'  => 1,
		'prev_text' => '<i class="fas fa-arrow-left" aria-hidden="true"></i><span>Newer</span>',
		'next_text' => '<span>Older</span><i class="fas fa-arrow-right" aria-hidden="true"></i>',
	) );
	echo '<nav class="axb-pagination" aria-label="Blog pages">' . $links . '</nav>'; // phpcs:ignore -- paginate_links output
}

/**
 * Closing call-to-action band (wholesale / OEM enquiries).
 */
function aurix_blog_cta() {
	$email = get_theme_mod( 'aurix_email', 'info@aurixinternational.com' );
	?>
	<section class="axb-cta">
		<div class="axb-cta__inner">
			<div>
				<span class="axb-eyebrow">Work with Aurix</span>
				<h2 class="axb-cta__title">Sourcing instruments for your clinic or distribution business?</h2>
				<p>Wholesale pricing, OEM manufacturing and private label programmes — talk to our team.</p>
			</div>
			<div class="axb-cta__actions">
				<a class="axb-btn axb-btn--gold" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Request a quote <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
				<a class="axb-btn axb-btn--ghost" href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><i class="fas fa-envelope" aria-hidden="true"></i> <?php echo esc_html( antispambot( $email ) ); ?></a>
			</div>
		</div>
	</section>
	<?php
}
