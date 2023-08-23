/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';

const forms = document.querySelectorAll(
	'[data-component="form-user_login"],' +
		'[data-component="form-user_register"],' +
		'[data-component="form-request_reset_password"],' +
		'[data-component="form-reset_password"]'
);

forms.forEach((form, index, array) => {
	form.addEventListener(
		'submit',
		(event) => {
			// Store reference to form to make later code easier to read.
			const form = event.target;
			const messages = form.querySelector('[data-component="messages"]');
			const submit = form.querySelector('button[type="submit"]');

			// Set button loading state.
			submit.classList.add('cbd-field--button__is-loading');

			// Reset the messages container class.
			messages.classList.remove(
				'cbd-form__message--error',
				'cbd-form__message--success'
			);

			// Reset the messages container display.
			// messages.style.display = 'none';

			// Reset the messages container content.
			messages.innerHTML = '';

			if (form.dataset.action) {
				const data = new FormData(form);

				// Add redirect to the form data.
				if (typeof form.dataset.redirect === 'string') {
					data.append('redirect', form.dataset.redirect);
				}

				apiFetch({
					path: form.dataset.action,
					method: form.method,
					body: data,
				})
					.then((res) => {
						console.log(res);

						// Set button loading state.
						submit.classList.remove(
							'cbd-field--button__is-loading'
						);

						// If the response contains a confirmation message, set the for data value.
						if (typeof res.confirmation === 'string') {
							form.dataset.confirmation = res.confirmation;
						}

						if (typeof res.redirect === 'string') {
							window.location.replace(res.redirect);
						} else if (
							typeof res.reload === 'boolean' &&
							true === res.reload
						) {
							window.location.reload();
						} else if (
							typeof res.reset === 'boolean' &&
							true === res.reset
						) {
							form.reset();
						}

						// Display the confirmation message.
						if (typeof form.dataset.confirmation === 'string') {
							messages.classList.add(
								'cbd-form__message--success'
							);
							messages.innerHTML =
								'<div>' + form.dataset.confirmation + '</div>';
						}

						// Enable the submit button to allow additional requests.
						submit.disabled = false;
					})
					.catch((err) => {
						console.log(err);

						messages.classList.add('cbd-form__message--error');

						if (err.message) {
							messages.innerHTML =
								'<div>' + err.message + '</div>';
						}

						// Set button loading state.
						submit.classList.remove(
							'cbd-field--button__is-loading'
						);

						// Enable the submit button to allow additional requests.
						submit.disabled = false;
					});
			}

			// Disable submit button to prevent additional requests.
			submit.disabled = true;

			// Prevent the default form submit.
			event.preventDefault();
		},
		false
	);
});
