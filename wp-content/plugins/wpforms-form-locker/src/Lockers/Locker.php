<?php

namespace WPFormsLocker\Lockers;

/**
 * Locker class.
 *
 * @since 2.3.0
 */
abstract class Locker {

	/**
	 * Current form information.
	 *
	 * @since 2.3.0
	 *
	 * @var array $form_data Form data.
	 */
	public $form_data;

	/**
	 * Locker type.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 * Init.
	 *
	 * @since 2.3.0
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Locker hooks.
	 *
	 * @since 2.3.0
	 */
	public function hooks() {

		// Enqueue front-end styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_styles' ] );
		// Enqueue gutenberg styles.
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_frontend_styles' ] );
	}

	/**
	 * Enqueue front-end styles.
	 *
	 * @since 2.8.0
	 */
	public function enqueue_frontend_styles() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-form-locker-frontend',
			wpforms_form_locker()->url . "assets/css/frontend{$min}.css",
			[],
			'2.8.0'
		);
	}

	/**
	 * Set current form information for internal use.
	 *
	 * @since 2.3.0
	 *
	 * @param array $form_data Form information.
	 */
	protected function set_form_data( $form_data ) {

		$this->form_data = $form_data;
	}

	/**
	 * Get form classes.
	 *
	 * @since 2.3.0
	 *
	 * @return array
	 */
	private function get_form_classes() {

		/**
		 * Modify locker container classes.
		 *
		 * @since 2.3.0
		 *
		 * @param array $classes   Array of classes.
		 * @param array $form_data Form data.
		 */
		return (array) apply_filters( 'wpforms_locker_lockers_locker_get_form_classes', [ sprintf( 'wpforms-%s-locked', $this->type ) ], $this->form_data );
	}

	/**
	 * Print opening tag for the form wrapper.
	 *
	 * @since 2.3.0
	 */
	protected function print_form_wrapper_open() {

		printf(
			'<div class="%s" id="wpforms-locked-%d">%s',
			wpforms_sanitize_classes( $this->get_form_classes(), true ),
			absint( $this->form_data['id'] ),
			$this->is_gb_editor() ? '<fieldset disabled>' : ''
		);
	}

	/**
	 * Print ending tag for the form wrapper.
	 *
	 * @since 2.3.0
	 */
	protected function print_form_wrapper_close() {

		if ( $this->is_gb_editor() ) {
			echo '</fieldset>';
		}
		echo '</div>';
	}

	/**
	 * Checking if is Gutenberg REST API call.
	 *
	 * @since 2.8.0
	 *
	 * @return bool True if is Gutenberg REST API call.
	 */
	private function is_gb_editor() {

		return defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_REQUEST['context'] ) && $_REQUEST['context'] === 'edit'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
}
