<?php
/**
 * Ninja Forms Uploads Service
 *
 * @package dekode
 */

declare( strict_types=1 );

use Dekode\NinjaForms\Azure\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NF_FU_External_S3_Service - the name structure is required by Ninja Forms Upload plugin
 */
class NF_FU_External_Services_Azure_Service extends \NF_FU_External_Abstracts_Service {
	/**
	 * Name of service for Ninja Forms Uploads
	 *
	 * @var string
	 */
	public $name = 'Microsoft Azure';

	/**
	 * Holds Azure controller
	 *
	 * @var Controller
	 */
	protected $controller = null;

	/**
	 * Maximum file size in bytes to send to service in a single request
	 *
	 * @var int
	 */
	protected $max_single_upload_file_size = 4194304000;

	/**
	 * Returns single registered controller
	 *
	 * @return Controller
	 */
	protected function get_controller(): Controller {
		if ( null === $this->controller ) {
			$this->controller = new Controller( $this );
		}
		return $this->controller;
	}

	/**
	 * Fetch a settings values
	 *
	 * This will allow for default values to be declared with constants, with overrides declared
	 * on a per-site basis if needed via the settings screen.
	 *
	 * @return array
	 */
	public function load_settings() : array {
		$settings = parent::load_settings();

		foreach ( $settings as $key => $value ) {
			if ( defined( $key ) ) {
				$settings[ $key ] = constant( $key );
			}
		}

		$this->settings = $settings;

		return $settings;
	}


	/**
	 * Is the service connected?
	 *
	 * @param null|array $settings Settings from Ninja Forms Settings page.
	 *
	 * @return bool
	 */
	public function is_connected( $settings = null ): bool { // phpcs:ignore
		if ( is_null( $settings ) ) {
			$settings = $this->load_settings();
		}

		foreach ( $settings as $key => $value ) {
			if ( ! is_array( $value ) && '' === trim( $value ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get path on Azure to upload to
	 *
	 * @return string
	 */
	protected function get_path_setting(): string {
		return 'MICROSOFT_AZURE_CONTAINER';
	}

	/**
	 * Upload the file to Azure
	 *
	 * @param array $data Meta data of file.
	 *
	 * @return array|bool
	 */
	public function upload_file( $data ) { // phpcs:ignore
		try {
			$result = $this->get_controller()->upload_file( $data['file_name'], $data['file_path'] );
		} catch ( \Exception $e ) {
			$this->get_controller()->error_log( $e->getMessage() );
			return false;
		}

		if ( false === $result || ! isset( $result['blobName'] ) ) {
			return false;
		}

		$data['blobName'] = $result['blobName'];

		return $data;
	}

	/**
	 * Get the Amazon S3 URL using bucket and region for the file, falling
	 * back to the settings bucket and region
	 *
	 * @param string $filename Uploaded file name.
	 * @param string $path Full path to file.
	 * @param array  $data Meta data of uploaded file.
	 *
	 * @return string
	 */
	public function get_url( $filename, $path = '', $data = [] ) { // phpcs:ignore
		return $this->get_controller()->get_blob_url( $data['blobName'] );
	}

	/**
	 * Check if the file should be uploaded in a single process or multipart upload to the service.
	 * 4.194304Gb Maximum block size
	 * https://docs.microsoft.com/en-us/rest/api/storageservices/understanding-block-blobs--append-blobs--and-page-blobs
	 *
	 * @param bool   $should_bg_upload Is enable background upload for file.
	 * @param string $file Uploaded file name.
	 * @param array  $field Field data.
	 * @param int    $form_id Id of form.
	 *
	 * @return bool
	 */
	protected function should_background_upload( $should_bg_upload, $file, $field, $form_id ) { // phpcs:ignore
		return false;
	}
}
