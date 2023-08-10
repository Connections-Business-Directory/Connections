/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Object is added to page utilizing the `wp_add_inline_script()` in Form\Reset_Password.
 *
 * @param {Object} _resetPassword
 * @param {Object} _resetPassword.ajax          An object containing the site's admin URL and request endpoint.
 * @param {string} _resetPassword.ajax.root     The site's admin URL.
 * @param {string} _resetPassword.ajax.endpoint The request endpoint.
 * @param {string} pwsL10n.unknown
 * @param {string} pwsL10n.bad
 * @param {string} pwsL10n.good
 * @param {string} pwsL10n.strong
 * @param {string} pwsL10n.short
 */

(function () {
	const _observer = new MutationObserver((mutations) => {
		mutations.forEach((mutation) => {
			if ('data-pw' === mutation.attributeName) {
				if ('pass1' === mutation.target.name) {
					mutation.target.value = mutation.target.dataset.pw;
				}
			}
		});
	});

	/**
	 * @param {string} password
	 * @return {number} The password strength score.
	 * @private
	 */
	const _checkPasswordStrength = (password) => {
		return wp.passwordStrength.meter(
			password,
			wp.passwordStrength.userInputDisallowedList(),
			password
		);
	};

	/**
	 * @param {number}    strength
	 * @param {string}    password
	 * @param {Element[]} elements
	 * @private
	 */
	const _setPasswordStrengthClass = (strength, password, elements) => {
		elements.forEach((element) => {
			element.classList.remove('short', 'bad', 'good', 'strong', 'empty');

			let text = '&nbsp;';

			if (!password || '' === password.trim()) {
				element.classList.add('empty');

				if ('password-strength-result' === element.dataset.component) {
					element.innerHTML = text;
				}
				return;
			}

			switch (strength) {
				case -1:
					element.classList.add('bad');
					text = pwsL10n.unknown;
					break;
				case 2:
					element.classList.add('bad');
					text = pwsL10n.bad;
					break;
				case 3:
					element.classList.add('good');
					text = pwsL10n.good;
					break;
				case 4:
					element.classList.add('strong');
					text = pwsL10n.strong;
					break;
				case 5:
					element.classList.add('short');
					text = pwsL10n.short;
					break;
				default:
					element.classList.add('short');
					text = pwsL10n.short;
			}

			if ('password-strength-result' === element.dataset.component) {
				element.innerHTML = text;
			}
		});
	};

	/**
	 * Whether the supplied string value is empty.
	 *
	 * @param {string} string
	 * @return {boolean} Whether the supplied string is empty after trimming the space character.
	 * @private
	 */
	const _isEmpty = (string) => {
		return !string.trim().length;
	};

	/**
	 * @param {number} score
	 * @return {boolean} Whether the score indicates a weak password.
	 * @private
	 */
	const _isWeakPw = (score) => {
		return ![3, 4].includes(score);
	};

	/**
	 * Toggle the input field type to password or text to show/hide a password.
	 *
	 * @param {Element} form
	 * @private
	 */
	const _togglePasswordFieldType = (form) => {
		const fields = form.querySelectorAll('.cbd-field--password');

		fields.forEach((field) => {
			if (field.type === 'password') {
				field.setAttribute('type', 'text');
			} else {
				field.setAttribute('type', 'password');
			}
		});
	};

	/**
	 * Toggle the display of the confirm weak password checkbox.
	 *
	 * @param {Element} element
	 * @param {boolean} display
	 * @private
	 */
	const _toggleWeakPasswordConfirm = (element, display) => {
		if (display) {
			element.style.display = 'block';
		} else {
			element.style.display = 'none';
		}
	};

	/**
	 * Toggle the CSS classes that apply the dashicons to the toggle password visibility button.
	 *
	 * @param {Element} button
	 * @private
	 */
	const _togglePasswordAttributes = (button) => {
		const span = button.querySelector('span.dashicons');
		if (span.classList.contains('dashicons-hidden')) {
			span.classList.remove('dashicons-hidden');
			span.classList.add('dashicons-visibility');
			button.setAttribute(
				'aria-label',
				__('Show password', 'connections')
			);
		} else {
			span.classList.remove('dashicons-visibility');
			span.classList.add('dashicons-hidden');
			button.setAttribute(
				'aria-label',
				__('Hide password', 'connections')
			);
		}
	};

	/**
	 * Trigger an event.
	 *
	 * @param {Element}      el
	 * @param {Event|string} eventType
	 * @private
	 */
	const _trigger = (el, eventType) => {
		if (
			typeof eventType === 'string' &&
			typeof el[eventType] === 'function'
		) {
			el[eventType]();
		} else {
			const event =
				typeof eventType === 'string'
					? new Event(eventType, { bubbles: true })
					: eventType;
			el.dispatchEvent(event);
		}
	};

	/*
	 * Select all password reset forms on the page.
	 */
	const forms = document.querySelectorAll(
		'[data-component="form-reset_password"]'
	);

	forms.forEach((form) => {
		const pass1 = form.querySelector('input[name="pass1"]');
		const pass2 = form.querySelector('input[name="pass2"]');
		/**
		 * @type {Element[]} inputs
		 */
		const inputs = [
			...form.querySelectorAll(
				'input[type="text"], input[type="password"]'
			),
		];
		const passwordStrength = form.querySelector(
			'[data-component="password-strength-result"]'
		);
		const weakpwConfirm = form.querySelector('input[name="pw_weak"]');
		const weakpw = weakpwConfirm.closest('div');
		const toggle = form.querySelector('button[name="password-toggle"]');
		const generate = form.querySelector('button[name="generate-password"]');
		const submit = form.querySelector('button[name="wp-submit"]');
		const score = new WeakMap();

		score.set(form, 0);

		/**
		 * Whether the submit button is enabled.
		 *
		 * @param {boolean} weakPasswordConfirmed
		 * @return {boolean} Returns true if the form submit is permitted.
		 * @private
		 */
		const _allowSubmit = (weakPasswordConfirmed = false) => {
			return (
				!_isEmpty(pass1.value) &&
				!_isEmpty(pass2.value) &&
				pass1.value === pass2.value &&
				(!_isWeakPw(score.get(form)) || weakPasswordConfirmed)
			);
		};

		inputs.forEach((input) => {
			/*
			 * Set the disabled status of the submit button based on
			 * whether values in both password fields are not empty and match.
			 */
			input.addEventListener('input', () => {
				submit.disabled = !inputs.every(() => {
					return _allowSubmit(weakpwConfirm.checked);
				});
			});

			/*
			 * Clear the "Confirm new password" field if user clears "New password" field and
			 * sets the "Confirm new password" field disabled status accordingly.
			 */
			if ('pass1' === input.name) {
				input.addEventListener('input', (event) => {
					const field = event.target;

					if (field.value.length === 0) {
						pass2.value = '';
						pass2.disabled = true;

						score.set(form, 0);

						_toggleWeakPasswordConfirm(weakpw, false);
					} else {
						score.set(form, _checkPasswordStrength(field.value));

						_toggleWeakPasswordConfirm(
							weakpw,
							_isWeakPw(score.get(form))
						);

						pass2.disabled = false;
					}

					_setPasswordStrengthClass(score.get(form), field.value, [
						input,
						passwordStrength,
					]);
				});
			}
		});

		/*
		 * The even listener to toggle password field types from password to text.
		 */
		toggle.addEventListener('click', (event) => {
			const button = event.currentTarget;

			_togglePasswordAttributes(button);

			_togglePasswordFieldType(
				button.closest('[data-component="form-reset_password"]')
			);
		});

		/*
		 * The event listener to toggle the submit button disable status
		 * when confirming the use of a weak password.
		 */
		weakpwConfirm.addEventListener('change', (event) => {
			submit.disabled = !_allowSubmit(event.target.checked);
		});

		/*
		 * The event listener that performs a remote request to generate a new password.
		 */
		generate.addEventListener('click', () => {
			// const button = event.target;

			const data = new FormData();

			data.append('action', 'generate-password');

			// eslint-disable-next-line no-undef
			fetch(_resetPassword.ajax.root + _resetPassword.ajax.endpoint, {
				method: 'POST',
				body: data,
			}).then((res) => {
				res.json().then((json) => {
					pass1.setAttribute('data-pw', json.data);
					pass2.value = pass1.dataset.pw;
					pass2.disabled = false;
					submit.disabled = false;

					score.set(form, _checkPasswordStrength(json.data));

					_setPasswordStrengthClass(score.get(form), json.data, [
						pass1,
						passwordStrength,
					]);

					_toggleWeakPasswordConfirm(
						weakpw,
						_isWeakPw(score.get(form))
					);
				});
			});
		});

		form.addEventListener('reset', () => {
			score.set(form, 0);

			_setPasswordStrengthClass(score.get(form), '', [
				pass1,
				passwordStrength,
			]);

			_toggleWeakPasswordConfirm(weakpw, false);
		});

		/*
		 * Hide the weak password confirmation field and label on load.
		 */
		_toggleWeakPasswordConfirm(weakpw, false);

		/*
		 * Watch for changes to the data attributes if the password field,
		 * as generated passwords are saved as a data attribute.
		 */
		_observer.observe(pass1, {
			attributes: true,
		});

		/*
		 * On page load, trigger the AJAX call to generate a fresh password.
		 */
		_trigger(generate, 'click');
	});
})();
