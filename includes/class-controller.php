<?php
/**
 * Controller to process all requests to work with Azure Storage
 *
 * @package dekode
 */

declare( strict_types=1 );

namespace Dekode\NinjaForms\Azure;

use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;
use NF_FU_External_Services_Azure_Service;

/**
 * Controller to process all requests to work with Azure Storage
 *
 * @package Dekode\NinjaForms\Azure
 */
class Controller {
	/**
	 * Stores service instance of Ninja Forms Upload class
	 *
	 * @var \NF_FU_External_Services_Azure_Service
	 */
	private $service;

	/**
	 * Creates instance of controller
	 *
	 * @param \NF_FU_External_Services_Azure_Service $service Ninja Forms Upload service class.
	 * @return void
	 */
	public function __construct( \NF_FU_External_Services_Azure_Service $service ) {
		$this->service = $service;
		$this->service->load_settings();
	}
	/**
	 * Official library API class instance
	 *
	 * @var false
	 */
	private static $blob_client = false;

	/**
	 * Logs messages to file for debugging
	 *
	 * @param string $message Text message to log.
	 * @return void
	 */
	public function error_log( string $message ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			\error_log( $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}

	/**
	 * Generates container name using site url,
	 * it leaves only allowed characters.
	 *
	 * @return string
	 */
	protected function get_container_name(): string {
		$parse  = \wp_parse_url( get_site_url() );
		$domain = $parse['host'];

		// Naming conventions, only letters
		// Container names must start or end with a letter or number, and can contain only letters, numbers, and the dash (-) character.
		// https://docs.microsoft.com/en-us/rest/api/storageservices/naming-and-referencing-containers--blobs--and-metadata.
		$container_name = strtolower( preg_replace( '/[^a-zA-Z0-9-]/', '-', $domain ) );

		return $container_name;
	}

	/**
	 * Builds connection string.
	 *
	 * @return string
	 */
	public function build_connection_string(): string {
		$settings = $this->service->settings;

		$endpoint            = isset( $settings['azure_blob_service_endpoint'] ) ? $settings['azure_blob_service_endpoint'] : '';
		$connection_string   = [];
		$connection_string[] = 'AccountName=' . $settings['azure_account_name'];
		$connection_string[] = 'AccountKey=' . $settings['azure_account_key'];
		$connection_string[] = 'DefaultEndpointsProtocol=' . strpos( $endpoint, 'https://' ) ? 'https' : 'http';
		$connection_string[] = 'BlobEndpoint=' . $endpoint;

		return join( ';', $connection_string );
	}

	// /**
	// * Checks connection to Azure Blob Storage.
	// *
	// * @return bool
	// */
	// public function check_connection(): bool {
	// return false;
	// }


	/**
	 * Creates the client proxy to Azure Blob Storage.
	 *
	 * @return BlobRestProxy
	 */
	protected function get_blob_client(): BlobRestProxy {
		if ( false === self::$blob_client ) {
			$blob_client = BlobRestProxy::createBlobService( DEKODE_NINJAFORMS_AZURE_CONNECTION_STRING );

			$container_name = $this->get_container_name();

			try {
				$blob_client->getContainerProperties( $container_name );
			} catch ( ServiceException $e ) {
				$create_container_options = new CreateContainerOptions();
				$create_container_options->setPublicAccess( PublicAccessType::BLOBS_ONLY );

				$blob_client->createContainer( $container_name, $create_container_options );
			}

			self::$blob_client = $blob_client;
		}

		return self::$blob_client;
	}

	/**
	 * Returns full url to file.
	 *
	 * @param string $blob_name Name of blob in Azure Blob Storage.
	 * @return string
	 */
	public function get_blob_url( string $blob_name ): string {
		$blob_client    = $this->get_blob_client();
		$container_name = $this->get_container_name();

		$source_blob_path = $blob_client->getBlobUrl(
			$container_name,
			$blob_name
		);
		return $source_blob_path;
	}

	/**
	 * Checks that blog exists in Azure Blob Storage.
	 *
	 * @param string $blob_name Name of blob in Azure Blob Storage.
	 * @return bool
	 */
	public function exists_file( string $blob_name ): bool {
		$blob_client    = $this->get_blob_client();
		$container_name = $this->get_container_name();

		$blob = false;

		try {
			$blob = $blob_client->getBlob( $container_name, $blob_name );
		} catch ( ServiceException $e ) {} // phpcs:ignore

		return $blob ? true : false;
	}

	/**
	 * Rename file in Azure Blob Storage.
	 *
	 * @param string $old_name Current name of blob in Azure Blob Storage.
	 * @param string $new_name New name of blob in Azure Blob Storage.
	 * @return void
	 */
	public function rename_file( string $old_name, string $new_name ): void {
		$blob_client    = $this->get_blob_client();
		$container_name = $this->get_container_name();

		$blob_client->copyBlob( $container_name, $new_name, $container_name, $old_name );
		$blob_client->deleteBlob( $container_name, $old_name );
	}

	/**
	 * Removes file in Azure Blob Storage.
	 *
	 * @param string $blob_name Name of blob in Azure Blob Storage.
	 * @return void
	 */
	public function delete_file( string $blob_name ): void {
		$blob_client    = $this->get_blob_client();
		$container_name = $this->get_container_name();
		$blob_client->deleteBlob( $container_name, $blob_name );
	}

	/**
	 * Generates name which includes date, blog_id and site name.
	 *
	 * @param string $file_name File name.
	 * @param string $folder Folder name.
	 * @return string
	 */
	public function generate_name( string $file_name, string $folder = 'temp' ): string {
		$container_name = $this->get_container_name();
		$blog_id        = get_current_blog_id();

		// Sanitize the filename for encoding.
		$file_name = sanitize_file_name( basename( $file_name ) );

		$file_name = wp_hash( $container_name . $file_name . wp_rand( 1, 10000 ) ) . '-' . $file_name;

		$file_name_parts = [ $blog_id, $folder, $file_name ];
		$blob_name       = join( '/', $file_name_parts );

		return $blob_name;
	}

	/**
	 * Process and upload a file to Azure Storage.
	 *
	 * @param string $file_name File name to upload.
	 * @param string $source_file_name Content file name to upload.
	 * @return array
	 * @throws \Exception Throws if it's not saved.
	 */
	public function upload_file( string $file_name, string $source_file_name ): array {
		\WP_Filesystem();
		global $wp_filesystem;
		$content = $wp_filesystem->get_contents( $source_file_name );

		$blob_client    = $this->get_blob_client();
		$container_name = $this->get_container_name();

		$new_blob_name = $this->generate_name( $file_name, join( '/', [ \gmdate( 'Y' ), \gmdate( 'm' ) ] ) );

		$file_type    = wp_check_filetype( $file_name );
		$content_type = 'plain/text';

		if ( $file_type && isset( $file_type['type'] ) && $file_type['type'] ) {
			$content_type = $file_type['type'];
		}

		$blob_options = new CreateBlockBlobOptions();
		$blob_options->setContentType( $content_type );
		$blob = $blob_client->createBlockBlob( $container_name, $new_blob_name, $content, $blob_options );

		if ( ! $blob ) {
			throw new \Exception( 'File not saved' );
		}

		$blob_url = $this->get_blob_url( $new_blob_name );

		return [
			'url'      => $blob_url,
			'blobName' => $new_blob_name,
		];
	}
}
