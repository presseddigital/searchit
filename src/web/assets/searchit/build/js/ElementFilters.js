var ElementFilters = (function() {
	"use strict";

	var defaults = {
		filters: {},
		debug: false
	};

	var constructor = function(options) {

		// Public
		// =========================================================================

		var api = {};
		var settings;
		var elementIndex;
		var elementFilters = {};

		// Private Methods
		// =========================================================================

		var initElementFilters = function() {

			var filters = Array.from(settings.filters);
			if(typeof elementIndex !== 'undefined' && filters.length > 0)
			{
				filters.forEach(function (filter, i) {

					var container = document.createElement('div');
					container.setAttribute('class', 'searchit--filters');
					container.setAttribute('data-element-filters', filter.elementType);

					var selects = Array.from(filter.filters);
					selects.forEach(function (options, i) {

						var wrapper = document.createElement('div');
						wrapper.setAttribute('class', 'select');
						container.appendChild(wrapper);

						var select = document.createElement('select');
						wrapper.appendChild(select);

						Object.keys(options).forEach(function (key) {
							var option = document.createElement('option');
						    option.value = key;
						    option.text = options[key];
						    select.appendChild(option);
						});

					});

					if(!elementFilters.hasOwnProperty(filter.elementType)) {
						elementFilters[filter.elementType] = {};
					}
					elementFilters[filter.elementType][filter.source] = container;
				});

				var toolbar = document.querySelector('.toolbar .flex');
				console.log(toolbar);
				if(toolbar) {
					toolbar.prepend(getElementFilters(elementIndex.elementType, '*'));
				}
			}
		};

		var getElementFilters = function(elementType, source) {
			if(!elementFilters.hasOwnProperty(elementType) || !elementFilters[elementType].hasOwnProperty(source))
			{
				return false;
			}
			return elementFilters[elementType][source];
		}

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

			elementIndex = Craft.elementIndex;

			initElementFilters();
		};

		api.init(options);
		return api;
	};

	return constructor;
})();
