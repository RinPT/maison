/* global wpforms_admin_builder_form_locker, wpforms_builder */

'use strict';

/**
 * WPForms Builder Form Locker function.
 *
 * @since 1.0.0
 */
const WPFormsBuilderFormLocker = window.WPFormsBuilderFormLocker || ( function( document, window, $ ) {

	/**
	 * Detepair DefaultTimeDelta parameter (minutes).
	 *
	 * @since 1.2.2
	 *
	 * @type {number}
	 */
	const datePairDefaultTimeDelta = 30;

	/**
	 * Elements.
	 *
	 * @since 1.2.2
	 *
	 * @type {object}
	 */
	const el = {
		scheduling: {
			start: {
				$dpicker: $( '#wpforms-panel-field-settings-form_locker_schedule_start_date' ),
				$tpicker: $( '#wpforms-panel-field-settings-form_locker_schedule_start_time' ),
			},
			end: {
				$dpicker: $( '#wpforms-panel-field-settings-form_locker_schedule_end_date' ),
				$tpicker: $( '#wpforms-panel-field-settings-form_locker_schedule_end_time' ),
			},
		},
	};

	/**
	 * Public functions and properties.
	 *
	 * @since 1.0.0
	 *
	 * @type {object}
	 */
	const app = {

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			$( app.ready );
		},

		/**
		 * Document ready.
		 *
		 * @since 1.0.0
		 */
		ready: function() {

			app.events();
			app.conditionals();
			app.selectConditionals();
			app.emailDropdownToggle();
			app.dateTimePicker();
			app.scheduleDatetimeEvents();
		},

		/**
		 * Register JS events.
		 *
		 * @since 1.0.0
		 */
		events: function() {

			$( document ).on( 'click', '.wpforms-panel-field-datetime .wpforms-clear-datetime-field', app.resetDateTimeField );
			$( document ).on( 'change', '#wpforms-panel-field-settings-form_locker_verification, #wpforms-panel-field-settings-form_locker_verification_type', app.selectConditionals );
			$( document ).on( 'change', '#wpforms-panel-field-settings-form_locker_user_entry_limit_enable, #wpforms-panel-field-settings-form_locker_user_entry_restrict_by_email', app.emailDropdownToggle );
			$( document ).on( 'keypress paste', '.wpforms-datepair-start, .wpforms-datepair-end', app.disableManualDateInput );
			$( document ).on( 'wpformsSaved', app.requireUserEntryLimitRestrictBy );
			$( '#wpforms-builder' ).on( 'wpformsBeforeSave', app.checkAndShowRequiredFieldsPopup );
		},

		/**
		 * Check if scheduling is enabled.
		 *
		 * @since 2.7.0
		 *
		 * @return {boolean} True if scheduling is enabled, false otherwise.
		 */
		isSchedulingEnabled() {
			return $( '#wpforms-panel-field-settings-form_locker_schedule_enable' ).is( ':checked' );
		},

		/**
		 * Check the required fields and show relevant popups.
		 *
		 * @since 2.7.0
		 *
		 * @param {Event} event Event.
		 */
		checkAndShowRequiredFieldsPopup( event ) {
			if ( ! app.isSchedulingEnabled() ) {
				return;
			}

			const start = {
				date: el.scheduling.start.$dpicker.val(),
				time: el.scheduling.start.$tpicker.val(),
			};
			const end = {
				date: el.scheduling.end.$dpicker.val(),
				time: el.scheduling.end.$tpicker.val(),
			};
			const shouldShowPopup = {
				// eslint-disable-next-line camelcase
				start_end: () => start.time && end.time && ! start.date && ! end.date,
				start: () => start.time && ! start.date,
				end: () => end.time && ! end.date,
			};

			app.removeRequiredFieldsMark();

			for ( const key of Object.keys( shouldShowPopup ) ) {
				if ( shouldShowPopup[ key ]() ) {
					app.markRequiredFields( key );

					app.showRequiredFieldsPopup(
						event,
						wpforms_admin_builder_form_locker[ `${ key }_date_required_title` ],
						wpforms_admin_builder_form_locker[ `${ key }_date_required_content` ]
					);

					break;
				}
			}
		},

		/**
		 * Remove required fields mark.
		 *
		 * @since 2.7.0
		 */
		removeRequiredFieldsMark() {
			el.scheduling.start.$dpicker.siblings( '.wpforms-datepair-date' ).removeClass( 'wpforms-error' );
			el.scheduling.end.$dpicker.siblings( '.wpforms-datepair-date' ).removeClass( 'wpforms-error' );
		},

		/**
		 * Mark required fields.
		 *
		 * @since 2.7.0
		 *
		 * @param {string} key Key of the field to mark.
		 */
		markRequiredFields( key ) {
			const start = el.scheduling.start.$dpicker.siblings( '.wpforms-datepair-date' );
			const end = el.scheduling.end.$dpicker.siblings( '.wpforms-datepair-date' );

			if ( 'start' === key ) {
				start.addClass( 'wpforms-error' );
			} else if ( 'end' === key ) {
				end.addClass( 'wpforms-error' );
			} else {
				start.addClass( 'wpforms-error' );
				end.addClass( 'wpforms-error' );
			}
		},

		/**
		 * Show the warning popup with a message.
		 *
		 * @since 2.7.0
		 *
		 * @param {Event}  event   Event.
		 * @param {string} title   Popup title.
		 * @param {string} content Popup content.
		 */
		showRequiredFieldsPopup( event, title, content ) {
			$.alert( {
				title,
				content,
				icon: 'fa fa-exclamation-circle',
				type: 'orange',
				buttons: {
					confirm: {
						text: wpforms_builder.ok,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
					},
				},
			} );

			event.preventDefault();
		},

		/**
		 * Clear DateTime field.
		 *
		 * @since 2.0.0
		 */
		resetDateTimeField: function() {

			const $input = $( this ).siblings( 'input' );

			if ( $input.prop( '_flatpickr' ) ) {
				$input.prop( '_flatpickr' ).clear();
			} else {
				$input.val( '' );
			}

			$( this ).hide();
		},

		/**
		 * Load conditionals.
		 *
		 * @since 1.0.0
		 */
		conditionals: function() {

			if ( typeof $.fn.conditions === 'undefined' ) {
				return;
			}

			const elements = [
				{
					id: '#wpforms-panel-field-settings-form_locker_verification',
					hides: '#wpforms-panel-field-settings-form_locker_verification_type-wrap',
				},
				{
					id: '#wpforms-panel-field-settings-form_locker_schedule_enable',
					hides: '#wpforms-form-locker-schedule-datetime-block,#wpforms-panel-field-settings-form_locker_schedule_message-wrap',
				},
				{
					id: '#wpforms-panel-field-settings-form_locker_user_enable',
					hides: '#wpforms-panel-field-settings-form_locker_user_message-wrap',
				},
				{
					id: '#wpforms-panel-field-settings-form_locker_entry_limit_enable',
					hides: '#wpforms-panel-field-settings-form_locker_entry_limit-wrap,#wpforms-panel-field-settings-form_locker_entry_limit_message-wrap',
				},
				{
					id: '#wpforms-panel-field-settings-form_locker_user_entry_limit_enable',
					hides: '#wpforms-panel-field-settings-form_locker_user_entry_restrict_by_ip-wrap,#wpforms-panel-field-settings-form_locker_user_entry_restrict_by_email-wrap,#wpforms-panel-field-settings-form_locker_user_entry_message_limited-wrap,.wpforms-form-locker-user-entry-container,#wpforms-panel-field-settings-form_locker_user_entry_email_field-wrap',
				},
			];

			$.each( elements, function( index, element ) {
				$( element.id ).conditions( {
					conditions: {
						element : element.id,
						type    : 'checked',
						operator: 'is',
					},
					actions   : {
						if  : {
							element: element.hides,
							action : 'show',
						},
						else: {
							element: element.hides,
							action : 'hide',
						},
					},
					effect    : 'appear',
				} );
			} );
		},

		/**
		 * Load smart select conditionals.
		 *
		 * @since 2.0.0
		 */
		selectConditionals: function() {

			const $verificationType   = $( '#wpforms-panel-field-settings-form_locker_verification_type' );
			const options             = $verificationType.find( 'option' );
			const value               = $verificationType.val();
			const isMainSwitchChecked = $( '#wpforms-panel-field-settings-form_locker_verification' ).is( ':checked' );

			if ( ! options.length ) {
				return;
			}

			options.each( function() {

				const val = $( this ).val();

				if ( ! isMainSwitchChecked || val !== value ) {
					$( '.wpforms-form-locker-' + val ).hide();
				}

				if ( isMainSwitchChecked && val === value ) {
					$( '.wpforms-form-locker-' + val ).show();
				}
			} );
		},

		/**
		 * Show Email fields dropdown only when checkbox is enabled.
		 *
		 * @since 2.0.0
		 */
		emailDropdownToggle: function() {

			const $hide               = $( '#wpforms-panel-field-settings-form_locker_user_entry_restrict_by_email' );
			const $block              = $( '#wpforms-panel-field-settings-form_locker_user_entry_email_field-wrap' );
			const isMainSwitchChecked = $( '#wpforms-panel-field-settings-form_locker_user_entry_limit_enable' ).is( ':checked' );

			if ( ! $hide.length ) {
				return;
			}

			if ( $hide.is( ':checked' ) && isMainSwitchChecked ) {
				$block.show();
			} else {
				$block.hide();
			}
		},

		/**
		 * Display an alert message if "Enable user entry limit" is toggled on
		 * and no type of restriction checked.
		 *
		 * @since 2.2.0
		 */
		requireUserEntryLimitRestrictBy: function() {

			const $userEntryLimitEnabledToggle = $( '#wpforms-panel-field-settings-form_locker_user_entry_limit_enable' );

			// Continue only if "Enable user entry limit" is toggled on.
			if ( ! $userEntryLimitEnabledToggle || ! $userEntryLimitEnabledToggle.is( ':checked' ) ) {
				return;
			}

			const $userEntryRestrictByIPCheckbox    = $( '#wpforms-panel-field-settings-form_locker_user_entry_restrict_by_ip' );
			const $userEntryRestrictByEmailCheckbox = $( '#wpforms-panel-field-settings-form_locker_user_entry_restrict_by_email' );

			// If either one is checked, do nothing.
			if ( $userEntryRestrictByIPCheckbox.is( ':checked' ) || $userEntryRestrictByEmailCheckbox.is( ':checked' ) ) {
				return;
			}

			$.alert( {
				title: wpforms_builder.heads_up,
				content: wpforms_admin_builder_form_locker.require_restrict_user_entry_option,
				icon: 'fa fa-exclamation-circle',
				type: 'orange',
				buttons: {
					confirm: {
						text: wpforms_builder.ok,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
					},
				},
			} );
		},

		/**
		 * Load date/time pickers.
		 *
		 * @since 1.0.0
		 */
		dateTimePicker: function() {

			app.datePicker();
			app.timePicker();
			app.datePair();
			app.dateTimeClearBtnsInit();
		},

		/**
		 * Load date picker.
		 *
		 * @since 1.0.0
		 */
		datePicker: function() {

			if ( 'undefined' === typeof $.fn.flatpickr ) {
				return;
			}

			$( '#wpforms-panel-field-settings-form_locker_schedule_start_date' ).flatpickr( app.datePickerGetArgs( 'start' ) );
			$( '#wpforms-panel-field-settings-form_locker_schedule_end_date' ).flatpickr( app.datePickerGetArgs( 'end' ) );
		},

		/**
		 * Load time picker.
		 *
		 * @since 1.0.0
		 */
		timePicker: function() {

			if ( 'undefined' === typeof $.fn.timepicker ) {
				return;
			}

			const args = {
				appendTo: $( '.wpforms-panel-content-section-form_locker' ),
				disableTextInput: true,
				timeFormat: wpforms_admin_builder_form_locker.time_format,
			};

			const $startTime = $( '#wpforms-panel-field-settings-form_locker_schedule_start_time' );
			const $endTime   = $( '#wpforms-panel-field-settings-form_locker_schedule_end_time' );

			$startTime.timepicker( args ).on( 'selectTime', app.dateTimeClearBtnsRefresh );
			$endTime.timepicker( args ).on( 'selectTime', app.dateTimeClearBtnsRefresh );
		},

		/**
		 * Load date pair.
		 *
		 * @since 1.0.0
		 */
		datePair() {
			const args = {
				anchor: null,
				defaultDateDelta: 0,
				defaultTimeDelta: datePairDefaultTimeDelta * 60 * 1000,
				dateClass: 'wpforms-datepair-date',
				timeClass: 'wpforms-datepair-time',
				startClass: 'wpforms-datepair-start',
				endClass: 'wpforms-datepair-end',
				parseDate( input ) {
					return $( input ).prop( '_flatpickr' ).selectedDates[ 0 ];
				},
				updateDate( input, dateObj ) {
					const $input = $( input );

					$input.prop( '_flatpickr' ).setDate( dateObj );
					app.dateTimeClearBtnsRefresh( $input );
					$input.trigger( 'change' );
					app.removeRequiredFieldsMark();
				},
				updateTime( input, dateObj ) {
					const $input = $( input );

					$input.timepicker( 'setTime', dateObj );
					app.dateTimeClearBtnsRefresh( $input );
					$input.trigger( 'change' );
				},
			};

			$( '#wpforms-form-locker-schedule-datetime-block' ).datepair( args );
		},

		/**
		 * Init delete/time field clear buttons.
		 *
		 * @since 1.2.0
		 */
		dateTimeClearBtnsInit: function() {

			$( '#wpforms-form-locker-schedule-datetime-block .wpforms-clear-datetime-field' )
				.each( function() {

					const $t = $( this );

					if ( '' === $t.siblings( '[id^="wpforms-panel-field-settings-form_locker_schedule_"]' ).val() ) {
						$t.hide();
					}
				} );
		},

		/**
		 * Refresh a clear button for an element.
		 *
		 * @since 1.2.2
		 *
		 * @param {jQuery|Event} $el Element to refresh a clear button for.
		 */
		dateTimeClearBtnsRefresh: function( $el ) {

			// Extract the element if an event was passed.
			// Useful when used as a callback.
			if ( $el.target ) {
				$el = $( $el.target );
			}

			if ( ! $el ) {
				return;
			}

			if ( '' !== $el.val() ) {
				$el.nextAll( 'button.wpforms-clear-datetime-field' ).show();
			}
		},

		/**
		 * Get arguments for date picker start/end date fields.
		 *
		 * @since 1.2.2
		 *
		 * @param {string} field Field type (e.g 'start' or 'end').
		 *
		 * @returns {{altFormat: string, dateFormat: string, altInput: boolean, onChange: Function}} Date picker arguments.
		 */
		datePickerGetArgs: function( field ) {

			field = field === 'start' ? 'start' : 'end';

			const $tpicker = el.scheduling[field].$tpicker;
			const $dpicker = el.scheduling[field].$dpicker;
			const args     = {
				altInput  : true,
				allowInput: true,
				altFormat : wpforms_admin_builder_form_locker.date_format,
				dateFormat: 'Y-m-d',
			};

			args.onChange = function( date ) {

				const second = {
					field : field === 'start' ? 'end' : 'start',
				};

				second.$dpicker = el.scheduling[second.field].$dpicker;
				second.$tpicker = el.scheduling[second.field].$tpicker;

				if (
					$tpicker.val() === '' &&
					second.$tpicker.val() === ''
				) {
					$tpicker.timepicker( 'setTime', new Date( date ) );
				}

				app.dateTimeClearBtnsRefresh( $tpicker );
				app.dateTimeClearBtnsRefresh( $dpicker );
				app.removeRequiredFieldsMark();
			};

			return args;
		},

		/**
		 * Schedule datetime events.
		 *
		 * @since 1.2.2
		 */
		scheduleDatetimeEvents: function() {

			// This is needed for handling wrong datetime range situations.
			$( '#wpforms-form-locker-schedule-datetime-block' ).on( 'change', 'input', function( e ) {

				const start = {
					date: el.scheduling.start.$dpicker.val(),
					time: el.scheduling.start.$tpicker.val(),
				};
				const end   = {
					date: el.scheduling.end.$dpicker.val(),
					time: el.scheduling.end.$tpicker.val(),
				};

				if ( start.date === '' || end.date === '' ) {
					return;
				}

				const sdateTime = ( start.date + ' ' + start.time ).replace( /-/g, '/' );
				const edateTime = ( end.date + ' ' + end.time ).replace( /-/g, '/' );
				const sdateObj  = new Date( sdateTime );
				const edateObj  = new Date( edateTime );

				if ( edateObj.getTime() > sdateObj.getTime() ) {
					return;
				}

				// Set end time as a sum of start time and datePairDefaultTimeDelta (30 minutes ).
				sdateObj.setMinutes( sdateObj.getMinutes() + datePairDefaultTimeDelta );
				el.scheduling.end.$tpicker.timepicker( 'setTime', sdateObj );
				el.scheduling.end.$dpicker.prop( '_flatpickr' ).setDate( sdateObj );

				app.dateTimeClearBtnsRefresh( el.scheduling.end.$tpicker );
				app.dateTimeClearBtnsRefresh( el.scheduling.end.$dpicker );
			} );
		},

		/**
		 * Disable manual date input.
		 *
		 * @since 2.8.0
		 *
		 * @param {Event} event Event.
		 */
		disableManualDateInput( event ) {
			event.preventDefault();
		},
	};

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );

// Initialize.
WPFormsBuilderFormLocker.init();
