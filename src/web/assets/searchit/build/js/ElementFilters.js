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
		var baseCriteria;

		var elementIndexPreview;

		var elementIndex;
		var elementIndexType;
		var elementFilters = {};

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

			// Filters
			prepFilters();

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

			// Filters
			prepFilters()

			// Update
			updateElementFilterPreview();
		};

		var prepFilters = function() {
			var filters = Array.from(settings.filters);
			filters.forEach(function (filter, filterIndex) {

				var container = document.createElement('div');
				container.setAttribute('class', 'searchit--filters');
				container.setAttribute(settings.attributes.filters, filter.elementType);

				var _filters = Array.from(filter.filters);
				_filters.forEach(function (_filter, optionIndex) {

					var wrapper = document.createElement('div');
					wrapper.setAttribute('id', 'element-filter-'+filterIndex+'-'+optionIndex);
					wrapper.setAttribute('class', 'searchit--filter');
					wrapper.setAttribute('data-filter-'+_filter.type, '');
					container.appendChild(wrapper);

					switch(_filter.type)
					{
						case('select'):
							wrapper.classList.add('select');

							var select = document.createElement('select');
							wrapper.appendChild(select);

							console.log(select, wrapper, container);

							Object.keys(_filter.options).forEach(function (key) {
								var option = document.createElement('option');
							    option.value = key;
							    option.text = _filter.options[key];
							    select.appendChild(option);
							});

							break;

						case('date'):
							wrapper.classList.add('datewrapper', 'texticon', 'icon');
							wrapper.setAttribute('data-icon', 'date');

							var input = document.createElement('input');
							input.setAttribute('type', 'text');
							input.setAttribute('class', 'text');
							input.setAttribute('autocomplete', 'off');
							wrapper.appendChild(input);

							break;
					}
				});

				// // Temp date
				// var date = document.createElement('span');
				// date.innerHTML = '<div class="searchit--filter datewrapper texticon icon" data-icon="date" data-filter-date><input data-filter-date-from type="text" name="postDate[from]" class="text" size="20" autocomplete="off" /></div>';
				// container.appendChild(date.firstChild);

				if(!elementFilters.hasOwnProperty(filter.elementType)) {
					elementFilters[filter.elementType] = {};
				}
				elementFilters[filter.elementType][filter.source] = container;
			});
		}

		var handleDateChange = function(event) {
			console.log('handleDateChange', event);
		}

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

				var filters  = getElementFilters(elementIndex.elementType, elementIndex.sourceKey);
				if(filters) {
					dom.search.parentNode.insertBefore(filters, dom.search);

					var $picker = $(filters.querySelector('input'));
					$picker.datepicker($.extend({
		                onSelect: function(event) {
		                	handleDateChange(event);
		                }
		            }, Craft.datepickerOptions));

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
				console.log('[ElementFilters][resetFilters]', elementIndex.settings.criteria);
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

			if(!settings.filters.length) {
				return;
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
