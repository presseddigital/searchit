var ElementFilters = (function() {
	"use strict";

	var defaults = {
		filters: {},
		debug: false,
		attributes: {
			id: 'data-element-filters-id',
			filters: 'data-element-filters',
		}
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
			toolbarHolder: null,
			toolbar: null,
			searchHolder: null,
			search: null,
		};

		// Private Methods
		// =========================================================================

		var initElementFilters = function() {

			// DOM Elements
			dom.toolbarHolder = elementIndex.$toolbar[0];
			dom.toolbar = elementIndex.$toolbarFlexContainer[0];
			dom.search = elementIndex.$search[0];
			dom.searchHolder = dom.search.closest('.search');

			// Filters
			var filters = Array.from(settings.filters);
			filters.forEach(function (filter, filterIndex) {

				var container = document.createElement('div');
				container.setAttribute('class', 'searchit--filters');
				container.setAttribute(settings.attributes.filters, filter.elementType);

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

			// Listeners
			dom.toolbar.addEventListener('change', filterHandler, false);

			elementIndex.on('updateElements', function() {
				checkElementFilters();
			});

			elementIndex.on('selectSource', function() {
				updateElementFilters();
			});

			// Status
			dom.toolbarHolder.setAttribute(settings.attributes.id, settings.id);
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

				var activeFilters = getActiveFilters();
				if(activeFilters) {
					resetFilters(activeFilters);
					activeFilters.remove();
				}

				var filters = getElementFilters(elementIndex.elementType, elementIndex.sourceKey);
				if(filters) {
					dom.searchHolder.parentNode.insertBefore(filters, dom.searchHolder);
				}
			}
		}

		var checkElementFilters = function() {

			console.log('elemens up');

			if(dom.toolbar && elementIndex) {
				var searchValue = dom.search.value;
				var activeFilters = getActiveFilters();
				if(activeFilters) {
					var selects = activeFilters.querySelectorAll('select');
					if(selects) {
						selects.forEach(function (select, index) {
							if(!searchValue.includes(select.value)) {
								select.value = '';
							}
						});
					}
				}
			}
		}

		var resetFilters = function(context) {
			var selects = context.querySelectorAll('select');
			if(selects) {
				selects.forEach(function (select, index) {
					select.value = '';
				});
			}
		}

		var getActiveFilters = function(context) {
			return dom.toolbar.querySelector('.searchit--filters');
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
			var filters = filter.closest('['+settings.attributes.filters+']');
			if (!filters) return;

			event.preventDefault();

			var searchValue = dom.search.value.trim();
			for (var i = 0; i < filter.options.length; i++) {
				if(filter.options[i].value != '') {
					searchValue = searchValue.replace(filter.options[i].value, '');
				}
		    }

			dom.search.value = (filter.value + ' ' + searchValue).replace(/  +/g, ' ').trim();
			tiggerChangeEvent(dom.search);

		};

		var searchHandler = function(event) {

			var search = event.target;
			if (!search.closest('.search')) return;

			console.log('SEARCH CHANGE');


			// event.preventDefault();

			// var searchValue = dom.search.value.trim();
			// for (var i = 0; i < filter.options.length; i++) {
			// 	if(filter.options[i].value != '') {
			// 		searchValue = searchValue.replace(filter.options[i].value, '');
			// 	}
		 //    }

			// dom.search.value = (filter.value + ' ' + searchValue).replace(/  +/g, ' ').trim();
			// tiggerChangeEvent(dom.search);

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

			if(elementIndex.$toolbar[0].hasAttribute(settings.attributes.id)){
				return;
			};

			initElementFilters();
			updateElementFilters();
		};

		api.init(options);
		return api;
	};

	return constructor;
})();
