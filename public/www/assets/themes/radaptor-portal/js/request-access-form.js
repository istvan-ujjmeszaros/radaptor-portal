(function initRequestAccessForm() {
	function resolveLocale() {
		try {
			if (typeof navigator.language === "string" && navigator.language.trim() !== "") {
				return navigator.language.trim();
			}
		} catch (e) {
			// Ignore browser locale lookup failures.
		}

		return "";
	}

	function resolveTimezone() {
		try {
			const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

			return typeof timezone === "string" ? timezone.trim() : "";
		} catch (e) {
			// Ignore browser timezone lookup failures.
		}

		return "";
	}

	function populateForm(form) {
		if (!(form instanceof HTMLFormElement)) {
			return;
		}

		const localeInput = form.querySelector('input[name="locale"]');
		const timezoneInput = form.querySelector('input[name="timezone"]');

		if (localeInput instanceof HTMLInputElement) {
			localeInput.value = resolveLocale();
		}

		if (timezoneInput instanceof HTMLInputElement) {
			timezoneInput.value = resolveTimezone();
		}
	}

	function bootstrap() {
		document.querySelectorAll("form[data-request-access-form]").forEach((form) => {
			populateForm(form);
			form.addEventListener("submit", () => populateForm(form));
		});
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", bootstrap, { once: true });
	} else {
		bootstrap();
	}
})();
