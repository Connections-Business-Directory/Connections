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

const _parse = (response) => {
	console.log(response);

	if (response.status === 204) {
		return null;
	}

	return response.json ? response.json() : Promise.reject(response);
};

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
					data.append('redirect_to', form.dataset.redirect);
				}

				apiFetch({
					path: form.dataset.action,
					method: form.method,
					body: data,
					/*
					 * Disable the automatic parsing of the Response instance and utilize a private callback instead
					 * because there are errors that can be returned by apiFetch that are not parsed and the native
					 * Response instance is returned instead of the expected parsed data making it inconsistent.
					 * Applying the private parse callback ensures a consistent parsed response for the error handler.
					 */
					parse: false,
				})
					.then((response) => {
						return _parse(response);
					})
					.then((success) => {
						console.log(success);

						// Set button loading state.
						submit.classList.remove(
							'cbd-field--button__is-loading'
						);

						// If the response contains a confirmation message, set the for data value.
						if (typeof success.confirmation === 'string') {
							form.dataset.confirmation = success.confirmation;
						}

						if (typeof success.redirect === 'string') {
							// let time = new Date().getTime();
							// document.head.innerHTML += '<meta name="304workaround" content="' + time + '">';
							// window.location.replace(success.redirect);
							window.location.replace(success.redirect + '?nocache=' + new Date().getTime());
						} else if (
							typeof success.reload === 'boolean' &&
							true === success.reload
						) {
							window.location.reload();
						} else if (
							typeof success.reset === 'boolean' &&
							true === success.reset
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
					.catch((response) => {
						return _parse(response);
					})
					.then((error) => {
						console.error(error);

						if (0 < error.message.length) {
							messages.classList.add('cbd-form__message--error');

							messages.innerHTML =
								'<div>' + error.message + '</div>';
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
