<?php
/**
 * Dental supplier directory.
 *
 * - "aurix_supplier" post type holds one listing per company (admin only,
 *   no single pages: every listing is shown on the directory page).
 * - template-parts/supplier-directory.php renders the page sections (search,
 *   filters, listings, guide, "Add your company" form, FAQ). Place them in any
 *   page with the [aurix_supplier_directory] shortcode, or choose the
 *   "Dental Supplier Directory" page template.
 * - Public submissions arrive as pending listings for an admin to review.
 * - Dental Suppliers → Starter list imports data/suppliers.json.
 *
 * Markup classes use the "axd-" prefix; styles live in
 * css/supplier-directory.css and behaviour in js/supplier-directory.js,
 * both loaded only on the directory page.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'AURIX_DIR_TEMPLATE', 'page-supplier-directory.php' );

/** Supplier types shown in filters and the submission form. */
function aurix_dir_types() {
	return array( 'Manufacturer', 'Manufacturer & distributor', 'Distributor', 'Online retailer', 'Marketplace' );
}

/** Product focus areas shown in filters and the submission form. */
function aurix_dir_focus_areas() {
	return array(
		'Hand instruments', 'Surgical instruments', 'Periodontal', 'Endodontics', 'Implants',
		'Orthodontics', 'Burs & rotary', 'Restorative materials', 'Infection control',
		'Equipment', 'Loupes & magnification', 'Full-line supplies',
	);
}

/** US states, keyed by postal code. */
function aurix_dir_us_states() {
	return array(
		'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California',
		'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'DC' => 'District of Columbia',
		'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois',
		'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana',
		'ME' => 'Maine', 'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota',
		'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
		'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
		'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon',
		'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota',
		'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia',
		'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming',
	);
}

/** World region for a country name, used by the region tabs. */
function aurix_dir_region( $country ) {
	static $map = null;
	if ( null === $map ) {
		$groups = array(
			'North America'        => array( 'United States', 'Canada', 'Mexico' ),
			'Latin America'        => array( 'Brazil', 'Argentina', 'Chile', 'Colombia', 'Peru', 'Ecuador', 'Uruguay', 'Venezuela', 'Costa Rica', 'Panama', 'Guatemala', 'Dominican Republic', 'Puerto Rico' ),
			'Europe'               => array( 'United Kingdom', 'Ireland', 'Germany', 'France', 'Italy', 'Spain', 'Portugal', 'Netherlands', 'Belgium', 'Luxembourg', 'Switzerland', 'Liechtenstein', 'Austria', 'Sweden', 'Norway', 'Denmark', 'Finland', 'Iceland', 'Poland', 'Czech Republic', 'Czechia', 'Slovakia', 'Hungary', 'Romania', 'Bulgaria', 'Greece', 'Croatia', 'Slovenia', 'Serbia', 'Estonia', 'Latvia', 'Lithuania', 'Ukraine', 'Malta', 'Cyprus' ),
			'Asia-Pacific'         => array( 'China', 'Hong Kong', 'Taiwan', 'Japan', 'South Korea', 'India', 'Pakistan', 'Bangladesh', 'Sri Lanka', 'Nepal', 'Singapore', 'Malaysia', 'Thailand', 'Vietnam', 'Indonesia', 'Philippines', 'Australia', 'New Zealand' ),
			'Middle East & Africa' => array( 'Turkey', 'Israel', 'United Arab Emirates', 'Saudi Arabia', 'Qatar', 'Kuwait', 'Bahrain', 'Oman', 'Jordan', 'Lebanon', 'Iran', 'Iraq', 'Egypt', 'Morocco', 'Tunisia', 'Algeria', 'South Africa', 'Nigeria', 'Kenya', 'Ghana', 'Ethiopia' ),
		);
		$map = array();
		foreach ( $groups as $region => $list ) {
			foreach ( $list as $c ) {
				$map[ strtolower( $c ) ] = $region;
			}
		}
	}
	$key = strtolower( trim( (string) $country ) );
	return isset( $map[ $key ] ) ? $map[ $key ] : 'Other';
}

/** First letter of a company name for the A–Z bar ("#" for digits and symbols). */
function aurix_dir_letter( $name ) {
	$ch = strtoupper( remove_accents( mb_substr( ltrim( (string) $name ), 0, 1 ) ) );
	return preg_match( '/^[A-Z]$/', $ch ) ? $ch : '#';
}

/** Listing fields stored as post meta: key => label. */
function aurix_dir_fields() {
	return array(
		'website' => 'Website',
		'city'    => 'City',
		'state'   => 'State / region',
		'country' => 'Country',
		'type'    => 'Supplier type',
		'focus'   => 'Product focus (separate with ;)',
		'phone'   => 'Public phone (optional)',
		'contact' => 'Contact name (private)',
		'email'   => 'Contact email (private)',
	);
}

function aurix_dir_meta( $post_id, $key ) {
	return (string) get_post_meta( $post_id, '_axd_' . $key, true );
}

/** "example.com" from any URL or bare domain. */
function aurix_dir_domain( $url ) {
	$url  = trim( strtolower( (string) $url ) );
	$host = wp_parse_url( preg_match( '#^https?://#', $url ) ? $url : 'https://' . $url, PHP_URL_HOST );
	return $host ? preg_replace( '/^www\./', '', $host ) : '';
}

/** True for this site's own listing (pinned first, links to the shop). */
function aurix_dir_is_own( $domain ) {
	return in_array( $domain, array( aurix_dir_domain( home_url() ), 'aurixinternational.com' ), true );
}

/* ── Post type ─────────────────────────────────────────────────────────── */

add_action( 'init', function () {
	register_post_type( 'aurix_supplier', array(
		'labels'             => array(
			'name'          => 'Dental Suppliers',
			'singular_name' => 'Dental Supplier',
			'add_new_item'  => 'Add supplier',
			'edit_item'     => 'Edit supplier',
			'all_items'     => 'All suppliers',
			'search_items'  => 'Search suppliers',
			'menu_name'     => 'Dental Suppliers',
		),
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'menu_position'      => 26,
		'menu_icon'          => 'dashicons-store',
		'supports'           => array( 'title', 'excerpt' ),
		'capability_type'    => 'post',
		'map_meta_cap'       => true,
	) );
} );

/** Menu bubble with the number of listings waiting for review. */
add_action( 'admin_menu', function () {
	global $menu;
	$pending = (int) wp_count_posts( 'aurix_supplier' )->pending;
	if ( ! $pending ) {
		return;
	}
	foreach ( $menu as $i => $item ) {
		if ( 'edit.php?post_type=aurix_supplier' === $item[2] ) {
			$menu[ $i ][0] .= ' <span class="awaiting-mod">' . $pending . '</span>';
		}
	}
}, 99 );

add_filter( 'enter_title_here', function ( $text, $post ) {
	return 'aurix_supplier' === $post->post_type ? 'Company name' : $text;
}, 10, 2 );

/* ── Edit screen ───────────────────────────────────────────────────────── */

add_action( 'add_meta_boxes_aurix_supplier', function () {
	add_meta_box( 'axd-details', 'Listing details', 'aurix_dir_meta_box', 'aurix_supplier', 'normal', 'high' );
} );

function aurix_dir_meta_box( $post ) {
	wp_nonce_field( 'axd_save', 'axd_nonce' );
	echo '<p>The short description is the <strong>Excerpt</strong> box below (up to about 200 characters).</p>';
	echo '<table class="form-table"><tbody>';
	foreach ( aurix_dir_fields() as $key => $label ) {
		$value = aurix_dir_meta( $post->ID, $key );
		echo '<tr><th><label for="axd-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
		if ( 'type' === $key ) {
			echo '<select id="axd-type" name="axd[type]">';
			foreach ( aurix_dir_types() as $t ) {
				echo '<option' . selected( $value, $t, false ) . '>' . esc_html( $t ) . '</option>';
			}
			echo '</select>';
		} else {
			echo '<input type="text" class="regular-text" id="axd-' . esc_attr( $key ) . '" name="axd[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '">';
			if ( 'focus' === $key ) {
				echo '<p class="description">Choose from: ' . esc_html( implode( '; ', aurix_dir_focus_areas() ) ) . '</p>';
			}
			if ( 'state' === $key ) {
				echo '<p class="description">For the US use the 2-letter code, e.g. NY.</p>';
			}
		}
		echo '</td></tr>';
	}
	foreach ( array( 'featured' => 'Featured (shown first, with a badge)', 'verified' => 'Verified (details checked with the company)' ) as $key => $label ) {
		echo '<tr><th>' . esc_html( ucfirst( $key ) ) . '</th><td><label><input type="checkbox" name="axd[' . esc_attr( $key ) . ']" value="1"' . checked( aurix_dir_meta( $post->ID, $key ), '1', false ) . '> ' . esc_html( $label ) . '</label></td></tr>';
	}
	echo '</tbody></table>';
}

add_action( 'save_post_aurix_supplier', function ( $post_id ) {
	if ( ! isset( $_POST['axd_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['axd_nonce'] ), 'axd_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$in = isset( $_POST['axd'] ) ? wp_unslash( (array) $_POST['axd'] ) : array();
	foreach ( array_keys( aurix_dir_fields() ) as $key ) {
		$value = isset( $in[ $key ] ) ? $in[ $key ] : '';
		$value = 'email' === $key ? sanitize_email( $value ) : sanitize_text_field( $value );
		if ( 'website' === $key ) {
			$value = aurix_dir_domain( $value );
		}
		update_post_meta( $post_id, '_axd_' . $key, $value );
	}
	foreach ( array( 'featured', 'verified' ) as $key ) {
		update_post_meta( $post_id, '_axd_' . $key, empty( $in[ $key ] ) ? '' : '1' );
	}
} );

/** Clear the cached directory whenever a listing changes. */
function aurix_dir_flush() {
	delete_transient( 'aurix_dir_listings' );
}
add_action( 'save_post_aurix_supplier', 'aurix_dir_flush', 20 );
add_action( 'deleted_post', 'aurix_dir_flush' );
add_action( 'transition_post_status', function ( $new, $old, $post ) {
	if ( 'aurix_supplier' === $post->post_type ) {
		aurix_dir_flush();
	}
}, 10, 3 );

/* ── Admin list columns ────────────────────────────────────────────────── */

add_filter( 'manage_aurix_supplier_posts_columns', function ( $cols ) {
	return array(
		'cb'          => $cols['cb'],
		'title'       => 'Company',
		'axd_type'    => 'Type',
		'axd_where'   => 'Location',
		'axd_website' => 'Website',
		'axd_flags'   => 'Flags',
		'date'        => $cols['date'],
	);
} );

add_action( 'manage_aurix_supplier_posts_custom_column', function ( $col, $post_id ) {
	switch ( $col ) {
		case 'axd_type':
			echo esc_html( aurix_dir_meta( $post_id, 'type' ) );
			break;
		case 'axd_where':
			echo esc_html( implode( ', ', array_filter( array( aurix_dir_meta( $post_id, 'city' ), aurix_dir_meta( $post_id, 'state' ), aurix_dir_meta( $post_id, 'country' ) ) ) ) );
			break;
		case 'axd_website':
			$site = aurix_dir_meta( $post_id, 'website' );
			if ( $site ) {
				echo '<a href="' . esc_url( 'https://' . $site ) . '" target="_blank" rel="noopener">' . esc_html( $site ) . '</a>';
			}
			break;
		case 'axd_flags':
			$flags = array();
			if ( aurix_dir_meta( $post_id, 'featured' ) ) $flags[] = 'Featured';
			if ( aurix_dir_meta( $post_id, 'verified' ) ) $flags[] = 'Verified';
			if ( 'submitted' === aurix_dir_meta( $post_id, 'source' ) ) $flags[] = 'Submitted';
			echo esc_html( implode( ', ', $flags ) );
			break;
	}
}, 10, 2 );

/* ── Data for the directory page ───────────────────────────────────────── */

/**
 * All published listings as plain arrays, featured first then A–Z.
 */
function aurix_dir_listings() {
	$cached = get_transient( 'aurix_dir_listings' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$posts = get_posts( array(
		'post_type'        => 'aurix_supplier',
		'post_status'      => 'publish',
		'numberposts'      => -1,
		'orderby'          => 'title',
		'order'            => 'ASC',
		'suppress_filters' => false,
	) );
	$out = array();
	foreach ( $posts as $p ) {
		$focus = array_values( array_filter( array_map( 'trim', explode( ';', aurix_dir_meta( $p->ID, 'focus' ) ) ) ) );
		$out[] = array(
			'id'       => $p->ID,
			'name'     => get_the_title( $p ),
			'desc'     => $p->post_excerpt,
			'website'  => aurix_dir_meta( $p->ID, 'website' ),
			'city'     => aurix_dir_meta( $p->ID, 'city' ),
			'state'    => aurix_dir_meta( $p->ID, 'state' ),
			'country'  => aurix_dir_meta( $p->ID, 'country' ),
			'region'   => aurix_dir_region( aurix_dir_meta( $p->ID, 'country' ) ),
			'type'     => aurix_dir_meta( $p->ID, 'type' ),
			'phone'    => aurix_dir_meta( $p->ID, 'phone' ),
			'focus'    => $focus,
			'featured' => (bool) aurix_dir_meta( $p->ID, 'featured' ),
			'verified' => (bool) aurix_dir_meta( $p->ID, 'verified' ),
			'own'      => aurix_dir_is_own( aurix_dir_meta( $p->ID, 'website' ) ),
		);
	}
	usort( $out, function ( $a, $b ) {
		if ( $a['own'] !== $b['own'] ) return $a['own'] ? -1 : 1;
		if ( $a['featured'] !== $b['featured'] ) return $a['featured'] ? -1 : 1;
		return strcasecmp( $a['name'], $b['name'] );
	} );
	set_transient( 'aurix_dir_listings', $out, DAY_IN_SECONDS );
	return $out;
}

/** True on a page that uses the directory template or contains the shortcode. */
function aurix_is_directory_page() {
	if ( is_admin() || ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	return AURIX_DIR_TEMPLATE === get_page_template_slug( $post )
		|| ( $post instanceof WP_Post && has_shortcode( $post->post_content, 'aurix_supplier_directory' ) );
}

function aurix_dir_enqueue() {
	if ( wp_script_is( 'aurix-directory', 'enqueued' ) ) {
		return;
	}
	$ver = wp_get_theme( get_template() )->get( 'Version' );
	wp_enqueue_style( 'aurix-directory', get_template_directory_uri() . '/css/supplier-directory.css', array( 'aurix-style' ), $ver );
	wp_enqueue_script( 'aurix-directory', get_template_directory_uri() . '/js/supplier-directory.js', array(), $ver, true );
	wp_localize_script( 'aurix-directory', 'aurixDir', array(
		'ajax' => admin_url( 'admin-ajax.php' ),
	) );
}

add_action( 'wp_enqueue_scripts', function () {
	if ( aurix_is_directory_page() ) {
		aurix_dir_enqueue();
	}
}, 20 );

/**
 * [aurix_supplier_directory title="..."] — all directory sections. Assets are
 * also enqueued here so the shortcode works inside page builders.
 */
add_shortcode( 'aurix_supplier_directory', function ( $atts ) {
	$atts = shortcode_atts( array( 'title' => 'Dental Instruments Supplier Directory' ), $atts, 'aurix_supplier_directory' );
	aurix_dir_enqueue();
	ob_start();
	get_template_part( 'template-parts/supplier-directory', null, array( 'embed' => true, 'title' => $atts['title'] ) );
	return ob_get_clean();
} );

/** The default page template shows the shortcode full width, without its own title. */
function aurix_dir_page_is_embed( $post = null ) {
	$post = get_post( $post );
	return $post && has_shortcode( $post->post_content, 'aurix_supplier_directory' );
}

/* ── Public submissions ────────────────────────────────────────────────── */

/** Signed timestamp for the form, so bots that post instantly are rejected. */
function aurix_dir_form_token() {
	$t = time();
	return $t . '.' . substr( wp_hash( 'axd' . $t ), 0, 16 );
}

function aurix_dir_submit() {
	$f = wp_unslash( $_POST );

	// Honeypot field must stay empty; token must be genuine and at least 4 seconds old.
	if ( ! empty( $f['axd_hp'] ) ) {
		wp_send_json_success( array( 'message' => 'Thank you.' ) );
	}
	$token = explode( '.', (string) ( $f['axd_t'] ?? '' ) );
	if ( 2 !== count( $token ) || ! hash_equals( substr( wp_hash( 'axd' . $token[0] ), 0, 16 ), $token[1] )
		|| time() - (int) $token[0] < 4 || time() - (int) $token[0] > 7 * DAY_IN_SECONDS ) {
		wp_send_json_error( array( 'message' => 'Please reload the page and try again.' ) );
	}

	// At most 5 submissions per IP address per hour.
	$ip_key = 'axd_rl_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$count  = (int) get_transient( $ip_key );
	if ( $count >= 5 ) {
		wp_send_json_error( array( 'message' => 'Too many submissions. Please try again later.' ) );
	}

	$name    = sanitize_text_field( $f['company'] ?? '' );
	$website = aurix_dir_domain( sanitize_text_field( $f['website'] ?? '' ) );
	$country = sanitize_text_field( $f['country'] ?? '' );
	$state   = sanitize_text_field( $f['state'] ?? '' );
	$city    = sanitize_text_field( $f['city'] ?? '' );
	$type    = sanitize_text_field( $f['type'] ?? '' );
	$desc    = sanitize_textarea_field( $f['desc'] ?? '' );
	$contact = sanitize_text_field( $f['contact'] ?? '' );
	$email   = sanitize_email( $f['email'] ?? '' );
	$phone   = sanitize_text_field( $f['phone'] ?? '' );
	$focus   = array_values( array_intersect( aurix_dir_focus_areas(), array_map( 'sanitize_text_field', (array) ( $f['focus'] ?? array() ) ) ) );
	$wants   = ! empty( $f['distributor'] );

	if ( ! $name || ! $website || ! strpos( $website, '.' ) || ! $country || ! is_email( $email ) || ! $contact
		|| ! in_array( $type, aurix_dir_types(), true ) || empty( $f['consent'] ) ) {
		wp_send_json_error( array( 'message' => 'Please fill in all required fields.' ) );
	}
	if ( mb_strlen( $desc ) > 300 ) {
		$desc = mb_substr( $desc, 0, 297 ) . '...';
	}

	// One listing per website.
	$dupe = get_posts( array(
		'post_type'   => 'aurix_supplier',
		'post_status' => array( 'publish', 'pending', 'draft' ),
		'meta_key'    => '_axd_website',
		'meta_value'  => $website,
		'fields'      => 'ids',
		'numberposts' => 1,
	) );
	if ( $dupe ) {
		wp_send_json_error( array( 'message' => 'A listing for ' . $website . ' already exists or is under review. To update it, contact us.' ) );
	}

	$post_id = wp_insert_post( array(
		'post_type'    => 'aurix_supplier',
		'post_status'  => 'pending',
		'post_title'   => $name,
		'post_excerpt' => $desc,
	), true );
	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'message' => 'Something went wrong. Please try again.' ) );
	}
	$meta = compact( 'website', 'country', 'state', 'city', 'type', 'contact', 'email', 'phone' );
	$meta['focus']  = implode( ';', $focus );
	$meta['source'] = 'submitted';
	foreach ( $meta as $k => $v ) {
		update_post_meta( $post_id, '_axd_' . $k, $v );
	}
	set_transient( $ip_key, $count + 1, HOUR_IN_SECONDS );

	if ( function_exists( 'aurix_inbox_add' ) ) {
		aurix_inbox_add( $wants ? 'distributor' : 'supplier', array(
			'name' => $contact, 'email' => $email, 'phone' => $phone, 'company' => $name, 'country' => $country,
			'subject' => $wants ? 'Directory listing + wants to become an Aurix distributor' : 'Supplier directory listing',
			'message' => $desc, 'ref' => 'import:supplier:' . $post_id,
			'data' => array(
				'Website' => $website, 'City / State' => implode( ', ', array_filter( array( $city, $state ) ) ), 'Company type' => $type,
				'Focus' => implode( ', ', $focus ), 'Wants to be an Aurix distributor' => $wants ? 'Yes' : 'No', 'Listing ID' => $post_id,
			),
		) );
	}

	$admin = get_option( 'admin_email' );
	wp_mail(
		$admin,
		'New supplier listing to review: ' . $name,
		"A company asked to be listed in the dental supplier directory.\n\n"
		. "Company: $name\nWebsite: $website\nLocation: " . implode( ', ', array_filter( array( $city, $state, $country ) ) ) . "\n"
		. "Type: $type\nFocus: " . implode( ', ', $focus ) . "\nContact: $contact <$email> $phone\n"
		. 'Interested in becoming an Aurix distributor: ' . ( $wants ? 'YES' : 'no' ) . "\n\n"
		. "Description:\n$desc\n\nReview and publish: " . admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
		array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $email )
	);
	wp_mail(
		$email,
		'We received your directory listing — Aurix International',
		"Hello,\n\nThank you for submitting your company to the Aurix dental supplier directory. Our team reviews every listing, usually within a few working days, and will contact you if we need more details.\n\nBest regards,\nAurix International\n" . home_url( '/' ),
		array( 'Content-Type: text/plain; charset=UTF-8' )
	);

	wp_send_json_success( array( 'message' => 'Thank you! Your listing has been submitted for review. We usually publish new listings within a few working days.' ) );
}
add_action( 'wp_ajax_aurix_dir_submit', 'aurix_dir_submit' );
add_action( 'wp_ajax_nopriv_aurix_dir_submit', 'aurix_dir_submit' );

/* ── Starter list import ───────────────────────────────────────────────── */

add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=aurix_supplier', 'Starter list', 'Starter list', 'manage_options', 'aurix-dir-import', 'aurix_dir_import_page' );
} );

/** Import data/suppliers.json; existing websites are skipped. Returns [added, skipped]. */
function aurix_dir_import() {
	$file = get_template_directory() . '/data/suppliers.json';
	$rows = file_exists( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : array();
	$added = 0;
	$skipped = 0;
	foreach ( (array) $rows as $r ) {
		$site = aurix_dir_domain( $r['website'] ?? '' );
		if ( ! $site || get_posts( array( 'post_type' => 'aurix_supplier', 'post_status' => 'any', 'meta_key' => '_axd_website', 'meta_value' => $site, 'fields' => 'ids', 'numberposts' => 1 ) ) ) {
			$skipped++;
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'    => 'aurix_supplier',
			'post_status'  => 'publish',
			'post_title'   => sanitize_text_field( $r['name'] ),
			'post_excerpt' => sanitize_text_field( $r['desc'] ?? '' ),
		) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		foreach ( array( 'city', 'state', 'country', 'type', 'focus' ) as $k ) {
			update_post_meta( $id, '_axd_' . $k, sanitize_text_field( $r[ $k ] ?? '' ) );
		}
		update_post_meta( $id, '_axd_website', $site );
		update_post_meta( $id, '_axd_source', 'seed' );
		if ( aurix_dir_is_own( $site ) ) {
			update_post_meta( $id, '_axd_featured', '1' );
			update_post_meta( $id, '_axd_verified', '1' );
		}
		$added++;
	}
	aurix_dir_flush();
	return array( $added, $skipped );
}

function aurix_dir_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$msg = '';
	if ( isset( $_POST['axd_import'] ) && check_admin_referer( 'axd_import' ) ) {
		list( $added, $skipped ) = aurix_dir_import();
		update_option( 'aurix_dir_imported', 1 );
		$msg = sprintf( 'Imported %d suppliers (%d already listed).', $added, $skipped );
	}
	$file  = get_template_directory() . '/data/suppliers.json';
	$total = file_exists( $file ) ? count( (array) json_decode( (string) file_get_contents( $file ), true ) ) : 0;
	echo '<div class="wrap"><h1>Supplier directory — starter list</h1>';
	if ( $msg ) {
		echo '<div class="notice notice-success"><p>' . wp_kses_post( $msg ) . '</p></div>';
	}
	echo '<p>The theme includes a starter list of <strong>' . (int) $total . '</strong> dental suppliers (mostly US). Importing adds them as published listings.</p>';
	echo '<p>Companies already in the directory (same website) are skipped, so it is safe to run again after a theme update adds new suppliers.</p>';
	echo '<form method="post">';
	wp_nonce_field( 'axd_import' );
	echo '<p><button class="button button-primary" name="axd_import" value="1">Import starter list</button></p></form>';
	echo '<h2>Showing the directory on a page</h2><ol>';
	echo '<li>Create a page (Pages → Add New), for example “Dental Instruments Supplier Directory”.</li>';
	echo '<li>Add this shortcode to the page content: <code>[aurix_supplier_directory]</code> — or, instead, choose <strong>Dental Supplier Directory</strong> under Page Attributes → Template.</li>';
	echo '<li>Optional: change the main heading with <code>[aurix_supplier_directory title="Your heading"]</code>.</li></ol>';
	echo '<p>Suggested SEO title: <code>Dental Instruments Supplier Directory: 100+ US &amp; Global</code><br>Suggested meta description: <code>Find dental instrument suppliers, manufacturers and distributors in the USA and worldwide. Filter by state, country and product. List your company free.</code></p>';
	echo '<h2>Reviewing submissions</h2><p>Companies that use the “Add your company” form appear under <a href="' . esc_url( admin_url( 'edit.php?post_type=aurix_supplier&post_status=pending' ) ) . '">Dental Suppliers → Pending</a>. Check the details, then click Publish. You also get an email for each one.</p></div>';
}

add_action( 'admin_notices', function () {
	if ( get_option( 'aurix_dir_imported' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	echo '<div class="notice notice-info"><p><strong>New: dental supplier directory.</strong> <a href="' . esc_url( admin_url( 'edit.php?post_type=aurix_supplier&page=aurix-dir-import' ) ) . '">Import the starter list</a>, then add <code>[aurix_supplier_directory]</code> to any page.</p></div>';
} );
