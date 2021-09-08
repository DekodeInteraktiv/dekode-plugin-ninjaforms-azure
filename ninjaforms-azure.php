<?php
/**
 * Plugin Name:     Ninja Forms - File Uploads to Azure
 * Plugin URI:      https://dekode.no
 * Description:     Add support for offloading Ninja Forms Uploads to the Microsoft Azure cloud.
 * Author:          Dekode
 * Author URI:      https://dekode.no
 * Text Domain:     dekode-ninjaforms-azure
 * Version:         1.0.2
 *
 * @package DekodeNinjaFormsAzure
 */

declare( strict_types=1 );

namespace Dekode\NinjaForms\Azure;

define( 'DEKODE_NINJAFORMS_AZURE_VERSION', '1.0.2' );
define( 'DEKODE_NINJAFORMS_AZURE_DIR_PATH', plugin_dir_path( __FILE__ ) );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

add_action( 'plugins_loaded', function() {
	// Check that te NinjaForms File Upload plugin is loaded before we try extending it.
	if ( ! class_exists( '\NF_FU_External_Abstracts_Service' ) ) {
		return;
	}

	include_once __DIR__ . '/includes/service/class-nf-fu-external-services-azure-service.php';
	include_once __DIR__ . '/includes/class-controller.php';
	include_once __DIR__ . '/includes/override.php';
}, 9 ); // It works with priority less than 10.
