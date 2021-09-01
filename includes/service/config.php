<?php
/**
 * The file defines settings fields
 *
 * @package dekode
 */

declare( strict_types=1 );

if ( ! function_exists( 'dekode_ninja_forms_uploads_azure_setting_wrapper' ) ) {
	/**
	 * Function adds hint to description about defined constant.
	 *
	 * @param array $config Settings config.
	 * @return array
	 */
	function dekode_ninja_forms_uploads_azure_setting_wrapper( array $config ): array {
		$setting_name = $config['id'];

		if ( $setting_name && defined( $setting_name ) ) {
			if ( ! isset( $config['desc'] ) ) {
				$config['desc'] = '';
			} else {
				$config['desc'] .= '<br/>';
			}

			$config['desc'] .= '<strong>'
				/* translators: %s: constant name */
				. sprintf( __( 'It\'s defined in wp-config.php. The defined value is \'%s\'. Fill the field to override the value.', 'dekode-ninjaforms-azure' ), constant( $setting_name ) )
				. '</strong>';
		}
		return $config;
	}
}

return apply_filters( 'ninja_forms_uploads_settings_azure', [
	'MICROSOFT_AZURE_ACCOUNT_NAME' => dekode_ninja_forms_uploads_azure_setting_wrapper([
		'id'      => 'MICROSOFT_AZURE_ACCOUNT_NAME',
		'type'    => 'textbox',
		'default' => '',
		'label'   => __( 'Account Name', 'dekode-ninjaforms-azure' ),
	]),
	'MICROSOFT_AZURE_ACCOUNT_KEY'  => dekode_ninja_forms_uploads_azure_setting_wrapper([
		'id'               => 'MICROSOFT_AZURE_ACCOUNT_KEY',
		'type'             => 'textbox',
		'default'          => '',
		'disabled'         => true,
		'display_function' => 'dddd',
		'label'            => __( 'Account Key', 'dekode-ninjaforms-azure' ),
	]),
	'MICROSOFT_AZURE_CONTAINER'    => dekode_ninja_forms_uploads_azure_setting_wrapper([
		'id'    => 'MICROSOFT_AZURE_CONTAINER',
		'type'  => 'textbox',
		'label' => __( 'Blob Service Endpoint', 'dekode-ninjaforms-azure' ),
		'desc'  => __( 'Service endpoint values in your connection strings must be well-formed URIs, including https:// (recommended) or http://.', 'dekode-ninjaforms-azure' ),
	]),
] );
