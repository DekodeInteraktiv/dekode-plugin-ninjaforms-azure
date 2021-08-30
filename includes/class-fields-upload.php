<?php
/**
 * It processes form submit action and moves file to final place
 *
 * @package dekode
 */

declare( strict_types=1 );

namespace Dekode\NinjaForms\Azure;

/**
 * It processes form submit action and moves file to final place
 *
 * @package Dekode\NinjaForms\Azure
 */
class Fields_Upload extends \NF_FU_Fields_Upload {


	/**
	 * Save the temp file to permanent location
	 *
	 * @param mixed $field Field meta.
	 * @param mixed $data Common settings.
	 *
	 * @return mixed
	 */
	public function process( $field, $data ) { // phpcs:ignore

		/*
		* If we don't have any files set or we are saving progress, bail early.
		*/
		if ( ! isset( $field['files'] ) || empty( $field['files'] ) || ( isset( $data['extra']['saveProgress'] ) && ! empty( $data['extra']['saveProgress'] ) ) ) {
			return $data;
		}

		$submission_data = [];

		$user_id   = $this->get_user_id();
		$base_dir  = NF_File_Uploads()->controllers->uploads->get_path( '' );
		$base_url  = NF_File_Uploads()->controllers->uploads->get_url( '' );
		$base_dir .= $data['form_id'] . '/';
		$base_url .= $data['form_id'] . '/';

		NF_File_Uploads()->controllers->custom_paths->set_data( 'formtitle', $data['settings']['title'] );

		$controller = Controller::$instance;

		// Loop through all files.
		foreach ( $field['files'] as $file_key => $file ) {

			$tmp_blob_name = $file['tmp_name'];

			if ( ! $controller->exists_file( $tmp_blob_name ) ) {
				$data['errors']['fields'][ $field['id'] ] = __( 'Temp file does not exist', 'ninja-forms-uploads' );

				return $data;
			}

			// Remove any path from the filename as a security measure.
			$original_filename = basename( $file['name'] );

			// Remove the extension from the file name.
			$file_parts = explode( '.', $original_filename );
			$ext        = array_pop( $file_parts );

			// Check for blacklisted file types.
			if ( \NF_FU_AJAX_Controllers_Uploads::blacklisted( \NF_FU_AJAX_Controllers_Uploads::get_extension_blacklist(), str_replace( '_', '', trim( $ext ) ) ) ) {
				$data['errors']['fields'][ $field['id'] ] = __( 'File extension not allowed', 'ninja-forms-uploads' );

				return $data;
			}

			if ( ! \NF_FU_AJAX_Controllers_Uploads::is_allowed_type( $original_filename ) ) {
				$data['errors']['fields'][ $field['id'] ] = __( 'File extension not allowed', 'ninja-forms-uploads' );

				return $data;
			}

			$original_filename_without_ext = implode( '.', $file_parts );

			// Set the file uploads mergetag values.
			Ninja_Forms()->merge_tags['file_uploads']->set_filename( $original_filename_without_ext );
			Ninja_Forms()->merge_tags['file_uploads']->set_extension( $ext );
			// Set the filename custom shortcode.

			NF_File_Uploads()->controllers->custom_paths->set_data( 'filename', $original_filename_without_ext );
			$file_name = $original_filename;

			$new_blob_name = $controller->generate_name( $original_filename, join( '/', [ \gmdate( 'Y' ), \gmdate( 'm' ) ] ) );

			// Move to permanent location.
			try {
				$controller->rename_file( $tmp_blob_name, $new_blob_name );
			} catch ( \Exception $e ) {
				$controller->error_log( $e->getMessage() );
				$data['errors']['fields'][ $field['id'] ] = __( 'File upload error', 'ninja-forms-uploads' );

				return $data;
			}

			$file_url = $controller->get_blob_url( $new_blob_name );

			// Add to FU table.
			$file_data = [
				'user_file_name'  => $original_filename,
				'file_name'       => $file_name,
				'file_path'       => $new_blob_name,
				'file_url'        => $file_url,
				'upload_location' => 'azure',
				'complete'        => 0,
			];

			$upload_id = NF_File_Uploads()->model->insert( $user_id, $data['form_id'], $field['id'], $file_data );

			$file_data['upload_id'] = $upload_id;

			NF_File_Uploads()->model->update( $upload_id, $file_data );

			// Store FU data in the processing object.
			$field['files'][ $file_key ]['data'] = $file_data;

			$submission_data[ $upload_id ] = $file_url;
		}

		foreach ( $data['fields'] as $key => $data_field ) {
			if ( $data_field['id'] !== $field['id'] ) {
				continue;
			}

			// Set the field value to the array of file upload data.
			$data['fields'][ $key ]['value'] = $submission_data;
			// Persist the data for each file.
			$data['fields'][ $key ]['files'] = $field['files'];

			break;
		}

		return $data;
	}
}
