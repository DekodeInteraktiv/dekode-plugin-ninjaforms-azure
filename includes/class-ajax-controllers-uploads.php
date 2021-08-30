<?php

declare( strict_types=1 );

namespace Dekode\NinjaForms\Azure;

/**
 *
 * @package Dekode\NinjaForms\Azure
 */
class Ajax_Controllers_Uploads extends \NF_FU_AJAX_Controllers_Uploads
{
    protected function _process()
    {
		\WP_Filesystem();
		global $wp_filesystem;

        foreach ( $this->_data['files'] as $key => $file ) {

            $file_name = $this->get_filename_from_chunk();
            if ($file_name ) {
                $file['name'] = $file_name;
            }

            $file_size = $this->get_file_size_from_chunk();
            if ($file_size ) {
                $file['size'] = $file_size;
            }

            if (false === $this->_validate($file) ) {
                unset($this->_data['files'][ $key ]);
                @unlink($file['tmp_name']);
                continue;
            }


            try {
				$content = $wp_filesystem->get_contents($file['tmp_name']);
                $result = Controller::$instance->upload_file($file['name'], $content);

            } catch(\Exception $e) {
                Controller::$instance->error_log($e->getMessage());
                unset($this->_data['files'][ $key ]);
                $this->_errors[] = __('Unable to move uploaded temp file', 'ninja-forms-uploads');

                continue;
            }

            // Schedule a clean up of the file if the form doesn't get submitted
            wp_schedule_single_event(apply_filters('ninja_forms_uploads_temp_file_delete_time', time() + HOUR_IN_SECONDS), 'nf_fu_delete_temporary_file', array( $result['tempBlobName'] ));

            $this->_data['files'][ $key ]['tmp_name'] = $result['tempBlobName'];
            $this->_data['files'][ $key ]['new_tmp_key'] = $result['newBlobName'];
            $this->_data['files'][ $key ]['url'] = $result['url'];
        }
    }

    /**
     * Delete temp blob
     *
     * @param $tempBlobName
     */
    public function delete_temporary_file( $tempBlobName )
    {
        Controller::$instance->delete_file($tempBlobName);
    }
}
