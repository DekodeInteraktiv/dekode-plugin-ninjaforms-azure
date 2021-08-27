<?php
/**
 * Plugin Name:     Ninja Forms - File Uploads to Azure
 * Plugin URI:      https://dekode.no
 * Description:
 * Author:          Dekode
 * Author URI:      https://dekode.no
 * Text Domain:     dekode-ninjaforms-azure
 * Version:         1.0.0
 *
 * @package DekodeNinjaFormsAzure
 */

declare( strict_types=1 );

namespace Dekode\NinjaForms\Azure;

define('DEKODE_NINJAFORMS_AZURE_VERSION', '1.0.0');
define('DEKODE_NINJAFORMS_AZURE_DIR_PATH', plugin_dir_path(__FILE__));


// define('DEKODE_NINJAFORMS_AZURE_CONNECTION_STRING', 'AccountName=devstoreaccount1;AccountKey=Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==;DefaultEndpointsProtocol=http;BlobEndpoint=http://127.0.0.1:10000/devstoreaccount1;');

require_once DEKODE_NINJAFORMS_AZURE_DIR_PATH . '/vendor/autoload.php';

require_once DEKODE_NINJAFORMS_AZURE_DIR_PATH . '/includes/class-bootstrap.php';

if((new Bootstrap())->check()) {
    include_once DEKODE_NINJAFORMS_AZURE_DIR_PATH . '/includes/class-controller.php';
    include_once DEKODE_NINJAFORMS_AZURE_DIR_PATH . '/includes/class-fields-upload.php';
    include_once DEKODE_NINJAFORMS_AZURE_DIR_PATH . '/includes/class-ajax-controllers-uploads.php';
    include_once DEKODE_NINJAFORMS_AZURE_DIR_PATH . '/includes/override.php';
}
