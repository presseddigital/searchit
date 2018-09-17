var searchits = {};

var Searchit = (function() {
	"use strict";

	var defaults = {
		id: null,
		namespacedId: null,
		name: null,
		debug: false,
	};

	var selectors = {
		searchit: '[data-searchit-searchit]',
		searchitColours: '[data-searchit-searchit-colours]',
		searchitColour: '[data-searchit-searchit-colour]',
		opacity: '[data-searchit-searchit-opacity]',
		custom: '[data-searchit-searchit-custom]',
		customColour: '[data-searchit-searchit-custom-colour]',
	};

	var classes = {
		selectedColour: 'searchit--searchit-colourIsSelected',
	};

	var constructor = function(options) {
		// Public
		// =========================================================================

		var api = {};

		// Private
		// =========================================================================

		var settings;
		var dom = {
			field: null,
			searchit: null,
			paleteColours: null,
			customColour: null,
			handleInput: null,
			opacityInput: null,
			customColourInput: null,
		};

		// Private Methods
		// =========================================================================

		var isValidHex = function(value) {
			return /^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i.test(value);
		};

		var hexToRgb = function(hex) {
		    var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
		    hex = hex.replace(shorthandRegex, function(m, r, g, b) {
		        return r + r + g + g + b + b;
		    });

		    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
		    return result ? {
		        r: parseInt(result[1], 16),
		        g: parseInt(result[2], 16),
		        b: parseInt(result[3], 16)
		    } : null;
		};

		var rgbToHex = function(r, g, b) {
		    return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
		};

		var setOpacity = function(colour) {
			var opacity = dom.opacityInput.value / 100;
			var hex = colour.getAttribute('data-colour');
			if(isValidHex(hex)) {
				var rgb = hexToRgb(hex);
				colour.style.backgroundColor = 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',' + (opacity || 1) + ')';
			}
		};

		var clearSearchitColourSelection = function() {
			dom.searchitColours.forEach(function (colour, index) {
				colour.classList.remove(classes.selectedColour);
	        });
	        dom.handleInput.value = '';
		};

		var clearCustomColourSelection = function(clearValue) {
	        if(clearValue) {
	        	dom.customColourInput.value = '';
	        }
	        if(dom.customColour) {
	        	dom.customColour.setAttribute('data-colour', '');
	        	dom.customColour.style.backgroundColor = null;
	        }
	        if (dom.custom) {
		        dom.custom.classList.remove(classes.selectedColour);
	        }

		};

		// Event Handlers
		// =========================================================================

		var colourHandler = function(event) {

			var colour = event.target.closest(selectors.searchitColour);
			if (!colour) return;

			event.preventDefault();
			event.stopPropagation();

			var isSelected = colour.classList.contains(classes.selectedColour);

			clearSearchitColourSelection();
			clearCustomColourSelection(true);

			if(!isSelected) {
				colour.classList.add(classes.selectedColour);
				dom.handleInput.value = colour.getAttribute('data-handle');
			}
		};

		var customColourHandler = function(event) {

			event.preventDefault();
			event.stopPropagation();

			clearSearchitColourSelection();

			var colour = dom.customColourInput.value;

			dom.handleInput.value = '_custom_';
			if(colour == '')
			{
				dom.handleInput.value = '';
			}

			if(!colour.match('^#') && colour != '#' && colour != '')
			{
				colour = '#' + colour;
				dom.customColourInput.value = colour;
			}

			if(isValidHex(colour)) {

				dom.customColour.setAttribute('data-colour', colour);
				setOpacity(dom.customColour);

				dom.custom.classList.add(classes.selectedColour);
			}
			else
			{
				clearCustomColourSelection();
			}
		};

		var opacityHandler = function(event) {

			event.preventDefault();
			event.stopPropagation();

			dom.searchitColours.forEach(function (colour, index) {
				setOpacity(colour);
	        });

			setOpacity(dom.customColour);

		};

		// Public Methods
		// =========================================================================

		api.init = function(options) {

			settings = extend(defaults, options || {});

			if (settings.debug) {
				console.log("[PALETTE][" + settings.namespacedId + "]", settings);
			}

			dom.field = document.getElementById(settings.namespacedId);
			if(dom.field) {

				dom.searchit = dom.field.querySelector(selectors.searchitColours);
				dom.searchitColours = dom.field.querySelectorAll(selectors.searchitColour);
				if(dom.searchitColours) {
					dom.searchit.addEventListener("click", colourHandler, false);
				}


				dom.handleInput = document.getElementById(settings.namespacedId + '-handle');

				dom.opacityInput = document.getElementById(settings.namespacedId + '-opacity');
				if(dom.opacityInput) {
					dom.opacityInput.addEventListener("change", opacityHandler, false);
					dom.opacityInput.addEventListener("keyup", opacityHandler, false);
				}

				dom.custom = dom.field.querySelector(selectors.custom);
				dom.customColour = dom.field.querySelector(selectors.customColour);
				dom.customColourInput = document.getElementById(settings.namespacedId + '-custom');
				if(dom.customColourInput) {
					dom.customColourInput.addEventListener("keyup", customColourHandler, false);
					dom.customColourInput.addEventListener("focus", customColourHandler, false);
				}

			}
		};

		api.init(options);
		return api;
	};

	return constructor;
})();
