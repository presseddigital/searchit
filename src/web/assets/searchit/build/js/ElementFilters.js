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
		var elementIndexType;
		var elementFilters = {};

		var dom = {
			holder: null,
			toolbar: null,
			search: null,
			searchHolder: null,
		};

		// Private Methods
		// =========================================================================

		var initElementFilters = function() {

			var filters = Array.from(settings.filters);
			filters.forEach(function (filter, filterIndex) {

				var container = document.createElement('div');
				container.setAttribute('class', 'searchit--filters');
				container.setAttribute('data-element-filters', filter.elementType);

				var selects = Array.from(filter.filters);
				selects.forEach(function (options, optionIndex) {

					var wrapper = document.createElement('div');
					wrapper.setAttribute('id', 'element-filter-'+filterIndex+'-'+optionIndex);
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

		var updateElementFilters = function() {

			if(dom.toolbar && elementIndex) {

				var activeFilters = dom.toolbar.querySelector('.searchit--filters');
				if(activeFilters) {
					activeFilters.remove();
				}

				var filters = getElementFilters(elementIndex.elementType, elementIndex.sourceKey);
				if(filters) {
					dom.searchHolder.parentNode.insertBefore(filters, dom.searchHolder);
				}
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

			var searchValue = dom.search.value.trim();
			for (var i = 0; i < filter.options.length; i++) {
				if(filter.options[i].value != '') {
					searchValue = searchValue.replace(filter.options[i].value, '');
				}
		    }

			dom.search.value = (filter.value + ' ' + searchValue).trim();
			tiggerChangeEvent(dom.search);

		};


		// Public Methods
		// =========================================================================

		api.init = function(options) {

			settings = extend(defaults, options || {});

			if (settings.debug) {
				console.log('[ElementFilters][settings]', settings);
			}

			if(!settings.filters.length) {
				return;
			}

			// Get Element Index
			if(typeof Craft.elementIndex !== 'undefined') {
				elementIndex = Craft.elementIndex;
				elementIndexType = 'inline';
			} else {
				var modal = Garnish.Modal.visibleModal;
				if(modal && typeof modal.elementIndex !== 'undefined') {
					elementIndex = modal.elementIndex;
					elementIndexType = 'modal';
				}
			}

			if(!elementIndex) {
				return;
			}

			if (settings.debug) {
				console.log('[ElementFilters][elementIndex]', elementIndexType, elementIndex);
			}

			// Store DOM
			dom.holder = elementIndex.$toolbar[0];
			dom.toolbar = elementIndex.$toolbarFlexContainer[0];
			dom.search = elementIndex.$search[0];
			dom.searchHolder = dom.search.closest('.search');


			initElementFilters();
			dom.holder.addEventListener('change', filterHandler, false);

			// https://craftcms.stackexchange.com/questions/25827/garnish-event-when-changing-category-group?rq=1
			elementIndex.on('selectSource', function() {
				updateElementFilters();
			});
			updateElementFilters();

		};

		api.init(options);
		return api;
	};

	return constructor;
})();
