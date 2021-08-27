<?php

declare( strict_types=1 );

namespace Dekode\NinjaForms\Azure;

class Bootstrap
{
    public function check()
    {
        if(!defined('DEKODE_NINJAFORMS_AZURE_CONNECTION_STRING')) {
            add_action(
                'admin_notices', function () {
                    ?>
                <div class="notice notice-error">
                    <p>
                    <?php
                    echo wp_kses(
                        __('Please DEKODE_NINJAFORMS_AZURE_CONNECTION_STRING constant. Plugin is not loaded.', 'dekode-ninjaforms-azure'),
                        [
                                'strong' => [],
                        ]
                    );
                    ?>
                    </p>
                </div>
                    <?php
                }
            );
            return false;
        }

        return true;
    }
}
