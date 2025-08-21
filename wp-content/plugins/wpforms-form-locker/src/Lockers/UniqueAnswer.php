<?php

namespace WPFormsLocker\Lockers;

/**
 * Require unique answer for selected field types.
 *
 * @since 1.0.0
 */
class UniqueAnswer extends Locker {

	/**
	 * Locker type.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $type = 'unique_answer';

	/**
	 * Locker hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		parent::hooks();

		add_action( 'wpforms_frontend_js', [ $this, 'enqueue_frontend_scripts' ] );

		add_filter( 'wpforms_settings_defaults', [ $this, 'register_settings_messages' ] );
		add_filter( 'wpforms_frontend_strings', [ $this, 'register_frontend_messages' ] );
		add_filter( 'wpforms_field_properties', [ $this, 'field_properties' ], 10, 2 );

		add_filter( 'wpforms_process_initial_errors', [ $this, 'submit_form' ], 10, 2 );

		add_action( 'wp_ajax_wpforms_form_locker_unique_answer', [ $this, 'is_unique_ajax' ] );
		add_action( 'wp_ajax_nopriv_wpforms_form_locker_unique_answer', [ $this, 'is_unique_ajax' ] );
	}

	/**
	 * Enqueue frontend scripts.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Added conditional check before the script will be enqueue.
	 *
	 * @param array $forms Forms on the current page.
	 */
	public function enqueue_frontend_scripts( $forms ) {

		$has_unique_answer = false;

		foreach ( $forms as $form ) {
			if ( $this->has_unique_answer( $form ) ) {
				$has_unique_answer = true;

				break;
			}
		}

		if ( ! $has_unique_answer ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-form-locker',
			wpforms_form_locker()->url . "assets/js/wpforms-form-locker{$min}.js",
			[ 'wpforms' ],
			WPFORMS_FORM_LOCKER_VERSION,
			true
		);

		wp_localize_script(
			'wpforms-form-locker',
			'wpforms_form_locker',
			[
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			]
		);
	}

	/**
	 * Whether the provided form has a field, which support unique answer, and it enabled.
	 *
	 * @since 2.0.0
	 *
	 * @param array $form Form data.
	 *
	 * @return bool
	 */
	protected function has_unique_answer( $form ) {

		if ( empty( $form['fields'] ) ) {
			return false;
		}

		foreach ( (array) $form['fields'] as $field ) {

			if (
				! empty( $field['type'] ) &&
				in_array( $field['type'], self::get_unique_answer_field_types(), true ) &&
				! empty( $field['unique_answer'] )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Register validation messages in settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings Array of current form settings.
	 *
	 * @return array
	 */
	public function register_settings_messages( $settings ) {

		$settings['validation']['validation-unique'] = [
			'id'      => 'validation-unique',
			'name'    => esc_html__( 'Unique Answer', 'wpforms-form-locker' ),
			'type'    => 'text',
			'default' => esc_html__( 'The value must be unique.', 'wpforms-form-locker' ),
		];

		return $settings;
	}

	/**
	 * Modify javascript `wpforms_settings` properties on site front end.
	 *
	 * @since 1.0.0
	 *
	 * @param array $strings Array wpforms_setting properties.
	 *
	 * @return array
	 */
	public function register_frontend_messages( $strings ) {

		$strings['val_unique'] = wpforms_setting( 'validation-unique', esc_html__( 'The value must be unique.', 'wpforms-form-locker' ) );

		return $strings;
	}

	/**
	 * Conditionally add properties to an element enabling jQuery Validate.
	 *
	 * @since 1.0.0
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Field settings.
	 *
	 * @return array Field properties.
	 */
	public function field_properties( $properties, $field ) {

		if ( empty( $field['unique_answer'] ) ) {
			return $properties;
		}

		// In case of the `Name` field.
		if ( ! empty( $field['type'] ) && $field['type'] === 'name' ) {
			return $this->name_field_properties( $properties, $field );
		}

		$properties['inputs']['primary']['class'][]             = 'wpforms-novalidate-onkeyup';
		$properties['inputs']['primary']['data']['rule-unique'] = 'true';

		return $properties;
	}

	/**
	 * Conditionally add properties to the Name field elements enabling jQuery Validate.
	 *
	 * @since 2.0.0
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Field settings.
	 *
	 * @return array Field properties.
	 */
	public function name_field_properties( $properties, $field ) {

		if ( empty( $field['unique_answer'] ) ) {
			return $properties;
		}

		if ( empty( $field['type'] ) || $field['type'] !== 'name' ) {
			return $properties;
		}

		$format = ! empty( $field['format'] ) ? $field['format'] : 'simple';

		switch ( $format ) {
			case 'first-last':
				$inputs = [ 'first', 'last' ];
				break;

			case 'first-middle-last':
				$inputs = [ 'first', 'middle', 'last' ];
				break;

			default:
				$inputs = [ 'primary' ];
				break;
		}

		foreach ( $inputs as $input ) {
			$properties['inputs'][ $input ]['class'][]             = 'wpforms-novalidate-onkeyup';
			$properties['inputs'][ $input ]['class'][]             = 'wpforms-validation-group-member';
			$properties['inputs'][ $input ]['data']['rule-unique'] = 'true';
		}

		$properties['container']['class'][] = 'wpforms-field-unique';

		return $properties;
	}

	/**
	 * On form submit actions.
	 *
	 * @since 1.0.0
	 *
	 * @param array $errors    Form submit errors.
	 * @param array $form_data Form information.
	 *
	 * @return array
	 */
	public function submit_form( $errors, $form_data ) {

		$this->set_form_data( $form_data );

		$unique_enabled = $this->get_unique_enabled_field_ids();

		if ( empty( $unique_enabled ) ) {
			return $errors;
		}

		$field_ids = $this->get_non_unique_field_ids( $unique_enabled );

		if ( empty( $field_ids ) ) {
			return $errors;
		}

		foreach ( $field_ids as $field_id ) {
			$errors[ $this->form_data['id'] ][ $field_id ] = wpforms_setting( 'validation-unique', esc_html__( 'The value must be unique.', 'wpforms-form-locker' ) );
		}

		return $errors;
	}

	/**
	 * Validate a value via AJAX call.
	 *
	 * @since 1.0.0
	 */
	public function is_unique_ajax() {

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$form_id  = ! empty( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		$field_id = ! empty( $_POST['field_id'] ) ? wpforms_validate_field_id( sanitize_text_field( wp_unslash( $_POST['field_id'] ) ) ) : 0;
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( empty( $form_id ) ) {
			echo wp_json_encode( true );
			exit();
		}

		$form_data = wpforms()->obj( 'form' )->get(
			$form_id,
			[ 'content_only' => true ]
		);

		if ( empty( $form_data ) ) {
			echo wp_json_encode( true );
			exit();
		}

		$this->set_form_data( $form_data );

		if ( $this->get_non_unique_field_ids( $field_id ) ) {
			echo wp_json_encode( false );
			exit();
		}

		// jQuery Validation requires an answer to be JSON-encoded 'true' or 'false'.
		// Can't use 'wp_send_json_success()' here and above.
		echo wp_json_encode( true );
		exit();
	}

	/**
	 * Get field ids with enabled "Require Unique Answer" setting.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_unique_enabled_field_ids() {

		$field_ids = [];

		foreach ( $this->form_data['fields'] as $field ) {
			if ( empty( $field['unique_answer'] ) ) {
				continue;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( empty( $_POST['wpforms']['fields'][ $field['id'] ] ) ) {
				continue;
			}

			$field_ids[] = $field['id'];
		}

		if ( empty( $field_ids ) ) {
			return [];
		}

		return $field_ids;
	}

	/**
	 * Check a field id(s) for uniqueness.
	 * Return an array on non-unique field ids.
	 *
	 * @since 1.0.0
	 *
	 * @param array|int $field_ids Field id(s) to check for uniqueness.
	 *
	 * @return array
	 */
	public function get_non_unique_field_ids( $field_ids ): array {

		$field_ids = (array) $field_ids;

		if ( empty( $field_ids ) || empty( $this->form_data['id'] ) ) {
			return [];
		}

		$db_fields        = $this->get_fields_from_completed_entries( $field_ids );
		$submitted_fields = $this->get_submitted_fields( $field_ids );

		$non_unique = [];

		$this->compare_field_values( $submitted_fields, $submitted_fields, $non_unique, true );
		$this->compare_field_values( $submitted_fields, $db_fields, $non_unique, false );

		return $non_unique;
	}

	/**
	 * Compare field values.
	 *
	 * @since 2.8.0
	 *
	 * @param array $fields         Fields.
	 * @param array $compare_fields Fields to compare.
	 * @param array $non_unique     Non-unique field ids.
	 * @param bool  $skip_self      Skip comparing with yourself.
	 */
	private function compare_field_values( array $fields, array $compare_fields, array &$non_unique, bool $skip_self ) {

		$fields         = $this->add_original_ids( $fields );
		$compare_fields = $this->add_original_ids( $compare_fields );

		foreach ( $fields as $field ) {
			foreach ( $compare_fields as $compare_field ) {
				if ( $skip_self && ( $field['field_id'] === $compare_field['field_id'] ) ) {
					continue;
				}

				if (
					( $field['original_id'] === $compare_field['original_id'] ) &&
					( strtolower( $field['value'] ) === strtolower( sanitize_text_field( $compare_field['value'] ) ) )
				) {
					$non_unique[] = $field['field_id'];
				}
			}
		}

		$non_unique = array_unique( $non_unique );
	}

	/**
	 * Get submitted fields.
	 *
	 * @since 2.8.0
	 *
	 * @param array $field_ids Field ids.
	 *
	 * @return array
	 */
	private function get_submitted_fields( array $field_ids ): array {

		$submitted_fields = [];

		foreach ( $field_ids as $field_id ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( empty( $_POST['wpforms']['fields'][ $field_id ] ) ) {
				continue;
			}

			$form_field_value_raw = wp_unslash( $_POST['wpforms']['fields'][ $field_id ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			$form_field_value = is_array( $form_field_value_raw )
				? sanitize_text_field( implode( ' ', array_filter( $form_field_value_raw ) ) )
				: sanitize_textarea_field( $form_field_value_raw );

			$submitted_fields[] = [
				'field_id' => $field_id,
				'value'    => $form_field_value,
			];
		}

		return $submitted_fields;
	}

	/**
	 * Get an array of field types that support Unique Answer Locker.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_unique_answer_field_types() {

		return apply_filters(
			'wpforms_form_locker_fields_get_unique_answer_field_types',
			[ 'text', 'name', 'email', 'url', 'password', 'phone' ]
		);
	}

	/**
	 * Check if AJAX is a 'field_new' call for Unique Answer enabled field.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_unique_answer_enabled_new_field_ajax() {

		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			return false;
		}

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'wpforms-builder' ) ) {
			return false;
		}

		if ( empty( $_POST['action'] ) ) {
			return false;
		}

		$prefix = 'wpforms_new_field_';
		$action = sanitize_text_field( wp_unslash( $_POST['action'] ) );

		if ( strpos( $action, $prefix ) !== 0 ) {
			return false;
		}

		$allowed_actions = preg_filter( '/^/', $prefix, self::get_unique_answer_field_types() );

		if ( ! in_array( $action, $allowed_actions, true ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Filter fields to only those which belongs to entries which has status 'completed'.
	 *
	 * @since 2.7.0
	 *
	 * @param array|int $field_ids Field id(s) to check for uniqueness.
	 *
	 * @return array
	 */
	private function get_fields_from_completed_entries( $field_ids ): array {

		$args                 = [
			'number'   => -1,
			'form_id'  => $this->form_data['id'],
			'field_id' => $field_ids,
			'orderby'  => 'field_id',
		];
		$entry_fields_handler = wpforms()->obj( 'entry_fields' );
		$entry_handler        = wpforms()->obj( 'entry' );

		if ( ! $entry_fields_handler || ! $entry_handler ) {
			return [];
		}

		$fields  = (array) $entry_fields_handler->get_fields( $args );
		$entries = (array) $entry_handler->get_entries( $args );

		// Return only those $fields which have the same entry_id as $entries have
		// and $entries status is within statuses considered as completed.
		$entry_lookup = [];

		foreach ( $entries as $entry ) {
			$entry_lookup[ $entry->entry_id ] = $entry;
		}

		foreach ( $fields as $field_id => $field ) {
			if ( isset( $entry_lookup[ $field->entry_id ] ) && ! $this->check_entry_status( $entry_lookup[ $field->entry_id ]->status ) ) {
				unset( $fields[ $field_id ] );
			}
		}

		return $fields;
	}

	/**
	 * Check the entry status.
	 *
	 * @since 2.7.0
	 *
	 * @param string $status Entry status.
	 *
	 * @return bool
	 */
	private function check_entry_status( string $status ): bool {

		// Completed status is actually a status equal to empty string.
		// The 'completed' and 'pending' status should be also included, they come from PayPal addon.
		return wpforms_is_empty_string( $status ) || in_array( $status, $this->get_allowed_entry_statuses(), true );
	}

	/**
	 * Get allowed entry statuses.
	 *
	 * @since 2.7.0
	 *
	 * @return array
	 */
	private function get_allowed_entry_statuses(): array {

		return (
			/**
			 * Filter allowed entry statuses for unique answers comparison.
			 *
			 * By default, Form Locker Unique Answers checker will check answers being
			 * submitted only with the already submitted answers (saved in entries with statuses 'completed' and 'pending').
			 * This filter allows to add more statuses to the list of allowed statuses.
			 *
			 * @since 2.7.0
			 *
			 * @param array $statuses Array of allowed statuses.
			 */
		(array) apply_filters(
			'wpforms_locker_lockers_unique_answer_get_allowed_statuses',
			[ 'completed', 'pending' ]
		)
		);
	}

	/**
	 * Add original ids to the field array.
	 *
	 * @since 2.8.0
	 *
	 * @param array $fields Fields.
	 *
	 * @return array
	 */
	private function add_original_ids( array $fields ): array {

		foreach ( $fields as &$field ) {
			$field                = (array) $field;
			$field['original_id'] = wpforms_get_repeater_field_ids( $field['field_id'] )['original_id'];
		}

		return $fields;
	}
}
