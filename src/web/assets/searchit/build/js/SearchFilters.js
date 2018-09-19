var SearchFilters = (function() {
	"use strict";

	var defaults = {
		filters: [],
		debug: false
	};

	var constructor = function(options) {

		// Public
		// =========================================================================

		var api = {};

		// Private
		// =========================================================================

		var _settings;

		// Private Methods
		// =========================================================================

		// var isValidHex = function(value) {
		// 	return /^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i.test(value);
		// };

		// Event Handlers
		// =========================================================================

		// var colourHandler = function(event) {

		// 	var colour = event.target.closest(selectors.searchitColour);
		// 	if (!colour) return;

		// 	event.preventDefault();
		// 	event.stopPropagation();

		// 	var isSelected = colour.classList.contains(classes.selectedColour);

		// 	clearSearchitColourSelection();
		// 	clearCustomColourSelection(true);

		// 	if(!isSelected) {
		// 		colour.classList.add(classes.selectedColour);
		// 		dom.handleInput.value = colour.getAttribute('data-handle');
		// 	}
		// };


		// Public Methods
		// =========================================================================

		api.init = function(options) {

			settings = extend(defaults, options || {});

			if (settings.debug) {
				console.log('[SearchFilters]', settings);
			}





			// dom.field = document.getElementById(settings.namespacedId);
			// if(dom.field) {

			// 	dom.searchit = dom.field.querySelector(selectors.searchitColours);
			// 	dom.searchitColours = dom.field.querySelectorAll(selectors.searchitColour);
			// 	if(dom.searchitColours) {
			// 		dom.searchit.addEventListener("click", colourHandler, false);
			// 	}


			// 	dom.handleInput = document.getElementById(settings.namespacedId + '-handle');

			// 	dom.opacityInput = document.getElementById(settings.namespacedId + '-opacity');
			// 	if(dom.opacityInput) {
			// 		dom.opacityInput.addEventListener("change", opacityHandler, false);
			// 		dom.opacityInput.addEventListener("keyup", opacityHandler, false);
			// 	}

			// 	dom.custom = dom.field.querySelector(selectors.custom);
			// 	dom.customColour = dom.field.querySelector(selectors.customColour);
			// 	dom.customColourInput = document.getElementById(settings.namespacedId + '-custom');
			// 	if(dom.customColourInput) {
			// 		dom.customColourInput.addEventListener("keyup", customColourHandler, false);
			// 		dom.customColourInput.addEventListener("focus", customColourHandler, false);
			// 	}

			// }
		};

		api.init(options);
		return api;
	};

	return constructor;
})();
