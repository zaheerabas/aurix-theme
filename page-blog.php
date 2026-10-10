<?php
/**
 * Blog page (Page with slug "blog").
 *
 * The page is organised into sections, one per "section" post category:
 *   News, Product Guides, Events, Industry Insights.
 * To place an article in a section, tick that category when writing the post.
 * Each section has its own layout (see 'layout' below). Empty sections are
 * hidden automatically. Any published post that is not in one of the section
 * categories appears under "More Articles" at the end.
 */
get_header();

if ( ! function_exists( 'aurix_blog_sections' ) ) {
	function aurix_blog_sections() {
		return array(
			'news'              => array(
				'title'  => 'Latest News',
				'intro'  => 'Company news, announcements and updates from Aurix International.',
				'icon'   => 'fa-newspaper',
				'layout' => 'magazine', // lead story + headline list
				'show'   => 5,
			),
			'product-guides'    => array(
				'title'  => 'Product Guides',
				'intro'  => 'In-depth guides to our dental instrument categories: how each instrument works, what to look for, and where to buy it.',
				'icon'   => 'fa-tooth',
				'layout' => 'grid', // image card grid
				'show'   => 6,
			),
			'events'            => array(
				'title'  => 'Events',
				'intro'  => 'Trade shows, exhibitions and events where you can meet the Aurix International team.',
				'icon'   => 'fa-calendar-alt',
				'layout' => 'timeline', // date badges on a vertical line
				'show'   => 4,
			),
			'industry-insights' => array(
				'title'  => 'Industry Insights',
				'intro'  => 'Market insights, supplier guides and trends from the dental industry.',
				'icon'   => 'fa-chart-line',
				'layout' => 'feature', // wide dark feature panels
				'show'   => 3,
			),
		);
	}
}

if ( ! function_exists( 'aurix_blog_read_time' ) ) {
	function aurix_blog_read_time( $post ) {
		$words = str_word_count( wp_strip_all_tags( $post->post_content ) );
		return max( 1, (int) ceil( $words / 200 ) );
	}
}

if ( ! function_exists( 'aurix_blog_meta' ) ) {
	function aurix_blog_meta( $post ) {
		return '<div class="abx-meta"><span><i class="fas fa-calendar" aria-hidden="true"></i> ' . esc_html( get_the_date( '', $post ) ) . '</span><span><i class="fas fa-clock" aria-hidden="true"></i> ' . (int) aurix_blog_read_time( $post ) . ' min read</span></div>';
	}
}

if ( ! function_exists( 'aurix_blog_thumb' ) ) {
	function aurix_blog_thumb( $post, $class, $icon, $size = 'medium_large' ) {
		$html = has_post_thumbnail( $post )
			? get_the_post_thumbnail( $post->ID, $size )
			: '<span class="abx-thumb-empty"><i class="fas ' . esc_attr( $icon ) . '"></i></span>';
		return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( get_permalink( $post ) ) . '" tabindex="-1" aria-hidden="true">' . $html . '</a>';
	}
}

if ( ! function_exists( 'aurix_blog_title' ) ) {
	function aurix_blog_title( $post, $tag = 'h3' ) {
		return '<' . $tag . ' class="abx-h"><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a></' . $tag . '>';
	}
}

if ( ! function_exists( 'aurix_blog_excerpt' ) ) {
	function aurix_blog_excerpt( $post, $words = 18 ) {
		return '<p class="abx-ex">' . esc_html( wp_trim_words( get_the_excerpt( $post ), $words ) ) . '</p>';
	}
}

// Collect every published post once, then sort each into its section.
$aurix_all = get_posts( array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 300,
	'orderby'        => 'date',
	'order'          => 'DESC',
) );

$aurix_sections = aurix_blog_sections();
$aurix_buckets  = array_fill_keys( array_keys( $aurix_sections ), array() );
$aurix_other    = array();

foreach ( $aurix_all as $aurix_post ) {
	$aurix_placed = false;
	foreach ( array_keys( $aurix_sections ) as $aurix_slug ) {
		if ( has_category( $aurix_slug, $aurix_post ) ) {
			$aurix_buckets[ $aurix_slug ][] = $aurix_post;
			$aurix_placed                   = true;
			break;
		}
	}
	if ( ! $aurix_placed ) {
		$aurix_other[] = $aurix_post;
	}
}

if ( $aurix_other ) {
	$aurix_sections['more'] = array(
		'title'  => 'More Articles',
		'intro'  => 'More reading from the Aurix International team.',
		'icon'   => 'fa-book-open',
		'layout' => 'list', // numbered headline list
		'show'   => 10,
	);
	$aurix_buckets['more'] = $aurix_other;
}
?>
<div class="aurix-page-wrap aurix-blog-index abx" style="max-width:1400px;">

	<header class="abx-header">
		<h1 class="abx-title">Blog</h1>
		<p class="abx-subtitle">News, product guides, events and industry insights from Aurix International.</p>
	</header>

	<?php if ( ! $aurix_all ) : ?>
		<p class="abx-empty">No articles yet. Please check back soon.</p>
	<?php else : ?>

	<nav class="abx-jump" aria-label="Blog sections">
		<?php foreach ( $aurix_sections as $aurix_slug => $aurix_sec ) :
			if ( empty( $aurix_buckets[ $aurix_slug ] ) ) { continue; } ?>
			<a href="#abx-<?php echo esc_attr( $aurix_slug ); ?>">
				<i class="fas <?php echo esc_attr( $aurix_sec['icon'] ); ?>" aria-hidden="true"></i>
				<?php echo esc_html( $aurix_sec['title'] ); ?>
				<span><?php echo (int) count( $aurix_buckets[ $aurix_slug ] ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>

	<?php foreach ( $aurix_sections as $aurix_slug => $aurix_sec ) :
		$aurix_posts = $aurix_buckets[ $aurix_slug ];
		if ( empty( $aurix_posts ) ) { continue; }
		$aurix_total  = count( $aurix_posts );
		$aurix_show   = (int) $aurix_sec['show'];
		$aurix_layout = $aurix_sec['layout'];
		$aurix_icon   = $aurix_sec['icon'];
		?>
	<section class="abx-section abx-l-<?php echo esc_attr( $aurix_layout ); ?>" id="abx-<?php echo esc_attr( $aurix_slug ); ?>">
		<div class="abx-section-head">
			<div>
				<h2 class="abx-section-title"><i class="fas <?php echo esc_attr( $aurix_icon ); ?>" aria-hidden="true"></i> <?php echo esc_html( $aurix_sec['title'] ); ?></h2>
				<p class="abx-section-intro"><?php echo esc_html( $aurix_sec['intro'] ); ?></p>
			</div>
			<span class="abx-count"><?php echo (int) $aurix_total; ?> <?php echo 1 === $aurix_total ? 'article' : 'articles'; ?></span>
		</div>

		<?php if ( 'magazine' === $aurix_layout ) :
			// Lead story on the left, headline list on the right.
			$aurix_lead = array_shift( $aurix_posts ); ?>
			<div class="abx-mag">
				<article class="abx-mag-lead">
					<?php echo aurix_blog_thumb( $aurix_lead, 'abx-mag-img', $aurix_icon, 'large' ); ?>
					<div class="abx-mag-body">
						<span class="abx-kicker">Top story</span>
						<?php echo aurix_blog_title( $aurix_lead, 'h3' ); ?>
						<?php echo aurix_blog_excerpt( $aurix_lead, 32 ); ?>
						<?php echo aurix_blog_meta( $aurix_lead ); ?>
					</div>
				</article>
				<?php if ( $aurix_posts ) : ?>
				<div class="abx-mag-list">
					<?php foreach ( $aurix_posts as $aurix_i => $aurix_p ) : ?>
					<article class="abx-mag-item"<?php echo ( $aurix_i + 1 ) >= $aurix_show ? ' hidden data-more="1"' : ''; ?>>
						<?php echo aurix_blog_thumb( $aurix_p, 'abx-mag-thumb', $aurix_icon, 'thumbnail' ); ?>
						<div>
							<?php echo aurix_blog_title( $aurix_p, 'h4' ); ?>
							<?php echo aurix_blog_meta( $aurix_p ); ?>
						</div>
					</article>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>

		<?php elseif ( 'timeline' === $aurix_layout ) : ?>
			<ol class="abx-tl">
				<?php foreach ( $aurix_posts as $aurix_i => $aurix_p ) : ?>
				<li class="abx-tl-item"<?php echo $aurix_i >= $aurix_show ? ' hidden data-more="1"' : ''; ?>>
					<div class="abx-tl-date">
						<span class="abx-tl-day"><?php echo esc_html( get_the_date( 'd', $aurix_p ) ); ?></span>
						<span class="abx-tl-mon"><?php echo esc_html( get_the_date( 'M Y', $aurix_p ) ); ?></span>
					</div>
					<div class="abx-tl-card">
						<div class="abx-tl-text">
							<?php echo aurix_blog_title( $aurix_p, 'h3' ); ?>
							<?php echo aurix_blog_excerpt( $aurix_p, 26 ); ?>
							<?php echo aurix_blog_meta( $aurix_p ); ?>
						</div>
						<?php echo aurix_blog_thumb( $aurix_p, 'abx-tl-thumb', $aurix_icon ); ?>
					</div>
				</li>
				<?php endforeach; ?>
			</ol>

		<?php elseif ( 'feature' === $aurix_layout ) : ?>
			<div class="abx-feat">
				<?php foreach ( $aurix_posts as $aurix_i => $aurix_p ) : ?>
				<article class="abx-feat-item<?php echo 1 === $aurix_i % 2 ? ' is-flip' : ''; ?>"<?php echo $aurix_i >= $aurix_show ? ' hidden data-more="1"' : ''; ?>>
					<?php echo aurix_blog_thumb( $aurix_p, 'abx-feat-img', $aurix_icon, 'large' ); ?>
					<div class="abx-feat-body">
						<span class="abx-kicker">Insight</span>
						<?php echo aurix_blog_title( $aurix_p, 'h3' ); ?>
						<?php echo aurix_blog_excerpt( $aurix_p, 40 ); ?>
						<?php echo aurix_blog_meta( $aurix_p ); ?>
					</div>
				</article>
				<?php endforeach; ?>
			</div>

		<?php elseif ( 'list' === $aurix_layout ) : ?>
			<ol class="abx-list">
				<?php foreach ( $aurix_posts as $aurix_i => $aurix_p ) : ?>
				<li class="abx-list-item"<?php echo $aurix_i >= $aurix_show ? ' hidden data-more="1"' : ''; ?>>
					<span class="abx-list-num"><?php echo esc_html( str_pad( (string) ( $aurix_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<div>
						<?php echo aurix_blog_title( $aurix_p, 'h3' ); ?>
						<?php echo aurix_blog_meta( $aurix_p ); ?>
					</div>
				</li>
				<?php endforeach; ?>
			</ol>

		<?php else : // grid ?>
			<div class="abx-grid">
				<?php foreach ( $aurix_posts as $aurix_i => $aurix_p ) : ?>
				<article class="abx-card"<?php echo $aurix_i >= $aurix_show ? ' hidden data-more="1"' : ''; ?>>
					<?php echo aurix_blog_thumb( $aurix_p, 'abx-thumb', $aurix_icon ); ?>
					<div class="abx-body">
						<?php echo aurix_blog_title( $aurix_p, 'h3' ); ?>
						<?php echo aurix_blog_excerpt( $aurix_p, 18 ); ?>
						<?php echo aurix_blog_meta( $aurix_p ); ?>
					</div>
				</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $aurix_total > $aurix_show ) :
			$aurix_more_label = 'Show all ' . $aurix_total . ' ' . strtolower( $aurix_sec['title'] ); ?>
			<div class="abx-more-wrap">
				<button type="button" class="abx-more" data-less="Show fewer" data-more="<?php echo esc_attr( $aurix_more_label ); ?>"><?php echo esc_html( $aurix_more_label ); ?></button>
			</div>
		<?php endif; ?>
	</section>
	<?php endforeach; ?>

	<?php endif; ?>
</div>

<style>
	/* Page header + section jump links */
	.abx-header{text-align:center;padding:48px 0 28px;}
	.abx-title{font-size:44px;margin:0 0 8px;color:var(--navy);}
	.abx-subtitle{color:var(--muted);margin:0 auto;max-width:640px;}
	.abx-empty{text-align:center;color:var(--muted);padding:40px 0;}
	.abx-jump{display:flex;flex-wrap:wrap;justify-content:center;gap:10px;padding:14px 0;margin-bottom:12px;}
	.abx-jump a{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border:1px solid var(--border-md);border-radius:999px;background:var(--white);color:var(--navy);font-weight:600;font-size:14px;text-decoration:none;transition:var(--t);}
	.abx-jump a:hover{border-color:var(--gold);color:var(--gold-dk);}
	.abx-jump a i{color:var(--gold);}
	.abx-jump a span{background:var(--gold-pale);color:var(--gold-dk);border-radius:999px;padding:1px 8px;font-size:12px;}

	/* Shared section pieces */
	.abx-section{padding:44px 0 20px;scroll-margin-top:90px;}
	.abx-section + .abx-section{border-top:1px solid var(--border);}
	.abx-section-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;margin-bottom:26px;}
	.abx-section-title{font-size:30px;margin:0 0 6px;color:var(--navy);}
	.abx-section-title i{color:var(--gold);font-size:.8em;margin-right:6px;}
	.abx-section-intro{color:var(--muted);margin:0;max-width:720px;}
	.abx-count{flex:0 0 auto;color:var(--muted);font-size:14px;font-weight:600;}
	.abx-h{margin:0 0 8px;line-height:1.35;}
	.abx-h a{color:var(--navy);text-decoration:none;}
	.abx-h a:hover{color:var(--gold-dk);}
	.abx-ex{color:var(--muted);font-size:14px;line-height:1.65;margin:0 0 12px;}
	.abx-meta{display:flex;gap:14px;flex-wrap:wrap;color:var(--muted);font-size:12px;}
	.abx-meta i{color:var(--gold);margin-right:4px;}
	.abx-kicker{display:inline-block;color:var(--gold-dk);font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;margin-bottom:10px;}
	.abx-thumb-empty{display:flex;width:100%;height:100%;min-height:90px;align-items:center;justify-content:center;color:var(--gold);font-size:40px;background:var(--off);}
	.abx [hidden]{display:none !important;}
	.abx-more-wrap{text-align:center;margin-top:28px;}
	.abx-more{border:1px solid var(--navy);background:transparent;color:var(--navy);padding:11px 26px;border-radius:999px;font-weight:700;font-size:14px;cursor:pointer;transition:var(--t);}
	.abx-more:hover{background:var(--navy);color:var(--white);}

	/* 1. Grid (Product Guides) */
	.abx-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:28px;align-items:stretch;}
	.abx-card{position:relative;display:flex;flex-direction:column;background:var(--white);border:1px solid var(--border);border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(13,27,42,.05),0 8px 24px rgba(13,27,42,.06);transition:transform .3s cubic-bezier(.2,.8,.2,1),box-shadow .3s,border-color .3s;}
	.abx-card::before{content:"";position:absolute;left:0;right:0;top:0;height:3px;background:linear-gradient(90deg,var(--gold-dk),var(--gold-lt));transform:scaleX(0);transform-origin:left;transition:transform .35s ease;z-index:2;}
	.abx-card:hover{transform:translateY(-6px);box-shadow:0 18px 44px rgba(13,27,42,.14);border-color:rgba(184,146,90,.35);}
	.abx-card:hover::before{transform:scaleX(1);}
	.abx-card:focus-within{outline:2px solid var(--gold);outline-offset:3px;}
	.abx-thumb{position:relative;display:block;aspect-ratio:4/3;background:#fff;overflow:hidden;border-bottom:1px solid var(--border);}
	.abx-thumb img{width:100%;height:100%;object-fit:cover;display:block;padding:0;transition:transform .5s cubic-bezier(.2,.8,.2,1);}
	.abx-card:hover .abx-thumb img{transform:scale(1.06);}
	.abx-body{display:flex;flex-direction:column;flex:1;padding:20px 22px 18px;}
	.abx-body .abx-h{font-size:18px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:2.8em;}
	.abx-card:hover .abx-h a{color:var(--gold-dk);}
	.abx-body .abx-ex{display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}
	.abx-body .abx-meta{margin-top:auto;padding-top:14px;border-top:1px solid var(--border);}

	/* Whole card / row is clickable in every layout (title link stretched over it) */
	.abx-mag-lead,.abx-mag-item,.abx-tl-card,.abx-feat-item,.abx-list-item{position:relative;}
	.abx-card .abx-h a::after,.abx-mag-lead .abx-h a::after,.abx-mag-item .abx-h a::after,.abx-tl-card .abx-h a::after,.abx-feat-item .abx-h a::after,.abx-list-item .abx-h a::after{content:"";position:absolute;inset:0;z-index:1;}
	.abx-mag-img img,.abx-tl-thumb img,.abx-feat-img img{transition:transform .5s cubic-bezier(.2,.8,.2,1);}
	.abx-mag-lead:hover .abx-mag-img img,.abx-tl-card:hover .abx-tl-thumb img,.abx-feat-item:hover .abx-feat-img img{transform:scale(1.04);}
	.abx-tl-card,.abx-mag-lead{transition:var(--t);}
	.abx-tl-card:hover,.abx-mag-lead:hover{box-shadow:var(--shadow-md);}

	/* 2. Magazine (News): lead story + headline list */
	.abx-mag{display:grid;grid-template-columns:1.4fr 1fr;gap:32px;align-items:start;}
	.abx-mag-lead{background:var(--white);border:1px solid var(--border-md);border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--shadow-sm);}
	.abx-mag-img{display:block;aspect-ratio:16/9;background:var(--off);overflow:hidden;}
	.abx-mag-img img{width:100%;height:100%;object-fit:cover;display:block;}
	.abx-mag-body{padding:24px 26px 26px;}
	.abx-mag-body .abx-h{font-size:26px;}
	.abx-mag-list{display:flex;flex-direction:column;}
	.abx-mag-item{display:grid;grid-template-columns:96px 1fr;gap:16px;align-items:center;padding:16px 0;border-bottom:1px solid var(--border);}
	.abx-mag-item:first-child{padding-top:0;}
	.abx-mag-thumb{display:block;width:96px;height:72px;border-radius:var(--r);overflow:hidden;background:var(--off);}
	.abx-mag-thumb img{width:100%;height:100%;object-fit:cover;display:block;}
	.abx-mag-thumb .abx-thumb-empty{min-height:72px;font-size:22px;}
	.abx-mag-item .abx-h{font-size:16px;margin-bottom:6px;}

	/* 3. Timeline (Events): date badges on a vertical line */
	.abx-tl{list-style:none;margin:0;padding:0 0 0 8px;position:relative;}
	.abx-tl::before{content:"";position:absolute;left:44px;top:8px;bottom:8px;width:2px;background:linear-gradient(var(--gold),var(--gold-pale));}
	.abx-tl-item{display:grid;grid-template-columns:74px 1fr;gap:24px;align-items:start;margin-bottom:22px;position:relative;}
	.abx-tl-date{position:relative;z-index:1;width:74px;text-align:center;background:var(--navy);color:var(--white);border-radius:var(--r-lg);padding:10px 4px;box-shadow:var(--shadow-sm);}
	.abx-tl-day{display:block;font-size:26px;font-weight:700;line-height:1;}
	.abx-tl-mon{display:block;font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--gold-lt);margin-top:4px;}
	.abx-tl-card{display:grid;grid-template-columns:1fr 200px;gap:20px;align-items:center;background:var(--white);border:1px solid var(--border-md);border-left:4px solid var(--gold);border-radius:var(--r-lg);padding:20px 22px;box-shadow:var(--shadow-xs);}
	.abx-tl-card .abx-h{font-size:20px;}
	.abx-tl-thumb{display:block;aspect-ratio:4/3;border-radius:var(--r);overflow:hidden;background:var(--off);}
	.abx-tl-thumb img{width:100%;height:100%;object-fit:cover;display:block;}

	/* 4. Feature (Industry Insights): wide dark panels, image alternates side */
	.abx-feat{display:flex;flex-direction:column;gap:28px;}
	.abx-feat-item{display:grid;grid-template-columns:1.1fr 1fr;background:var(--navy);border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--shadow-md);}
	.abx-feat-item.is-flip .abx-feat-img{order:2;}
	.abx-feat-img{display:block;min-height:320px;background:var(--navy-lt);overflow:hidden;}
	.abx-feat-img img{width:100%;height:100%;object-fit:cover;display:block;}
	.abx-feat-body{padding:40px 44px;display:flex;flex-direction:column;justify-content:center;}
	.abx-feat-body .abx-kicker{color:var(--gold-lt);}
	.abx-feat-body .abx-h{font-size:28px;}
	.abx-feat-body .abx-h a{color:var(--white);}
	.abx-feat-body .abx-h a:hover{color:var(--gold-lt);}
	.abx-feat-body .abx-ex{color:rgba(255,255,255,.75);font-size:15px;}
	.abx-feat-body .abx-meta{color:rgba(255,255,255,.65);}

	/* 5. List (More Articles): numbered headlines in two columns */
	.abx-list{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:1fr 1fr;gap:0 40px;}
	.abx-list-item{display:grid;grid-template-columns:52px 1fr;gap:14px;padding:18px 0;border-bottom:1px solid var(--border);}
	.abx-list-num{font-size:28px;font-weight:700;color:var(--gold);line-height:1;}
	.abx-list-item .abx-h{font-size:17px;margin-bottom:6px;}

	@media (max-width:1024px){
		.abx-grid{grid-template-columns:repeat(2,1fr);}
		.abx-mag{grid-template-columns:1fr;}
		.abx-feat-item,.abx-feat-item.is-flip{grid-template-columns:1fr;}
		.abx-feat-item.is-flip .abx-feat-img{order:0;}
		.abx-feat-img{min-height:0;aspect-ratio:16/9;}
	}
	@media (max-width:640px){
		.abx-title{font-size:34px;}
		.abx-jump{justify-content:flex-start;flex-wrap:nowrap;overflow-x:auto;-webkit-overflow-scrolling:touch;}
		.abx-jump a{flex:0 0 auto;}
		.abx-section-head{flex-direction:column;align-items:flex-start;}
		.abx-section-title{font-size:24px;}
		.abx-grid{grid-template-columns:1fr;gap:18px;}
		.abx-mag-body .abx-h{font-size:21px;}
		.abx-tl::before{left:30px;}
		.abx-tl-item{grid-template-columns:48px 1fr;gap:14px;}
		.abx-tl-date{width:48px;padding:8px 2px;}
		.abx-tl-day{font-size:20px;}
		.abx-tl-mon{font-size:9px;}
		.abx-tl-card{grid-template-columns:1fr;padding:16px;}
		.abx-tl-thumb{order:-1;}
		.abx-feat-body{padding:24px 22px;}
		.abx-feat-body .abx-h{font-size:22px;}
		.abx-list{grid-template-columns:1fr;}
	}
</style>
<script>
	(function () {
		document.querySelectorAll('.abx-more').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var section = btn.closest('.abx-section');
				var extra = section.querySelectorAll('[data-more]');
				var opening = extra.length && extra[0].hidden;
				extra.forEach(function (c) { c.hidden = !opening; });
				btn.textContent = opening ? btn.getAttribute('data-less') : btn.getAttribute('data-more');
				if (!opening) { section.scrollIntoView({ behavior: 'smooth' }); }
			});
		});
	})();
</script>
<?php get_footer(); ?>
