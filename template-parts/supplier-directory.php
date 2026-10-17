<?php
/**
 * Dental supplier directory sections: hero with search, filters and listing
 * grid, buyer's guide, distributor band, "Add your company" form and FAQ.
 *
 * Used by the [aurix_supplier_directory] shortcode (embed mode) and by the
 * "Dental Supplier Directory" page template (inc/supplier-directory.php).
 *
 * $args: title (hero heading), embed (true when inside page content).
 */
defined( 'ABSPATH' ) || exit;

$title = ! empty( $args['title'] ) ? $args['title'] : 'Dental Instruments Supplier Directory';
$embed = ! empty( $args['embed'] );
$listings  = aurix_dir_listings();
$countries = array_count_values( array_filter( wp_list_pluck( $listings, 'country' ) ) );
ksort( $countries );
$us        = array_filter( $listings, function ( $l ) { return 'United States' === $l['country']; } );
$us_states = array_intersect_key( aurix_dir_us_states(), array_flip( array_filter( wp_list_pluck( $us, 'state' ) ) ) );
$dist_url  = home_url( '/become-an-aurix-distributor-global-distributor-network/' );
$year      = gmdate( 'Y' );

$faqs = array(
	array( 'What does a dental instruments supplier do?', 'A dental instruments supplier sells hand, surgical and rotary instruments to dental practices, schools, hospitals and resellers. Some make the instruments themselves (manufacturers), some buy from several brands and resell them (distributors), and some sell mainly online.' ),
	array( 'How do I choose a dental instrument supplier in the USA?', 'Compare instrument quality (steel grade, finish, edge retention), the range of patterns available, pricing at your order volume, US stock and delivery times, returns and warranty terms, and whether the supplier offers sets, custom kits or private label. Ask for samples before placing a large order.' ),
	array( 'How can my company be listed in this directory?', 'Use the “Add your company” form on this page. Listings are free. Our team reviews each submission and usually publishes it within a few working days.' ),
	array( 'Does a listing mean Aurix recommends the company?', 'No. Listings are provided for information only and do not imply endorsement, partnership or verification unless a listing is marked Verified. Always check a supplier’s details directly before you buy.' ),
	array( 'How do I update or remove a listing?', 'Contact us from the company’s own email domain with the change you need and we will update or remove the listing.' ),
	array( 'Can I become an Aurix distributor?', 'Yes. Distributors, dealers and online retailers can apply to join the Aurix global distributor network for wholesale pricing, private label and priority supply.' ),
);

?>
<div class="axd<?php echo $embed ? ' axd--embed' : ''; ?>">

<section class="axd-hero">
	<div class="axd-wrap">
		<nav class="axd-crumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><span><?php echo esc_html( get_the_title() ); ?></span></nav>
		<span class="axd-eyebrow">Supplier directory <?php echo esc_html( $year ); ?></span>
		<h1 class="axd-title"><?php echo esc_html( $title ); ?></h1>
		<p class="axd-lead">Find dental instrument suppliers, manufacturers and distributors in the USA and around the world. Search by name, filter by state, country, supplier type and product focus, or add your own company for free.</p>

		<form class="axd-search" role="search" onsubmit="return false">
			<i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
			<label class="screen-reader-text" for="axd-q">Search suppliers</label>
			<input type="search" id="axd-q" placeholder="Search suppliers, cities or products…" autocomplete="off">
		</form>

		<ul class="axd-stats">
			<li><strong><?php echo count( $listings ); ?></strong><span>suppliers listed</span></li>
			<li><strong><?php echo count( $countries ); ?></strong><span>countries</span></li>
			<li><strong><?php echo count( $us ); ?></strong><span>US suppliers</span></li>
			<li><strong><?php echo count( $us_states ); ?></strong><span>US states</span></li>
		</ul>

		<div class="axd-hero-ctas">
			<a class="axd-btn axd-btn--gold" href="#add-company"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add your company — free</a>
			<a class="axd-btn axd-btn--ghost" href="<?php echo esc_url( $dist_url ); ?>"><i class="fa-solid fa-handshake" aria-hidden="true"></i> Become an Aurix distributor</a>
		</div>
	</div>
</section>

<section class="axd-main">
	<div class="axd-wrap axd-layout">

		<aside class="axd-filters" aria-label="Filter suppliers">
			<div class="axd-filters-head">
				<h2>Filter</h2>
				<button type="button" class="axd-reset" id="axd-reset">Clear all</button>
			</div>

			<label class="axd-label" for="axd-country">Country</label>
			<select id="axd-country" class="axd-select">
				<option value="">All countries</option>
				<?php foreach ( $countries as $c => $n ) : ?>
					<option value="<?php echo esc_attr( $c ); ?>"><?php echo esc_html( $c . ' (' . $n . ')' ); ?></option>
				<?php endforeach; ?>
			</select>

			<div class="axd-state-wrap" id="axd-state-wrap" hidden>
				<label class="axd-label" for="axd-state">US state</label>
				<select id="axd-state" class="axd-select">
					<option value="">All states</option>
					<?php foreach ( $us_states as $code => $label ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<label class="axd-label" for="axd-type">Supplier type</label>
			<select id="axd-type" class="axd-select">
				<option value="">All types</option>
				<?php foreach ( aurix_dir_types() as $t ) : ?>
					<option value="<?php echo esc_attr( $t ); ?>"><?php echo esc_html( $t ); ?></option>
				<?php endforeach; ?>
			</select>

			<span class="axd-label">Product focus</span>
			<div class="axd-chips" id="axd-focus">
				<?php foreach ( aurix_dir_focus_areas() as $f ) : ?>
					<button type="button" class="axd-chip" data-focus="<?php echo esc_attr( $f ); ?>" aria-pressed="false"><?php echo esc_html( $f ); ?></button>
				<?php endforeach; ?>
			</div>

			<div class="axd-side-card">
				<i class="fa-solid fa-store" aria-hidden="true"></i>
				<h3>Are you a supplier?</h3>
				<p>Get your company in front of clinics, schools and distributors searching for dental instruments.</p>
				<a class="axd-btn axd-btn--gold axd-btn--block" href="#add-company">List your company</a>
			</div>
		</aside>

		<div class="axd-results">
			<div class="axd-toolbar">
				<p class="axd-count" id="axd-count" aria-live="polite">Showing <?php echo count( $listings ); ?> suppliers</p>
				<label class="axd-sort"><span>Sort</span>
					<select id="axd-sort" class="axd-select">
						<option value="featured">Featured first</option>
						<option value="az">Name A–Z</option>
						<option value="za">Name Z–A</option>
					</select>
				</label>
			</div>

			<div class="axd-grid" id="axd-grid">
				<?php
				foreach ( $listings as $i => $l ) :
					$where    = implode( ', ', array_filter( array( $l['city'], $l['state'], $l['country'] ) ) );
					$initials = strtoupper( implode( '', array_map( function ( $w ) { return mb_substr( $w, 0, 1 ); }, array_slice( preg_split( '/[\s\-]+/', preg_replace( '/[^\p{L}\s\-]/u', '', $l['name'] ) ), 0, 2 ) ) ) );
					$search   = strtolower( implode( ' ', array( $l['name'], $l['desc'], $l['city'], $l['state'], $l['country'], $l['type'], implode( ' ', $l['focus'] ), $l['website'] ) ) );
					?>
					<article class="axd-card<?php echo $l['own'] ? ' axd-card--own' : ( $l['featured'] ? ' axd-card--featured' : '' ); ?>"
						data-search="<?php echo esc_attr( $search ); ?>"
						data-name="<?php echo esc_attr( strtolower( $l['name'] ) ); ?>"
						data-order="<?php echo (int) $i; ?>"
						data-country="<?php echo esc_attr( $l['country'] ); ?>"
						data-state="<?php echo esc_attr( $l['state'] ); ?>"
						data-type="<?php echo esc_attr( $l['type'] ); ?>"
						data-focus="<?php echo esc_attr( implode( '|', $l['focus'] ) ); ?>">
						<header class="axd-card-head">
							<span class="axd-mono" aria-hidden="true"><?php echo esc_html( $initials ); ?></span>
							<div>
								<h3 class="axd-card-name"><?php echo esc_html( $l['name'] ); ?></h3>
								<?php if ( $where ) : ?>
									<p class="axd-card-where"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?php echo esc_html( $where ); ?></p>
								<?php endif; ?>
							</div>
						</header>
						<div class="axd-badges">
							<span class="axd-type"><?php echo esc_html( $l['type'] ); ?></span>
							<?php if ( $l['featured'] || $l['own'] ) : ?><span class="axd-badge axd-badge--featured"><i class="fa-solid fa-star" aria-hidden="true"></i> Featured</span><?php endif; ?>
							<?php if ( $l['verified'] ) : ?><span class="axd-badge axd-badge--verified"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span><?php endif; ?>
						</div>
						<?php if ( $l['desc'] ) : ?>
							<p class="axd-card-desc"><?php echo esc_html( $l['desc'] ); ?></p>
						<?php endif; ?>
						<?php if ( $l['focus'] ) : ?>
							<ul class="axd-tags">
								<?php foreach ( $l['focus'] as $f ) : ?><li><?php echo esc_html( $f ); ?></li><?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<footer class="axd-card-foot">
							<?php if ( $l['own'] ) : ?>
								<a class="axd-link axd-link--solid" href="<?php echo esc_url( home_url( '/shop/' ) ); ?>">Shop instruments</a>
								<a class="axd-link" href="<?php echo esc_url( $dist_url ); ?>">Become a distributor</a>
							<?php elseif ( $l['website'] ) : ?>
								<a class="axd-link" href="<?php echo esc_url( 'https://' . $l['website'] ); ?>" target="_blank" rel="nofollow noopener noreferrer"><?php echo esc_html( $l['website'] ); ?> <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
							<?php endif; ?>
							<?php if ( $l['phone'] ) : ?>
								<a class="axd-link" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $l['phone'] ) ); ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i> <?php echo esc_html( $l['phone'] ); ?></a>
							<?php endif; ?>
						</footer>
					</article>
				<?php endforeach; ?>
			</div>

			<div class="axd-empty" id="axd-empty" hidden>
				<i class="fa-regular fa-face-meh" aria-hidden="true"></i>
				<p>No suppliers match these filters yet.</p>
				<a class="axd-btn axd-btn--gold" href="#add-company">Add your company</a>
			</div>
			<div class="axd-more-wrap"><button type="button" class="axd-btn axd-btn--outline" id="axd-more" hidden>Show more suppliers</button></div>
		</div>
	</div>
</section>

<section class="axd-guide">
	<div class="axd-wrap axd-guide-grid">
		<div>
			<span class="axd-eyebrow axd-eyebrow--dark">Buyer’s guide</span>
			<h2>How to choose a dental instruments supplier</h2>
			<p>The right supplier saves time in every appointment. Instruments that hold an edge, match your existing patterns and arrive when you need them matter more than the lowest unit price. Use this checklist when you compare suppliers in the directory:</p>
			<ol class="axd-checklist">
				<li><strong>Quality you can check.</strong> Ask about the steel grade, hardening and finish, and request samples before a large order.</li>
				<li><strong>Range and consistency.</strong> Make sure the supplier stocks the patterns you use today and can supply identical replacements later.</li>
				<li><strong>Pricing at your volume.</strong> Compare total annual cost, wholesale tiers and set pricing, not just single-item prices.</li>
				<li><strong>Delivery and stock location.</strong> US stock means faster delivery and simpler returns for US practices.</li>
				<li><strong>Service.</strong> Look for clear warranty and return terms, reprocessing guidance and a real person to talk to.</li>
				<li><strong>Flexibility.</strong> Custom kits, private label and OEM options matter for groups, schools and distributors.</li>
			</ol>
		</div>
		<div>
			<h3>Supplier types explained</h3>
			<table class="axd-table">
				<thead><tr><th>Type</th><th>Best for</th></tr></thead>
				<tbody>
					<tr><td>Manufacturer</td><td>Specific brands and designs, technical specifications, large or OEM orders</td></tr>
					<tr><td>Manufacturer &amp; distributor</td><td>Factory quality with direct sales, wholesale, kits and private label</td></tr>
					<tr><td>Distributor</td><td>One account for many brands, consolidated ordering and local service</td></tr>
					<tr><td>Online retailer</td><td>Fast reorders of everyday items and price comparison</td></tr>
					<tr><td>Marketplace</td><td>Comparing offers from several vendors in one place</td></tr>
				</tbody>
			</table>
			<div class="axd-aurix-box">
				<h3>Looking for a supplier now?</h3>
				<p>Aurix International supplies surgical-grade dental and surgical instruments, complete kits, wholesale and private label from the USA.</p>
				<a class="axd-btn axd-btn--gold" href="<?php echo esc_url( home_url( '/shop/' ) ); ?>">Browse Aurix instruments</a>
			</div>
		</div>
	</div>
</section>

<section class="axd-band">
	<div class="axd-wrap axd-band-inner">
		<div>
			<span class="axd-eyebrow">Global distributor network</span>
			<h2>Sell Aurix instruments in your market</h2>
			<p>Dealers, distributors and online retailers get wholesale pricing, private label options, early access to new lines and priority support.</p>
		</div>
		<a class="axd-btn axd-btn--gold" href="<?php echo esc_url( $dist_url ); ?>">Become an Aurix distributor <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
	</div>
</section>

<section class="axd-add" id="add-company">
	<div class="axd-wrap axd-add-grid">
		<div class="axd-add-intro">
			<span class="axd-eyebrow axd-eyebrow--dark">Free listing</span>
			<h2>Add your company to the directory</h2>
			<p>Manufacturers, distributors and online suppliers of dental instruments and products from any country can be listed free of charge.</p>
			<ul class="axd-ticks">
				<li>Reach clinics, schools and buyers searching for dental suppliers</li>
				<li>Appear in country, state, type and product filters</li>
				<li>Link to your website</li>
				<li>Reviewed by our team, usually within a few working days</li>
			</ul>
			<p class="axd-small">Want to resell Aurix products instead? <a href="<?php echo esc_url( $dist_url ); ?>">Apply to become a distributor</a>.</p>
		</div>

		<form class="axd-form" id="axd-form" novalidate>
			<input type="hidden" name="action" value="aurix_dir_submit">
			<input type="hidden" name="axd_t" value="<?php echo esc_attr( aurix_dir_form_token() ); ?>">
			<div class="axd-hp" aria-hidden="true"><label>Leave empty <input type="text" name="axd_hp" tabindex="-1" autocomplete="off"></label></div>

			<div class="axd-row">
				<label class="axd-field"><span>Company name *</span><input type="text" name="company" required maxlength="120"></label>
				<label class="axd-field"><span>Website *</span><input type="text" name="website" required placeholder="example.com" maxlength="200"></label>
			</div>
			<div class="axd-row">
				<label class="axd-field"><span>Country *</span>
					<input type="text" name="country" id="axd-f-country" required list="axd-country-list" placeholder="United States" maxlength="80">
				</label>
				<label class="axd-field"><span>Supplier type *</span>
					<select name="type" required>
						<option value="">Choose…</option>
						<?php foreach ( aurix_dir_types() as $t ) : ?><option><?php echo esc_html( $t ); ?></option><?php endforeach; ?>
					</select>
				</label>
			</div>
			<div class="axd-row">
				<label class="axd-field" id="axd-f-state-us"><span>State</span>
					<select name="state">
						<option value="">Choose…</option>
						<?php foreach ( aurix_dir_us_states() as $code => $label ) : ?><option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $label ); ?></option><?php endforeach; ?>
					</select>
				</label>
				<label class="axd-field" id="axd-f-state-other" hidden><span>State / region</span><input type="text" name="state" maxlength="80" disabled></label>
				<label class="axd-field"><span>City</span><input type="text" name="city" maxlength="80"></label>
			</div>
			<fieldset class="axd-field">
				<legend>Product focus</legend>
				<div class="axd-checks">
					<?php foreach ( aurix_dir_focus_areas() as $f ) : ?>
						<label><input type="checkbox" name="focus[]" value="<?php echo esc_attr( $f ); ?>"> <?php echo esc_html( $f ); ?></label>
					<?php endforeach; ?>
				</div>
			</fieldset>
			<label class="axd-field"><span>Short description (max 300 characters)</span><textarea name="desc" rows="3" maxlength="300" placeholder="What you make or supply, and who you serve."></textarea></label>
			<div class="axd-row">
				<label class="axd-field"><span>Your name *</span><input type="text" name="contact" required maxlength="80" autocomplete="name"></label>
				<label class="axd-field"><span>Business email *</span><input type="email" name="email" required maxlength="120" autocomplete="email"></label>
			</div>
			<label class="axd-field"><span>Public phone (optional, shown on your listing)</span><input type="tel" name="phone" maxlength="40"></label>
			<label class="axd-check"><input type="checkbox" name="distributor" value="1"> I am also interested in becoming an Aurix distributor</label>
			<label class="axd-check"><input type="checkbox" name="consent" value="1" required> I confirm I represent this company and agree that the company details above (not my name or email) may be shown publicly. *</label>
			<button type="submit" class="axd-btn axd-btn--gold axd-btn--block" id="axd-submit">Submit listing for review</button>
			<p class="axd-form-msg" id="axd-form-msg" role="status" hidden></p>
		</form>
		<datalist id="axd-country-list">
			<?php
			$list = array( 'United States', 'Canada', 'Mexico', 'Brazil', 'Argentina', 'Chile', 'Colombia', 'United Kingdom', 'Ireland', 'Germany', 'France', 'Italy', 'Spain', 'Portugal', 'Netherlands', 'Belgium', 'Switzerland', 'Austria', 'Sweden', 'Norway', 'Denmark', 'Finland', 'Poland', 'Czech Republic', 'Hungary', 'Romania', 'Greece', 'Turkey', 'Israel', 'United Arab Emirates', 'Saudi Arabia', 'Qatar', 'Kuwait', 'Egypt', 'South Africa', 'Nigeria', 'Kenya', 'Pakistan', 'India', 'Bangladesh', 'Sri Lanka', 'China', 'Hong Kong', 'Taiwan', 'Japan', 'South Korea', 'Singapore', 'Malaysia', 'Thailand', 'Vietnam', 'Indonesia', 'Philippines', 'Australia', 'New Zealand' );
			foreach ( $list as $c ) {
				echo '<option value="' . esc_attr( $c ) . '">';
			}
			?>
		</datalist>
	</div>
</section>

<section class="axd-faq">
	<div class="axd-wrap axd-faq-inner">
		<?php if ( ! $embed ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			if ( trim( get_the_content() ) ) :
				?>
				<div class="axd-content entry-content"><?php the_content(); ?></div>
				<?php
			endif;
		endwhile;
		?>
		<?php endif; ?>
		<h2>Frequently asked questions</h2>
		<?php foreach ( $faqs as $q ) : ?>
			<details class="axd-qa">
				<summary><?php echo esc_html( $q[0] ); ?></summary>
				<p><?php echo esc_html( $q[1] ); ?></p>
			</details>
		<?php endforeach; ?>
		<p class="axd-disclaimer">Directory information comes from public sources and company submissions and may change. Listings do not imply endorsement by or partnership with Aurix International unless marked Verified. Company names and trademarks belong to their owners. To correct or remove a listing, <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">contact us</a>.</p>
	</div>
</section>

</div>
<?php
// Structured data: the directory as an ItemList of organisations, plus the FAQ.
$items = array();
foreach ( array_slice( $listings, 0, 200 ) as $i => $l ) {
	$org = array( '@type' => 'Organization', 'name' => $l['name'] );
	if ( $l['website'] ) {
		$org['url'] = 'https://' . $l['website'];
	}
	if ( $l['country'] ) {
		$org['address'] = array_filter( array( '@type' => 'PostalAddress', 'addressLocality' => $l['city'], 'addressRegion' => $l['state'], 'addressCountry' => $l['country'] ) );
	}
	$items[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'item' => $org );
}
$schema = array(
	'@context' => 'https://schema.org',
	'@graph'   => array(
		array(
			'@type'       => 'CollectionPage',
			'name'        => 'Dental Instruments Supplier Directory',
			'url'         => get_permalink(),
			'description' => 'Directory of dental instrument suppliers, manufacturers and distributors in the USA and worldwide.',
			'mainEntity'  => array( '@type' => 'ItemList', 'numberOfItems' => count( $listings ), 'itemListElement' => $items ),
		),
		array(
			'@type'      => 'FAQPage',
			'mainEntity' => array_map( function ( $q ) {
				return array( '@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $q[1] ) );
			}, $faqs ),
		),
	),
);
echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
