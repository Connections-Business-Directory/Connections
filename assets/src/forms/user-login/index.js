/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';

const form = document.querySelector('[data-component="form-user_login"]');

form.addEventListener(
	'submit',
	(event) => {
		// Store reference to form to make later code easier to read.
		const form = event.target;
		const messages = form.querySelector('[data-component="messages"]');
		const submit = form.querySelector('button[type="submit"]');

		// Set button loading state.
		submit.classList.add('cbd-field--button__is-loading');

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
					submit.classList.remove('cbd-field--button__is-loading');

					// Enable the submit button to allow additional requests.
					submit.disabled = false;

					if (typeof res.redirect === 'string') {
						window.location.replace(res.redirect);
					} else if (typeof res.reload === 'boolean') {
						window.location.reload();
					}
				})
				.catch((err) => {
					console.log(err);

					if (err.message) {
						messages.innerHTML = '<div>' + err.message + '</div>';
					}

					// Set button loading state.
					submit.classList.remove('cbd-field--button__is-loading');

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
