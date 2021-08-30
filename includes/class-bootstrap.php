<?php
/**
 * Bootstrap class checks that all requeired settings defined and contains methods to start plugin features.
 *
 * @package dekode
 */

declare( strict_types=1 );

namespace Dekode\NinjaForms\Azure;

/**
 * Bootstrap class checks that all requeired settings defined and
 *
 * @package Dekode\NinjaForms\Azure
 */
class Bootstrap {
	/**
	 * Checks all required variables to allow loading
	 *
	 * @return bool
	 */
	public function check(): bool {
		if ( ! defined( 'DEKODE_NINJAFORMS_AZURE_CONNECTION_STRING' ) ) {
			add_action(
				'admin_notices', function () {
					?>
				<div class="notice notice-error">
					<p>
					<?php
					echo wp_kses(
						__( 'Please DEKODE_NINJAFORMS_AZURE_CONNECTION_STRING constant. Plugin is not loaded.', 'dekode-ninjaforms-azure' ),
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
