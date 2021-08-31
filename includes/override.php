<?php
/**
 * Loads hooks which connects plugin
 *
 * @package dekode
 */

declare( strict_types=1 );

namespace Dekode\NinjaForms\Azure;

add_filter( 'ninja_forms_uploads_external_service', __NAMESPACE__ . '\dekode_ninja_forms_uploads_external_service_azure', 1, 1 );

/**
 * Registers Azure service
 *
 * @param array $services List of already registered services.
 * @return array
 */
function dekode_ninja_forms_uploads_external_service_azure( array $services ): array {
	$services[ DEKODE_NINJAFORMS_AZURE_DIR_PATH . '/includes/service/class-nf-fu-external-services-azure-service.php' ] = 'azure';
	return $services;
}
