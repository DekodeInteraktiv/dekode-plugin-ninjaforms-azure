<?php

declare( strict_types=1 );

namespace Dekode\NinjaForms\Azure;

use NF_FU_File_Uploads;


add_action(
    'ninja_forms_loaded', function () {
        // Remove all actions which was registered by
        // original NF_FU_AJAX_Controllers_Uploads->init()
        remove_all_actions('wp_ajax_nf_fu_upload');
        remove_all_actions('wp_ajax_nopriv_nf_fu_upload');
        remove_all_actions('wp_ajax_nf_fu_get_new_nonce');
        remove_all_actions('wp_ajax_nopriv_nf_fu_get_new_nonce');
        remove_all_actions('nf_fu_delete_temporary_file');

        $ajax_upload = new Ajax_Controllers_Uploads();
        $ajax_upload->init();


    }
);

add_filter(
    'ninja_forms_register_fields', function ($fields) {
        $fields[ NF_FU_File_Uploads::TYPE ] = new Fields_Upload();

        return $fields;
    }, 1000
);
