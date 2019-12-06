<?php
// Display admin notice on screen load
function woo_ts_admin_notice( $message = '', $priority = 'updated', $screen = '' ) {
	if( $priority == false || $priority == '' )
		$priority = 'updated';
	if( $message <> '' ) {
		ob_start();
		woo_ts_admin_notice_html( $message, $priority, $screen );
		$output = ob_get_contents();
		ob_end_clean();
		// Check if an existing notice is already in queue
		$existing_notice = get_transient( WOO_TS_PREFIX . '_notice' );
		if( $existing_notice !== false ) {
			$existing_notice = base64_decode( $existing_notice );
			$output = $existing_notice . $output;
		}
		$response = set_transient( WOO_TS_PREFIX . '_notice', base64_encode( $output ), MINUTE_IN_SECONDS );
		// Check if the Transient was saved
		if( $response !== false )
			add_action( 'admin_notices', 'woo_ts_admin_notice_print' );
	}

}

// HTML template for admin notice
function woo_ts_admin_notice_html( $message = '', $priority = 'updated', $screen = '' ) {
	// Display admin notice on specific screen
	if( !empty( $screen ) ) {

		global $pagenow;

		if( is_array( $screen ) ) {
			if( in_array( $pagenow, $screen ) == false )
				return;
		} else {
			if( $pagenow <> $screen )
				return;
		}

	} ?>
<div id="message" class="<?php echo $priority; ?>">
	<p><?php echo $message; ?></p>
</div>
<?php

}

// Grabs the WordPress transient that holds the admin notice and prints it
function woo_ts_admin_notice_print() {

	$output = get_transient( WOO_TS_PREFIX . '_notice' );
	if( $output !== false ) {
		delete_transient( WOO_TS_PREFIX . '_notice' );
		$output = base64_decode( $output );
		echo $output;
	}

}

// HTML template header on Product Importer screen
function woo_ts_template_header( $title = '', $icon = 'woocommerce' ) { ?>
<div id="woo-pi" class="wrap">
	<div id="icon-<?php echo $icon; ?>" class="icon32 icon32-woocommerce-importer"><br /></div>
	<h2><?php echo $title; ?></h2>
<?php

}

// HTML template footer on Product Importer screen
function woo_ts_template_footer() { ?>
</div>
<!-- .wrap -->
<?php

}

// Add Product Import to WordPress Administration menu
function woo_ts_admin_menu() {

	$page = add_submenu_page( 'woocommerce', __( 'Ticket Importer', 'woo_ts' ), __( 'Ticket Importer', 'woo_ts' ), 'manage_woocommerce', 'woo_ts', 'woo_ts_html_page' );
	add_action( 'admin_print_styles-' . $page, 'woo_ts_enqueue_scripts' );

}
add_action( 'admin_menu', 'woo_ts_admin_menu', 11 );

// Load CSS and jQuery scripts for Product Importer screen
function woo_ts_enqueue_scripts( $hook ) {
	// Simple check that WooCommerce is activated
	if( class_exists( 'WooCommerce' ) ) {
		global $woocommerce;
		// Load WooCommerce default Admin styling
		wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );

	}

	// Common
	wp_enqueue_style( 'woo_ts_styles', plugins_url( '/templates/admin/import.css', WOO_TS_RELPATH ) );
	wp_enqueue_script( 'woo_ts_scripts', plugins_url( '/templates/admin/import.js', WOO_TS_RELPATH ), array( 'jquery' ) );
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_script( 'jquery-toggleblock', plugins_url( '/js/toggleblock.js', WOO_TS_RELPATH ), array( 'jquery' ) );
	wp_enqueue_style( 'woo_vm_styles', plugins_url( '/templates/admin/woocommerce-admin_dashboard_vm-plugins.css', WOO_TS_RELPATH ) );
}

// HTML active class for the currently selected tab on the Product Importer screen
function woo_ts_admin_active_tab( $tab_name = null, $tab = null ) {
	if( isset( $_GET['tab'] ) && !$tab )
		$tab = $_GET['tab'];
	else if( !isset( $_GET['tab'] ) && woo_ts_get_option( 'skip_overview', false ) )
		$tab = 'import';
	else
		$tab = 'overview';

	$output = '';
	if( isset( $tab_name ) && $tab_name ) {
		if( $tab_name == $tab )
			$output = ' nav-tab-active';
	}
	echo $output;

}

// HTML template for each tab on the Product Importer screen
function woo_ts_tab_template( $tab = '' ) {

	global $import;

	if (!$tab)
		$tab = 'overview';

	switch($tab) {
		case 'overview':
			$skip_overview = woo_ts_get_option( 'skip_overview', false );
			break;

		case 'import':
			/*$upload_dir = wp_upload_dir();
			$max_upload = absint( ini_get( 'upload_max_filesize' ) );
			$max_post = absint( ini_get( 'post_max_size' ) );
			$memory_limit = absint( ini_get( 'memory_limit' ) );
			$wp_upload_limit = round( wp_max_upload_size() / 1024 / 1024, 2 );
			$upload_mb = min( $max_upload, $max_post, $memory_limit, $wp_upload_limit );
			$file_path = $upload_dir['basedir'] . '/';
			$file_path_relative = 'imports/store-a.csv';
			$file_url = 'http://www.domain.com/wp-content/uploads/imports/store-a.jpg';
			$file_ftp_host = 'ftp.domain.com';
			$file_ftp_user = 'user';
			$file_ftp_pass = 'password';
			$file_ftp_port = '';
			$file_ftp_path = 'wp-content/uploads/imports/store-a.jpg';
			$file_ftp_timeout = '';
			if( isset( $_POST['csv_file_path'] ) )
				$file_path_relative = $_POST['csv_file_path'];

			$csv_sample_link = 'http://www.visser.com.au/woocommerce/plugins/product-importer-deluxe/#sample-csv';
			$csv_template_link = 'http://www.visser.com.au/woocommerce/plugins/product-importer-deluxe/#blank-csv';*/

			if( isset( $_GET['import'] ) && $_GET['import'] == WOO_TS_PREFIX )
				$url = 'import';
			if( isset( $_GET['page'] ) && $_GET['page'] == WOO_TS_PREFIX )
				$url = 'page';
			break;

		case 'settings':
			$mode = woo_ts_get_option( 'mode', '' );
			$api_key = woo_ts_get_option( 'api_key', ',' );
			$promoter_email = woo_ts_get_option( 'promoter_email', '');
			$ticket_html_template = woo_ts_get_option( 'ticket_html_template', '>' );
			break;

}
	if( $tab ) {
		if( file_exists( WOO_TS_PATH . 'templates/admin/tabs-' . $tab . '.php' ) ) //{
			include_once( WOO_TS_PATH . 'templates/admin/tabs-' . $tab . '.php' );
		/*} else {
			$message = sprintf( __( 'We couldn\'t load the import template file <code>%s</code> within <code>%s</code>, this file should be present.', 'woo_ts' ), 'tabs-' . $tab . '.php', WOO_TS_PATH . 'templates/admin/...' );
			woo_ts_admin_notice_html( $message, 'error' );
			ob_start(); ?>
<p><?php _e( 'You can see this error for one of a few common reasons', 'woo_ts' ); ?>:</p>
<ul class="ul-disc">
	<li><?php _e( 'WordPress was unable to create this file when the Plugin was installed or updated', 'woo_ts' ); ?></li>
	<li><?php _e( 'The Plugin files have been recently changed and there has been a file conflict', 'woo_ts' ); ?></li>
	<li><?php _e( 'The Plugin file has been locked and cannot be opened by WordPress', 'woo_ts' ); ?></li>
</ul>
<p><?php _e( 'Jump onto our website and download a fresh copy of this Plugin as it might be enough to fix this issue. If this persists get in touch with us.', 'woo_ts' ); ?></p>
<?php
			ob_end_flush();
		}*/
	}

}

function woo_ts_modules_status_class( $status = 'inactive' ) {
	$output = '';
	switch( $status ) {
		case 'active':
			$output = 'green';
			break;

		case 'inactive':
			$output = 'yellow';
			break;
	}
	echo $output;
}

function woo_ts_modules_status_label( $status = 'inactive' ) {
	$output = '';
	switch( $status ) {
		case 'active':
			$output = __( 'OK', 'woo_ts' );
			break;

		case 'inactive':
			$output = __( 'Install', 'woo_ts' );
			break;
	}
	echo $output;
}

?>