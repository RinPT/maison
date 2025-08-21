<?php
/**
 * Plugin Name:       WPForms Form Locker
 * Plugin URI:        https://wpforms.com
 * Description:       Create Form Locker with WPForms.
 * Requires at least: 5.5
 * Requires PHP:      7.1
 * Author:            WPForms
 * Author URI:        https://wpforms.com
 * Version:           2.9.0
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpforms-form-locker
 * Domain Path:       /languages
 *
 * WPForms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WPForms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WPForms. If not, see <https://www.gnu.org/licenses/>.
 */

use WPFormsLocker\Loader;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version.
 *
 * @since 2.5.0
 */
const WPFORMS_FORM_LOCKER_VERSION = '2.9.0';

/**
 * Plugin file.
 *
 * @since 2.5.0
 */
const WPFORMS_FORM_LOCKER_FILE = __FILE__;

/**
 * Plugin path.
 *
 * @since 2.5.0
 */
define( 'WPFORMS_FORM_LOCKER_PATH', plugin_dir_path( WPFORMS_FORM_LOCKER_FILE ) );

/**
 * Check addon requirements.
 *
 * @since 2.0.0
 * @since 2.5.0 Renamed from wpforms_form_locker_required to wpforms_form_locker_load.
 * @since 2.5.0 Uses requirements feature.
 */
function wpforms_form_locker_load() {

	// Check requirements.
	$requirements = [
		'file'    => WPFORMS_FORM_LOCKER_FILE,
		'wpforms' => '1.9.1',
	];

	if ( ! function_exists( 'wpforms_requirements' ) || ! wpforms_requirements( $requirements ) ) {
		return;
	}

	wpforms_form_locker();
}

add_action( 'wpforms_loaded', 'wpforms_form_locker_load' );

/**
 * Get the instance of the addon main class.
 *
 * @since 1.0.0
 *
 * @return Loader
 */
function wpforms_form_locker() {

	require_once WPFORMS_FORM_LOCKER_PATH . 'vendor/autoload.php';

	return Loader::get_instance();
}
