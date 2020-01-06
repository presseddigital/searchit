var ElementFilters = (function() {
	"use strict";

	var defaults = {
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
		var baseCriteria;

		var elementIndexPreview;

		var elementIndex;
		var elementIndexType;
		var elementFilters = {};
		var elementFilterCount = 0;

		var dom = {
			preview: null,
			toolbar: null,
			search: null,
		};

		// Private Methods
		// =========================================================================

		var initElementFilters = function() {

			// Copy Criteria
			baseCriteria = copy(elementIndex.settings.criteria);

			// DOM Elements
			dom.toolbar = elementIndex.$toolbar[0];
			dom.search = elementIndex.$search[0].closest('.search');

			// Listeners
			dom.toolbar.addEventListener('change', filterHandler, false);

			elementIndex.on('selectSource', function() {
				updateElementFilters();
			});

			// Status
			dom.toolbar.setAttribute(settings.attributes.id, settings.id);

			// Update
			updateElementFilters();
		};

		var initElementFilterPreview = function() {

			if (!elementIndexPreview) {
				return;
			}

			// DOM Elements
			dom.preview = elementIndexPreview;
			dom.search = dom.preview.querySelector('.search');

			// Update
			updateElementFilterPreview();
		};


		var prepFilters = function(filter) {

			if(filter && (!elementFilters.hasOwnProperty(filter.elementType) || !elementFilters[filter.elementType].hasOwnProperty(filter.source)))
			{
				var container = document.createElement('div');
				container.setAttribute('class', 'searchit--filters');
				container.setAttribute(settings.attributes.filters, filter.elementType);

				var selects = Array.from(filter.filters);
				selects.forEach(function (options, optionIndex) {

					var wrapper = document.createElement('div');
					elementFilterCount = elementFilterCount + 1;
					wrapper.setAttribute('id', 'element-filter-'+elementFilterCount+'-'+optionIndex);
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

				storeFilters(filter.elementType, filter.source, container);
			}
		}

		var storeFilters = function(elementType, source, value) {
			if(!elementFilters.hasOwnProperty(elementType)) {
				elementFilters[elementType] = {};
			}
			elementFilters[elementType][source] = value;
		};

		var getElementFilters = function(elementType, source) {

			if(elementFilters.hasOwnProperty(elementType) && elementFilters[elementType].hasOwnProperty(source))
			{
				return elementFilters[elementType][source];
			}

			var xhr = new XMLHttpRequest();
			xhr.open("POST", Craft.baseCpUrl, true);
			xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			xhr.setRequestHeader("Accept", "application/json");

			xhr.onreadystatechange = function() {

				if (xhr.readyState !== 4) return;
				if (xhr.status === 200) {

					var response = parseIfJson(xhr.response);
					var filters = response.filters || false;
					if(filters) {
						prepFilters(filters);
						updateElementFilters();
						updateElementFilterPreview();
					} else {
						storeFilters(elementType, source, false);
					}
				}
			};

			var formData = new FormData();
			formData.append("action", "searchit/element-filters/get");
			formData.append("type", elementType);
			formData.append("source", source);
			formData.append(settings.csrfTokenName, settings.csrfTokenValue);

			xhr.responseType = "json";
			xhr.send(formData);
		}

		var parseIfJson = function parseIfJson(value) {
		    try {
		        var parsed = JSON.parse(value);
		        return parsed;
		    } catch (e) {
		        return value;
		    }
		}

		var updateElementFilters = function() {

			if(dom.toolbar && elementIndex) {

				var activeFilters = getActiveFilters();
				if(activeFilters) {
					resetFilters(activeFilters);
					activeFilters.remove();
				}

				var filters  = getElementFilters(elementIndex.elementType, elementIndex.sourceKey);
				if(filters) {
					dom.search.parentNode.insertBefore(filters, dom.search);
				}
			}
		}

		var updateElementFilterPreview = function() {

			if(dom.preview) {

				var filters = getElementFilters(dom.preview.getAttribute('data-type'), dom.preview.getAttribute('data-source'));
				if(filters) {
					dom.search.parentNode.insertBefore(filters, dom.search);
				}
			}
		}

		var getActiveFilters = function(context) {
			return dom.toolbar.querySelector('.searchit--filters');
		}

		var resetFilters = function(context) {
			var selects = context.querySelectorAll('select');
			if(selects) {
				selects.forEach(function (select, index) {
					select.value = '';
				});
				resetCriteria();
				if(settings.debug) {
					console.log('[ElementFilters][resetFilters]', elementIndex.settings.criteria);
				}
			}
		}

		var resetCriteria = function() {
			elementIndex.settings.criteria = copy(baseCriteria);
		}

		var updateCriteria = function(values) {

			if(trueTypeOf(values) !== 'array') return;

			values.forEach(function (value, index) {
				updateCriteriaValue(value);
			});

			if(settings.debug) {
				console.log('[ElementFilters][updateCriteria]', elementIndex.settings.criteria);
			}
		}

		var updateCriteriaValue = function(criteriaValue) {

			criteriaValue = JSON.parse(criteriaValue);

			if(trueTypeOf(criteriaValue) === 'string') {
				criteriaValue = {
					search: criteriaValue
				}
			}

			Object.keys(criteriaValue).forEach(function (key, index) {

				var _newCriteria;
				var _existingCriteriaValue = elementIndex.settings.criteria.hasOwnProperty(key) ? copy(elementIndex.settings.criteria[key]) : false;

				switch(key) {
					case('relatedTo'):
						_newCriteria = prepCriteriaValue(criteriaValue[key], _existingCriteriaValue);
						break;
					default:
						_newCriteria = criteriaValue[key];
						break;
				}

				elementIndex.settings.criteria[key] = _newCriteria;

			});
		}

		var prepCriteriaValue = function(newValue, existingValue) {

			if(!existingValue) return newValue;

			if(trueTypeOf(existingValue) === 'array' && existingValue[0] !== 'and') {
				return existingValue.push(newValue);
			} else {
				return ['and', existingValue, newValue];
			}
		}

		// Event Handlers
		// =========================================================================

		var filterHandler = function(event) {

			var holder = event.target.closest('['+settings.attributes.filters+']');
			if(!holder) return;
			var selects = holder.querySelectorAll('select');
			if(!selects) return;

			event.preventDefault();

			resetCriteria();

			var values = [];
			selects.forEach(function (select, index) {
				if(select.value != '') {
					values.push(select.value)
				}
			});
			if(values.length > 0) {
				updateCriteria(values);
			}
			elementIndex.updateElements();

		};

		// Public Methods
		// =========================================================================

		api.init = function(options) {

			settings = extend(defaults, options || {});

			if (settings.debug) {
				console.log('[ElementFilters][settings]', settings);
			}

			// Preview
			elementIndexPreview = document.querySelector('[data-element-filter-preview]');
			if (elementIndexPreview) {
				initElementFilterPreview();
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

			if(!elementIndex) return;

			if (settings.debug) {
				console.log('[ElementFilters][elementIndex]', elementIndexType, elementIndex);
			}

			if(elementIndex.$toolbar[0].hasAttribute(settings.attributes.id)) return;

			initElementFilters();
		};

		api.init(options);
		return api;
	};

	return constructor;
})();
