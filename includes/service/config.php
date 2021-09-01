<?php
/**
 * The file defines settings fields
 *
 * @package dekode
 */

declare( strict_types=1 );

return apply_filters( 'ninja_forms_uploads_settings_azure', [
	'MICROSOFT_AZURE_ACCOUNT_NAME' => [
		'id'      => 'azure_account_name',
		'type'    => 'textbox',
		'default' => '',
		'label'   => __( 'Account Name', 'dekode-ninjaforms-azure' ),
	],
	'MICROSOFT_AZURE_ACCOUNT_KEY'  => [
		'id'      => 'azure_account_key',
		'type'    => 'textbox',
		'default' => '',
		'label'   => __( 'Account Key', 'dekode-ninjaforms-azure' ),
	],
	'MICROSOFT_AZURE_CONTAINER'    => [
		'id'    => 'azure_blob_service_endpoint',
		'type'  => 'textbox',
		'label' => __( 'Blob Service Endpoint', 'dekode-ninjaforms-azure' ),
		'desc'  => __( 'Service endpoint values in your connection strings must be well-formed URIs, including https:// (recommended) or http://.', 'dekode-ninjaforms-azure' ),
	],
] );
