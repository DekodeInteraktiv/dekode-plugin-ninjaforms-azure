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

/**
 * Disables saving to uploads directory.
 *
 * @param array $fields List of fields.
 * @param int   $form_id Form ID.
 * @return array
 */
function dekode_ninja_forms_get_fields( array $fields, int $form_id ): array {
	// Only enact total overrides if the appropriate constant is set.
	if ( ! defined( 'MICROSOFT_AZURE_FORCE_EXTERNAL_UPLOAD' ) || ! MICROSOFT_AZURE_FORCE_EXTERNAL_UPLOAD ) {
		return $fields;
	}

	foreach ( $fields as $field ) {
		if ( 'file_upload' === $field->get_setting( 'type' ) ) {

			$field->update_setting( 'save_to_server', 0 );
			$field->update_setting( 'media_library', 0 );
		}
	}

	return $fields;
}

add_filter( 'ninja_forms_get_fields', __NAMESPACE__ . '\\dekode_ninja_forms_get_fields', 1, 2 );

/**
 * Adds virtual action for all upload fields.
 *
 * @param array $actions List of actions.
 * @param array $form_cache Form configuration.
 * @param array $form_data Form data.
 * @return array
 */
function dekode_ninja_forms_submission_actions( array $actions, array $form_cache, array $form_data ):array {
	// Only enact total overrides if the appropriate constant is set.
	if ( ! defined( 'MICROSOFT_AZURE_FORCE_EXTERNAL_UPLOAD' ) || ! MICROSOFT_AZURE_FORCE_EXTERNAL_UPLOAD ) {
		return $actions;
	}

	$fields = $form_cache['fields'];

	$settings = [
		'title'              => null,
		'key'                => null,
		'type'               => 'file-upload-external',
		'active'             => '1',
		'fields-save-toggle' => 'save_all',
		'drawerDisabled'     => '',
	];

	foreach ( $fields as $field ) {
		if ( 'file_upload' === $field['settings']['type'] ) {
			$settings[ 'field_list_azure-' . $field['settings']['key'] ] = 1;
		}
	}

	$actions[] = [
		'id'       => 1000000,
		'settings' => $settings,
	];

	return $actions;
}

add_filter( 'ninja_forms_submission_actions', __NAMESPACE__ . '\\dekode_ninja_forms_submission_actions', 1, 3 );

