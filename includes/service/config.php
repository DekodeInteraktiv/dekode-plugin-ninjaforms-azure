<?php
/**
 * The file defines settings fields
 *
 * @package dekode
 */

declare( strict_types=1 );

return apply_filters( 'ninja_forms_uploads_settings_azure', [
	'MICROSOFT_AZURE_ACCOUNT_NAME' => [
		'id'      => 'MICROSOFT_AZURE_ACCOUNT_NAME',
		'type'    => 'textbox',
		'default' => '',
		'label'   => __( 'Account Name', 'dekode-ninjaforms-azure' ),
	],
	'MICROSOFT_AZURE_ACCOUNT_KEY'  => [
		'id'      => 'MICROSOFT_AZURE_ACCOUNT_KEY',
		'type'    => 'textbox',
		'default' => '',
		'label'   => __( 'Account Key', 'dekode-ninjaforms-azure' ),
	],
	'MICROSOFT_AZURE_CONTAINER'    => [
		'id'    => 'MICROSOFT_AZURE_CONTAINER',
		'type'  => 'textbox',
		'label' => __( 'Blob Service Endpoint', 'dekode-ninjaforms-azure' ),
		'desc'  => __( 'Service endpoint values in your connection strings must be well-formed URIs, including https:// (recommended) or http://.', 'dekode-ninjaforms-azure' ),
	],
] );
