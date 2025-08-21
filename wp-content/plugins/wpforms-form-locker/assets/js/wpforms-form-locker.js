/* global wpforms, wpforms_settings, wpforms_form_locker */

/**
 * WPForms Form Locker function.
 *
 * @since 1.0.0
 */
const WPFormsFormLocker = window.WPFormsFormLocker || ( function( document, window, $ ) {
	/**
	 * Public functions and properties.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	const app = {

		/**
		 * Cache.
		 *
		 * @since 2.7.0
		 */
		cache: {},

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init() {
			$( app.ready );
		},

		/**
		 * Document ready.
		 *
		 * @since 1.0.0
		 */
		ready() {
			app.validation();
			app.initNameGroupsValidation();

			document.onwpformsRepeaterFieldCloneCreated = function() {
				app.initNameGroupsValidation();
			};
		},

		/**
		 * Register custom validation.
		 *
		 * @since 1.0.0
		 * @since 2.1.0 Handle Smart Phone field.
		 */
		validation() { // eslint-disable-line max-lines-per-function
			// Only load if jQuery validation library exists
			if ( typeof $.fn.validate === 'undefined' ) {
				return;
			}

			$.validator.addMethod( 'unique', function( value, element, param, method ) { // eslint-disable-line max-lines-per-function, complexity
				// This code is copied from JQuery Validate 'remote' method with several changes:
				// - 'data' variable is not empty;
				// - 'url' and 'type' parameters are added to $.ajax() call.
				// - Added support for the complex `Name` field. Passing combined value instead of the single input.
				method = typeof method === 'string' && method || 'unique'; // eslint-disable-line no-mixed-operators

				const $el = $( element );
				const $field = $el.closest( '.wpforms-field' );
				const previous = this.previousValue( element, method );
				const formId = $el.closest( '.wpforms-form' ).data( 'formid' );
				const fieldId = $field.data( 'field-id' );

				let validator, name;

				const data = {
					action: 'wpforms_form_locker_unique_answer',
					form_id: formId, // eslint-disable-line camelcase
					field_id: fieldId, // eslint-disable-line camelcase
				};

				name = element.name;

				if ( $field.hasClass( 'wpforms-field-name' ) ) { // In case of the 'Name' field we need to get combined value.
					value = app.getCombinedValue( $field );
					name = 'wpforms[fields][' + data.field_id + ']';
				} else if ( $el.hasClass( 'wpforms-smart-phone-field' ) ) { // In case of 'Smart' Phone field.
					name = 'wpforms[fields][' + data.field_id + ']';
					value = $el.siblings( '[name="' + name + '"]' ).first().val();
				}

				data[ name ] = value;

				if ( ! this.settings.messages[ element.name ] ) {
					this.settings.messages[ element.name ] = {};
				}
				previous.originalMessage = previous.originalMessage || this.settings.messages[ element.name ][ method ];
				this.settings.messages[ element.name ][ method ] = previous.message;

				param = typeof param === 'string' && { url: param } || param; // eslint-disable-line no-mixed-operators

				const optionDataString = $.param( $.extend( { data: value }, param.data ) );

				if ( previous.valid !== null && previous.old === optionDataString ) {
					return previous.valid;
				}

				previous.old = optionDataString;
				previous.valid = null;
				validator = this;

				app.cache[ formId ] = app.cache[ formId ] || {};
				app.cache[ formId ][ fieldId ] = app.cache[ formId ][ fieldId ] || {};

				// Return cached result if it exists.
				if (
					Object.prototype.hasOwnProperty.call( app.cache, formId ) &&
					Object.prototype.hasOwnProperty.call( app.cache[ formId ], fieldId ) &&
					app.cache[ formId ][ fieldId ].nonUniqueValues &&
					app.cache[ formId ][ fieldId ].nonUniqueValues.includes( value )
				) {
					return false;
				}
				this.startRequest( element );

				$.ajax( $.extend( true, {
					url: wpforms_form_locker.ajaxurl,
					type: 'post',
					mode: 'abort',
					port: 'validate' + element.name,
					dataType: 'json',
					context: validator.currentForm,
					data,
					success( response ) {
						const valid = response === true || response === 'true';

						let errors, message, submitted;

						// To get the subfield name prefix we should remove subfield name suffix (like [first]).
						const subfieldPrefix = element.name.replace( /(\[[a-z]*\])$/g, '' );

						app.cache[ formId ][ fieldId ].nonUniqueValues = app.cache[ formId ][ fieldId ].nonUniqueValues || [];

						validator.settings.messages[ element.name ][ method ] = previous.originalMessage;

						if ( valid ) {
							submitted = validator.formSubmitted;
							validator.resetInternals();
							validator.formSubmitted = submitted;
							validator = app.updateValidatorLists( validator, subfieldPrefix, $field, valid );
							validator.hideThese( validator.errorsFor( element ) );
							app.revalidateGroupedFields( validator, $el );
						} else {
							errors = {};
							message = response || validator.defaultMessage( element, { method, parameters: value } );
							errors[ element.name ] = previous.message = message;
							validator = app.updateValidatorLists( validator, subfieldPrefix, $field, valid );
							validator.showErrors( errors );
							if ( ! app.cache[ formId ][ fieldId ].nonUniqueValues.includes( value ) ) {
								app.cache[ formId ][ fieldId ].nonUniqueValues.push( value );
							}
						}

						previous.valid = valid;
						validator.stopRequest( element, valid );
					},
				}, param ) );
				return 'pending';
			}, wpforms_settings.val_unique );
		},

		/**
		 * Init validator groups for all unique Name fields.
		 *
		 * @since 2.0.0
		 */
		initNameGroupsValidation() {
			$( '.wpforms-validate' ).each( function() {
				const $form = $( this );
				const validator = $form.data( 'validator' );

				$.extend( validator.groups, app.getNameGroups( $form ) );
			} );
		},

		/**
		 * Get field combined value.
		 *
		 * @since 2.0.0
		 *
		 * @param {jQuery} $field WPForms field jQuery object.
		 *
		 * @return {string} Field combined value.
		 */
		getCombinedValue( $field ) {
			const values = [];

			$field.find( 'input[data-rule-unique]' ).each( function() {
				const val = $( this ).val();

				if ( ! wpforms.empty( val ) ) {
					values.push( val );
				}
			} );

			return values.join( ' ' );
		},

		/**
		 * Generate validator groups data.
		 *
		 * @since 2.0.0
		 *
		 * @param {jQuery} $form Form jQuery object.
		 *
		 * @return {Object} Array like object.
		 */
		getNameGroups( $form ) {
			const result = {};

			$form.find( '.wpforms-field.wpforms-field-name.wpforms-field-unique' ).each( function() {
				const $field = $( this );
				const groupName = $field.attr( 'id' ).replace( '-container', '' ) + '-field-group';

				$.each( [ 'first', 'middle', 'last' ], function( i, subfield ) {
					const $subfield = $field.find( '.wpforms-field-name-' + subfield );
					const name = $subfield.attr( 'name' ) || false;

					if ( name ) {
						result[ name ] = groupName;
					}
				} );
			} );
			return result;
		},

		/**
		 * Update validator lists.
		 *
		 * @since 2.0.0
		 *
		 * @param {Object}  validator      jQuery validator object.
		 * @param {string}  subfieldPrefix Subfield name prefix.
		 * @param {jQuery}  $field         WPForms field jQuery object.
		 * @param {boolean} valid          Field validation state.
		 *
		 * @return {Object} Modified jQuery validator object.
		 */
		updateValidatorLists( validator, subfieldPrefix, $field, valid ) {
			$.each( validator.invalid, function( key ) {
				if ( key.indexOf( subfieldPrefix ) < 0 ) {
					return;
				}

				validator.invalid[ key ] = ! valid;

				const $input = $field.find( 'input[name="' + key + '"]' );

				if ( valid ) {
					validator.successList.push( $input[ 0 ] );
				}
			} );

			return validator;
		},
		/**
		 * We should validate elements if they are a part of the group.
		 * After successful check via "unique" method, errors are hidden.
		 * But in the case of the required Name field, it can be unique with one
		 * empty field for which should be displayed error message.
		 *
		 * @since 2.9.0
		 *
		 * @param {Object} validator jQuery validator object.
		 * @param {jQuery} $el       WPForms field jQuery object.
		 */
		revalidateGroupedFields( validator, $el ) {
			if ( ! validator.groups?.[ $el[ 0 ].name ] ) {
				return;
			}

			// Delete unique method to prevent recursion.
			const uniqueMethod = $.validator.methods.unique.bind( validator );
			delete $.validator.methods.unique;

			// Run validation.
			$el.each( function() {
				validator.element( this );
			} );

			// Revert unique method.
			$.validator.methods.unique = uniqueMethod;
		},
	};

	// Provide access to public functions/properties.
	return app;
}( document, window, jQuery ) );

// Initialize.
WPFormsFormLocker.init();
