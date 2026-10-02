<?php
/**
 * Plugin Name: WattDrop (Eco Favicon)
 * Description: Stops 404 log spam and saves massive server resources (CPU/DB) by intercepting missing icon requests before WordPress loads its core. Pick an icon via the Media Library, or let WattDrop generate an Eco Logo from your site initials — served straight from memory.
 * Version: 1.0.0
 * Author: John Bubak
 * Author URI: https://github.com/johnbubak
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: wattdrop
 *
 * WattDrop | MIT License | More Code. Less Energy. Zero Useless Consumption.
 * Public-facing language: English.
 *
 * How it works:
 *  - We hook as early as possible (plugins_loaded, priority 0) and check the
 *    request URI against all standard icon paths. If it matches, WordPress
 *    never boots the DB/theme layer — we answer with the chosen icon or a
 *    generative SVG from memory and exit.
 *  - Real icon files on disk always win (checked against ABSPATH).
 *  - Admin picks an icon via the native Media Library (crop/convert happens
 *    in WordPress' own image editor — no custom canvas).
 *  - No icon chosen? A generative "Eco Logo" from the site name initials is
 *    built in memory (green→blue gradient, same as wattdrop.js).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'WATTDROP_OPTION_ICON', 'wattdrop_icon' );
define( 'WATTDROP_TRANSIENT_SVG', 'wattdrop_eco_svg' );

/**
 * All standard icon paths browsers and bots request blindly.
 *
 * @return string[] Paths (leading slash), lower-case.
 */
function wattdrop_icon_paths() {
	return array(
		'/favicon.ico',
		'/favicon-16x16.png',
		'/favicon-32x32.png',
		'/apple-touch-icon.png',
		'/apple-touch-icon-precomposed.png',
	);
}

/**
 * Does the request target a standard icon?
 *
 * @param string $path Request URI path (no query string).
 * @return bool
 */
function wattdrop_is_icon_request( $path ) {
	// Exact matches.
	if ( in_array( strtolower( $path ), wattdrop_icon_paths(), true ) ) {
		return true;
	}
	// Wildcard pattern: apple-touch-icon-*.png (e.g. -57x57, -72x72, -152x152).
	if ( preg_match( '#^/apple-touch-icon.*\.png$#', $path ) ) {
		return true;
	}
	return false;
}

/**
 * The core intercept. Runs before WordPress boots the DB/theme layer.
 */
function wattdrop_intercept_icon_requests() {
	// Only for front-end requests, never in wp-admin or CLI.
	if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}

	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) : '';
	if ( ! $path ) {
		return;
	}

	if ( ! wattdrop_is_icon_request( $path ) ) {
		return;
	}

	// 1. Real files always win — we never interfere.
	$real_file = ABSPATH . ltrim( $path, '/' );
	if ( file_exists( $real_file ) ) {
		return; // Let the server / WordPress serve the real file.
	}

	// 2. Admin-chosen icon from the Media Library.
	if ( wattdrop_serve_chosen_icon() ) {
		exit;
	}

	// 3. Generative Eco Logo from site initials, built in memory.
	wattdrop_serve_eco_logo();

	exit;
}
add_action( 'plugins_loaded', 'wattdrop_intercept_icon_requests', 0 );

/**
 * Serve the admin-chosen icon (attachment bytes + correct mime type).
 *
 * @return bool True if an icon was served, false otherwise.
 */
function wattdrop_serve_chosen_icon() {
	$attachment_id = absint( get_option( WATTDROP_OPTION_ICON, 0 ) );
	if ( ! $attachment_id ) {
		return false;
	}

	$file = get_attached_file( $attachment_id );
	if ( ! $file || ! file_exists( $file ) ) {
		return false;
	}

	$mime = get_post_mime_type( $attachment_id );
	if ( ! $mime ) {
		$mime = 'image/svg+xml';
	}

	wattdrop_send_headers( $mime );
	readfile( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- byte-serving a local file.
	return true;
}

/**
 * Build the generative Eco Logo SVG from the site name initials.
 *
 * Mirrors wattdrop.js: green→blue gradient, domain/site initials, "ZI" fallback.
 *
 * @return string SVG markup.
 */
function wattdrop_build_eco_logo() {
	$name    = get_bloginfo( 'name', 'display' );
	$clean   = preg_replace( '/[^a-zA-Z0-9]/', '', $name );
	$initials = strtoupper( substr( $clean, 0, 2 ) );
	if ( '' === $initials ) {
		$initials = 'ZI'; // Fallback, same as wattdrop.js.
	}

	return sprintf(
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">' .
		'<defs><linearGradient id="wd-grad" x1="0%%" y1="0%%" x2="100%%" y2="100%%">' .
		'<stop offset="0%%" stop-color="#10b981"/><stop offset="100%%" stop-color="#0ea5e9"/>' .
		'</linearGradient></defs>' .
		'<rect width="100" height="100" rx="22" fill="url(#wd-grad)"/>' .
		'<text x="50%%" y="55%%" font-size="46" text-anchor="middle" fill="#ffffff" ' .
		'font-family="system-ui, monospace" font-weight="bold">%s</text>' .
		'</svg>',
		esc_html( $initials )
	);
}

/**
 * Serve the Eco Logo SVG from a transient cache (or build + cache it).
 */
function wattdrop_serve_eco_logo() {
	$svg = get_transient( WATTDROP_TRANSIENT_SVG );
	if ( false === $svg ) {
		$svg = wattdrop_build_eco_logo();
		set_transient( WATTDROP_TRANSIENT_SVG, $svg, DAY_IN_SECONDS );
	}

	wattdrop_send_headers( 'image/svg+xml' );
	echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput -- trusted, generated SVG.
}

/**
 * Send cache-friendly headers for the served icon.
 *
 * @param string $mime Content-Type.
 */
function wattdrop_send_headers( $mime ) {
	nocache_headers();
	header( 'Content-Type: ' . $mime );
	// Icons are small and immutable in practice — browsers revalidate cheaply.
	header( 'Cache-Control: public, max-age=86400' );
}

/* --------------------------------------------------------------------------
 * Admin: icon selection via the native Media Library.
 * ------------------------------------------------------------------------ */

/**
 * Register the settings page.
 */
function wattdrop_admin_menu() {
	add_options_page(
		__( 'WattDrop', 'wattdrop' ),
		__( 'WattDrop', 'wattdrop' ),
		'manage_options',
		'wattdrop',
		'wattdrop_admin_page'
	);
}
add_action( 'admin_menu', 'wattdrop_admin_menu' );

/**
 * Register the option (handles nonce + capability via the Settings API).
 */
function wattdrop_register_settings() {
	register_setting(
		'wattdrop',
		WATTDROP_OPTION_ICON,
		array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 0,
		)
	);
}
add_action( 'admin_init', 'wattdrop_register_settings' );

/**
 * Enqueue the Media Library picker on the WattDrop settings page.
 *
 * @param string $hook Current admin page hook.
 */
function wattdrop_admin_assets( $hook ) {
	if ( 'settings_page_wattdrop' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'wattdrop-admin', plugin_dir_url( __FILE__ ) . 'wattdrop-admin.js', array( 'jquery' ), '1.0.0', true );
	wp_localize_script(
		'wattdrop-admin',
		'wattdrop',
		array(
			'chooseTitle' => __( 'Choose your icon', 'wattdrop' ),
			'chooseText'  => __( 'Use this image', 'wattdrop' ),
			'removeText'  => __( 'No icon chosen — the generative Eco Logo (site initials) is served.', 'wattdrop' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'wattdrop_admin_assets' );

/**
 * Render the settings page.
 */
function wattdrop_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$attachment_id = absint( get_option( WATTDROP_OPTION_ICON, 0 ) );
	$preview       = $attachment_id ? wp_get_attachment_image( $attachment_id, array( 64, 64 ), true ) : '';
	$crop_url      = $attachment_id ? admin_url( 'media.php?attachment_id=' . $attachment_id . '&action=edit' ) : '';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'WattDrop — Eco Favicon', 'wattdrop' ); ?></h1>
		<p><?php esc_html_e( 'WattDrop intercepts missing icon requests before WordPress boots, saving CPU, DB queries, disk I/O and electricity.', 'wattdrop' ); ?></p>

		<form method="post" action="options.php">
			<?php settings_fields( 'wattdrop' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Icon', 'wattdrop' ); ?></th>
					<td>
						<input type="hidden" name="<?php echo esc_attr( WATTDROP_OPTION_ICON ); ?>" id="wattdrop-icon-id" value="<?php echo esc_attr( $attachment_id ); ?>" />

						<div id="wattdrop-preview" style="margin-bottom:10px;">
							<?php
							if ( $preview ) {
								echo $preview; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_get_attachment_image output.
							} else {
								echo '<span class="description">' . esc_html__( 'No icon chosen — the generative Eco Logo (site initials) is served.', 'wattdrop' ) . '</span>';
							}
							?>
						</div>

						<button type="button" class="button" id="wattdrop-choose"><?php esc_html_e( 'Choose icon', 'wattdrop' ); ?></button>
						<button type="button" class="button" id="wattdrop-remove"><?php esc_html_e( 'Use Eco Logo', 'wattdrop' ); ?></button>

						<?php if ( $crop_url ) : ?>
							<p class="description">
								<a href="<?php echo esc_url( $crop_url ); ?>" target="_blank">
									<?php esc_html_e( 'Crop / convert in the WordPress image editor', 'wattdrop' ); ?>
								</a>
							</p>
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Invalidate the Eco Logo cache when the icon changes.
 */
function wattdrop_flush_cache() {
	delete_transient( WATTDROP_TRANSIENT_SVG );
}
add_action( 'update_option_' . WATTDROP_OPTION_ICON, 'wattdrop_flush_cache' );
add_action( 'add_option_' . WATTDROP_OPTION_ICON, 'wattdrop_flush_cache' );