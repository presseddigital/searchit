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

		};

		var getElementFilters = function(elementType, source) {

			if(!elementFilters.hasOwnProperty(elementType) || !elementFilters[elementType].hasOwnProperty(source))
			{
				return false;
			}
			return elementFilters[elementType][source];

		}

		var renderElementFilters = function() {

			var toolbar = document.querySelector('.toolbar .flex');
			if(toolbar && elementIndex) {
				toolbar.prepend(getElementFilters(elementIndex.elementType, elementIndex.sourceKey));
			}


		}

		var tiggerChangeEvent = function (element)
		{
			if ("createEvent" in document) {
			    var evt = document.createEvent("HTMLEvents");
			    evt.initEvent("change", false, true);
			    element.dispatchEvent(evt);
			}
			else
			{
			    element.fireEvent("onchange");
			}
		}

		// Event Handlers
		// =========================================================================

		var filterHandler = function(event) {

			var filter = event.target;
			var filters = filter.closest('[data-element-filters]');
			if (!filters) return;

			event.preventDefault();

			var toolbar = filter.closest('.toolbar');
			var search = toolbar.querySelector('.search input');

			var searchValue = search.value.trim();

			for (var i = 0; i < filter.options.length; i++) {
				if(filter.options[i].value != '') {
					searchValue = searchValue.replace(filter.options[i].value, '');
				}
		    }

			search.value = (filter.value + ' ' + searchValue).trim();
			tiggerChangeEvent(search);

		};


		// Public Methods
		// =========================================================================

		api.init = function(options) {

			settings = extend(defaults, options || {});

			if (settings.debug) {
				console.log('[SearchFilters]', settings);
			}

			elementIndex = Craft.elementIndex;
			if(typeof elementIndex !== 'undefined' && settings.filters.length > 0)
			{
				initElementFilters();
				document.addEventListener('change', filterHandler, false);
				renderElementFilters();
			}

		};

		api.init(options);
		return api;
	};

	return constructor;
})();
