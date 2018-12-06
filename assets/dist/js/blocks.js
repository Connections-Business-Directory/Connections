/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 327);
/******/ })
/************************************************************************/
/******/ ({

/***/ 327:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__directory_index_js__ = __webpack_require__(328);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__upcoming_index_js__ = __webpack_require__(331);
/**
 * Import the blocks
 */



/***/ }),

/***/ 328:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__styles_editor_scss__ = __webpack_require__(329);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__styles_editor_scss___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__styles_editor_scss__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__styles_public_scss__ = __webpack_require__(330);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__styles_public_scss___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__styles_public_scss__);
var _wp$i18n = wp.i18n,
    __ = _wp$i18n.__,
    _n = _wp$i18n._n,
    _nx = _wp$i18n._nx,
    _x = _wp$i18n._x;
var registerBlockType = wp.blocks.registerBlockType;
var _wp$editor = wp.editor,
    InspectorControls = _wp$editor.InspectorControls,
    InspectorAdvancedControls = _wp$editor.InspectorAdvancedControls;
var _wp$components = wp.components,
    ServerSideRender = _wp$components.ServerSideRender,
    PanelBody = _wp$components.PanelBody,
    TextControl = _wp$components.TextControl,
    ToggleControl = _wp$components.ToggleControl;

// Import CSS




/**
 * Register Block
 */
/* unused harmony default export */ var _unused_webpack_default_export = (registerBlockType('connections-directory/shortcode-connections', {
	title: __('Directory', 'connections'),
	description: __('Display the Connections Business Directory.', 'connections'),
	category: 'connections-directory',
	// icon:        giveLogo,
	keywords: ['connections', __('directory', 'connections')],
	supports: {
		// Remove the support for the generated className.
		className: false,
		// Remove the support for the custom className.
		customClassName: false,
		// Remove the support for editing the block using the block HTML editor.
		html: false
	},
	attributes: {
		advancedBlockOptions: {
			type: 'string',
			default: ''
		},
		characterIndex: {
			type: 'boolean',
			default: true
		},
		isEditorPreview: {
			type: 'boolean',
			default: true
		},
		repeatCharacterIndex: {
			type: 'boolean',
			default: false
		},
		sectionHead: {
			type: 'boolean',
			default: false
		}
	},
	edit: function edit(_ref) {
		var attributes = _ref.attributes,
		    setAttributes = _ref.setAttributes;
		var advancedBlockOptions = attributes.advancedBlockOptions,
		    characterIndex = attributes.characterIndex,
		    repeatCharacterIndex = attributes.repeatCharacterIndex,
		    sectionHead = attributes.sectionHead;


		return [wp.element.createElement(
			InspectorControls,
			null,
			wp.element.createElement(
				PanelBody,
				{ title: __('Settings', 'connections') },
				wp.element.createElement(ToggleControl, {
					label: __('Display Character Index?', 'connections'),
					help: __('Display the A-Z index above the directory.', 'connections'),
					checked: !!characterIndex,
					onChange: function onChange() {
						return setAttributes({ characterIndex: !characterIndex });
					}
				}),
				wp.element.createElement(ToggleControl, {
					label: __('Repeat Character Index?', 'connections'),
					help: __('Repeat the Character Index at the beginning of each character group.', 'connections'),
					checked: !!repeatCharacterIndex,
					onChange: function onChange() {
						return setAttributes({ repeatCharacterIndex: !repeatCharacterIndex });
					}
				}),
				wp.element.createElement(ToggleControl, {
					label: __('Display Current Character Heading?', 'connections'),
					help: __('Display the current character heading at the beginning of each character group.', 'connections'),
					checked: !!sectionHead,
					onChange: function onChange() {
						return setAttributes({ sectionHead: !sectionHead });
					}
				})
			)
		), wp.element.createElement(
			InspectorAdvancedControls,
			null,
			wp.element.createElement(TextControl, {
				label: __('Additional Options', 'connections'),
				value: advancedBlockOptions,
				onChange: function onChange(newValue) {
					setAttributes({
						advancedBlockOptions: newValue
					});
				}
			})
		), wp.element.createElement(ServerSideRender, {
			attributes: attributes,
			block: 'connections-directory/shortcode-connections'
		})];
	},
	save: function save() {
		// Server side rendering via shortcode.
		return null;
	}
}));

/***/ }),

/***/ 329:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 330:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 331:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__components_range_contol_js__ = __webpack_require__(332);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__styles_editor_scss__ = __webpack_require__(334);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__styles_editor_scss___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__styles_editor_scss__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__styles_public_scss__ = __webpack_require__(335);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__styles_public_scss___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2__styles_public_scss__);
var _wp$i18n = wp.i18n,
    __ = _wp$i18n.__,
    _n = _wp$i18n._n,
    _nx = _wp$i18n._nx,
    _x = _wp$i18n._x;
var registerBlockType = wp.blocks.registerBlockType;
var _wp$editor = wp.editor,
    InspectorControls = _wp$editor.InspectorControls,
    InspectorAdvancedControls = _wp$editor.InspectorAdvancedControls;
var _wp$components = wp.components,
    ExternalLink = _wp$components.ExternalLink,
    PanelBody = _wp$components.PanelBody,
    RadioControl = _wp$components.RadioControl,
    SelectControl = _wp$components.SelectControl,
    ServerSideRender = _wp$components.ServerSideRender,
    TextControl = _wp$components.TextControl,
    ToggleControl = _wp$components.ToggleControl;

// Import components



// Import CSS



var dateTypes = cbDir.blockSettings.dateTypes;

/**
 * Register Block
 */

/* unused harmony default export */ var _unused_webpack_default_export = (registerBlockType('connections-directory/shortcode-upcoming', {
	title: __('Upcoming', 'connections'),
	description: __('Display the list of upcoming event dates.', 'connections'),
	category: 'connections-directory',
	// icon:        giveLogo,
	keywords: ['connections', __('directory', 'connections'), __('upcoming', 'connections')],
	supports: {
		// Remove the support for the generated className.
		className: false,
		// Remove the support for the custom className.
		customClassName: false,
		// Remove the support for editing the block using the block HTML editor.
		html: false
	},
	attributes: {
		advancedBlockOptions: {
			type: 'string',
			default: ''
		},
		displayLastName: {
			type: 'boolean',
			default: false
		},
		dateFormat: {
			type: 'string',
			default: 'F jS'
		},
		days: {
			type: 'integer',
			default: 30
		},
		heading: {
			type: 'string',
			default: ''
		},
		includeToday: {
			type: 'boolean',
			default: true
		},
		isEditorPreview: {
			type: 'boolean',
			default: true
		},
		listType: {
			type: 'string',
			default: 'birthday'
		},
		template: {
			type: 'string',
			default: 'anniversary-light'
		},
		noResults: {
			type: 'string',
			default: __('No results.', 'connections')
		},
		yearFormat: {
			type: 'string',
			default: '%y ' + __('Year(s)', 'connections')
		},
		yearType: {
			type: 'string',
			default: 'upcoming'
		}
	},
	edit: function edit(_ref) {
		var attributes = _ref.attributes,
		    setAttributes = _ref.setAttributes;
		var advancedBlockOptions = attributes.advancedBlockOptions,
		    displayLastName = attributes.displayLastName,
		    dateFormat = attributes.dateFormat,
		    days = attributes.days,
		    heading = attributes.heading,
		    includeToday = attributes.includeToday,
		    listType = attributes.listType,
		    template = attributes.template,
		    noResults = attributes.noResults,
		    yearFormat = attributes.yearFormat,
		    yearType = attributes.yearType;


		var dateTypeSelectOptions = [];

		for (var property in dateTypes) {

			// noinspection JSUnfilteredForInLoop
			dateTypeSelectOptions.push({
				label: dateTypes[property],
				value: property
			});
		}

		return [wp.element.createElement(
			InspectorControls,
			null,
			wp.element.createElement(
				PanelBody,
				{ title: __('Settings', 'connections') },
				wp.element.createElement(SelectControl, {
					label: __('Type', 'connections'),
					value: listType,
					options: dateTypeSelectOptions,
					onChange: function onChange(listType) {
						return setAttributes({ listType: listType });
					}
				}),
				wp.element.createElement(SelectControl, {
					label: __('Style', 'connections'),
					value: template,
					options: [{ label: 'Light', value: 'anniversary-light' }, { label: 'Dark', value: 'anniversary-dark' }],
					onChange: function onChange(template) {
						return setAttributes({ template: template });
					}
				}),
				wp.element.createElement(TextControl, {
					label: __('Heading', 'connections'),
					help: __('Type %d to insert the number of days in the heading.', 'connections'),
					placeholder: __('Type the heading here.', 'connections'),
					value: heading,
					onChange: function onChange(newValue) {
						setAttributes({
							heading: newValue
						});
					}
				}),
				wp.element.createElement(ToggleControl, {
					label: __('Display last name?', 'connections'),
					checked: !!displayLastName,
					onChange: function onChange() {
						return setAttributes({ displayLastName: !displayLastName });
					}
				}),
				wp.element.createElement(__WEBPACK_IMPORTED_MODULE_0__components_range_contol_js__["a" /* RangeControl */], {
					label: __('The number of days ahead to display.', 'connections'),
					help: __('To display date events for today only, slide the slider to 0 and enable the Include today option.', 'connections'),
					value: days,
					onChange: function onChange(days) {
						return setAttributes({ days: days });
					},
					min: 0,
					max: 90,
					allowReset: true,
					initialPosition: 30
				}),
				wp.element.createElement(ToggleControl, {
					label: __('Include today?', 'connections'),
					help: __('Whether or not to include the date events for today.', 'connections'),
					checked: !!includeToday,
					onChange: function onChange() {
						return setAttributes({ includeToday: !includeToday });
					}
				}),
				wp.element.createElement(RadioControl, {
					label: __('Year Display', 'connections')
					// help={__( '', 'connections' )}
					, selected: yearType,
					options: [{ label: __('Original Year', 'connections'), value: 'original' }, { label: __('Upcoming Year', 'connections'), value: 'upcoming' }, { label: __('Years Since', 'connections'), value: 'since' }],
					onChange: function onChange(newValue) {
						setAttributes({
							yearType: newValue
						});
					}
				}),
				wp.element.createElement(TextControl, {
					label: __('No Results Notice', 'connections'),
					help: __('This message is displayed when there are no upcoming event dates within the specified number of days.', 'connections'),
					placeholder: __('Type the no result message here.', 'connections'),
					value: noResults,
					onChange: function onChange(newValue) {
						setAttributes({
							noResults: newValue
						});
					}
				})
			)
		), wp.element.createElement(
			InspectorAdvancedControls,
			null,
			wp.element.createElement(TextControl, {
				label: __('Date Format', 'connections'),
				help: wp.element.createElement(
					ExternalLink,
					{
						href: 'https://codex.wordpress.org/Formatting_Date_and_Time',
						target: '_blank'
					},
					__('Documentation on date and time formatting.', 'connections')
				),
				value: dateFormat,
				onChange: function onChange(newValue) {
					setAttributes({
						dateFormat: newValue
					});
				}
			}),
			wp.element.createElement(TextControl, {
				label: __('Years Since Format', 'connections'),
				help: wp.element.createElement(
					ExternalLink,
					{
						href: 'http://php.net/manual/en/dateinterval.format.php',
						target: '_blank'
					},
					__('Documentation on date interval formatting.', 'connections')
				),
				value: yearFormat,
				onChange: function onChange(newValue) {
					setAttributes({
						yearFormat: newValue
					});
				}
			}),
			wp.element.createElement(TextControl, {
				label: __('Additional Options', 'connections'),
				value: advancedBlockOptions,
				onChange: function onChange(newValue) {
					setAttributes({
						advancedBlockOptions: newValue
					});
				}
			})
		), wp.element.createElement(ServerSideRender, {
			attributes: attributes,
			block: 'connections-directory/shortcode-upcoming'
		})];
	},
	save: function save() {
		// Server side rendering via shortcode.
		return null;
	}
}));

/***/ }),

/***/ 332:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return asInstance; });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_classnames__ = __webpack_require__(333);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_classnames___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_classnames__);
var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

function _objectWithoutProperties(obj, keys) { var target = {}; for (var i in obj) { if (keys.indexOf(i) >= 0) continue; if (!Object.prototype.hasOwnProperty.call(obj, i)) continue; target[i] = obj[i]; } return target; }

/**
 * External dependencies
 */

var _lodash = lodash,
    isFinite = _lodash.isFinite;

/**
 * WordPress dependencies
 */

var __ = wp.i18n.__;
var withInstanceId = wp.compose.withInstanceId;

/**
 * Internal dependencies
 */

var _wp$components = wp.components,
    BaseControl = _wp$components.BaseControl,
    Button = _wp$components.Button,
    Dashicon = _wp$components.Dashicon;


function RangeControl(_ref) {
	var className = _ref.className,
	    label = _ref.label,
	    value = _ref.value,
	    instanceId = _ref.instanceId,
	    onChange = _ref.onChange,
	    beforeIcon = _ref.beforeIcon,
	    afterIcon = _ref.afterIcon,
	    help = _ref.help,
	    allowReset = _ref.allowReset,
	    initialPosition = _ref.initialPosition,
	    props = _objectWithoutProperties(_ref, ['className', 'label', 'value', 'instanceId', 'onChange', 'beforeIcon', 'afterIcon', 'help', 'allowReset', 'initialPosition']);

	var id = 'inspector-range-control-' + instanceId;
	var resetValue = function resetValue() {
		return onChange();
	};
	var onChangeValue = function onChangeValue(event) {
		var newValue = event.target.value;
		if (newValue === '') {
			resetValue();
			return;
		}
		onChange(Number(newValue));
	};
	var initialSliderValue = isFinite(value) ? value : initialPosition || '';

	return wp.element.createElement(
		BaseControl,
		{
			label: label,
			id: id,
			help: help,
			className: __WEBPACK_IMPORTED_MODULE_0_classnames___default()('components-range-control', className)
		},
		beforeIcon && wp.element.createElement(Dashicon, { icon: beforeIcon }),
		wp.element.createElement('input', _extends({
			className: 'components-range-control__slider',
			id: id,
			type: 'range',
			value: initialSliderValue,
			onChange: onChangeValue,
			'aria-describedby': !!help ? id + '__help' : undefined
		}, props)),
		afterIcon && wp.element.createElement(Dashicon, { icon: afterIcon }),
		wp.element.createElement('input', _extends({
			className: 'components-range-control__number',
			type: 'number',
			onChange: onChangeValue,
			'aria-label': label,
			value: initialSliderValue
		}, props)),
		allowReset && wp.element.createElement(
			Button,
			{ onClick: resetValue, disabled: value === undefined },
			__('Reset')
		)
	);
}

var asInstance = withInstanceId(RangeControl);


// export default withInstanceId( RangeControl );

/***/ }),

/***/ 333:
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/*!
  Copyright (c) 2017 Jed Watson.
  Licensed under the MIT License (MIT), see
  http://jedwatson.github.io/classnames
*/
/* global define */

(function () {
	'use strict';

	var hasOwn = {}.hasOwnProperty;

	function classNames () {
		var classes = [];

		for (var i = 0; i < arguments.length; i++) {
			var arg = arguments[i];
			if (!arg) continue;

			var argType = typeof arg;

			if (argType === 'string' || argType === 'number') {
				classes.push(arg);
			} else if (Array.isArray(arg) && arg.length) {
				var inner = classNames.apply(null, arg);
				if (inner) {
					classes.push(inner);
				}
			} else if (argType === 'object') {
				for (var key in arg) {
					if (hasOwn.call(arg, key) && arg[key]) {
						classes.push(key);
					}
				}
			}
		}

		return classes.join(' ');
	}

	if (typeof module !== 'undefined' && module.exports) {
		classNames.default = classNames;
		module.exports = classNames;
	} else if (true) {
		// register as 'classnames', consistent with npm package name
		!(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_RESULT__ = (function () {
			return classNames;
		}).apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
	} else {
		window.classNames = classNames;
	}
}());


/***/ }),

/***/ 334:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 335:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ })

/******/ });
//# sourceMappingURL=blocks.js.map