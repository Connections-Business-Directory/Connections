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
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
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
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./includes/blocks/blocks.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./includes/blocks/blocks.js":
/*!***********************************!*\
  !*** ./includes/blocks/blocks.js ***!
  \***********************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _store_entries__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./store/entries */ "./includes/blocks/store/entries.js");
/* harmony import */ var _store_categories_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./store/categories.js */ "./includes/blocks/store/categories.js");
/* harmony import */ var _carousel__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./carousel */ "./includes/blocks/carousel/index.js");
/* harmony import */ var _directory_index_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./directory/index.js */ "./includes/blocks/directory/index.js");
/* harmony import */ var _team___WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./team/ */ "./includes/blocks/team/index.js");
/* harmony import */ var _upcoming_index_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./upcoming/index.js */ "./includes/blocks/upcoming/index.js");
/**
 * Register the data stores.
 */


/**
 * Import the blocks
 */






/***/ }),

/***/ "./includes/blocks/carousel/edit.js":
/*!******************************************!*\
  !*** ./includes/blocks/carousel/edit.js ***!
  \******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/extends */ "./node_modules/@babel/runtime/helpers/extends.js");
/* harmony import */ var _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/assertThisInitialized */ "./node_modules/@babel/runtime/helpers/assertThisInitialized.js");
/* harmony import */ var _babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _babel_runtime_helpers_objectDestructuringEmpty__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @babel/runtime/helpers/objectDestructuringEmpty */ "./node_modules/@babel/runtime/helpers/objectDestructuringEmpty.js");
/* harmony import */ var _babel_runtime_helpers_objectDestructuringEmpty__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_objectDestructuringEmpty__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var react_slick__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! react-slick */ "./node_modules/react-slick/lib/index.js");
/* harmony import */ var react_slick__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(react_slick__WEBPACK_IMPORTED_MODULE_9__);
/* harmony import */ var slick_carousel_slick_slick_css__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! slick-carousel/slick/slick.css */ "./node_modules/slick-carousel/slick/slick.css");
/* harmony import */ var slick_carousel_slick_slick_css__WEBPACK_IMPORTED_MODULE_10___default = /*#__PURE__*/__webpack_require__.n(slick_carousel_slick_slick_css__WEBPACK_IMPORTED_MODULE_10__);
/* harmony import */ var slick_carousel_slick_slick_theme_css__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! slick-carousel/slick/slick-theme.css */ "./node_modules/slick-carousel/slick/slick-theme.css");
/* harmony import */ var slick_carousel_slick_slick_theme_css__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(slick_carousel_slick_slick_theme_css__WEBPACK_IMPORTED_MODULE_11__);
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! lodash */ "lodash");
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_12___default = /*#__PURE__*/__webpack_require__.n(lodash__WEBPACK_IMPORTED_MODULE_12__);
/* harmony import */ var _Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! @Connections-Directory/components */ "./includes/blocks/components/index.js");
/* harmony import */ var _Connections_Directory_components_utility__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! @Connections-Directory/components/utility */ "./includes/blocks/components/utility/index.js");
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! ./style.scss */ "./includes/blocks/carousel/style.scss");
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_15___default = /*#__PURE__*/__webpack_require__.n(_style_scss__WEBPACK_IMPORTED_MODULE_15__);










function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_1___default()(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function () { var Super = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_7___default()(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_7___default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_6___default()(this, result); }; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

/**
 * WordPress dependencies
 */
var _wp = wp,
    apiFetch = _wp.apiFetch;
var _wp$i18n = wp.i18n,
    __ = _wp$i18n.__,
    _n = _wp$i18n._n,
    _nx = _wp$i18n._nx,
    _x = _wp$i18n._x; // const { registerBlockType } = wp.blocks;

var _wp$blockEditor = wp.blockEditor,
    ColorPalette = _wp$blockEditor.ColorPalette,
    InspectorControls = _wp$blockEditor.InspectorControls,
    InspectorAdvancedControls = _wp$blockEditor.InspectorAdvancedControls;
var _wp$components = wp.components,
    BaseControl = _wp$components.BaseControl,
    ColorIndicator = _wp$components.ColorIndicator,
    RadioControl = _wp$components.RadioControl,
    PanelBody = _wp$components.PanelBody,
    SelectControl = _wp$components.SelectControl,
    Spinner = _wp$components.Spinner,
    TextControl = _wp$components.TextControl,
    ToggleControl = _wp$components.ToggleControl;
var _wp$compose = wp.compose,
    compose = _wp$compose.compose,
    withInstanceId = _wp$compose.withInstanceId;
var _wp$data = wp.data,
    select = _wp$data.select,
    withDispatch = _wp$data.withDispatch,
    withSelect = _wp$data.withSelect;

_babel_runtime_helpers_objectDestructuringEmpty__WEBPACK_IMPORTED_MODULE_8___default()(wp.editor);

var _wp$element = wp.element,
    Component = _wp$element.Component,
    Fragment = _wp$element.Fragment; // const { decodeEntities } = wp.htmlEntities;

var addQueryArgs = wp.url.addQueryArgs;
/**
 * External dependencies
 */
// import { CarouselProvider, DotGroup, Slider, Slide, ButtonBack, ButtonNext } from 'pure-react-carousel';
// import 'pure-react-carousel/dist/react-carousel.es.css';




/**
 * External dependencies
 */


/**
 * Internal dependencies
 */




/**
 * Import styles.
 */


var entryTypes = cbDir.blockSettings.entryTypes;
var ENDPOINT = '/cn-api/v1/entry/';

var colorIndicator = function colorIndicator(label, value) {
  return wp.element.createElement(Fragment, null, wp.element.createElement("p", null, label, value && wp.element.createElement(ColorIndicator, {
    colorValue: value,
    style: {
      background: value,
      verticalAlign: 'bottom'
    }
  })));
};

var Carousel = /*#__PURE__*/function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_5___default()(Carousel, _Component);

  var _super = _createSuper(Carousel);

  /**
   * Constructor for the Carousel Component.
   *
   * Sets up state, and creates bindings for functions.
   *
   * @param {object} props Component properties.
   */
  function Carousel(props) {
    var _this;

    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_2___default()(this, Carousel);

    // super( ...arguments );
    _this = _super.call(this, props); // console.log( this.props.name, ": constructor()" );
    // console.log( 'constructor()::arguments ', arguments );
    // console.log( 'constructor()::props ', props );
    // console.log( 'constructor()::this.props ', this.props );

    var _this$props = _this.props,
        blockId = _this$props.attributes.blockId,
        metaCarousels = _this$props.metaCarousels,
        clientId = _this$props.clientId,
        setAttributes = _this$props.setAttributes;
    _this.getIndex = _this.getIndex.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4___default()(_this));
    _this.findIndex = _this.findIndex.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4___default()(_this));
    _this.getAttribute = _this.getAttribute.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4___default()(_this));
    _this.setAttributes = _this.setAttributes.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4___default()(_this));
    _this.prepareQueryArgs = _this.prepareQueryArgs.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4___default()(_this));
    _this.fetchAPI = _this.fetchAPI.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4___default()(_this));
    _this.fetchEntries = _this.fetchEntries.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4___default()(_this)); // this.isNumber = this.isNumber.bind( this );

    var id = Object(lodash__WEBPACK_IMPORTED_MODULE_12__["isUndefined"])(blockId) ? clientId : blockId;
    var blocks = JSON.parse(metaCarousels);

    var index = _this.findIndex(id, blocks);
    /**
     * Slider key state will change when toggling autoPlay.
     *
     * If autoplay is changed state will be updated in componentDidUpdate().
     *
     * @link https://github.com/akiran/react-slick/issues/1634
     */


    _this.state = {
      blockId: id,
      blockIndex: index,
      queryResults: [],
      isLoading: true,
      sliderKey: new Date().getTime() / 1000
    }; // console.log( 'constructor()::this.state ', this.state );
    // console.log( 'constructor()::metaCarousels ', metaCarousels );

    setAttributes({
      blockId: id
    });
    return _this;
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_3___default()(Carousel, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      // console.log( this.props.name, ': componentDidMount()' );
      var index = this.getIndex();

      if (-1 === index) {
        this.setAttributes({
          listType: 'all'
        });
      } // Add `style` tag to page header for block styles.


      var styleTag = document.createElement('style');
      styleTag.setAttribute('id', 'slick-slider-block-' + this.state.blockId);
      document.head.appendChild(styleTag);
      this.fetchEntries();
    }
    /**
     * @param {object} prevProps
     * @param {object} prevState
     */

  }, {
    key: "componentDidUpdate",
    value: function componentDidUpdate(prevProps, prevState) {
      // console.log( this.props.name, ': componentDidUpdate()' );
      // console.log( 'componentDidUpdate()::prevProps ', prevProps );
      // console.log( 'componentDidUpdate()::props ', this.props );
      var prevMetaCarousels = prevProps.metaCarousels; // console.log( 'componentDidUpdate()::prevMetaCarousels ', prevMetaCarousels );

      var metaCarousels = this.props.metaCarousels;
      var prevBlocks = JSON.parse(prevMetaCarousels);
      var blocks = JSON.parse(metaCarousels);
      var index = this.findIndex(this.state.blockId, blocks);
      var prevCarousel = prevBlocks[index];
      var carousel = blocks[index];

      if (index !== this.state.blockIndex) {
        // console.log( 'componentDidUpdate()::new index ', index );
        this.setState({
          blockIndex: index
        });
      }
      /*
       * See note in constructor() about the sliderKey.
       */


      if (!Object(lodash__WEBPACK_IMPORTED_MODULE_12__["isUndefined"])(prevCarousel) && prevCarousel.autoplay !== carousel.autoplay) {
        this.setState({
          sliderKey: new Date().getTime() / 1000
        });
      }

      var element = document.getElementById('slick-slider-block-' + this.state.blockId);

      if (null != element && 'undefined' != typeof element) {
        var arrowDotsColor = this.getAttribute('arrowDotsColor', '#000000');
        var backgroundColor = this.getAttribute('backgroundColor', '#FFFFFF');
        var borderColor = this.getAttribute('borderColor', '#000000');
        var borderRadius = this.getAttribute('borderRadius', 0);
        var borderWidth = this.getAttribute('borderWidth', 0);
        var color = this.getAttribute('color', '#000000');
        var imageBorderColor = this.getAttribute('imageBorderColor', '#000000');
        var imageBorderRadius = this.getAttribute('imageBorderRadius', 0);
        var imageBorderWidth = this.getAttribute('imageBorderWidth', 0);
        var imageShape = this.getAttribute('imageShape', 'square'); // Using the "Clear" button set the value to empty string. Use default color.

        if (!arrowDotsColor) {
          color = '#000000';
        }

        if (!backgroundColor) {
          backgroundColor = '#FFFFFF';
        }

        if (!borderColor) {
          borderColor = '#000000';
        }

        if (!color) {
          color = '#000000';
        }

        if (!imageBorderColor) {
          imageBorderColor = '#000000';
        }

        var id = '#slick-slider-block-' + this.state.blockId;
        var arrowDotStyle = ["color: ".concat(arrowDotsColor, ";")];
        var blockStyle = ["background-color: ".concat(backgroundColor), "color: ".concat(color)];
        var slideStyle = ["border-color: ".concat(borderColor), "border-radius: ".concat(borderRadius, "px"), "border-style: solid", "border-width: ".concat(borderWidth, "px")];
        imageBorderRadius = 'circle' === imageShape ? '50%' : imageBorderRadius + 'px';
        var imageStyle = ["border-color: ".concat(imageBorderColor), "border-radius: ".concat(imageBorderRadius), "border-style: solid", "border-width: ".concat(imageBorderWidth, "px"), 'overflow: hidden'];
        var nameStyle = ["color: ".concat(color, ";")];
        var css = '';
        css += "\n".concat(id, " .slick-arrow.slick-next:before { ").concat(arrowDotStyle.join('\n'), " }");
        css += "\n".concat(id, " .slick-arrow.slick-prev:before { ").concat(arrowDotStyle.join('\n'), " }");
        css += "\n".concat(id, " .slick-dots li button:before { ").concat(arrowDotStyle.join('\n'), " }");
        css += "\n".concat(id, " { ").concat(blockStyle.join(';\n'), " }");
        css += "\n".concat(id, " .slick-slide { ").concat(slideStyle.join(';\n'), " }");
        css += "\n".concat(id, " .slick-slide h3 { ").concat(nameStyle.join('\n'), " }");
        css += "\n".concat(id, " .slick-slide .cn-image-style { ").concat(imageStyle.join(';\n'), " }");
        element.innerHTML = css;
      }
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      var _this2 = this;

      // console.log( this.props.name, ': componentWillUnmount()' );
      var _this$props2 = this.props,
          blockId = _this$props2.attributes.blockId,
          metaCarousels = _this$props2.metaCarousels,
          setMetaFieldValue = _this$props2.setMetaFieldValue;
      var editorBlocks = select('core/block-editor').getBlocks();
      /*
       * Because `select( 'core/block-editor' ).getBlocks()` return a nested array where the `innerBlocks` property
       * can contain nested blocks, it needs flattened first so it can be filtered by block name and then searched
       * for the current `blockId`.
       *
       * @link https://stackoverflow.com/a/35272973/5351316
       */

      var flatten = function flatten(into, node) {
        if (node == null) return into;
        if (Array.isArray(node)) return node.reduce(flatten, into);
        into.push(node);
        return flatten(into, node.innerBlocks);
      };

      var blocksFlattend = flatten([], editorBlocks); // Filter blocks by block name.

      var selectEditorBlocks = blocksFlattend.filter(function (block) {
        return _this2.props.name === block.name;
      }); // Find this block within the editor blocks.

      var blockExists = selectEditorBlocks.find(function (block) {
        return blockId === block.attributes.blockId;
      }); // If this block was not found the `blockExists` var will be undefined, remove it from the post meta.

      if (Object(lodash__WEBPACK_IMPORTED_MODULE_12__["isUndefined"])(blockExists)) {
        var blocks = JSON.parse(metaCarousels);
        var index = this.findIndex(this.state.blockId, blocks); // console.log( 'componentWillUnmount()::blocks : before ', blocks );
        // console.log( 'componentWillUnmount()::index ', index );

        blocks.splice(index, 1); // console.log( 'componentWillUnmount()::blocks : after ', blocks );

        var blocksJSON = JSON.stringify(blocks);
        setMetaFieldValue(blocksJSON);
      }
    }
    /**
     * @param {object} args
     */

  }, {
    key: "prepareQueryArgs",
    value: function prepareQueryArgs(args) {
      var carousels = this.props.attributes.carousels;
      var query = {};
      var index = this.getIndex();
      var blocks = JSON.parse(carousels); // console.log( 'prepareQueryArgs::blocks ', blocks );

      if (-1 < index) {
        var block = blocks[index];

        if (Object(lodash__WEBPACK_IMPORTED_MODULE_12__["has"])(block, 'listType')) {
          query['type'] = block.listType;
        }

        if (Object(lodash__WEBPACK_IMPORTED_MODULE_12__["has"])(block, 'categories')) {
          query['categories'] = block.categories;
        }

        if (Object(lodash__WEBPACK_IMPORTED_MODULE_12__["has"])(block, 'categoriesIn') && true === block.categoriesIn) {
          query['category_in'] = true;
        }

        if (Object(lodash__WEBPACK_IMPORTED_MODULE_12__["has"])(block, 'categoriesExclude')) {
          query['categories_exclude'] = block.categoriesExclude;
        }

        if (Object(lodash__WEBPACK_IMPORTED_MODULE_12__["has"])(block, 'limit')) {
          query['per_page'] = block.limit;
        }

        if (Object(lodash__WEBPACK_IMPORTED_MODULE_12__["has"])(block, 'excerptWordLimit')) {
          if (Object(_Connections_Directory_components_utility__WEBPACK_IMPORTED_MODULE_14__["isNumber"])(block.excerptWordLimit)) {
            query['_excerpt'] = {
              length: block.excerptWordLimit
            };
          } else {
            query['_excerpt'] = {};
          }
        }

        var zc = Object(lodash__WEBPACK_IMPORTED_MODULE_12__["has"])(block, 'imageCropMode') ? block.imageCropMode : 1;

        if (Object(lodash__WEBPACK_IMPORTED_MODULE_12__["has"])(args, 'imageCropMode')) {
          zc = args.imageCropMode;
          delete args.imageCropMode;
        }

        query['_images'] = [{
          type: 'logo',
          size: 'custom',
          width: 600,
          height: 600,
          zc: zc
        }, {
          type: 'photo',
          size: 'custom',
          width: 600,
          height: 600,
          zc: zc
        }];
        query['_fields'] = 'fn.rendered,job_title.rendered,phone,email,social,excerpt.rendered,images';
        query = _objectSpread(_objectSpread({}, query), args);
      } // console.log( query );


      return query;
    }
  }, {
    key: "fetchAPI",
    value: function fetchAPI(query) {
      var path = addQueryArgs(ENDPOINT, _objectSpread(_objectSpread({}, query), {}, {
        context: 'view'
      })); // console.log( 'Fetching... ', query );

      return apiFetch({
        path: path
      });
    }
    /**
     * @param {object} args
     */

  }, {
    key: "fetchEntries",
    value: function fetchEntries() {
      var _this3 = this;

      var args = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      this.fetchAPI(this.prepareQueryArgs(args)).then(function (results) {
        _this3.setState({
          isLoading: false,
          queryResults: results
        });
      });
    }
  }, {
    key: "findIndex",

    /**
     * @param {string} id
     * @param {array} blocks
     *
     * @return {number}
     */
    value: function findIndex(id, blocks) {
      // console.log( 'findIndex::blocks ', blocks );
      // console.log( 'findIndex::blockId ', id );
      var index = Object(lodash__WEBPACK_IMPORTED_MODULE_12__["findIndex"])(blocks, function (o) {
        // console.log( 'findIndex::o ', o );
        return !Object(lodash__WEBPACK_IMPORTED_MODULE_12__["isUndefined"])(o) && o.blockId === id;
      }); // console.log( 'findIndex:: ', index );


      return index;
    }
    /**
     * @return {number}
     */

  }, {
    key: "getIndex",
    value: function getIndex() {
      return this.state.blockIndex;
    }
    /**
     * @param {string} key
     * @param {*} defaultValue
     *
     * @return {*}
     */

  }, {
    key: "getAttribute",
    value: function getAttribute(key) {
      var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      var metaCarousels = this.props.metaCarousels;
      var index = this.getIndex();
      var blocks = JSON.parse(metaCarousels); // console.log( 'getAttributes::typeof blocks ', typeof blocks );
      // console.log( 'getAttributes::blocks ', blocks );
      // console.log( 'getAttributes::key ', key );

      if (-1 === index || !Object(lodash__WEBPACK_IMPORTED_MODULE_12__["has"])(blocks, [index, key])) {
        // console.log( 'getAttributes::defaultValue ', defaultValue );
        return defaultValue;
      } else {
        // console.log( 'getAttributes::value ', blocks[ index ][ key ] );
        return blocks[index][key];
      }
    }
    /**
     * The blockId index.
     *
     * The `blockAttributes` attribute is an array of block instance attributes. One for each block in the post.
     * This will update the index in the `blockAttributes` attributes array with the current component state.
     *
     * @param {object} attributes
     */

  }, {
    key: "setAttributes",
    value: function setAttributes(attributes) {
      var _this$props3 = this.props,
          metaCarousels = _this$props3.metaCarousels,
          setMetaFieldValue = _this$props3.setMetaFieldValue;
      var blocks = JSON.parse(metaCarousels);
      var index = this.getIndex(); // console.log( 'setAttributes::blocks ', blocks );

      if (-1 < index) {
        var block = blocks[index];
        block = _objectSpread(_objectSpread({}, block), attributes);
        blocks[index] = block; // console.log( 'setAttributes::block (hasIndex) ', block );
      } else {
        var blockCount = blocks.push(_objectSpread({
          blockId: this.state.blockId
        }, attributes));
        this.setState({
          blockIndex: blockCount - 1
        }); // console.log( 'setAttributes::block (pushNew) ', blockCount - 1 );
      } // console.log( 'setAttributes::blocks (updated) ', blocks );


      var blocksJSON = JSON.stringify(blocks); // console.log( 'setAttributes::blocksJSON ', blocksJSON );

      setMetaFieldValue(blocksJSON);
    }
  }, {
    key: "render",
    value: function render() {
      var _this4 = this;

      // console.log( this.props.name, ': render()' );
      var attributes = this.props.attributes;
      var blockId = attributes.blockId; // const blockIndex = this.getIndex();

      var entryTypeSelectOptions = []; // console.log( 'render::blockIndex ', blockIndex );

      for (var property in entryTypes) {
        // noinspection JSUnfilteredForInLoop
        entryTypeSelectOptions.push({
          label: entryTypes[property],
          value: property
        });
      }

      var categoriesIn = this.getAttribute('categoriesIn', false);
      var arrows = this.getAttribute('arrows', true);
      var autoplay = this.getAttribute('autoplay', false);
      var dots = this.getAttribute('dots', true);
      var infinite = this.getAttribute('infinite', false);
      var pause = this.getAttribute('pause', true);
      var displayTitle = this.getAttribute('displayTitle', true);
      var displayExcerpt = this.getAttribute('displayExcerpt', true);
      var displayPhone = this.getAttribute('displayPhone', true);
      var displayEmail = this.getAttribute('displayEmail', true);
      var displaySocial = this.getAttribute('displaySocial', true);
      var backgroundColor = this.getAttribute('backgroundColor', '#FFFFFF');
      var textColor = this.getAttribute('color', '#000000');
      var arrowDotsColor = this.getAttribute('arrowDotsColor', '#000000');
      var borderColor = this.getAttribute('borderColor', '#000000');
      var borderRadius = this.getAttribute('borderRadius', 0);
      var borderWidth = this.getAttribute('borderWidth', 0);
      var displayDropShadow = this.getAttribute('displayDropShadow', false);
      var imageBorderColor = this.getAttribute('imageBorderColor', '#000000');
      var imageBorderRadius = this.getAttribute('imageBorderRadius', 0);
      var imageBorderWidth = this.getAttribute('imageBorderWidth', 0);
      var imageCropMode = this.getAttribute('imageCropMode', '1');
      var imageShape = this.getAttribute('imageShape', 'square');
      var imageType = this.getAttribute('imageType', 'photo');
      var excerptWordLimit = this.getAttribute('excerptWordLimit', '');
      var inspectorControls = wp.element.createElement(Fragment, null, wp.element.createElement(InspectorControls, null, wp.element.createElement(PanelBody, {
        title: __('Carousel', 'connections'),
        initialOpen: true
      }, wp.element.createElement("div", {
        style: {
          marginTop: '20px'
        }
      }, wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["RangeControl"], {
        label: __('Maximum Number of Slides', 'connections'),
        value: this.getAttribute('limit', 10),
        min: 1,
        max: 100,
        initialPosition: 10,
        allowReset: true,
        onChange: function onChange(value) {
          _this4.setAttributes({
            limit: value
          });

          _this4.fetchEntries({
            per_page: value
          });
        }
      }), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["RangeControl"], {
        label: __('Number of Slides to Display per Frame', 'connections'),
        value: this.getAttribute('slidesToShow', 1),
        min: 1,
        max: 4,
        initialPosition: 1,
        allowReset: true,
        onChange: function onChange(value) {
          var parameters = {
            slidesToShow: value
          };

          var slidesToScroll = _this4.getAttribute('slidesToScroll', 1);

          if (value <= slidesToScroll) {
            parameters['slidesToScroll'] = value;
          }

          _this4.setAttributes(parameters);
        }
      }), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["RangeControl"], {
        label: __('Number of Slides to Scroll per Frame', 'connections'),
        value: this.getAttribute('slidesToScroll', 1),
        min: 1,
        max: this.getAttribute('slidesToShow', 1),
        initialPosition: 1,
        allowReset: true,
        onChange: function onChange(value) {
          return _this4.setAttributes({
            slidesToScroll: value
          });
        }
      }), wp.element.createElement("div", {
        style: {
          marginTop: '20px'
        }
      }, wp.element.createElement(ToggleControl, {
        label: __('Autoplay?', 'connections') // help={__( '', 'connections' )}
        ,
        checked: !!autoplay,
        onChange: function onChange() {
          return _this4.setAttributes({
            autoplay: !autoplay
          });
        }
      }), autoplay && wp.element.createElement(Fragment, null, wp.element.createElement(ToggleControl, {
        label: __('Pause on hover?', 'connections') // help={__( '', 'connections' )}
        ,
        checked: !!pause,
        onChange: function onChange() {
          return _this4.setAttributes({
            pause: !pause
          });
        }
      }), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["RangeControl"], {
        label: __('Frame Advance Speed in Milliseconds', 'connections'),
        value: this.getAttribute('autoplaySpeed', 3000),
        min: 100,
        max: 10000,
        initialPosition: 3000,
        allowReset: true,
        onChange: function onChange(value) {
          return _this4.setAttributes({
            autoplaySpeed: value
          });
        }
      })), wp.element.createElement(ToggleControl, {
        label: __('Infinite loop?', 'connections') // help={__( '', 'connections' )}
        ,
        checked: !!infinite,
        onChange: function onChange() {
          return _this4.setAttributes({
            infinite: !infinite
          });
        }
      }), wp.element.createElement(ToggleControl, {
        label: __('Display arrows?', 'connections') // help={__( '', 'connections' )}
        ,
        checked: !!arrows,
        onChange: function onChange() {
          return _this4.setAttributes({
            arrows: !arrows
          });
        }
      }), wp.element.createElement(ToggleControl, {
        label: __('Display dots?', 'connections') // help={__( '', 'connections' )}
        ,
        checked: !!dots,
        onChange: function onChange() {
          return _this4.setAttributes({
            dots: !dots
          });
        }
      }), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["RangeControl"], {
        label: __('Frame Animation Speed in Milliseconds', 'connections'),
        value: this.getAttribute('speed', 500),
        min: 100,
        max: 5000,
        initialPosition: 500,
        allowReset: true,
        onChange: function onChange(value) {
          return _this4.setAttributes({
            speed: value
          });
        }
      })))), wp.element.createElement(PanelBody, {
        title: __('Select', 'connections'),
        initialOpen: false
      }, wp.element.createElement("p", null, __('This section controls which entries from your directory will be displayed.', 'connections')), wp.element.createElement("div", {
        style: {
          marginTop: '20px'
        }
      }, wp.element.createElement(SelectControl, {
        label: __('Entry Type', 'connections'),
        help: __('Select which entry type to display. The default is to display all.', 'connections'),
        value: this.getAttribute('listType', 'all'),
        options: [{
          label: __('All', 'connections'),
          value: 'all'
        }].concat(entryTypeSelectOptions),
        onChange: function onChange(value) {
          _this4.setAttributes({
            listType: value
          });

          _this4.fetchEntries({
            type: value
          });
        }
      })), wp.element.createElement("div", {
        style: {
          marginTop: '20px'
        }
      }, wp.element.createElement("p", null, __('Choose the categories to include in the entry list.', 'connections'))), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["HierarchicalTermSelector"], {
        taxonomy: "category",
        terms: this.getAttribute('categories', []),
        onChange: function onChange(value) {
          _this4.setAttributes({
            categories: value
          });

          _this4.fetchEntries({
            categories: value
          });
        }
      }), wp.element.createElement("div", {
        style: {
          marginTop: '20px'
        }
      }, wp.element.createElement(ToggleControl, {
        label: __('Entries must be assigned to all the above chosen categories?', 'connections') // help={__( '', 'connections' )}
        ,
        checked: !!categoriesIn,
        onChange: function onChange() {
          _this4.setAttributes({
            categoriesIn: !categoriesIn
          });

          _this4.fetchEntries({
            category_in: !categoriesIn
          });
        }
      })), wp.element.createElement("div", {
        style: {
          marginTop: '20px'
        }
      }, wp.element.createElement("p", null, __('Choose the categories to exclude from the entry list.', 'connections'))), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["HierarchicalTermSelector"], {
        taxonomy: "category",
        terms: this.getAttribute('categoriesExclude', []),
        onChange: function onChange(value) {
          _this4.setAttributes({
            categoriesExclude: value
          });

          _this4.fetchEntries({
            categories_exclude: value
          });
        }
      })), wp.element.createElement(PanelBody, {
        title: __('Image', 'connections'),
        initialOpen: false
      }, wp.element.createElement("div", {
        style: {
          marginTop: '20px'
        }
      }, wp.element.createElement(RadioControl, {
        label: __('Type', 'connections'),
        selected: imageType,
        options: [{
          value: 'logo',
          label: __('Logo', 'connections')
        }, {
          value: 'photo',
          label: __('Photo', 'connections')
        }],
        onChange: function onChange(value) {
          return _this4.setAttributes({
            imageType: value
          });
        }
      }), wp.element.createElement(RadioControl, {
        label: __('Shape', 'connections'),
        selected: imageShape,
        options: [{
          value: 'circle',
          label: __('Circle', 'connections')
        }, {
          value: 'square',
          label: __('Square', 'connections')
        }],
        onChange: function onChange(value) {
          var atts = {
            imageShape: value
          };

          if ('square' === value) {
            atts.imageBorderRadius = 0;
          }

          _this4.setAttributes(atts);
        }
      }))), wp.element.createElement(PanelBody, {
        title: __('Display Details', 'connections'),
        initialOpen: false
      }, wp.element.createElement("div", {
        style: {
          marginTop: '20px'
        }
      }, wp.element.createElement(ToggleControl, {
        label: __('Display Title?', 'connections'),
        checked: !!displayTitle,
        onChange: function onChange() {
          return _this4.setAttributes({
            displayTitle: !displayTitle
          });
        }
      }), wp.element.createElement(ToggleControl, {
        label: __('Display Primary Phone?', 'connections'),
        checked: !!displayPhone,
        onChange: function onChange() {
          return _this4.setAttributes({
            displayPhone: !displayPhone
          });
        }
      }), wp.element.createElement(ToggleControl, {
        label: __('Display Primary Email?', 'connections'),
        checked: !!displayEmail,
        onChange: function onChange() {
          return _this4.setAttributes({
            displayEmail: !displayEmail
          });
        }
      }), wp.element.createElement(ToggleControl, {
        label: __('Display Excerpt?', 'connections'),
        checked: !!displayExcerpt,
        onChange: function onChange() {
          return _this4.setAttributes({
            displayExcerpt: !displayExcerpt
          });
        }
      }), wp.element.createElement(ToggleControl, {
        label: __('Display Social Networks?', 'connections'),
        checked: !!displaySocial,
        onChange: function onChange() {
          return _this4.setAttributes({
            displaySocial: !displaySocial
          });
        }
      }))), wp.element.createElement(PanelBody, {
        title: __('Style', 'connections'),
        initialOpen: false
      }, wp.element.createElement("div", {
        style: {
          marginTop: '20px'
        }
      }, wp.element.createElement(BaseControl, {
        className: "editor-color-palette-control",
        label: colorIndicator(__('Background Color', 'connections'), backgroundColor)
      }, wp.element.createElement(ColorPalette, {
        className: "editor-color-palette-control__color-palette",
        value: backgroundColor,
        onChange: function onChange(value) {
          return _this4.setAttributes({
            backgroundColor: value
          });
        }
      })), wp.element.createElement(BaseControl, {
        className: "editor-color-palette-control",
        label: colorIndicator(__('Text Color', 'connections'), textColor)
      }, wp.element.createElement(ColorPalette, {
        className: "editor-color-palette-control__color-palette",
        value: textColor,
        onChange: function onChange(value) {
          return _this4.setAttributes({
            color: value
          });
        }
      })), wp.element.createElement(BaseControl, {
        className: "editor-color-palette-control",
        label: colorIndicator(__('Arrow & Dots Color', 'connections'), arrowDotsColor)
      }, wp.element.createElement(ColorPalette, {
        className: "editor-color-palette-control__color-palette",
        value: arrowDotsColor,
        onChange: function onChange(value) {
          return _this4.setAttributes({
            arrowDotsColor: value
          });
        }
      })), wp.element.createElement(BaseControl, {
        className: "editor-color-palette-control",
        label: colorIndicator(__('Border Color', 'connections'), borderColor)
      }, wp.element.createElement(ColorPalette, {
        className: "editor-color-palette-control__color-palette",
        value: borderColor,
        onChange: function onChange(value) {
          return _this4.setAttributes({
            borderColor: value
          });
        }
      })), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["RangeControl"], {
        label: __('Border Radius', 'connections'),
        value: borderRadius,
        min: 0,
        max: 20,
        initialPosition: 3,
        allowReset: true,
        onChange: function onChange(value) {
          return _this4.setAttributes({
            borderRadius: value
          });
        }
      }), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["RangeControl"], {
        label: __('Border Width', 'connections'),
        value: borderWidth,
        min: 0,
        max: 5,
        initialPosition: 2,
        allowReset: true,
        onChange: function onChange(value) {
          return _this4.setAttributes({
            borderWidth: value
          });
        }
      }), wp.element.createElement(ToggleControl, {
        label: __('Display Drop Shadow?', 'connections'),
        checked: !!displayDropShadow,
        onChange: function onChange() {
          return _this4.setAttributes({
            displayDropShadow: !displayDropShadow
          });
        }
      }), wp.element.createElement(BaseControl, {
        className: "editor-color-palette-control",
        label: colorIndicator(__('Image Border Color', 'connections'), imageBorderColor)
      }, wp.element.createElement(ColorPalette, {
        className: "editor-color-palette-control__color-palette",
        value: imageBorderColor,
        onChange: function onChange(value) {
          return _this4.setAttributes({
            imageBorderColor: value
          });
        }
      })), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["RangeControl"], {
        label: __('Image Border Width', 'connections'),
        value: imageBorderWidth,
        min: 0,
        max: 5,
        initialPosition: 0,
        allowReset: true,
        onChange: function onChange(value) {
          return _this4.setAttributes({
            imageBorderWidth: value
          });
        }
      }), imageShape === 'square' && wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["RangeControl"], {
        label: __('Image Border Radius', 'connections'),
        value: imageBorderRadius,
        min: 0,
        max: 20,
        initialPosition: 0,
        allowReset: true,
        onChange: function onChange(value) {
          return _this4.setAttributes({
            imageBorderRadius: value
          });
        }
      })))), wp.element.createElement(InspectorAdvancedControls, null, wp.element.createElement("div", {
        style: {
          marginTop: '20px'
        }
      }, wp.element.createElement(RadioControl, {
        label: __('Image Crop Mode', 'connections'),
        selected: String(imageCropMode),
        options: [{
          value: '1',
          label: __('Crop and resize proportionally, maintaining the aspect ratio. Crop is based on image center.', 'connections')
        }, {
          value: '2',
          label: __('Resize proportionally to fit entire image and add margins if required.', 'connections')
        }, // {
        // 	value: '3',
        // 	label: __( 'Resize proportionally adjusting the size of scaled image so there are no margins added.', 'connections' )
        // },
        {
          value: '0',
          label: __('Resize to fit, no cropping. Image aspect ratio will not be maintained and will likely result in a stretch or squashed image.', 'connections')
        }],
        onChange: function onChange(value) {
          _this4.setAttributes({
            imageCropMode: value
          });

          _this4.fetchEntries({
            imageCropMode: value
          });
        }
      }), wp.element.createElement(TextControl, {
        label: __('Excerpt Word Limit', 'connections'),
        help: __('Enter 0 for the first sentence only. If the excerpt exceeds the word limit, the excerpt will be truncated at the of the next sentence if it can be determined automatically, so the excerpt word limit may be exceeded in order to display a complete sentence.', 'connections'),
        type: 'number',
        value: excerptWordLimit,
        onChange: function onChange(value) {
          _this4.setAttributes({
            excerptWordLimit: value
          });

          if (value) {
            _this4.fetchEntries({
              _excerpt: {
                length: value
              }
            });
          } else {
            _this4.fetchEntries({
              _excerpt: {}
            });
          }
        }
      }))));
      var entries = this.state.queryResults;
      var hasEntries = Array.isArray(entries) && entries.length;

      if (!hasEntries) {
        return wp.element.createElement(Fragment, null, inspectorControls, wp.element.createElement("div", null, this.state.isLoading ? wp.element.createElement("p", null, __('Loading...', 'connections'), " ", wp.element.createElement(Spinner, null)) : wp.element.createElement("p", null, __('No directory entries found.', 'connections'))));
      } else {
        var settings = {
          arrows: arrows,
          autoplay: autoplay,
          autoplaySpeed: this.getAttribute('autoplaySpeed', 3000),
          dots: dots,
          infinite: infinite,
          lazyLoad: false,
          pauseOnFocus: pause,
          pauseOnHover: pause,
          pauseOnDotsHover: pause,
          speed: this.getAttribute('speed', 500),
          slidesToShow: this.getAttribute('slidesToShow', 1),
          slidesToScroll: this.getAttribute('slidesToScroll', 1)
        }; // const imageSize = 'photo' === imageType ? 'large' : 'scaled';

        var slides = entries.map(function (entry, i) {
          return wp.element.createElement("div", {
            key: i
          }, wp.element.createElement("div", {
            className: "slick-slide-grid"
          }, wp.element.createElement("div", {
            className: "slick-slide-column"
          }, wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["EntryImage"], {
            entry: entry,
            type: imageType,
            size: "custom"
          }), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["EntryName"], {
            tag: "h3",
            entry: entry
          }), displayTitle && wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["EntryTitle"], {
            entry: entry
          }), displayPhone && wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["EntryPhoneNumbers"], {
            entry: entry,
            preferred: true
          }), displayEmail && wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["EntryEmail"], {
            entry: entry,
            preferred: true
          }), displaySocial && wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["EntrySocialNetworks"], {
            entry: entry
          })), wp.element.createElement("div", {
            className: "slick-slide-column"
          }, displayExcerpt && wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_13__["EntryExcerpt"], {
            entry: entry
          }))));
        });
        var classNames = ['slick-slider-block'];
        if (arrows) classNames.push('slick-slider-has-arrows');
        if (dots) classNames.push('slick-slider-has-dots');
        if (displayDropShadow) classNames.push('slick-slider-has-shadow');
        classNames.push("slick-slider-slides-".concat(settings.slidesToShow));
        return wp.element.createElement(Fragment, null, inspectorControls, wp.element.createElement("div", {
          className: classNames.join(' '),
          id: 'slick-slider-block-' + blockId
        }, wp.element.createElement(react_slick__WEBPACK_IMPORTED_MODULE_9___default.a, _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0___default()({
          key: this.state.sliderKey
        }, settings), slides)));
      }
    }
  }]);

  return Carousel;
}(Component);

/* harmony default export */ __webpack_exports__["default"] = (compose([withDispatch(function (dispatch, _ref) {
  var value = _ref.value;
  return {
    setMetaFieldValue: function setMetaFieldValue(value) {
      dispatch('core/editor').editPost({
        meta: {
          _cbd_carousel_blocks: value
        }
      });
    }
  };
}), withSelect(function (select, props) {
  return {
    // editorBlocks: select( 'core/editor' ).getBlocks(),
    metaCarousels: select('core/editor').getEditedPostAttribute('meta')._cbd_carousel_blocks
  };
}), withInstanceId])(Carousel));

/***/ }),

/***/ "./includes/blocks/carousel/index.js":
/*!*******************************************!*\
  !*** ./includes/blocks/carousel/index.js ***!
  \*******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./edit */ "./includes/blocks/carousel/edit.js");
/**
 * WordPress dependencies
 */
var _wp$i18n = wp.i18n,
    __ = _wp$i18n.__,
    _n = _wp$i18n._n,
    _nx = _wp$i18n._nx,
    _x = _wp$i18n._x;
var registerBlockType = wp.blocks.registerBlockType; // const { withSelect } = wp.data;

/**
 * Block dependencies
 */


/**
 * Register Block
 */

/* harmony default export */ __webpack_exports__["default"] = (registerBlockType('connections-directory/carousel', {
  title: __('Carousel', 'connections'),
  description: __('Display members of your team in a carousel.', 'connections'),
  category: 'connections-directory',
  // icon:        giveLogo,
  keywords: ['connections', __('carousel', 'connections'), __('slider', 'connections')],
  supports: {
    // Remove the support for the generated className.
    className: false,
    // Remove the support for the custom className.
    customClassName: false,
    // Remove the support for editing the block using the block HTML editor.
    html: false
  },
  attributes: {
    blockId: {
      type: 'string' // default: '',

    },
    borderRadius: {
      type: 'integer',
      default: 12
    },
    borderWidth: {
      type: 'integer',
      default: 1
    },
    carousels: {
      type: 'string',
      default: '[]',
      source: 'meta',
      meta: '_cbd_carousel_blocks'
    },
    displayDropShadow: {
      type: 'boolean',
      default: true
    },
    excerptWordLimit: {
      type: 'string',
      default: '10'
    },
    imageBorderColor: {
      default: '#BABABA'
    },
    imageBorderRadius: {
      type: 'integer',
      default: 0
    },
    imageBorderWidth: {
      type: 'integer',
      default: 0
    },
    imageCropMode: {
      type: 'string',
      default: '1'
    },
    imageShape: {
      type: 'string',
      default: 'square'
    },
    imageType: {
      type: 'string',
      default: 'photo'
    }
  },
  edit: _edit__WEBPACK_IMPORTED_MODULE_0__["default"],
  save: function save() {
    // Server side rendering via shortcode.
    return null;
  }
}));

/***/ }),

/***/ "./includes/blocks/carousel/style.scss":
/*!*********************************************!*\
  !*** ./includes/blocks/carousel/style.scss ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "./includes/blocks/components/entry/email.js":
/*!***************************************************!*\
  !*** ./includes/blocks/components/entry/email.js ***!
  \***************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! lodash */ "lodash");
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(lodash__WEBPACK_IMPORTED_MODULE_5__);






function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function () { var Super = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default()(this, result); }; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

/**
 * WordPress dependencies
 */
var Component = wp.element.Component; // const { decodeEntities } = wp.htmlEntities;

/**
 * External dependencies
 */



var EntryEmail = /*#__PURE__*/function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default()(EntryEmail, _Component);

  var _super = _createSuper(EntryEmail);

  function EntryEmail() {
    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, EntryEmail);

    return _super.apply(this, arguments);
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(EntryEmail, [{
    key: "render",
    value: function render() {
      var _this$props = this.props,
          entry = _this$props.entry,
          _this$props$preferred = _this$props.preferred,
          preferred = _this$props$preferred === void 0 ? false : _this$props$preferred;
      if (Object(lodash__WEBPACK_IMPORTED_MODULE_5__["isUndefined"])(entry.email) || 0 === entry.email.length) return null;
      var addresses = entry.email.filter(function (email) {
        return preferred === email.preferred || false === preferred;
      }).map(function (email) {
        return wp.element.createElement("div", {
          key: email.id
        }, email.address.rendered);
      });

      if (0 === addresses.length) {
        addresses = wp.element.createElement("div", {
          key: entry.email[0].id
        }, entry.email[0].address.rendered);
      }

      return wp.element.createElement("div", {
        className: "email-addresses"
      }, addresses);
    }
  }]);

  return EntryEmail;
}(Component);

/* harmony default export */ __webpack_exports__["default"] = (EntryEmail);

/***/ }),

/***/ "./includes/blocks/components/entry/excerpt.js":
/*!*****************************************************!*\
  !*** ./includes/blocks/components/entry/excerpt.js ***!
  \*****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! lodash */ "lodash");
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(lodash__WEBPACK_IMPORTED_MODULE_5__);






function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function () { var Super = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default()(this, result); }; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

/**
 * WordPress dependencies
 */
var Component = wp.element.Component;
var decodeEntities = wp.htmlEntities.decodeEntities;
/**
 * External dependencies
 */



var EntryExcerpt = /*#__PURE__*/function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default()(EntryExcerpt, _Component);

  var _super = _createSuper(EntryExcerpt);

  function EntryExcerpt() {
    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, EntryExcerpt);

    return _super.apply(this, arguments);
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(EntryExcerpt, [{
    key: "render",
    value: function render() {
      var entry = this.props.entry;
      if (Object(lodash__WEBPACK_IMPORTED_MODULE_5__["isUndefined"])(entry.excerpt) || 1 > entry.excerpt.rendered.length) return null;
      return wp.element.createElement("div", {
        className: "excerpt",
        dangerouslySetInnerHTML: {
          __html: decodeEntities(entry.excerpt.rendered)
        }
      });
    }
  }]);

  return EntryExcerpt;
}(Component);

/* harmony default export */ __webpack_exports__["default"] = (EntryExcerpt);

/***/ }),

/***/ "./includes/blocks/components/entry/image.js":
/*!***************************************************!*\
  !*** ./includes/blocks/components/entry/image.js ***!
  \***************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! lodash */ "lodash");
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(lodash__WEBPACK_IMPORTED_MODULE_5__);






function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function () { var Super = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default()(this, result); }; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

/**
 * WordPress dependencies
 */
var Component = wp.element.Component;
var decodeEntities = wp.htmlEntities.decodeEntities;
var _wp$i18n = wp.i18n,
    __ = _wp$i18n.__,
    _n = _wp$i18n._n,
    _nx = _wp$i18n._nx,
    _x = _wp$i18n._x,
    sprintf = _wp$i18n.sprintf;
/**
 * External dependencies
 */



var EntryImage = /*#__PURE__*/function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default()(EntryImage, _Component);

  var _super = _createSuper(EntryImage);

  function EntryImage() {
    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, EntryImage);

    return _super.apply(this, arguments);
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(EntryImage, [{
    key: "render",
    value: function render() {
      var _this$props = this.props,
          entry = _this$props.entry,
          size = _this$props.size,
          type = _this$props.type,
          _this$props$usePlaceh = _this$props.usePlaceholder,
          usePlaceholder = _this$props$usePlaceh === void 0 ? false : _this$props$usePlaceh,
          _this$props$placehold = _this$props.placeholderWidth,
          placeholderWidth = _this$props$placehold === void 0 ? 600 : _this$props$placehold,
          _this$props$placehold2 = _this$props.placeholderHeight,
          placeholderHeight = _this$props$placehold2 === void 0 ? 520 : _this$props$placehold2;

      if (Object(lodash__WEBPACK_IMPORTED_MODULE_5__["has"])(entry.images, [type, size])) {
        var _entry$images$type$si = entry.images[type][size],
            height = _entry$images$type$si.height,
            url = _entry$images$type$si.url,
            width = _entry$images$type$si.width;
        return wp.element.createElement("span", {
          className: "cn-image-style"
        }, wp.element.createElement("span", {
          style: {
            display: 'block',
            maxWidth: '100%',
            width: "".concat(width, "px")
          }
        }, wp.element.createElement("img", {
          className: 'cn-image ' + type,
          width: width,
          height: height,
          srcSet: url.replace(/^https?:/, '') + ' 1x',
          alt: sprintf(__('%s of %s', 'connections'), type.charAt(0).toUpperCase() + type.slice(1), decodeEntities(entry.fn.rendered)) // loading='lazy'

        })));
      } else if (true === usePlaceholder) {
        var style = {
          display: 'inline-block',
          paddingBottom: "calc(".concat(placeholderHeight, " / ").concat(placeholderWidth, " * 100%)"),
          width: placeholderWidth + 'px'
        };
        return wp.element.createElement("span", {
          className: 'cn-image-' + type + ' cn-image-none',
          style: style
        }, wp.element.createElement("span", null, __('No Image Available', 'connections')));
      } else {
        return null;
      }
    }
  }]);

  return EntryImage;
}(Component);

/* harmony default export */ __webpack_exports__["default"] = (EntryImage);

/***/ }),

/***/ "./includes/blocks/components/entry/name.js":
/*!**************************************************!*\
  !*** ./includes/blocks/components/entry/name.js ***!
  \**************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__);






function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function () { var Super = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default()(this, result); }; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

/**
 * WordPress dependencies
 */
var _wp$element = wp.element,
    Component = _wp$element.Component,
    Fragment = _wp$element.Fragment;
var decodeEntities = wp.htmlEntities.decodeEntities;

var EntryName = /*#__PURE__*/function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default()(EntryName, _Component);

  var _super = _createSuper(EntryName);

  function EntryName() {
    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, EntryName);

    return _super.apply(this, arguments);
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(EntryName, [{
    key: "render",
    value: function render() {
      var _this$props = this.props,
          _this$props$asPermali = _this$props.asPermalink,
          asPermalink = _this$props$asPermali === void 0 ? false : _this$props$asPermali,
          attributes = _this$props.attributes,
          entry = _this$props.entry,
          tag = _this$props.tag;
      var Tag = tag;
      var name = decodeEntities(entry.fn.rendered);

      if (asPermalink) {
        name = wp.element.createElement("a", {
          href: entry.link
        }, name);
      }

      return wp.element.createElement(Tag, null, name);
    }
  }]);

  return EntryName;
}(Component);

/* harmony default export */ __webpack_exports__["default"] = (EntryName);

/***/ }),

/***/ "./includes/blocks/components/entry/phone-numbers.js":
/*!***********************************************************!*\
  !*** ./includes/blocks/components/entry/phone-numbers.js ***!
  \***********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! lodash */ "lodash");
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(lodash__WEBPACK_IMPORTED_MODULE_5__);






function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function () { var Super = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default()(this, result); }; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

/**
 * WordPress dependencies
 */
var Component = wp.element.Component; // const { decodeEntities } = wp.htmlEntities;

/**
 * External dependencies
 */



var EntryPhoneNumbers = /*#__PURE__*/function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default()(EntryPhoneNumbers, _Component);

  var _super = _createSuper(EntryPhoneNumbers);

  function EntryPhoneNumbers() {
    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, EntryPhoneNumbers);

    return _super.apply(this, arguments);
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(EntryPhoneNumbers, [{
    key: "render",
    value: function render() {
      var _this$props = this.props,
          entry = _this$props.entry,
          _this$props$preferred = _this$props.preferred,
          preferred = _this$props$preferred === void 0 ? false : _this$props$preferred;
      if (Object(lodash__WEBPACK_IMPORTED_MODULE_5__["isUndefined"])(entry.tel) || 0 === entry.tel.length) return null;
      var numbers = entry.tel.filter(function (phone) {
        return preferred === phone.preferred || false === preferred;
      }).map(function (phone) {
        return wp.element.createElement("div", {
          key: phone.id
        }, phone.number.rendered);
      });

      if (0 === numbers.length) {
        numbers = wp.element.createElement("div", {
          key: entry.tel[0].id
        }, entry.tel[0].number.rendered);
      }

      return wp.element.createElement("div", {
        className: "phone-numbers"
      }, numbers);
    }
  }]);

  return EntryPhoneNumbers;
}(Component);

/* harmony default export */ __webpack_exports__["default"] = (EntryPhoneNumbers);

/***/ }),

/***/ "./includes/blocks/components/entry/social-network-icon.js":
/*!*****************************************************************!*\
  !*** ./includes/blocks/components/entry/social-network-icon.js ***!
  \*****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__);






function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function () { var Super = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default()(this, result); }; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

/**
 * WordPress dependencies
 */
var Component = wp.element.Component; // const { decodeEntities } = wp.htmlEntities;

var SocialNetworkIcon = /*#__PURE__*/function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default()(SocialNetworkIcon, _Component);

  var _super = _createSuper(SocialNetworkIcon);

  function SocialNetworkIcon() {
    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, SocialNetworkIcon);

    return _super.apply(this, arguments);
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(SocialNetworkIcon, [{
    key: "render",
    value: function render() {
      var _this$props = this.props,
          network = _this$props.network,
          size = _this$props.size;
      var classes = ["cn-brandicon-".concat(network.slug), "cn-brandicon-size-".concat(size)];
      return wp.element.createElement("span", {
        className: "social-media-network cn-social-media-network"
      }, wp.element.createElement("a", {
        className: "url ".concat(network.type),
        href: network.url,
        target: "_blank",
        rel: "noopener",
        title: network.name
      }, wp.element.createElement("i", {
        className: classes.join(' ')
      })));
    }
  }]);

  return SocialNetworkIcon;
}(Component);

/* harmony default export */ __webpack_exports__["default"] = (SocialNetworkIcon);

/***/ }),

/***/ "./includes/blocks/components/entry/social-networks.js":
/*!*************************************************************!*\
  !*** ./includes/blocks/components/entry/social-networks.js ***!
  \*************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! lodash */ "lodash");
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(lodash__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _Connections_Directory_components__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @Connections-Directory/components */ "./includes/blocks/components/index.js");






function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function () { var Super = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default()(this, result); }; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

/**
 * WordPress dependencies
 */
var Component = wp.element.Component; // const { decodeEntities } = wp.htmlEntities;

/**
 * External dependencies
 */


/**
 * Internal dependencies
 */



var EntrySocialNetworks = /*#__PURE__*/function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default()(EntrySocialNetworks, _Component);

  var _super = _createSuper(EntrySocialNetworks);

  function EntrySocialNetworks() {
    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, EntrySocialNetworks);

    return _super.apply(this, arguments);
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(EntrySocialNetworks, [{
    key: "render",
    value: function render() {
      var entry = this.props.entry;
      if (Object(lodash__WEBPACK_IMPORTED_MODULE_5__["isUndefined"])(entry.social) || 0 === entry.social.length) return null;
      var networks = entry.social.map(function (network) {
        return wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_6__["SocialNetworkIcon"], {
          key: network.id,
          network: network,
          size: 24
        });
      });
      return wp.element.createElement("span", {
        className: "social-media-block"
      }, networks);
    }
  }]);

  return EntrySocialNetworks;
}(Component);

/* harmony default export */ __webpack_exports__["default"] = (EntrySocialNetworks);

/***/ }),

/***/ "./includes/blocks/components/entry/title.js":
/*!***************************************************!*\
  !*** ./includes/blocks/components/entry/title.js ***!
  \***************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! lodash */ "lodash");
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(lodash__WEBPACK_IMPORTED_MODULE_5__);






function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function () { var Super = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default()(this, result); }; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

/**
 * WordPress dependencies
 */
var _wp$element = wp.element,
    Component = _wp$element.Component,
    Fragment = _wp$element.Fragment;
var decodeEntities = wp.htmlEntities.decodeEntities;
/**
 * External dependencies
 */



var EntryTitle = /*#__PURE__*/function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default()(EntryTitle, _Component);

  var _super = _createSuper(EntryTitle);

  function EntryTitle() {
    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, EntryTitle);

    return _super.apply(this, arguments);
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(EntryTitle, [{
    key: "render",
    value: function render() {
      var entry = this.props.entry;
      if (Object(lodash__WEBPACK_IMPORTED_MODULE_5__["isUndefined"])(entry.job_title) || 1 > entry.job_title.rendered.length) return null;
      return wp.element.createElement("div", {
        className: "title"
      }, decodeEntities(entry.job_title.rendered));
    }
  }]);

  return EntryTitle;
}(Component);

/* harmony default export */ __webpack_exports__["default"] = (EntryTitle);

/***/ }),

/***/ "./includes/blocks/components/filter-tag-selector/index.js":
/*!*****************************************************************!*\
  !*** ./includes/blocks/components/filter-tag-selector/index.js ***!
  \*****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/assertThisInitialized */ "./node_modules/@babel/runtime/helpers/assertThisInitialized.js");
/* harmony import */ var _babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! lodash */ "lodash");
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(lodash__WEBPACK_IMPORTED_MODULE_7__);








function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function () { var Super = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_6___default()(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_6___default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_5___default()(this, result); }; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */

var Component = wp.element.Component;
var FormTokenField = wp.components.FormTokenField;
var _wp = wp,
    apiFetch = _wp.apiFetch;
var addQueryArgs = wp.url.addQueryArgs;
/**
 * Module constants
 */

var DEFAULT_QUERY = {
  per_page: -1
};
var MAX_TERMS_SUGGESTIONS = 20;

var isSameTermName = function isSameTermName(termA, termB) {
  return termA.toLowerCase() === termB.toLowerCase();
};

var FilterTagSelector = /*#__PURE__*/function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4___default()(FilterTagSelector, _Component);

  var _super = _createSuper(FilterTagSelector);

  function FilterTagSelector() {
    var _this;

    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1___default()(this, FilterTagSelector);

    _this = _super.apply(this, arguments);
    _this.unescapeTerm = _this.unescapeTerm.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_3___default()(_this));
    _this.unescapeTerms = _this.unescapeTerms.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_3___default()(_this));
    _this.onChange = _this.onChange.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_3___default()(_this));
    _this.searchTerms = Object(lodash__WEBPACK_IMPORTED_MODULE_7__["throttle"])(_this.searchTerms.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_3___default()(_this)), 300);
    _this.state = {
      loading: false,
      availableTerms: [],
      selectedTerms: []
    };
    return _this;
  }
  /**
   * Returns a term object with name unescaped.
   * The unescape of the name property is done using lodash unescape function.
   *
   * @param {Object} term The term object to unescape.
   *
   * @return {Object} Term object with name property unescaped.
   */


  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2___default()(FilterTagSelector, [{
    key: "unescapeTerm",
    value: function unescapeTerm(term) {
      var renderField = this.props.renderField;
      return _objectSpread(_objectSpread({}, term), {}, {
        unescapeName: Object(lodash__WEBPACK_IMPORTED_MODULE_7__["unescape"])(term[renderField])
      });
    }
  }, {
    key: "unescapeTerms",

    /**
     * Returns an array of term objects with names unescaped.
     * The unescape of each term is performed using the unescapeTerm function.
     *
     * @param {Object[]} terms Array of term objects to unescape.
     *
     * @return {Object[]} Array of therm objects unescaped.
     */
    value: function unescapeTerms(terms) {
      return Object(lodash__WEBPACK_IMPORTED_MODULE_7__["map"])(terms, this.unescapeTerm);
    }
  }, {
    key: "componentDidMount",
    value: function componentDidMount() {
      var _this2 = this;

      if (!Object(lodash__WEBPACK_IMPORTED_MODULE_7__["isEmpty"])(this.props.terms)) {
        this.setState({
          loading: false
        });
        this.initRequest = this.fetchTerms({
          search: this.props.terms.join(','),
          per_page: -1
        });
        this.initRequest.then(function () {
          _this2.setState({
            loading: false
          });
        }, function (xhr) {
          if (xhr.statusText === 'abort') {
            return;
          }

          _this2.setState({
            loading: false
          });
        });
      }
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      Object(lodash__WEBPACK_IMPORTED_MODULE_7__["invoke"])(this.initRequest, ['abort']);
      Object(lodash__WEBPACK_IMPORTED_MODULE_7__["invoke"])(this.searchRequest, ['abort']);
    }
  }, {
    key: "componentDidUpdate",
    value: function componentDidUpdate(prevProps) {
      if (prevProps.terms !== this.props.terms) {
        this.updateSelectedTerms(this.props.terms);
      }
    }
  }, {
    key: "fetchTerms",
    value: function fetchTerms() {
      var _this3 = this;

      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      var _this$props = this.props,
          getFields = _this$props.getFields,
          type = _this$props.type,
          renderField = _this$props.renderField;

      var query = _objectSpread(_objectSpread(_objectSpread({}, DEFAULT_QUERY), params), {
        _fields: getFields
      });

      var request = apiFetch({
        path: addQueryArgs("/cn-api/v1/autocomplete/".concat(type), query)
      });
      request.then(this.unescapeTerms).then(function (terms) {
        _this3.setState(function (state) {
          return {
            availableTerms: state.availableTerms.concat(terms.filter(function (term) {
              return !Object(lodash__WEBPACK_IMPORTED_MODULE_7__["find"])(state.availableTerms, function (availableTerm) {
                return availableTerm[renderField] === term[renderField];
              });
            }))
          };
        });

        _this3.updateSelectedTerms(_this3.props.terms);
      });
      return request;
    }
  }, {
    key: "updateSelectedTerms",
    value: function updateSelectedTerms() {
      var _this4 = this;

      var terms = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
      var _this$props2 = this.props,
          renderField = _this$props2.renderField,
          returnField = _this$props2.returnField;
      var selectedTerms = terms.reduce(function (result, termId) {
        // console.log( termId );
        var termObject = Object(lodash__WEBPACK_IMPORTED_MODULE_7__["find"])(_this4.state.availableTerms, function (term) {
          return term[returnField] === termId;
        });

        if (termObject) {
          result.push(termObject[renderField]);
        }

        return result;
      }, []); // console.log( 'this.state.availableTerms=', this.state.availableTerms );
      // console.log( 'updateSelectedTerms::selectedTerms=', selectedTerms );

      this.setState({
        selectedTerms: selectedTerms
      });
    }
  }, {
    key: "onChange",
    value: function onChange(termNames) {
      var _this5 = this;

      var _this$props3 = this.props,
          onChange = _this$props3.onChange,
          renderField = _this$props3.renderField,
          returnField = _this$props3.returnField; // console.log( 'onChange::onChange=', termNames );

      var renderFieldToReturnField = function renderFieldToReturnField(names, availableTerms) {
        return names.map(function (termName) {
          return Object(lodash__WEBPACK_IMPORTED_MODULE_7__["find"])(availableTerms, function (term) {
            return isSameTermName(term[renderField], termName);
          })[returnField];
        });
      }; // console.log( 'termNames:', termNames );


      var uniqueTerms = Object(lodash__WEBPACK_IMPORTED_MODULE_7__["uniqBy"])(termNames, function (term) {
        return term.toLowerCase();
      });
      var addTerm = uniqueTerms.filter(function (termName) {
        return Object(lodash__WEBPACK_IMPORTED_MODULE_7__["find"])(_this5.state.availableTerms, function (term) {
          return isSameTermName(term[renderField], termName);
        });
      }); // console.log( 'addTerm:', addTerm );

      var selectedTermIDs = renderFieldToReturnField(addTerm, this.state.availableTerms); // console.log( 'selectedTermIDs:', selectedTermIDs );

      onChange(selectedTermIDs);
    }
  }, {
    key: "searchTerms",
    value: function searchTerms() {
      var search = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      Object(lodash__WEBPACK_IMPORTED_MODULE_7__["invoke"])(this.searchRequest, ['abort']);
      this.searchRequest = this.fetchTerms({
        search: search
      });
    }
  }, {
    key: "render",
    value: function render() {
      var _this$props4 = this.props,
          label = _this$props4.label,
          messages = _this$props4.messages;
      var _this$state = this.state,
          loading = _this$state.loading,
          availableTerms = _this$state.availableTerms,
          selectedTerms = _this$state.selectedTerms;
      var termNames = availableTerms.map(function (term) {
        return term.unescapeName;
      });
      return wp.element.createElement(FormTokenField, {
        value: selectedTerms,
        suggestions: termNames,
        onChange: this.onChange,
        onInputChange: this.searchTerms,
        maxSuggestions: MAX_TERMS_SUGGESTIONS,
        disabled: loading,
        label: label,
        messages: messages
      });
    }
  }]);

  return FilterTagSelector;
}(Component);

/* harmony default export */ __webpack_exports__["default"] = (FilterTagSelector);

/***/ }),

/***/ "./includes/blocks/components/hierarchical-term-selector/index.js":
/*!************************************************************************!*\
  !*** ./includes/blocks/components/hierarchical-term-selector/index.js ***!
  \************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/toConsumableArray */ "./node_modules/@babel/runtime/helpers/toConsumableArray.js");
/* harmony import */ var _babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/assertThisInitialized */ "./node_modules/@babel/runtime/helpers/assertThisInitialized.js");
/* harmony import */ var _babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! lodash */ "lodash");
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(lodash__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var _utils_terms__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ../../utils/terms */ "./includes/blocks/utils/terms.js");
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./style.scss */ "./includes/blocks/components/hierarchical-term-selector/style.scss");
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_10___default = /*#__PURE__*/__webpack_require__.n(_style_scss__WEBPACK_IMPORTED_MODULE_10__);









function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function () { var Super = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_7___default()(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_7___default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_6___default()(this, result); }; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */

var _wp$i18n = wp.i18n,
    __ = _wp$i18n.__,
    _x = _wp$i18n._x,
    _n = _wp$i18n._n,
    sprintf = _wp$i18n.sprintf;
var Component = wp.element.Component;
var _wp$components = wp.components,
    CheckboxControl = _wp$components.CheckboxControl,
    withSpokenMessages = _wp$components.withSpokenMessages,
    Spinner = _wp$components.Spinner; // const { withSelect, withDispatch } = wp.data;

var _wp$compose = wp.compose,
    withInstanceId = _wp$compose.withInstanceId,
    compose = _wp$compose.compose;
var _wp = wp,
    apiFetch = _wp.apiFetch;
var addQueryArgs = wp.url.addQueryArgs;
/**
 * Internal dependencies
 */



/**
 * Module Constants
 */

var DEFAULT_QUERY = {
  per_page: -1,
  orderby: 'name',
  order: 'asc',
  _fields: 'id,name,parent'
};
var MIN_TERMS_COUNT_FOR_FILTER = 8;

var HierarchicalTermSelector = /*#__PURE__*/function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_5___default()(HierarchicalTermSelector, _Component);

  var _super = _createSuper(HierarchicalTermSelector);

  function HierarchicalTermSelector() {
    var _this;

    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_2___default()(this, HierarchicalTermSelector);

    _this = _super.apply(this, arguments);
    _this.findTerm = _this.findTerm.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4___default()(_this));
    _this.onChange = _this.onChange.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4___default()(_this));
    _this.setFilterValue = _this.setFilterValue.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4___default()(_this));
    _this.sortBySelected = _this.sortBySelected.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4___default()(_this));
    _this.state = {
      loading: true,
      availableTermsTree: [],
      availableTerms: [],
      filterValue: '',
      filteredTermsTree: []
    };
    return _this;
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_3___default()(HierarchicalTermSelector, [{
    key: "onChange",
    value: function onChange(termId) {
      var _this$props = this.props,
          onChange = _this$props.onChange,
          terms = _this$props.terms; // const termId   = parseInt( event.target.value, 10 );

      var hasTerm = terms.indexOf(termId) !== -1;
      var newTerms = hasTerm ? Object(lodash__WEBPACK_IMPORTED_MODULE_8__["without"])(terms, termId) : [].concat(_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_1___default()(terms), [termId]);
      onChange(newTerms);
    }
  }, {
    key: "findTerm",
    value: function findTerm(terms, parent, name) {
      return Object(lodash__WEBPACK_IMPORTED_MODULE_8__["find"])(terms, function (term) {
        return (!term.parent && !parent || parseInt(term.parent) === parseInt(parent)) && term.name.toLowerCase() === name.toLowerCase();
      });
    }
  }, {
    key: "componentDidMount",
    value: function componentDidMount() {
      this.fetchTerms();
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      Object(lodash__WEBPACK_IMPORTED_MODULE_8__["invoke"])(this.fetchRequest, ['abort']);
    }
  }, {
    key: "fetchTerms",
    value: function fetchTerms() {
      var _this2 = this;

      var taxonomy = this.props.taxonomy;

      if (!taxonomy) {
        return;
      }

      this.fetchRequest = apiFetch({
        path: addQueryArgs("/cn-api/v1/".concat(taxonomy, "/"), DEFAULT_QUERY)
      });
      this.fetchRequest.then(function (terms) {
        // resolve
        var availableTermsTree = _this2.sortBySelected(Object(_utils_terms__WEBPACK_IMPORTED_MODULE_9__["buildTermsTree"])(terms));

        _this2.fetchRequest = null;

        _this2.setState({
          loading: false,
          availableTermsTree: availableTermsTree,
          availableTerms: terms
        });
      }, function (xhr) {
        // reject
        if (xhr.statusText === 'abort') {
          return;
        }

        _this2.fetchRequest = null;

        _this2.setState({
          loading: false
        });
      });
    }
  }, {
    key: "sortBySelected",
    value: function sortBySelected(termsTree) {
      var terms = this.props.terms;

      var treeHasSelection = function treeHasSelection(termTree) {
        if (terms.indexOf(termTree.id) !== -1) {
          return true;
        }

        if (undefined === termTree.children) {
          return false;
        }

        var anyChildIsSelected = termTree.children.map(treeHasSelection).filter(function (child) {
          return child;
        }).length > 0;

        if (anyChildIsSelected) {
          return true;
        }

        return false;
      };

      var termOrChildIsSelected = function termOrChildIsSelected(termA, termB) {
        var termASelected = treeHasSelection(termA);
        var termBSelected = treeHasSelection(termB);

        if (termASelected === termBSelected) {
          return 0;
        }

        if (termASelected && !termBSelected) {
          return -1;
        }

        if (!termASelected && termBSelected) {
          return 1;
        }

        return 0;
      };

      termsTree.sort(termOrChildIsSelected);
      return termsTree;
    }
  }, {
    key: "setFilterValue",
    value: function setFilterValue(event) {
      var availableTermsTree = this.state.availableTermsTree;
      var filterValue = event.target.value;
      var filteredTermsTree = availableTermsTree.map(this.getFilterMatcher(filterValue)).filter(function (term) {
        return term;
      });

      var getResultCount = function getResultCount(terms) {
        var count = 0;

        for (var i = 0; i < terms.length; i++) {
          count++;

          if (undefined !== terms[i].children) {
            count += getResultCount(terms[i].children);
          }
        }

        return count;
      };

      this.setState({
        filterValue: filterValue,
        filteredTermsTree: filteredTermsTree
      });
      var resultCount = getResultCount(filteredTermsTree);
      var resultsFoundMessage = sprintf(_n('%d result found.', '%d results found.', resultCount, 'connections'), resultCount);
      this.props.debouncedSpeak(resultsFoundMessage, 'assertive');
    }
  }, {
    key: "getFilterMatcher",
    value: function getFilterMatcher(filterValue) {
      var matchTermsForFilter = function matchTermsForFilter(originalTerm) {
        if ('' === filterValue) {
          return originalTerm;
        } // Shallow clone, because we'll be filtering the term's children and
        // don't want to modify the original term.


        var term = _objectSpread({}, originalTerm); // Map and filter the children, recursive so we deal with grandchildren
        // and any deeper levels.


        if (term.children.length > 0) {
          term.children = term.children.map(matchTermsForFilter).filter(function (child) {
            return child;
          });
        } // If the term's name contains the filterValue, or it has children
        // (i.e. some child matched at some point in the tree) then return it.


        if (-1 !== term.name.toLowerCase().indexOf(filterValue) || term.children.length > 0) {
          return term;
        } // Otherwise, return false. After mapping, the list of terms will need
        // to have false values filtered out.


        return false;
      };

      return matchTermsForFilter;
    }
  }, {
    key: "renderTerms",
    value: function renderTerms(renderedTerms) {
      var _this3 = this;

      var _this$props$terms = this.props.terms,
          terms = _this$props$terms === void 0 ? [] : _this$props$terms;
      return renderedTerms.map(function (term) {
        var id = "editor-post-taxonomies-hierarchical-term-".concat(term.id);
        return wp.element.createElement("div", {
          key: term.id,
          className: "editor-post-taxonomies__hierarchical-terms-choice cbd__hierarchical-terms-choice"
        }, wp.element.createElement(CheckboxControl, {
          checked: terms.indexOf(term.id) !== -1,
          onChange: function onChange() {
            var termId = parseInt(term.id, 10);

            _this3.onChange(termId);
          },
          label: Object(lodash__WEBPACK_IMPORTED_MODULE_8__["unescape"])(term.name)
        }), !!term.children.length && wp.element.createElement("div", {
          className: "editor-post-taxonomies__hierarchical-terms-subchoices"
        }, _this3.renderTerms(term.children)));
      });
    }
  }, {
    key: "render",
    value: function render() {
      var _this$props2 = this.props,
          taxonomy = _this$props2.taxonomy,
          instanceId = _this$props2.instanceId;
      var _this$state = this.state,
          availableTermsTree = _this$state.availableTermsTree,
          availableTerms = _this$state.availableTerms,
          filteredTermsTree = _this$state.filteredTermsTree,
          loading = _this$state.loading,
          filterValue = _this$state.filterValue;

      if (loading) {
        return wp.element.createElement("p", null, wp.element.createElement(Spinner, null), __('Loading Data', 'connections'));
      }

      var filterInputId = "editor-post-taxonomies__hierarchical-terms-filter-".concat(instanceId);
      var filterLabel = sprintf(_x('Search %s', 'term', 'connections'), Object(lodash__WEBPACK_IMPORTED_MODULE_8__["startCase"])(taxonomy));
      var groupLabel = sprintf(_x('Available %s', 'term', 'connections'), Object(lodash__WEBPACK_IMPORTED_MODULE_8__["startCase"])(taxonomy));
      var showFilter = availableTerms.length >= MIN_TERMS_COUNT_FOR_FILTER;
      return [showFilter && wp.element.createElement("label", {
        key: "filter-label",
        htmlFor: filterInputId
      }, filterLabel), showFilter && wp.element.createElement("input", {
        type: "search",
        id: filterInputId,
        value: filterValue,
        onChange: this.setFilterValue,
        className: "editor-post-taxonomies__hierarchical-terms-filter",
        key: "term-filter-input"
      }), wp.element.createElement("div", {
        className: "editor-post-taxonomies__hierarchical-terms-list",
        key: "term-list",
        tabIndex: "0",
        role: "group",
        "aria-label": groupLabel
      }, this.renderTerms('' !== filterValue ? filteredTermsTree : availableTermsTree))];
    }
  }]);

  return HierarchicalTermSelector;
}(Component);

/* harmony default export */ __webpack_exports__["default"] = (compose([// withSelect( ( select, { slug } ) => {
// 	// const { getCurrentPost } = select( 'core/editor' );
// 	// const { getTaxonomy } = select( 'core' );
// 	// const taxonomy = getTaxonomy( slug );
// 	return {
// 		// hasCreateAction: taxonomy ? get( getCurrentPost(), [ '_links', 'wp:action-create-' + taxonomy.rest_base ], false ) : false,
// 		// hasAssignAction: taxonomy ? get( getCurrentPost(), [ '_links', 'wp:action-assign-' + taxonomy.rest_base ], false ) : false,
// 		// terms: select( 'connections-directory/categories' ).getCategories( {per_page: -1} ),
// 		// terms: [],
// 		// taxonomy,
// 	};
// } ),
// withDispatch( ( dispatch ) => ( {
// 	onUpdateTerms( terms, restBase ) {
// 		dispatch( 'connections-directory/categories' ).receiveItems( terms );
// 	},
// } ) ),
withSpokenMessages, withInstanceId // withFilters( 'editor.PostTaxonomyType' ),
])(HierarchicalTermSelector));

/***/ }),

/***/ "./includes/blocks/components/hierarchical-term-selector/style.scss":
/*!**************************************************************************!*\
  !*** ./includes/blocks/components/hierarchical-term-selector/style.scss ***!
  \**************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "./includes/blocks/components/index.js":
/*!*********************************************!*\
  !*** ./includes/blocks/components/index.js ***!
  \*********************************************/
/*! exports provided: FilterTagSelector, HierarchicalTermSelector, PageSelect, RangeControl, EntryName, EntryTitle, EntryImage, EntryPhoneNumbers, EntryEmail, EntrySocialNetworks, EntryExcerpt, SocialNetworkIcon */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _filter_tag_selector___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./filter-tag-selector/ */ "./includes/blocks/components/filter-tag-selector/index.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "FilterTagSelector", function() { return _filter_tag_selector___WEBPACK_IMPORTED_MODULE_0__["default"]; });

/* harmony import */ var _hierarchical_term_selector___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./hierarchical-term-selector/ */ "./includes/blocks/components/hierarchical-term-selector/index.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "HierarchicalTermSelector", function() { return _hierarchical_term_selector___WEBPACK_IMPORTED_MODULE_1__["default"]; });

/* harmony import */ var _page_select___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./page-select/ */ "./includes/blocks/components/page-select/index.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "PageSelect", function() { return _page_select___WEBPACK_IMPORTED_MODULE_2__["default"]; });

/* harmony import */ var _range_control___WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./range-control/ */ "./includes/blocks/components/range-control/index.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "RangeControl", function() { return _range_control___WEBPACK_IMPORTED_MODULE_3__["default"]; });

/* harmony import */ var _entry_name__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./entry/name */ "./includes/blocks/components/entry/name.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "EntryName", function() { return _entry_name__WEBPACK_IMPORTED_MODULE_4__["default"]; });

/* harmony import */ var _entry_title__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./entry/title */ "./includes/blocks/components/entry/title.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "EntryTitle", function() { return _entry_title__WEBPACK_IMPORTED_MODULE_5__["default"]; });

/* harmony import */ var _entry_image__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./entry/image */ "./includes/blocks/components/entry/image.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "EntryImage", function() { return _entry_image__WEBPACK_IMPORTED_MODULE_6__["default"]; });

/* harmony import */ var _entry_phone_numbers__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./entry/phone-numbers */ "./includes/blocks/components/entry/phone-numbers.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "EntryPhoneNumbers", function() { return _entry_phone_numbers__WEBPACK_IMPORTED_MODULE_7__["default"]; });

/* harmony import */ var _entry_email__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./entry/email */ "./includes/blocks/components/entry/email.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "EntryEmail", function() { return _entry_email__WEBPACK_IMPORTED_MODULE_8__["default"]; });

/* harmony import */ var _entry_social_networks__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./entry/social-networks */ "./includes/blocks/components/entry/social-networks.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "EntrySocialNetworks", function() { return _entry_social_networks__WEBPACK_IMPORTED_MODULE_9__["default"]; });

/* harmony import */ var _entry_excerpt__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./entry/excerpt */ "./includes/blocks/components/entry/excerpt.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "EntryExcerpt", function() { return _entry_excerpt__WEBPACK_IMPORTED_MODULE_10__["default"]; });

/* harmony import */ var _entry_social_network_icon__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./entry/social-network-icon */ "./includes/blocks/components/entry/social-network-icon.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "SocialNetworkIcon", function() { return _entry_social_network_icon__WEBPACK_IMPORTED_MODULE_11__["default"]; });

/**
 * Export the component library so they can be imported like:
 *
 * import { PageSelect } from '@Connections-Directory/components';
 *
 * NOTE: This works because of the resolve alias config in webpack.config.js
 */




/**
 * Entry components.
 */










/***/ }),

/***/ "./includes/blocks/components/page-select/index.js":
/*!*********************************************************!*\
  !*** ./includes/blocks/components/page-select/index.js ***!
  \*********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/extends */ "./node_modules/@babel/runtime/helpers/extends.js");
/* harmony import */ var _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_objectWithoutProperties__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/objectWithoutProperties */ "./node_modules/@babel/runtime/helpers/objectWithoutProperties.js");
/* harmony import */ var _babel_runtime_helpers_objectWithoutProperties__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_objectWithoutProperties__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _utils_terms__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../utils/terms */ "./includes/blocks/utils/terms.js");



/**
 * External dependencies
 */
var _lodash = lodash,
    get = _lodash.get;
/**
 * WordPress dependencies
 */

var __ = wp.i18n.__;
var _wp$data = wp.data,
    select = _wp$data.select,
    withSelect = _wp$data.withSelect;
var Spinner = wp.components.Spinner;
/**
 * Internal dependencies
 */

var TreeSelect = wp.components.TreeSelect; // Import components



function PageSelect(_ref) {
  var postType = _ref.postType,
      postTypeMeta = _ref.postTypeMeta,
      label = _ref.label,
      value = _ref.value,
      noOptionLabel = _ref.noOptionLabel,
      options = _ref.options,
      onChange = _ref.onChange,
      props = _babel_runtime_helpers_objectWithoutProperties__WEBPACK_IMPORTED_MODULE_1___default()(_ref, ["postType", "postTypeMeta", "label", "value", "noOptionLabel", "options", "onChange"]);

  if (null === options) {
    return wp.element.createElement("p", null, wp.element.createElement(Spinner, null), __('Loading Data', 'connections'));
  } // const { getPostType } = select( 'core' );
  // const postTypeMeta   = getPostType( postType );


  var isHierarchical = get(postTypeMeta, ['hierarchical'], false);
  var pageItems = options || [];
  var pagesTree = [];

  if (!pageItems.length) {
    return null;
  }

  if (isHierarchical) {
    pagesTree = Object(_utils_terms__WEBPACK_IMPORTED_MODULE_2__["buildTermsTree"])(pageItems.map(function (item) {
      return {
        id: item.id,
        parent: item.parent,
        name: item.title.raw ? item.title.raw : "#".concat(item.id, " (").concat(__('no title'), ")")
      };
    }));
  } else {
    pagesTree = pageItems.map(function (item) {
      return {
        id: item.id,
        name: item.title.raw ? item.title.raw : "#".concat(item.id, " (").concat(__('no title'), ")")
      };
    });
  }

  return wp.element.createElement(TreeSelect, _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0___default()({
    className: "connections-directory--attributes__home_id",
    label: label,
    noOptionLabel: noOptionLabel,
    tree: pagesTree,
    selectedId: value,
    onChange: onChange
  }, props));
}

var applyWithSelect = withSelect(function (select, ownProps) {
  var _select = select('core'),
      getEntityRecords = _select.getEntityRecords,
      getPostType = _select.getPostType;

  var _select2 = select('core/editor'),
      getCurrentPostId = _select2.getCurrentPostId;

  var postType = typeof ownProps.postType === 'undefined' ? 'page' : ownProps.postType;
  var postTypeMeta = getPostType(postType);
  var postId = getCurrentPostId();
  var query = {
    per_page: -1,
    exclude: postId,
    parent_exclude: postId,
    orderby: 'title',
    order: 'asc',
    _fields: 'id,parent,title'
  };
  return {
    options: getEntityRecords('postType', postType, query),
    postTypeMeta: postTypeMeta
  };
}); // const renderedSelect = applyWithSelect( PageSelect );
// export { renderedSelect as PageSelect };

/* harmony default export */ __webpack_exports__["default"] = (applyWithSelect(PageSelect));

/***/ }),

/***/ "./includes/blocks/components/range-control/index.js":
/*!***********************************************************!*\
  !*** ./includes/blocks/components/range-control/index.js ***!
  \***********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/extends */ "./node_modules/@babel/runtime/helpers/extends.js");
/* harmony import */ var _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_objectWithoutProperties__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/objectWithoutProperties */ "./node_modules/@babel/runtime/helpers/objectWithoutProperties.js");
/* harmony import */ var _babel_runtime_helpers_objectWithoutProperties__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_objectWithoutProperties__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! classnames */ "./node_modules/classnames/index.js");
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(classnames__WEBPACK_IMPORTED_MODULE_2__);



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
      props = _babel_runtime_helpers_objectWithoutProperties__WEBPACK_IMPORTED_MODULE_1___default()(_ref, ["className", "label", "value", "instanceId", "onChange", "beforeIcon", "afterIcon", "help", "allowReset", "initialPosition"]);

  var id = "inspector-range-control-".concat(instanceId);

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
  return wp.element.createElement(BaseControl, {
    label: label,
    id: id,
    help: help,
    className: classnames__WEBPACK_IMPORTED_MODULE_2___default()('components-range-control', className)
  }, beforeIcon && wp.element.createElement(Dashicon, {
    icon: beforeIcon
  }), wp.element.createElement("input", _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0___default()({
    className: "components-range-control__slider",
    id: id,
    type: "range",
    value: initialSliderValue,
    onChange: onChangeValue,
    "aria-describedby": !!help ? id + '__help' : undefined
  }, props)), afterIcon && wp.element.createElement(Dashicon, {
    icon: afterIcon
  }), wp.element.createElement("input", _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0___default()({
    className: "components-range-control__number",
    type: "number",
    onChange: onChangeValue,
    "aria-label": label,
    value: initialSliderValue
  }, props)), allowReset && wp.element.createElement(Button, {
    onClick: resetValue,
    disabled: value === undefined
  }, __('Reset')));
} // const asInstance = withInstanceId( RangeControl );
// export { asInstance as RangeControl };


/* harmony default export */ __webpack_exports__["default"] = (withInstanceId(RangeControl));

/***/ }),

/***/ "./includes/blocks/components/utility/index.js":
/*!*****************************************************!*\
  !*** ./includes/blocks/components/utility/index.js ***!
  \*****************************************************/
/*! exports provided: isNumber */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "isNumber", function() { return isNumber; });
/**
 * Tests whether or not supplied variable is numeric or not.
 *
 * @todo This should be moved to a helper library.
 * @link https://stackoverflow.com/a/15043984/5351316
 *
 * @param n
 *
 * @return {boolean}
 */
function isNumber(n) {
  return (Object.prototype.toString.call(n) === '[object Number]' || Object.prototype.toString.call(n) === '[object String]') && !isNaN(parseFloat(n)) && isFinite(n.toString().replace(/^-/, ''));
}

/***/ }),

/***/ "./includes/blocks/directory/index.js":
/*!********************************************!*\
  !*** ./includes/blocks/directory/index.js ***!
  \********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @Connections-Directory/components */ "./includes/blocks/components/index.js");
/**
 * WordPress dependencies
 */
var _wp$i18n = wp.i18n,
    __ = _wp$i18n.__,
    _n = _wp$i18n._n,
    _nx = _wp$i18n._nx,
    _x = _wp$i18n._x; // const { select } = wp.data;

var registerBlockType = wp.blocks.registerBlockType;
var _wp$blockEditor = wp.blockEditor,
    InspectorControls = _wp$blockEditor.InspectorControls,
    InspectorAdvancedControls = _wp$blockEditor.InspectorAdvancedControls;
var _wp$components = wp.components,
    PanelBody = _wp$components.PanelBody,
    SelectControl = _wp$components.SelectControl,
    TextControl = _wp$components.TextControl,
    ToggleControl = _wp$components.ToggleControl;
var Fragment = wp.element.Fragment;
var _wp = wp,
    ServerSideRender = _wp.serverSideRender;
/**
 * Internal dependencies
 */

 // Import CSS
// import './styles/editor.scss';
// import './styles/public.scss';

var _cbDir$blockSettings = cbDir.blockSettings,
    entryTypes = _cbDir$blockSettings.entryTypes,
    dateTypes = _cbDir$blockSettings.dateTypes,
    templates = _cbDir$blockSettings.templates;
/**
 * Register Block
 */

/* harmony default export */ __webpack_exports__["default"] = (registerBlockType('connections-directory/shortcode-connections', {
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
    // Valid attribute types are: string, boolean, object, null, array, integer, number
    // @see link https://github.com/WordPress/gutenberg/blob/master/packages/blocks/src/api/parser.js
    advancedBlockOptions: {
      type: 'string',
      default: ''
    },
    categories: {
      type: 'string',
      default: '[]'
    },
    characterIndex: {
      type: 'boolean',
      default: true
    },
    city: {
      type: 'array',
      default: []
    },
    county: {
      type: 'array',
      default: []
    },
    country: {
      type: 'array',
      default: []
    },
    department: {
      type: 'array',
      default: []
    },
    district: {
      type: 'array',
      default: []
    },
    excludeCategories: {
      type: 'string',
      default: '[]'
    },
    forceHome: {
      type: 'boolean',
      default: false
    },
    fullName: {
      type: 'array',
      default: []
    },
    homePage: {
      type: 'string',
      default: ''
    },
    inCategories: {
      type: 'boolean',
      default: false
    },
    isEditorPreview: {
      type: 'boolean',
      default: true
    },
    lastName: {
      type: 'array',
      default: []
    },
    listType: {
      type: 'string',
      default: 'all'
    },
    order: {
      type: 'string',
      default: 'asc'
    },
    orderBy: {
      type: 'string',
      default: 'default'
    },
    orderRandom: {
      type: 'boolean',
      default: false
    },
    organization: {
      type: 'array',
      default: []
    },
    parseQuery: {
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
    },
    state: {
      type: 'array',
      default: []
    },
    template: {
      type: 'string',
      default: templates.active
    },
    title: {
      type: 'array',
      default: []
    },
    zipcode: {
      type: 'array',
      default: []
    }
  },
  edit: function edit(_ref) {
    var attributes = _ref.attributes,
        setAttributes = _ref.setAttributes;
    var advancedBlockOptions = attributes.advancedBlockOptions,
        categories = attributes.categories,
        characterIndex = attributes.characterIndex,
        city = attributes.city,
        county = attributes.county,
        country = attributes.country,
        department = attributes.department,
        district = attributes.district,
        excludeCategories = attributes.excludeCategories,
        forceHome = attributes.forceHome,
        fullName = attributes.fullName,
        homePage = attributes.homePage,
        inCategories = attributes.inCategories,
        lastName = attributes.lastName,
        listType = attributes.listType,
        order = attributes.order,
        orderBy = attributes.orderBy,
        orderRandom = attributes.orderRandom,
        organization = attributes.organization,
        parseQuery = attributes.parseQuery,
        repeatCharacterIndex = attributes.repeatCharacterIndex,
        sectionHead = attributes.sectionHead,
        state = attributes.state,
        template = attributes.template,
        title = attributes.title,
        zipcode = attributes.zipcode; // const { getCurrentPostId } = select( 'core/editor' );
    // const postId               = getCurrentPostId();

    var templateOptions = [];
    var entryTypeSelectOptions = [];
    var dateTypeSelectOptions = [];

    for (var property in templates.registered) {
      // noinspection JSUnfilteredForInLoop
      templateOptions.push({
        label: templates.registered[property],
        value: property
      });
    }

    for (var _property in entryTypes) {
      // noinspection JSUnfilteredForInLoop
      entryTypeSelectOptions.push({
        label: entryTypes[_property],
        value: _property
      });
    }

    for (var _property2 in dateTypes) {
      // noinspection JSUnfilteredForInLoop
      dateTypeSelectOptions.push({
        label: __('Date:', 'connections') + ' ' + dateTypes[_property2],
        value: _property2
      });
    }

    return wp.element.createElement(Fragment, null, wp.element.createElement(InspectorControls, null, wp.element.createElement(PanelBody, {
      title: __('Character Index', 'connections'),
      initialOpen: false
    }, wp.element.createElement(ToggleControl, {
      label: __('Display Character Index?', 'connections'),
      help: __('Display the A-Z index above the directory.', 'connections'),
      checked: !!characterIndex,
      onChange: function onChange() {
        return setAttributes({
          characterIndex: !characterIndex
        });
      }
    }), wp.element.createElement(ToggleControl, {
      label: __('Repeat Character Index?', 'connections'),
      help: __('Repeat the Character Index at the beginning of each character group.', 'connections'),
      checked: !!repeatCharacterIndex,
      onChange: function onChange() {
        return setAttributes({
          repeatCharacterIndex: !repeatCharacterIndex
        });
      }
    }), wp.element.createElement(ToggleControl, {
      label: __('Display Current Character Heading?', 'connections'),
      help: __('Display the current character heading at the beginning of each character group.', 'connections'),
      checked: !!sectionHead,
      onChange: function onChange() {
        return setAttributes({
          sectionHead: !sectionHead
        });
      }
    })), wp.element.createElement(PanelBody, {
      title: __('Template', 'connections'),
      initialOpen: false
    }, wp.element.createElement(SelectControl, {
      label: __('Template', 'connections'),
      help: __('Select which to use when displaying the directory.', 'connections'),
      value: template,
      options: templateOptions,
      onChange: function onChange(template) {
        return setAttributes({
          template: template
        });
      }
    })), wp.element.createElement(PanelBody, {
      title: __('Select', 'connections'),
      initialOpen: true
    }, wp.element.createElement("p", null, __('This section controls which entries from your directory will be displayed.', 'connections')), wp.element.createElement("div", {
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(SelectControl, {
      label: __('Entry Type', 'connections'),
      help: __('Select which entry type to display. The default is to display all.', 'connections'),
      value: listType,
      options: [{
        label: __('All', 'connections'),
        value: 'all'
      }].concat(entryTypeSelectOptions),
      onChange: function onChange(listType) {
        return setAttributes({
          listType: listType
        });
      }
    })), wp.element.createElement("div", {
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement("p", null, __('Choose the categories to include in the entry list.', 'connections'))), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["HierarchicalTermSelector"], {
      taxonomy: "category",
      terms: JSON.parse(categories),
      onChange: function onChange(categories) {
        return setAttributes({
          categories: JSON.stringify(categories)
        });
      }
    }), wp.element.createElement("div", {
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(ToggleControl, {
      label: __('Entries must be assigned to all the above chosen categories?', 'connections') // help={__( '', 'connections' )}
      ,
      checked: !!inCategories,
      onChange: function onChange() {
        return setAttributes({
          inCategories: !inCategories
        });
      }
    })), wp.element.createElement("div", {
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement("p", null, __('Choose the categories to exclude from the entry list.', 'connections'))), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["HierarchicalTermSelector"], {
      taxonomy: "category",
      terms: JSON.parse(excludeCategories),
      onChange: function onChange(excludeCategories) {
        return setAttributes({
          excludeCategories: JSON.stringify(excludeCategories)
        });
      }
    })), wp.element.createElement(PanelBody, {
      title: __('Filters', 'connections'),
      initialOpen: false
    }, wp.element.createElement("div", {
      className: 'components-base-control',
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement("p", null, __('Only the entries which match all of the selected filters below will be displayed in the results list.', 'connections')), wp.element.createElement("p", {
      className: 'components-base-control__help',
      style: {
        marginTop: '4px'
      }
    }, __('Start typing for suggestions. You must choose a suggested term for the filter to be applied. More than one term can be entered per filter. If more than one is entered, as long as the entry has a term in any of the inputs, it will display as a result.', 'connections'))), wp.element.createElement("div", {
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["FilterTagSelector"], {
      type: 'name' // The autocomplete type.
      ,
      label: __('Full Name', 'connections'),
      renderField: 'name' // The field to display as tags / in autocomplete list.
      ,
      getFields: 'id,name' // The fields to return from the REST query.
      ,
      returnField: 'id' // This is the field to return and saved.
      ,
      messages: {
        added: __('Full Name Added to Filter', 'connections'),
        removed: __('Full Name Removed', 'connections'),
        remove: __('Remove Full Name from Filter', 'connections')
      },
      terms: fullName,
      onChange: function onChange(value) {
        return setAttributes({
          fullName: value
        });
      }
    })), wp.element.createElement("div", {
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["FilterTagSelector"], {
      type: 'last_name' // The autocomplete type.
      ,
      label: __('Last Name', 'connections'),
      renderField: 'last_name' // The field to display as tags / in autocomplete list.
      ,
      getFields: 'last_name' // The fields to return from the REST query.
      ,
      returnField: 'last_name' // This is the field to return and saved.
      ,
      messages: {
        added: __('Last Name Added to Filter', 'connections'),
        removed: __('Last Name Removed', 'connections'),
        remove: __('Remove Last Name from Filter', 'connections')
      },
      terms: lastName,
      onChange: function onChange(value) {
        return setAttributes({
          lastName: value
        });
      }
    })), wp.element.createElement("div", {
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["FilterTagSelector"], {
      type: 'title' // The autocomplete type.
      ,
      label: __('Title', 'connections'),
      renderField: 'title' // The field to display as tags / in autocomplete list.
      ,
      getFields: 'title' // The fields to return from the REST query.
      ,
      returnField: 'title' // This is the field to return and saved.
      ,
      messages: {
        added: __('Title Added to Filter', 'connections'),
        removed: __('Title Removed', 'connections'),
        remove: __('Remove Title from Filter', 'connections')
      },
      terms: title,
      onChange: function onChange(value) {
        return setAttributes({
          title: value
        });
      }
    })), wp.element.createElement("div", {
      className: 'components-base-control',
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["FilterTagSelector"], {
      type: 'department' // The autocomplete type.
      ,
      label: __('Department', 'connections'),
      renderField: 'department' // The field to display as tags / in autocomplete list.
      ,
      getFields: 'department' // The fields to return from the REST query.
      ,
      returnField: 'department' // This is the field to return and saved.
      ,
      messages: {
        added: __('Department Added to Filter', 'connections'),
        removed: __('Department Removed', 'connections'),
        remove: __('Remove Department from Filter', 'connections')
      },
      terms: department,
      onChange: function onChange(value) {
        return setAttributes({
          department: value
        });
      }
    })), wp.element.createElement("div", {
      className: 'components-base-control',
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["FilterTagSelector"], {
      type: 'organization' // The autocomplete type.
      ,
      label: __('Organization', 'connections'),
      renderField: 'organization' // The field to display as tags / in autocomplete list.
      ,
      getFields: 'organization' // The fields to return from the REST query.
      ,
      returnField: 'organization' // This is the field to return and saved.
      ,
      messages: {
        added: __('Organization Added to Filter', 'connections'),
        removed: __('Organization Removed', 'connections'),
        remove: __('Remove Organization from Filter', 'connections')
      },
      terms: organization,
      onChange: function onChange(value) {
        return setAttributes({
          organization: value
        });
      }
    })), wp.element.createElement("div", {
      className: 'components-base-control',
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["FilterTagSelector"], {
      type: 'district' // The autocomplete type.
      ,
      label: __('District', 'connections'),
      renderField: 'district' // The field to display as tags / in autocomplete list.
      ,
      getFields: 'district' // The fields to return from the REST query.
      ,
      returnField: 'district' // This is the field to return and saved.
      ,
      messages: {
        added: __('District Added to Filter', 'connections'),
        removed: __('District Removed', 'connections'),
        remove: __('Remove District from Filter', 'connections')
      },
      terms: district,
      onChange: function onChange(value) {
        return setAttributes({
          district: value
        });
      }
    })), wp.element.createElement("div", {
      className: 'components-base-control',
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["FilterTagSelector"], {
      type: 'county' // The autocomplete type.
      ,
      label: __('County', 'connections'),
      renderField: 'county' // The field to display as tags / in autocomplete list.
      ,
      getFields: 'county' // The fields to return from the REST query.
      ,
      returnField: 'county' // This is the field to return and saved.
      ,
      messages: {
        added: __('County Added to Filter', 'connections'),
        removed: __('County Removed', 'connections'),
        remove: __('Remove County from Filter', 'connections')
      },
      terms: county,
      onChange: function onChange(value) {
        return setAttributes({
          county: value
        });
      }
    })), wp.element.createElement("div", {
      className: 'components-base-control',
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["FilterTagSelector"], {
      type: 'city' // The autocomplete type.
      ,
      label: __('City', 'connections'),
      renderField: 'city' // The field to display as tags / in autocomplete list.
      ,
      getFields: 'city' // The fields to return from the REST query.
      ,
      returnField: 'city' // This is the field to return and saved.
      ,
      messages: {
        added: __('City Added to Filter', 'connections'),
        removed: __('City Removed', 'connections'),
        remove: __('Remove City from Filter', 'connections')
      },
      terms: city,
      onChange: function onChange(value) {
        return setAttributes({
          city: value
        });
      }
    })), wp.element.createElement("div", {
      className: 'components-base-control',
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["FilterTagSelector"], {
      type: 'state' // The autocomplete type.
      ,
      label: __('State', 'connections'),
      renderField: 'state' // The field to display as tags / in autocomplete list.
      ,
      getFields: 'state' // The fields to return from the REST query.
      ,
      returnField: 'state' // This is the field to return and saved.
      ,
      messages: {
        added: __('State Added to Filter', 'connections'),
        removed: __('State Removed', 'connections'),
        remove: __('Remove State from Filter', 'connections')
      },
      terms: state,
      onChange: function onChange(value) {
        return setAttributes({
          state: value
        });
      }
    })), wp.element.createElement("div", {
      className: 'components-base-control',
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["FilterTagSelector"], {
      type: 'zipcode' // The autocomplete type.
      ,
      label: __('Zipcode', 'connections'),
      renderField: 'zipcode' // The field to display as tags / in autocomplete list.
      ,
      getFields: 'zipcode' // The fields to return from the REST query.
      ,
      returnField: 'zipcode' // This is the field to return and saved.
      ,
      messages: {
        added: __('Zipcode Added to Filter', 'connections'),
        removed: __('Zipcode Removed', 'connections'),
        remove: __('Remove Zipcode from Filter', 'connections')
      },
      terms: zipcode,
      onChange: function onChange(value) {
        return setAttributes({
          zipcode: value
        });
      }
    })), wp.element.createElement("div", {
      className: 'components-base-control',
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["FilterTagSelector"], {
      type: 'country' // The autocomplete type.
      ,
      label: __('Country', 'connections'),
      renderField: 'country' // The field to display as tags / in autocomplete list.
      ,
      getFields: 'country' // The fields to return from the REST query.
      ,
      returnField: 'country' // This is the field to return and saved.
      ,
      messages: {
        added: __('Country Added to Filter', 'connections'),
        removed: __('Country Removed', 'connections'),
        remove: __('Remove Country from Filter', 'connections')
      },
      terms: country,
      onChange: function onChange(value) {
        return setAttributes({
          country: value
        });
      }
    }))), wp.element.createElement(PanelBody, {
      title: __('Order', 'connections'),
      initialOpen: false
    }, wp.element.createElement("p", null, __('This section controls the order in which the selected entries will be displayed.', 'connections')), wp.element.createElement(SelectControl, {
      label: __('Order By', 'connections'),
      value: orderBy,
      options: [{
        label: __('Default', 'connections'),
        value: 'default'
      }, {
        label: __('First Name', 'connections'),
        value: 'first_name'
      }, {
        label: __('Last Name', 'connections'),
        value: 'last_name'
      }, {
        label: __('Title', 'connections'),
        value: 'title'
      }, {
        label: __('Organization', 'connections'),
        value: 'organization'
      }, {
        label: __('Department', 'connections'),
        value: 'department'
      }, {
        label: __('City', 'connections'),
        value: 'city'
      }, {
        label: __('State', 'connections'),
        value: 'state'
      }, {
        label: __('Zipcode', 'connections'),
        value: 'zipcode'
      }, {
        label: __('Country', 'connections'),
        value: 'country'
      }, {
        label: __('Date: Entry Added', 'connections'),
        value: 'date_added'
      }, {
        label: __('Date: Entry Last Modified', 'connections'),
        value: 'date_modified'
      }].concat(dateTypeSelectOptions),
      onChange: function onChange(orderBy) {
        return setAttributes({
          orderBy: orderBy
        });
      },
      disabled: !!orderRandom
    }), wp.element.createElement(SelectControl, {
      label: __('Order', 'connections'),
      value: order,
      options: [{
        label: __('Ascending', 'connections'),
        value: 'asc'
      }, {
        label: __('Descending', 'connections'),
        value: 'desc'
      }, {
        label: __('Random', 'connections'),
        value: 'random'
      }],
      onChange: function onChange(order) {
        return setAttributes({
          order: order,
          orderBy: 'random' === order ? 'default' : orderBy,
          orderRandom: 'random' === order
        });
      }
    }))), wp.element.createElement(InspectorAdvancedControls, null, wp.element.createElement("p", null, __('This section controls advanced options which effect the directory features and functions.', 'connections')), wp.element.createElement(ToggleControl, {
      label: __('Parse query?', 'connections'),
      help: __('Permit the Directory block instance to parse queries in order to affect the displayed results. Example, allowing keyword searches. The default is to allow query parsing.', 'connections'),
      checked: !!parseQuery,
      onChange: function onChange() {
        return setAttributes({
          parseQuery: !parseQuery
        });
      }
    }), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["PageSelect"] // postType={'post'}
    , {
      label: __('Directory Home Page', 'connections'),
      noOptionLabel: __('Current Page', 'connections'),
      value: homePage,
      onChange: function onChange(homePage) {
        return setAttributes({
          homePage: homePage
        });
      },
      disabled: !!forceHome
    }), wp.element.createElement(ToggleControl, {
      label: __('Force directory permalinks to resolve to the Global Directory Homes page?', 'connections'),
      checked: !!forceHome,
      onChange: function onChange() {
        return setAttributes({
          forceHome: !forceHome,
          homePage: ''
        });
      }
    }), wp.element.createElement(TextControl, {
      label: __('Additional Options', 'connections'),
      value: advancedBlockOptions,
      onChange: function onChange(newValue) {
        setAttributes({
          advancedBlockOptions: newValue
        });
      }
    })), wp.element.createElement(ServerSideRender, {
      attributes: attributes,
      block: "connections-directory/shortcode-connections"
    }));
  },
  save: function save() {
    // Server side rendering via shortcode.
    return null;
  }
}));

/***/ }),

/***/ "./includes/blocks/store/categories.js":
/*!*********************************************!*\
  !*** ./includes/blocks/store/categories.js ***!
  \*********************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__);


function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

/**
 * WordPress dependencies
 */
var addQueryArgs = wp.url.addQueryArgs;
var registerStore = wp.data.registerStore;
var _wp = wp,
    apiFetch = _wp.apiFetch;
var actions = {
  receiveItems: function receiveItems(terms) {
    return {
      type: 'RECEIVE_ITEMS',
      terms: terms
    };
  },
  fetchFromAPI: function fetchFromAPI(path, query) {
    return {
      type: 'FETCH_FROM_API',
      path: path,
      query: query
    };
  }
};
var DEFAULT_STATE = {
  terms: []
};
registerStore('connections-directory/categories', {
  reducer: function reducer() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : DEFAULT_STATE;
    var action = arguments.length > 1 ? arguments[1] : undefined;

    // console.log( 'state', state );
    // console.log( 'action', action );
    switch (action.type) {
      case 'RECEIVE_ITEMS':
        return _objectSpread(_objectSpread({}, state), {}, {
          terms: action.terms
        });
    }

    return state;
  },
  actions: actions,
  selectors: {
    getCategories: function getCategories(state, query) {
      // console.log( 'state', state );
      // console.log( 'query', query );
      return state.terms;
    }
  },
  controls: {
    FETCH_FROM_API: function FETCH_FROM_API(action) {
      return apiFetch({
        path: addQueryArgs(action.path, action.query)
      });
    }
  },
  resolvers: {
    getCategories: /*#__PURE__*/regeneratorRuntime.mark(function getCategories() {
      var query,
          terms,
          _args = arguments;
      return regeneratorRuntime.wrap(function getCategories$(_context) {
        while (1) {
          switch (_context.prev = _context.next) {
            case 0:
              query = _args.length > 0 && _args[0] !== undefined ? _args[0] : {};
              console.log(query);
              _context.next = 4;
              return actions.fetchFromAPI("/cn-api/v1/category/", query);

            case 4:
              terms = _context.sent;
              _context.next = 7;
              return actions.receiveItems(terms, query);

            case 7:
            case "end":
              return _context.stop();
          }
        }
      }, getCategories);
    })
  }
});

/***/ }),

/***/ "./includes/blocks/store/entries.js":
/*!******************************************!*\
  !*** ./includes/blocks/store/entries.js ***!
  \******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__);


function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

/**
 * WordPress dependencies
 */
var _wp = wp,
    apiFetch = _wp.apiFetch;
var registerStore = wp.data.registerStore;
var addQueryArgs = wp.url.addQueryArgs;
var ENDPOINT = '/cn-api/v1/entry/';
var actions = {
  addEntities: function addEntities(entities) {
    // console.log( 'actions=>addEntities::entities', entities );
    return {
      type: 'ADD_ENTITIES',
      entities: entities
    };
  },
  receiveEntityRecords: function receiveEntityRecords(path) {
    // console.log( 'actions=>receiveEntityRecords::path', path );
    return {
      type: 'RECEIVE_ENTITY_RECORDS',
      path: path
    };
  }
};
var store = registerStore('connections-directory/entries', {
  reducer: function reducer() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
      entities: {},
      entityType: 'all'
    };
    var action = arguments.length > 1 ? arguments[1] : undefined;

    // console.log( 'reducer::state', state );
    // console.log( 'reducer::action', action );
    switch (action.type) {
      case 'ADD_ENTITIES':
        return _objectSpread(_objectSpread({}, state), {}, {
          entities: action.entities
        });
    }

    return state;
  },
  actions: actions,
  selectors: {
    getEntityRecords: function getEntityRecords(state, query) {
      // console.log( 'selectors=>getEntityRecords::state', state );
      // console.log( 'selectors=>getEntityRecords::query', query );
      var entities = state.entities;
      return entities;
    }
  },
  controls: {
    RECEIVE_ENTITY_RECORDS: function RECEIVE_ENTITY_RECORDS(action) {
      // console.log( 'controls=>RECEIVE_ENTITY_RECORDS::query', action );
      return apiFetch({
        path: action.path
      });
    }
  },
  resolvers: {
    getEntityRecords: /*#__PURE__*/regeneratorRuntime.mark(function getEntityRecords() {
      var query,
          path,
          entities,
          _args = arguments;
      return regeneratorRuntime.wrap(function getEntityRecords$(_context) {
        while (1) {
          switch (_context.prev = _context.next) {
            case 0:
              query = _args.length > 0 && _args[0] !== undefined ? _args[0] : {};
              // console.log( 'resolvers=>getEntityRecords::query', query );
              path = addQueryArgs(ENDPOINT, _objectSpread(_objectSpread({}, query), {}, {
                context: 'edit'
              }));
              _context.next = 4;
              return actions.receiveEntityRecords(path);

            case 4:
              entities = _context.sent;
              return _context.abrupt("return", actions.addEntities(entities));

            case 6:
            case "end":
              return _context.stop();
          }
        }
      }, getEntityRecords);
    })
  }
});
/* harmony default export */ __webpack_exports__["default"] = (store);

/***/ }),

/***/ "./includes/blocks/team/index.js":
/*!***************************************!*\
  !*** ./includes/blocks/team/index.js ***!
  \***************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @Connections-Directory/components */ "./includes/blocks/components/index.js");
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./style.scss */ "./includes/blocks/team/style.scss");
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_style_scss__WEBPACK_IMPORTED_MODULE_1__);
/**
 * WordPress dependencies
 */
var _wp$i18n = wp.i18n,
    __ = _wp$i18n.__,
    _n = _wp$i18n._n,
    _nx = _wp$i18n._nx,
    _x = _wp$i18n._x; // const { select } = wp.data;

var registerBlockType = wp.blocks.registerBlockType;
var _wp$components = wp.components,
    BaseControl = _wp$components.BaseControl,
    ColorIndicator = _wp$components.ColorIndicator,
    ColorPalette = _wp$components.ColorPalette,
    PanelBody = _wp$components.PanelBody,
    RadioControl = _wp$components.RadioControl,
    SelectControl = _wp$components.SelectControl,
    TextControl = _wp$components.TextControl,
    ToggleControl = _wp$components.ToggleControl;
var _wp$blockEditor = wp.blockEditor,
    InspectorControls = _wp$blockEditor.InspectorControls,
    InspectorAdvancedControls = _wp$blockEditor.InspectorAdvancedControls;
var Fragment = wp.element.Fragment;
var _wp = wp,
    ServerSideRender = _wp.serverSideRender;
/**
 * Internal dependencies
 */


/**
 * Import styles.
 */


var entryTypes = cbDir.blockSettings.entryTypes;

var colorIndicator = function colorIndicator(label, value) {
  return wp.element.createElement(Fragment, null, label, value && wp.element.createElement(ColorIndicator, {
    colorValue: value
  }));
};
/**
 * Register Block
 */


/* harmony default export */ __webpack_exports__["default"] = (registerBlockType('connections-directory/team', {
  title: __('Team', 'connections'),
  description: __('Display members of your team. Use multiple Team blocks to create a team page.', 'connections'),
  category: 'connections-directory',
  // icon:        giveLogo,
  keywords: ['connections', __('team', 'connections'), __('person', 'connections')],
  supports: {
    // Remove the support for the generated className.
    className: false,
    // Remove the support for the custom className.
    customClassName: false,
    // Remove the support for editing the block using the block HTML editor.
    html: false
  },
  attributes: {
    // Valid attribute types are: string, boolean, object, null, array, integer, number
    // @see link https://github.com/WordPress/gutenberg/blob/master/packages/blocks/src/api/parser.js
    advancedBlockOptions: {
      type: 'string',
      default: ''
    },
    categories: {
      type: 'string',
      default: '[]'
    },
    categoriesExclude: {
      type: 'string',
      default: '[]'
    },
    categoriesIn: {
      type: 'boolean',
      default: false
    },
    columns: {
      type: 'integer',
      default: 3
    },
    borderColor: {
      default: '#BABABA'
    },
    borderRadius: {
      type: 'integer',
      default: 12
    },
    borderWidth: {
      type: 'integer',
      default: 1
    },
    displayDropShadow: {
      type: 'boolean',
      default: true
    },
    displayEmail: {
      type: 'boolean',
      default: true
    },
    displayExcerpt: {
      type: 'boolean',
      default: false
    },
    displayPhone: {
      type: 'boolean',
      default: true
    },
    displaySocial: {
      type: 'boolean',
      default: true
    },
    displayTitle: {
      type: 'boolean',
      default: true
    },
    excerptWordLimit: {
      type: 'string',
      default: '10'
    },
    gutterWidth: {
      type: 'integer',
      default: 25
    },
    imageBorderColor: {
      default: '#BABABA'
    },
    imageBorderRadius: {
      type: 'integer',
      default: 0
    },
    imageBorderWidth: {
      type: 'integer',
      default: 0
    },
    imageCropMode: {
      type: 'string',
      default: '1'
    },
    imageShape: {
      type: 'string',
      default: 'square'
    },
    imageType: {
      type: 'string',
      default: 'photo'
    },
    isEditorPreview: {
      type: 'boolean',
      default: true
    },
    layout: {
      type: 'string',
      default: 'grid'
    },
    listType: {
      type: 'string',
      default: 'all'
    },
    // rows:              {
    // 	type:    'integer',
    // 	default: 1,
    // },
    position: {
      type: 'string',
      default: 'left'
    },
    style: {
      type: 'string',
      default: 'clean'
    },
    variation: {
      type: 'string',
      default: 'card'
    }
  },
  edit: function edit(_ref) {
    var attributes = _ref.attributes,
        setAttributes = _ref.setAttributes;
    var advancedBlockOptions = attributes.advancedBlockOptions,
        categories = attributes.categories,
        categoriesExclude = attributes.categoriesExclude,
        categoriesIn = attributes.categoriesIn,
        columns = attributes.columns,
        borderColor = attributes.borderColor,
        borderRadius = attributes.borderRadius,
        borderWidth = attributes.borderWidth,
        displayDropShadow = attributes.displayDropShadow,
        displayEmail = attributes.displayEmail,
        displayExcerpt = attributes.displayExcerpt,
        displayPhone = attributes.displayPhone,
        displaySocial = attributes.displaySocial,
        displayTitle = attributes.displayTitle,
        excerptWordLimit = attributes.excerptWordLimit,
        gutterWidth = attributes.gutterWidth,
        imageBorderColor = attributes.imageBorderColor,
        imageBorderRadius = attributes.imageBorderRadius,
        imageBorderWidth = attributes.imageBorderWidth,
        imageCropMode = attributes.imageCropMode,
        imageShape = attributes.imageShape,
        imageType = attributes.imageType,
        layout = attributes.layout,
        listType = attributes.listType,
        position = attributes.position,
        style = attributes.style,
        variation = attributes.variation;
    var entryTypeSelectOptions = [];

    for (var property in entryTypes) {
      // noinspection JSUnfilteredForInLoop
      entryTypeSelectOptions.push({
        label: entryTypes[property],
        value: property
      });
    }
    /*
     * Ideas:
     * - https://codecanyon.net/item/the-team-pro-team-showcase-wordpress-plugin/17521235
     * - https://codecanyon.net/item/heroes-assemble-team-showcase-wordpress-plugin/11469747
     * - https://wpdarko.com/items/team-members-pro/
     * - http://teammembers.themescode.com/
     *
     * @Style Options
     * - Grid
     * -- Drawer
     * --- Full Overlay
     * --- Bottom Overlay
     * -- Modal
     * --- Full Overlay
     * --- Bottom Overlay
     * -- Slide
     * --- Left
     * ---- Full Overlay
     * ---- Bottom Overlay
     * --- Right
     * ---- Full Overlay
     * ---- Bottom Overlay
     * -- Tile
     * --- Slide Up
     * --- Card Pop Up
     * --- Overlay
     * -- Circle
     * --- Overlay
     * --- Fade
     * --- Flip
     * -- Card
     * --- Clean/Simple
     * --- Overlay
     * --- Flip
     * - Table
     * - List
     * -- Circle
     * --- Left
     * --- Right
     * -- Square
     * --- Left
     * --- Right
     */


    var setLayoutDefaults = function setLayoutDefaults(layout) {
      switch (layout) {
        case 'grid':
          setAttributes({
            columns: 3,
            displayDropShadow: true,
            displayExcerpt: false,
            excerptWordLimit: 10,
            gutterWidth: 25,
            imageShape: 'square',
            layout: layout,
            style: 'clean',
            variation: 'card'
          });
          break;

        case 'list':
          setAttributes({
            borderRadius: 0,
            borderWidth: 0,
            displayDropShadow: false,
            displayExcerpt: true,
            excerptWordLimit: 55,
            imageShape: 'square',
            layout: layout,
            position: 'left'
          });
          break;

        case 'table':
          setAttributes({
            borderRadius: 0,
            borderWidth: 0,
            displayDropShadow: false,
            displayExcerpt: true,
            excerptWordLimit: 55,
            imageShape: 'square',
            layout: layout // rows:       1,

          });
          break;
      }
    };

    var setVariationDefaults = function setVariationDefaults(variation) {
      switch (variation) {
        case 'card':
          setAttributes({
            imageShape: 'square',
            style: 'clean',
            variation: variation
          });
          break;

        case 'circle':
          setAttributes({
            imageShape: 'circle',
            style: 'fade',
            variation: variation
          });
          break;

        case 'drawer':
          setAttributes({
            imageShape: 'square',
            style: 'overlay-bottom',
            variation: variation
          });
          break;

        case 'modal':
          setAttributes({
            imageShape: 'square',
            variation: variation
          });
          break;

        case 'slide':
          setAttributes({
            imageShape: 'square',
            style: 'overlay-bottom',
            position: 'left',
            variation: variation
          });
          break;
      }
    };

    var variationOptions = function variationOptions() {
      var options = [];

      switch (layout) {
        case 'grid':
          options = [{
            value: 'card',
            label: __('Card', 'connections')
          } // { value: 'circle', label: __( 'Circle', 'connections' ) },
          // { value: 'drawer', label: __( 'Drawer', 'connections' ) },
          // { value: 'modal', label: __( 'Modal', 'connections' ) },
          // { value: 'slide', label: __( 'Slide', 'connections' ) },
          ];
          return wp.element.createElement(RadioControl, {
            label: __('Variation', 'connections'),
            selected: variation,
            options: options,
            onChange: function onChange(value) {
              setVariationDefaults(value);
            }
          });

        case 'list':
          return null;

        case 'table':
          return null;

        default:
          return null;
      }
    };

    var positionOptions = function positionOptions() {
      if (layout === 'list' || layout === 'grid' && variation === 'slide') {
        var options = [{
          value: 'left',
          label: __('Left', 'connections')
        }, {
          value: 'right',
          label: __('Right', 'connections')
        }];
        return wp.element.createElement(RadioControl, {
          label: __('Position', 'connections'),
          selected: position,
          options: options,
          onChange: function onChange(value) {
            return setAttributes({
              position: value
            });
          }
        });
      }
    };

    var styleOptions = function styleOptions() {
      var options = [];

      if ('grid' === layout) {
        switch (variation) {
          case 'card':
            options.push({
              value: 'clean',
              label: __('Clean', 'connections')
            });
            options.push({
              value: 'flip',
              label: __('Flip', 'connections')
            });
            options.push({
              value: 'slide',
              label: __('Slide', 'connections')
            });
            options.push({
              value: 'overlay',
              label: __('Overlay', 'connections'),
              disabled: true
            });
            break;

          case 'circle':
            options.push({
              value: 'fade',
              label: __('Fade', 'connections')
            });
            options.push({
              value: 'flip',
              label: __('Flip', 'connections')
            });
            options.push({
              value: 'overlay',
              label: __('Overlay', 'connections')
            });
            break;

          case 'drawer':
            options.push({
              value: 'overlay-bottom',
              label: __('Bottom Overlay', 'connections')
            });
            options.push({
              value: 'overlay-full',
              label: __('Full Overlay', 'connections')
            });
            break;

          case 'modal':
            options.push({
              value: 'overlay-bottom',
              label: __('Bottom Overlay', 'connections')
            });
            options.push({
              value: 'overlay-full',
              label: __('Full Overlay', 'connections')
            });
            break;

          case 'slide':
            options.push({
              value: 'overlay-bottom',
              label: __('Bottom Overlay', 'connections')
            });
            options.push({
              value: 'overlay-full',
              label: __('Full Overlay', 'connections')
            });
            break;
        }
      }

      if (0 < options.length) {
        return wp.element.createElement(RadioControl, {
          label: __('Style', 'connections'),
          selected: style,
          options: options,
          onChange: function onChange(value) {
            return setAttributes({
              style: value
            });
          }
        });
      }
    };

    return wp.element.createElement(Fragment, null, wp.element.createElement(InspectorControls, null, wp.element.createElement(PanelBody, {
      title: __('Layout', 'connections'),
      initialOpen: true
    }, wp.element.createElement(RadioControl, {
      label: __('Layout', 'connections'),
      selected: layout,
      options: [{
        value: 'grid',
        label: __('Grid', 'connections')
      }, {
        value: 'list',
        label: __('List', 'connections')
      }, {
        value: 'table',
        label: __('Table', 'connections')
      }],
      onChange: function onChange(value) {
        setLayoutDefaults(value);
      }
    }), variationOptions(), styleOptions(), positionOptions(), layout === 'grid' && wp.element.createElement(Fragment, null, wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["RangeControl"], {
      label: __('Columns', 'connections'),
      value: columns,
      min: 1,
      max: 5,
      initialPosition: 3,
      allowReset: false,
      onChange: function onChange(value) {
        return setAttributes({
          columns: value
        });
      }
    }), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["RangeControl"], {
      label: __('Gutter Width', 'connections'),
      help: __('The space between columns.', 'connections'),
      value: gutterWidth,
      min: 0,
      max: 50,
      initialPosition: 25,
      allowReset: true,
      onChange: function onChange(value) {
        return setAttributes({
          gutterWidth: value
        });
      }
    })),  false && false), wp.element.createElement(PanelBody, {
      title: __('Select', 'connections'),
      initialOpen: false
    }, wp.element.createElement("p", null, __('This section controls which entries from your directory will be displayed.', 'connections')), wp.element.createElement("div", {
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(SelectControl, {
      label: __('Entry Type', 'connections'),
      help: __('Select which entry type to display. The default is to display all.', 'connections'),
      value: listType,
      options: [{
        label: __('All', 'connections'),
        value: 'all'
      }].concat(entryTypeSelectOptions),
      onChange: function onChange(value) {
        return setAttributes({
          listType: value
        });
      }
    })), wp.element.createElement("div", {
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement("p", null, __('Choose the categories to include in the entry list.', 'connections'))), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["HierarchicalTermSelector"], {
      taxonomy: "category",
      terms: JSON.parse(categories),
      onChange: function onChange(value) {
        return setAttributes({
          categories: JSON.stringify(value)
        });
      }
    }), wp.element.createElement("div", {
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(ToggleControl, {
      label: __('Entries must be assigned to all the above chosen categories?', 'connections') // help={__( '', 'connections' )}
      ,
      checked: !!categoriesIn,
      onChange: function onChange() {
        return setAttributes({
          categoriesIn: !categoriesIn
        });
      }
    })), wp.element.createElement("div", {
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement("p", null, __('Choose the categories to exclude from the entry list.', 'connections'))), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["HierarchicalTermSelector"], {
      taxonomy: "category",
      terms: JSON.parse(categoriesExclude),
      onChange: function onChange(value) {
        return setAttributes({
          Categories: JSON.stringify(value)
        });
      }
    })), wp.element.createElement(PanelBody, {
      title: __('Image', 'connections'),
      initialOpen: false
    }, wp.element.createElement("div", {
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(RadioControl, {
      label: __('Type', 'connections'),
      selected: imageType,
      options: [{
        value: 'logo',
        label: __('Logo', 'connections')
      }, {
        value: 'photo',
        label: __('Photo', 'connections')
      }],
      onChange: function onChange(value) {
        return setAttributes({
          imageType: value
        });
      }
    }), !(variation === 'circle' || variation === 'drawer') && wp.element.createElement(RadioControl, {
      label: __('Shape', 'connections'),
      selected: imageShape,
      options: [{
        value: 'circle',
        label: __('Circle', 'connections')
      }, {
        value: 'square',
        label: __('Square', 'connections')
      }],
      onChange: function onChange(value) {
        return setAttributes({
          imageShape: value
        });
      }
    }))), wp.element.createElement(PanelBody, {
      title: __('Display Details', 'connections'),
      initialOpen: false
    }, wp.element.createElement("div", {
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(ToggleControl, {
      label: __('Display Title?', 'connections'),
      checked: !!displayTitle,
      onChange: function onChange() {
        return setAttributes({
          displayTitle: !displayTitle
        });
      }
    }), wp.element.createElement(ToggleControl, {
      label: __('Display Excerpt?', 'connections'),
      checked: !!displayExcerpt,
      onChange: function onChange() {
        return setAttributes({
          displayExcerpt: !displayExcerpt
        });
      }
    }), wp.element.createElement(ToggleControl, {
      label: __('Display Primary Phone?', 'connections'),
      checked: !!displayPhone,
      onChange: function onChange() {
        return setAttributes({
          displayPhone: !displayPhone
        });
      }
    }), wp.element.createElement(ToggleControl, {
      label: __('Display Primary Email?', 'connections'),
      checked: !!displayEmail,
      onChange: function onChange() {
        return setAttributes({
          displayEmail: !displayEmail
        });
      }
    }), wp.element.createElement(ToggleControl, {
      label: __('Display Social Networks?', 'connections'),
      checked: !!displaySocial,
      onChange: function onChange() {
        return setAttributes({
          displaySocial: !displaySocial
        });
      }
    }))), wp.element.createElement(PanelBody, {
      title: __('Style', 'connections'),
      initialOpen: false
    }, wp.element.createElement("div", {
      style: {
        marginTop: '20px'
      }
    }, (layout === 'grid' || layout === 'list') && wp.element.createElement(Fragment, null, wp.element.createElement(BaseControl, {
      className: "editor-color-palette-control",
      label: colorIndicator(__('Border Color', 'connections'), borderColor)
    }, wp.element.createElement(ColorPalette, {
      className: "editor-color-palette-control__color-palette",
      value: borderColor,
      onChange: function onChange(value) {
        return setAttributes({
          borderColor: value
        });
      }
    })), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["RangeControl"], {
      label: __('Border Radius', 'connections'),
      value: borderRadius,
      min: 0,
      max: 20,
      initialPosition: 12,
      allowReset: true,
      onChange: function onChange(value) {
        return setAttributes({
          borderRadius: value
        });
      }
    }), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["RangeControl"], {
      label: __('Border Width', 'connections'),
      value: borderWidth,
      min: 0,
      max: 5,
      initialPosition: 1,
      allowReset: true,
      onChange: function onChange(value) {
        return setAttributes({
          borderWidth: value
        });
      }
    }), wp.element.createElement(ToggleControl, {
      label: __('Display Drop Shadow?', 'connections'),
      checked: !!displayDropShadow,
      onChange: function onChange() {
        return setAttributes({
          displayDropShadow: !displayDropShadow
        });
      }
    }), wp.element.createElement(BaseControl, {
      className: "editor-color-palette-control",
      label: colorIndicator(__('Image Border Color', 'connections'), imageBorderColor)
    }, wp.element.createElement(ColorPalette, {
      className: "editor-color-palette-control__color-palette",
      value: imageBorderColor,
      onChange: function onChange(value) {
        return setAttributes({
          imageBorderColor: value
        });
      }
    }))), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["RangeControl"], {
      label: __('Image Border Width', 'connections'),
      value: imageBorderWidth,
      min: 0,
      max: 5,
      initialPosition: 0,
      allowReset: true,
      onChange: function onChange(value) {
        return setAttributes({
          imageBorderWidth: value
        });
      }
    }), imageShape === 'square' && wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["RangeControl"], {
      label: __('Image Border Radius', 'connections'),
      value: imageBorderRadius,
      min: 0,
      max: 20,
      initialPosition: 0,
      allowReset: true,
      onChange: function onChange(value) {
        return setAttributes({
          imageBorderRadius: value
        });
      }
    })))), wp.element.createElement(InspectorAdvancedControls, null, wp.element.createElement("div", {
      style: {
        marginTop: '20px'
      }
    }, wp.element.createElement(RadioControl, {
      label: __('Image Crop Mode', 'connections'),
      selected: imageCropMode,
      options: [{
        value: '1',
        label: __('Crop and resize proportionally, maintaining the aspect ratio. Crop is based on image center.', 'connections')
      }, {
        value: '2',
        label: __('Resize proportionally to fit entire image and add margins if required.', 'connections')
      }, // {
      // 	value: '3',
      // 	label: __( 'Resize proportionally adjusting the size of scaled image so there are no margins added.', 'connections' )
      // },
      {
        value: '0',
        label: __('Resize to fit, no cropping. Image aspect ratio will not be maintained and will likely result in a stretch or squashed image.', 'connections')
      }],
      onChange: function onChange(value) {
        return setAttributes({
          imageCropMode: value
        });
      }
    }), wp.element.createElement(TextControl, {
      label: __('Excerpt Word Limit', 'connections'),
      help: __('Enter 0 for the first sentence only. If the excerpt exceeds the word limit, the excerpt will be truncated at the of the next sentence if it can be determined automatically, so the excerpt word limit may be exceeded in order to display a complete sentence.', 'connections'),
      type: 'number',
      value: excerptWordLimit,
      onChange: function onChange(value) {
        return setAttributes({
          excerptWordLimit: value
        });
      }
    }), wp.element.createElement(TextControl, {
      label: __('Additional Options', 'connections'),
      value: advancedBlockOptions,
      onChange: function onChange(value) {
        return setAttributes({
          advancedBlockOptions: value
        });
      }
    }))), wp.element.createElement(ServerSideRender, {
      attributes: attributes,
      block: "connections-directory/team"
    }));
  },
  save: function save() {
    // Server side rendering via shortcode.
    return null;
  }
}));

/***/ }),

/***/ "./includes/blocks/team/style.scss":
/*!*****************************************!*\
  !*** ./includes/blocks/team/style.scss ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "./includes/blocks/upcoming/index.js":
/*!*******************************************!*\
  !*** ./includes/blocks/upcoming/index.js ***!
  \*******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @Connections-Directory/components */ "./includes/blocks/components/index.js");
/* harmony import */ var _styles_editor_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./styles/editor.scss */ "./includes/blocks/upcoming/styles/editor.scss");
/* harmony import */ var _styles_editor_scss__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_styles_editor_scss__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _styles_public_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./styles/public.scss */ "./includes/blocks/upcoming/styles/public.scss");
/* harmony import */ var _styles_public_scss__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_styles_public_scss__WEBPACK_IMPORTED_MODULE_2__);
/**
 * WordPress dependencies
 */
var _wp$i18n = wp.i18n,
    __ = _wp$i18n.__,
    _n = _wp$i18n._n,
    _nx = _wp$i18n._nx,
    _x = _wp$i18n._x;
var registerBlockType = wp.blocks.registerBlockType;
var _wp$blockEditor = wp.blockEditor,
    InspectorControls = _wp$blockEditor.InspectorControls,
    InspectorAdvancedControls = _wp$blockEditor.InspectorAdvancedControls;
var _wp$components = wp.components,
    ExternalLink = _wp$components.ExternalLink,
    PanelBody = _wp$components.PanelBody,
    RadioControl = _wp$components.RadioControl,
    SelectControl = _wp$components.SelectControl,
    TextControl = _wp$components.TextControl,
    ToggleControl = _wp$components.ToggleControl;
var Fragment = wp.element.Fragment;
var _wp = wp,
    ServerSideRender = _wp.serverSideRender;
/**
 * Internal dependencies
 */

 // Import CSS



var dateTypes = cbDir.blockSettings.dateTypes;
/**
 * Register Block
 */

/* harmony default export */ __webpack_exports__["default"] = (registerBlockType('connections-directory/shortcode-upcoming', {
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

    return wp.element.createElement(Fragment, null, wp.element.createElement(InspectorControls, null, wp.element.createElement(PanelBody, {
      title: __('Settings', 'connections')
    }, wp.element.createElement(SelectControl, {
      label: __('Type', 'connections'),
      value: listType,
      options: dateTypeSelectOptions,
      onChange: function onChange(listType) {
        return setAttributes({
          listType: listType
        });
      }
    }), wp.element.createElement(SelectControl, {
      label: __('Style', 'connections'),
      value: template,
      options: [{
        label: 'Light',
        value: 'anniversary-light'
      }, {
        label: 'Dark',
        value: 'anniversary-dark'
      }],
      onChange: function onChange(template) {
        return setAttributes({
          template: template
        });
      }
    }), wp.element.createElement(TextControl, {
      label: __('Heading', 'connections'),
      help: __('Type %d to insert the number of days in the heading.', 'connections'),
      placeholder: __('Type the heading here.', 'connections'),
      value: heading,
      onChange: function onChange(newValue) {
        setAttributes({
          heading: newValue
        });
      }
    }), wp.element.createElement(ToggleControl, {
      label: __('Display last name?', 'connections'),
      checked: !!displayLastName,
      onChange: function onChange() {
        return setAttributes({
          displayLastName: !displayLastName
        });
      }
    }), wp.element.createElement(_Connections_Directory_components__WEBPACK_IMPORTED_MODULE_0__["RangeControl"], {
      label: __('The number of days ahead to display.', 'connections'),
      help: __('To display date events for today only, slide the slider to 0 and enable the Include today option.', 'connections'),
      value: days,
      onChange: function onChange(days) {
        return setAttributes({
          days: days
        });
      },
      min: 0,
      max: 90,
      allowReset: true,
      initialPosition: 30
    }), wp.element.createElement(ToggleControl, {
      label: __('Include today?', 'connections'),
      help: __('Whether or not to include the date events for today.', 'connections'),
      checked: !!includeToday,
      onChange: function onChange() {
        return setAttributes({
          includeToday: !includeToday
        });
      }
    }), wp.element.createElement(RadioControl, {
      label: __('Year Display', 'connections') // help={__( '', 'connections' )}
      ,
      selected: yearType,
      options: [{
        label: __('Original Year', 'connections'),
        value: 'original'
      }, {
        label: __('Upcoming Year', 'connections'),
        value: 'upcoming'
      }, {
        label: __('Years Since', 'connections'),
        value: 'since'
      }],
      onChange: function onChange(newValue) {
        setAttributes({
          yearType: newValue
        });
      }
    }), wp.element.createElement(TextControl, {
      label: __('No Results Notice', 'connections'),
      help: __('This message is displayed when there are no upcoming event dates within the specified number of days.', 'connections'),
      placeholder: __('Type the no result message here.', 'connections'),
      value: noResults,
      onChange: function onChange(newValue) {
        setAttributes({
          noResults: newValue
        });
      }
    }))), wp.element.createElement(InspectorAdvancedControls, null, wp.element.createElement(TextControl, {
      label: __('Date Format', 'connections'),
      help: wp.element.createElement(ExternalLink, {
        href: "https://codex.wordpress.org/Formatting_Date_and_Time",
        target: "_blank"
      }, __('Documentation on date and time formatting.', 'connections')),
      value: dateFormat,
      onChange: function onChange(newValue) {
        setAttributes({
          dateFormat: newValue
        });
      }
    }), wp.element.createElement(TextControl, {
      label: __('Years Since Format', 'connections'),
      help: wp.element.createElement(ExternalLink, {
        href: "http://php.net/manual/en/dateinterval.format.php",
        target: "_blank"
      }, __('Documentation on date interval formatting.', 'connections')),
      value: yearFormat,
      onChange: function onChange(newValue) {
        setAttributes({
          yearFormat: newValue
        });
      }
    }), wp.element.createElement(TextControl, {
      label: __('Additional Options', 'connections'),
      value: advancedBlockOptions,
      onChange: function onChange(newValue) {
        setAttributes({
          advancedBlockOptions: newValue
        });
      }
    })), wp.element.createElement(ServerSideRender, {
      attributes: attributes,
      block: "connections-directory/shortcode-upcoming"
    }));
  },
  save: function save() {
    // Server side rendering via shortcode.
    return null;
  }
}));

/***/ }),

/***/ "./includes/blocks/upcoming/styles/editor.scss":
/*!*****************************************************!*\
  !*** ./includes/blocks/upcoming/styles/editor.scss ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "./includes/blocks/upcoming/styles/public.scss":
/*!*****************************************************!*\
  !*** ./includes/blocks/upcoming/styles/public.scss ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "./includes/blocks/utils/terms.js":
/*!****************************************!*\
  !*** ./includes/blocks/utils/terms.js ***!
  \****************************************/
/*! exports provided: buildTermsTree */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "buildTermsTree", function() { return buildTermsTree; });
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__);


function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

/**
 * External dependencies
 */
var _lodash = lodash,
    groupBy = _lodash.groupBy;
/**
 * Returns terms in a tree form.
 *
 * Copies from WP core since it does not appear to be available in the global wp object.
 * @link https://github.com/WordPress/gutenberg/blob/master/packages/editor/src/utils/terms.js
 *
 * @param {Array} flatTerms  Array of terms in flat format.
 *
 * @return {Array} Array of terms in tree format.
 */

function buildTermsTree(flatTerms) {
  var termsByParent = groupBy(flatTerms, 'parent');

  var fillWithChildren = function fillWithChildren(terms) {
    return terms.map(function (term) {
      var children = termsByParent[term.id];
      return _objectSpread(_objectSpread({}, term), {}, {
        children: children && children.length ? fillWithChildren(children) : []
      });
    });
  };

  return fillWithChildren(termsByParent['0'] || []);
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/arrayWithoutHoles.js":
/*!******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/arrayWithoutHoles.js ***!
  \******************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _arrayWithoutHoles(arr) {
  if (Array.isArray(arr)) {
    for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) {
      arr2[i] = arr[i];
    }

    return arr2;
  }
}

module.exports = _arrayWithoutHoles;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/assertThisInitialized.js":
/*!**********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/assertThisInitialized.js ***!
  \**********************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _assertThisInitialized(self) {
  if (self === void 0) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }

  return self;
}

module.exports = _assertThisInitialized;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/classCallCheck.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/classCallCheck.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

module.exports = _classCallCheck;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/createClass.js":
/*!************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/createClass.js ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

module.exports = _createClass;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/defineProperty.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/defineProperty.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperty(obj, key, value) {
  if (key in obj) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
  } else {
    obj[key] = value;
  }

  return obj;
}

module.exports = _defineProperty;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/extends.js":
/*!********************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/extends.js ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _extends() {
  module.exports = _extends = Object.assign || function (target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i];

      for (var key in source) {
        if (Object.prototype.hasOwnProperty.call(source, key)) {
          target[key] = source[key];
        }
      }
    }

    return target;
  };

  return _extends.apply(this, arguments);
}

module.exports = _extends;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/getPrototypeOf.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _getPrototypeOf(o) {
  module.exports = _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) {
    return o.__proto__ || Object.getPrototypeOf(o);
  };
  return _getPrototypeOf(o);
}

module.exports = _getPrototypeOf;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/inherits.js":
/*!*********************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/inherits.js ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var setPrototypeOf = __webpack_require__(/*! ./setPrototypeOf */ "./node_modules/@babel/runtime/helpers/setPrototypeOf.js");

function _inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function");
  }

  subClass.prototype = Object.create(superClass && superClass.prototype, {
    constructor: {
      value: subClass,
      writable: true,
      configurable: true
    }
  });
  if (superClass) setPrototypeOf(subClass, superClass);
}

module.exports = _inherits;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/iterableToArray.js":
/*!****************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/iterableToArray.js ***!
  \****************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _iterableToArray(iter) {
  if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter);
}

module.exports = _iterableToArray;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/nonIterableSpread.js":
/*!******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/nonIterableSpread.js ***!
  \******************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _nonIterableSpread() {
  throw new TypeError("Invalid attempt to spread non-iterable instance");
}

module.exports = _nonIterableSpread;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/objectDestructuringEmpty.js":
/*!*************************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/objectDestructuringEmpty.js ***!
  \*************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _objectDestructuringEmpty(obj) {
  if (obj == null) throw new TypeError("Cannot destructure undefined");
}

module.exports = _objectDestructuringEmpty;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/objectWithoutProperties.js":
/*!************************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/objectWithoutProperties.js ***!
  \************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var objectWithoutPropertiesLoose = __webpack_require__(/*! ./objectWithoutPropertiesLoose */ "./node_modules/@babel/runtime/helpers/objectWithoutPropertiesLoose.js");

function _objectWithoutProperties(source, excluded) {
  if (source == null) return {};
  var target = objectWithoutPropertiesLoose(source, excluded);
  var key, i;

  if (Object.getOwnPropertySymbols) {
    var sourceSymbolKeys = Object.getOwnPropertySymbols(source);

    for (i = 0; i < sourceSymbolKeys.length; i++) {
      key = sourceSymbolKeys[i];
      if (excluded.indexOf(key) >= 0) continue;
      if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue;
      target[key] = source[key];
    }
  }

  return target;
}

module.exports = _objectWithoutProperties;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/objectWithoutPropertiesLoose.js":
/*!*****************************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/objectWithoutPropertiesLoose.js ***!
  \*****************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _objectWithoutPropertiesLoose(source, excluded) {
  if (source == null) return {};
  var target = {};
  var sourceKeys = Object.keys(source);
  var key, i;

  for (i = 0; i < sourceKeys.length; i++) {
    key = sourceKeys[i];
    if (excluded.indexOf(key) >= 0) continue;
    target[key] = source[key];
  }

  return target;
}

module.exports = _objectWithoutPropertiesLoose;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js":
/*!**************************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js ***!
  \**************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var _typeof = __webpack_require__(/*! ../helpers/typeof */ "./node_modules/@babel/runtime/helpers/typeof.js");

var assertThisInitialized = __webpack_require__(/*! ./assertThisInitialized */ "./node_modules/@babel/runtime/helpers/assertThisInitialized.js");

function _possibleConstructorReturn(self, call) {
  if (call && (_typeof(call) === "object" || typeof call === "function")) {
    return call;
  }

  return assertThisInitialized(self);
}

module.exports = _possibleConstructorReturn;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/setPrototypeOf.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/setPrototypeOf.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _setPrototypeOf(o, p) {
  module.exports = _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
    o.__proto__ = p;
    return o;
  };

  return _setPrototypeOf(o, p);
}

module.exports = _setPrototypeOf;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/toConsumableArray.js":
/*!******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/toConsumableArray.js ***!
  \******************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var arrayWithoutHoles = __webpack_require__(/*! ./arrayWithoutHoles */ "./node_modules/@babel/runtime/helpers/arrayWithoutHoles.js");

var iterableToArray = __webpack_require__(/*! ./iterableToArray */ "./node_modules/@babel/runtime/helpers/iterableToArray.js");

var nonIterableSpread = __webpack_require__(/*! ./nonIterableSpread */ "./node_modules/@babel/runtime/helpers/nonIterableSpread.js");

function _toConsumableArray(arr) {
  return arrayWithoutHoles(arr) || iterableToArray(arr) || nonIterableSpread();
}

module.exports = _toConsumableArray;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/typeof.js":
/*!*******************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/typeof.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _typeof2(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof2 = function _typeof2(obj) { return typeof obj; }; } else { _typeof2 = function _typeof2(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof2(obj); }

function _typeof(obj) {
  if (typeof Symbol === "function" && _typeof2(Symbol.iterator) === "symbol") {
    module.exports = _typeof = function _typeof(obj) {
      return _typeof2(obj);
    };
  } else {
    module.exports = _typeof = function _typeof(obj) {
      return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : _typeof2(obj);
    };
  }

  return _typeof(obj);
}

module.exports = _typeof;

/***/ }),

/***/ "./node_modules/classnames/index.js":
/*!******************************************!*\
  !*** ./node_modules/classnames/index.js ***!
  \******************************************/
/*! no static exports found */
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

	if ( true && module.exports) {
		classNames.default = classNames;
		module.exports = classNames;
	} else if (true) {
		// register as 'classnames', consistent with npm package name
		!(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_RESULT__ = (function () {
			return classNames;
		}).apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
	} else {}
}());


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js!./node_modules/slick-carousel/slick/slick-theme.css":
/*!*************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js!./node_modules/slick-carousel/slick/slick-theme.css ***!
  \*************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// Imports
var ___CSS_LOADER_API_IMPORT___ = __webpack_require__(/*! ../../css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
var ___CSS_LOADER_GET_URL_IMPORT___ = __webpack_require__(/*! ../../css-loader/dist/runtime/getUrl.js */ "./node_modules/css-loader/dist/runtime/getUrl.js");
var ___CSS_LOADER_URL_IMPORT_0___ = __webpack_require__(/*! ./ajax-loader.gif */ "./node_modules/slick-carousel/slick/ajax-loader.gif");
var ___CSS_LOADER_URL_IMPORT_1___ = __webpack_require__(/*! ./fonts/slick.eot */ "./node_modules/slick-carousel/slick/fonts/slick.eot");
var ___CSS_LOADER_URL_IMPORT_2___ = __webpack_require__(/*! ./fonts/slick.woff */ "./node_modules/slick-carousel/slick/fonts/slick.woff");
var ___CSS_LOADER_URL_IMPORT_3___ = __webpack_require__(/*! ./fonts/slick.ttf */ "./node_modules/slick-carousel/slick/fonts/slick.ttf");
var ___CSS_LOADER_URL_IMPORT_4___ = __webpack_require__(/*! ./fonts/slick.svg */ "./node_modules/slick-carousel/slick/fonts/slick.svg");
exports = ___CSS_LOADER_API_IMPORT___(false);
var ___CSS_LOADER_URL_REPLACEMENT_0___ = ___CSS_LOADER_GET_URL_IMPORT___(___CSS_LOADER_URL_IMPORT_0___);
var ___CSS_LOADER_URL_REPLACEMENT_1___ = ___CSS_LOADER_GET_URL_IMPORT___(___CSS_LOADER_URL_IMPORT_1___);
var ___CSS_LOADER_URL_REPLACEMENT_2___ = ___CSS_LOADER_GET_URL_IMPORT___(___CSS_LOADER_URL_IMPORT_1___, { hash: "?#iefix" });
var ___CSS_LOADER_URL_REPLACEMENT_3___ = ___CSS_LOADER_GET_URL_IMPORT___(___CSS_LOADER_URL_IMPORT_2___);
var ___CSS_LOADER_URL_REPLACEMENT_4___ = ___CSS_LOADER_GET_URL_IMPORT___(___CSS_LOADER_URL_IMPORT_3___);
var ___CSS_LOADER_URL_REPLACEMENT_5___ = ___CSS_LOADER_GET_URL_IMPORT___(___CSS_LOADER_URL_IMPORT_4___, { hash: "#slick" });
// Module
exports.push([module.i, "@charset 'UTF-8';\n/* Slider */\n.slick-loading .slick-list\n{\n    background: #fff url(" + ___CSS_LOADER_URL_REPLACEMENT_0___ + ") center center no-repeat;\n}\n\n/* Icons */\n@font-face\n{\n    font-family: 'slick';\n    font-weight: normal;\n    font-style: normal;\n\n    src: url(" + ___CSS_LOADER_URL_REPLACEMENT_1___ + ");\n    src: url(" + ___CSS_LOADER_URL_REPLACEMENT_2___ + ") format('embedded-opentype'), url(" + ___CSS_LOADER_URL_REPLACEMENT_3___ + ") format('woff'), url(" + ___CSS_LOADER_URL_REPLACEMENT_4___ + ") format('truetype'), url(" + ___CSS_LOADER_URL_REPLACEMENT_5___ + ") format('svg');\n}\n/* Arrows */\n.slick-prev,\n.slick-next\n{\n    font-size: 0;\n    line-height: 0;\n\n    position: absolute;\n    top: 50%;\n\n    display: block;\n\n    width: 20px;\n    height: 20px;\n    padding: 0;\n    -webkit-transform: translate(0, -50%);\n    -ms-transform: translate(0, -50%);\n    transform: translate(0, -50%);\n\n    cursor: pointer;\n\n    color: transparent;\n    border: none;\n    outline: none;\n    background: transparent;\n}\n.slick-prev:hover,\n.slick-prev:focus,\n.slick-next:hover,\n.slick-next:focus\n{\n    color: transparent;\n    outline: none;\n    background: transparent;\n}\n.slick-prev:hover:before,\n.slick-prev:focus:before,\n.slick-next:hover:before,\n.slick-next:focus:before\n{\n    opacity: 1;\n}\n.slick-prev.slick-disabled:before,\n.slick-next.slick-disabled:before\n{\n    opacity: .25;\n}\n\n.slick-prev:before,\n.slick-next:before\n{\n    font-family: 'slick';\n    font-size: 20px;\n    line-height: 1;\n\n    opacity: .75;\n    color: white;\n\n    -webkit-font-smoothing: antialiased;\n    -moz-osx-font-smoothing: grayscale;\n}\n\n.slick-prev\n{\n    left: -25px;\n}\n[dir='rtl'] .slick-prev\n{\n    right: -25px;\n    left: auto;\n}\n.slick-prev:before\n{\n    content: '←';\n}\n[dir='rtl'] .slick-prev:before\n{\n    content: '→';\n}\n\n.slick-next\n{\n    right: -25px;\n}\n[dir='rtl'] .slick-next\n{\n    right: auto;\n    left: -25px;\n}\n.slick-next:before\n{\n    content: '→';\n}\n[dir='rtl'] .slick-next:before\n{\n    content: '←';\n}\n\n/* Dots */\n.slick-dotted.slick-slider\n{\n    margin-bottom: 30px;\n}\n\n.slick-dots\n{\n    position: absolute;\n    bottom: -25px;\n\n    display: block;\n\n    width: 100%;\n    padding: 0;\n    margin: 0;\n\n    list-style: none;\n\n    text-align: center;\n}\n.slick-dots li\n{\n    position: relative;\n\n    display: inline-block;\n\n    width: 20px;\n    height: 20px;\n    margin: 0 5px;\n    padding: 0;\n\n    cursor: pointer;\n}\n.slick-dots li button\n{\n    font-size: 0;\n    line-height: 0;\n\n    display: block;\n\n    width: 20px;\n    height: 20px;\n    padding: 5px;\n\n    cursor: pointer;\n\n    color: transparent;\n    border: 0;\n    outline: none;\n    background: transparent;\n}\n.slick-dots li button:hover,\n.slick-dots li button:focus\n{\n    outline: none;\n}\n.slick-dots li button:hover:before,\n.slick-dots li button:focus:before\n{\n    opacity: 1;\n}\n.slick-dots li button:before\n{\n    font-family: 'slick';\n    font-size: 6px;\n    line-height: 20px;\n\n    position: absolute;\n    top: 0;\n    left: 0;\n\n    width: 20px;\n    height: 20px;\n\n    content: '•';\n    text-align: center;\n\n    opacity: .25;\n    color: black;\n\n    -webkit-font-smoothing: antialiased;\n    -moz-osx-font-smoothing: grayscale;\n}\n.slick-dots li.slick-active button:before\n{\n    opacity: .75;\n    color: black;\n}\n", ""]);
// Exports
module.exports = exports;


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js!./node_modules/slick-carousel/slick/slick.css":
/*!*******************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js!./node_modules/slick-carousel/slick/slick.css ***!
  \*******************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// Imports
var ___CSS_LOADER_API_IMPORT___ = __webpack_require__(/*! ../../css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
exports = ___CSS_LOADER_API_IMPORT___(false);
// Module
exports.push([module.i, "/* Slider */\n.slick-slider\n{\n    position: relative;\n\n    display: block;\n    box-sizing: border-box;\n\n    -webkit-user-select: none;\n       -moz-user-select: none;\n        -ms-user-select: none;\n            user-select: none;\n\n    -webkit-touch-callout: none;\n    -khtml-user-select: none;\n    -ms-touch-action: pan-y;\n        touch-action: pan-y;\n    -webkit-tap-highlight-color: transparent;\n}\n\n.slick-list\n{\n    position: relative;\n\n    display: block;\n    overflow: hidden;\n\n    margin: 0;\n    padding: 0;\n}\n.slick-list:focus\n{\n    outline: none;\n}\n.slick-list.dragging\n{\n    cursor: pointer;\n    cursor: hand;\n}\n\n.slick-slider .slick-track,\n.slick-slider .slick-list\n{\n    -webkit-transform: translate3d(0, 0, 0);\n       -moz-transform: translate3d(0, 0, 0);\n        -ms-transform: translate3d(0, 0, 0);\n         -o-transform: translate3d(0, 0, 0);\n            transform: translate3d(0, 0, 0);\n}\n\n.slick-track\n{\n    position: relative;\n    top: 0;\n    left: 0;\n\n    display: block;\n    margin-left: auto;\n    margin-right: auto;\n}\n.slick-track:before,\n.slick-track:after\n{\n    display: table;\n\n    content: '';\n}\n.slick-track:after\n{\n    clear: both;\n}\n.slick-loading .slick-track\n{\n    visibility: hidden;\n}\n\n.slick-slide\n{\n    display: none;\n    float: left;\n\n    height: 100%;\n    min-height: 1px;\n}\n[dir='rtl'] .slick-slide\n{\n    float: right;\n}\n.slick-slide img\n{\n    display: block;\n}\n.slick-slide.slick-loading img\n{\n    display: none;\n}\n.slick-slide.dragging img\n{\n    pointer-events: none;\n}\n.slick-initialized .slick-slide\n{\n    display: block;\n}\n.slick-loading .slick-slide\n{\n    visibility: hidden;\n}\n.slick-vertical .slick-slide\n{\n    display: block;\n\n    height: auto;\n\n    border: 1px solid transparent;\n}\n.slick-arrow.slick-hidden {\n    display: none;\n}\n", ""]);
// Exports
module.exports = exports;


/***/ }),

/***/ "./node_modules/css-loader/dist/runtime/api.js":
/*!*****************************************************!*\
  !*** ./node_modules/css-loader/dist/runtime/api.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/*
  MIT License http://www.opensource.org/licenses/mit-license.php
  Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
// eslint-disable-next-line func-names
module.exports = function (useSourceMap) {
  var list = []; // return the list of modules as css string

  list.toString = function toString() {
    return this.map(function (item) {
      var content = cssWithMappingToString(item, useSourceMap);

      if (item[2]) {
        return "@media ".concat(item[2], " {").concat(content, "}");
      }

      return content;
    }).join('');
  }; // import a list of modules into the list
  // eslint-disable-next-line func-names


  list.i = function (modules, mediaQuery, dedupe) {
    if (typeof modules === 'string') {
      // eslint-disable-next-line no-param-reassign
      modules = [[null, modules, '']];
    }

    var alreadyImportedModules = {};

    if (dedupe) {
      for (var i = 0; i < this.length; i++) {
        // eslint-disable-next-line prefer-destructuring
        var id = this[i][0];

        if (id != null) {
          alreadyImportedModules[id] = true;
        }
      }
    }

    for (var _i = 0; _i < modules.length; _i++) {
      var item = [].concat(modules[_i]);

      if (dedupe && alreadyImportedModules[item[0]]) {
        // eslint-disable-next-line no-continue
        continue;
      }

      if (mediaQuery) {
        if (!item[2]) {
          item[2] = mediaQuery;
        } else {
          item[2] = "".concat(mediaQuery, " and ").concat(item[2]);
        }
      }

      list.push(item);
    }
  };

  return list;
};

function cssWithMappingToString(item, useSourceMap) {
  var content = item[1] || ''; // eslint-disable-next-line prefer-destructuring

  var cssMapping = item[3];

  if (!cssMapping) {
    return content;
  }

  if (useSourceMap && typeof btoa === 'function') {
    var sourceMapping = toComment(cssMapping);
    var sourceURLs = cssMapping.sources.map(function (source) {
      return "/*# sourceURL=".concat(cssMapping.sourceRoot || '').concat(source, " */");
    });
    return [content].concat(sourceURLs).concat([sourceMapping]).join('\n');
  }

  return [content].join('\n');
} // Adapted from convert-source-map (MIT)


function toComment(sourceMap) {
  // eslint-disable-next-line no-undef
  var base64 = btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap))));
  var data = "sourceMappingURL=data:application/json;charset=utf-8;base64,".concat(base64);
  return "/*# ".concat(data, " */");
}

/***/ }),

/***/ "./node_modules/css-loader/dist/runtime/getUrl.js":
/*!********************************************************!*\
  !*** ./node_modules/css-loader/dist/runtime/getUrl.js ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


module.exports = function (url, options) {
  if (!options) {
    // eslint-disable-next-line no-param-reassign
    options = {};
  } // eslint-disable-next-line no-underscore-dangle, no-param-reassign


  url = url && url.__esModule ? url.default : url;

  if (typeof url !== 'string') {
    return url;
  } // If url is already wrapped in quotes, remove them


  if (/^['"].*['"]$/.test(url)) {
    // eslint-disable-next-line no-param-reassign
    url = url.slice(1, -1);
  }

  if (options.hash) {
    // eslint-disable-next-line no-param-reassign
    url += options.hash;
  } // Should url be wrapped?
  // See https://drafts.csswg.org/css-values-3/#urls


  if (/["'() \t\n]/.test(url) || options.needQuotes) {
    return "\"".concat(url.replace(/"/g, '\\"').replace(/\n/g, '\\n'), "\"");
  }

  return url;
};

/***/ }),

/***/ "./node_modules/enquire.js/src/MediaQuery.js":
/*!***************************************************!*\
  !*** ./node_modules/enquire.js/src/MediaQuery.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var QueryHandler = __webpack_require__(/*! ./QueryHandler */ "./node_modules/enquire.js/src/QueryHandler.js");
var each = __webpack_require__(/*! ./Util */ "./node_modules/enquire.js/src/Util.js").each;

/**
 * Represents a single media query, manages it's state and registered handlers for this query
 *
 * @constructor
 * @param {string} query the media query string
 * @param {boolean} [isUnconditional=false] whether the media query should run regardless of whether the conditions are met. Primarily for helping older browsers deal with mobile-first design
 */
function MediaQuery(query, isUnconditional) {
    this.query = query;
    this.isUnconditional = isUnconditional;
    this.handlers = [];
    this.mql = window.matchMedia(query);

    var self = this;
    this.listener = function(mql) {
        // Chrome passes an MediaQueryListEvent object, while other browsers pass MediaQueryList directly
        self.mql = mql.currentTarget || mql;
        self.assess();
    };
    this.mql.addListener(this.listener);
}

MediaQuery.prototype = {

    constuctor : MediaQuery,

    /**
     * add a handler for this query, triggering if already active
     *
     * @param {object} handler
     * @param {function} handler.match callback for when query is activated
     * @param {function} [handler.unmatch] callback for when query is deactivated
     * @param {function} [handler.setup] callback for immediate execution when a query handler is registered
     * @param {boolean} [handler.deferSetup=false] should the setup callback be deferred until the first time the handler is matched?
     */
    addHandler : function(handler) {
        var qh = new QueryHandler(handler);
        this.handlers.push(qh);

        this.matches() && qh.on();
    },

    /**
     * removes the given handler from the collection, and calls it's destroy methods
     *
     * @param {object || function} handler the handler to remove
     */
    removeHandler : function(handler) {
        var handlers = this.handlers;
        each(handlers, function(h, i) {
            if(h.equals(handler)) {
                h.destroy();
                return !handlers.splice(i,1); //remove from array and exit each early
            }
        });
    },

    /**
     * Determine whether the media query should be considered a match
     *
     * @return {Boolean} true if media query can be considered a match, false otherwise
     */
    matches : function() {
        return this.mql.matches || this.isUnconditional;
    },

    /**
     * Clears all handlers and unbinds events
     */
    clear : function() {
        each(this.handlers, function(handler) {
            handler.destroy();
        });
        this.mql.removeListener(this.listener);
        this.handlers.length = 0; //clear array
    },

    /*
        * Assesses the query, turning on all handlers if it matches, turning them off if it doesn't match
        */
    assess : function() {
        var action = this.matches() ? 'on' : 'off';

        each(this.handlers, function(handler) {
            handler[action]();
        });
    }
};

module.exports = MediaQuery;


/***/ }),

/***/ "./node_modules/enquire.js/src/MediaQueryDispatch.js":
/*!***********************************************************!*\
  !*** ./node_modules/enquire.js/src/MediaQueryDispatch.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var MediaQuery = __webpack_require__(/*! ./MediaQuery */ "./node_modules/enquire.js/src/MediaQuery.js");
var Util = __webpack_require__(/*! ./Util */ "./node_modules/enquire.js/src/Util.js");
var each = Util.each;
var isFunction = Util.isFunction;
var isArray = Util.isArray;

/**
 * Allows for registration of query handlers.
 * Manages the query handler's state and is responsible for wiring up browser events
 *
 * @constructor
 */
function MediaQueryDispatch () {
    if(!window.matchMedia) {
        throw new Error('matchMedia not present, legacy browsers require a polyfill');
    }

    this.queries = {};
    this.browserIsIncapable = !window.matchMedia('only all').matches;
}

MediaQueryDispatch.prototype = {

    constructor : MediaQueryDispatch,

    /**
     * Registers a handler for the given media query
     *
     * @param {string} q the media query
     * @param {object || Array || Function} options either a single query handler object, a function, or an array of query handlers
     * @param {function} options.match fired when query matched
     * @param {function} [options.unmatch] fired when a query is no longer matched
     * @param {function} [options.setup] fired when handler first triggered
     * @param {boolean} [options.deferSetup=false] whether setup should be run immediately or deferred until query is first matched
     * @param {boolean} [shouldDegrade=false] whether this particular media query should always run on incapable browsers
     */
    register : function(q, options, shouldDegrade) {
        var queries         = this.queries,
            isUnconditional = shouldDegrade && this.browserIsIncapable;

        if(!queries[q]) {
            queries[q] = new MediaQuery(q, isUnconditional);
        }

        //normalise to object in an array
        if(isFunction(options)) {
            options = { match : options };
        }
        if(!isArray(options)) {
            options = [options];
        }
        each(options, function(handler) {
            if (isFunction(handler)) {
                handler = { match : handler };
            }
            queries[q].addHandler(handler);
        });

        return this;
    },

    /**
     * unregisters a query and all it's handlers, or a specific handler for a query
     *
     * @param {string} q the media query to target
     * @param {object || function} [handler] specific handler to unregister
     */
    unregister : function(q, handler) {
        var query = this.queries[q];

        if(query) {
            if(handler) {
                query.removeHandler(handler);
            }
            else {
                query.clear();
                delete this.queries[q];
            }
        }

        return this;
    }
};

module.exports = MediaQueryDispatch;


/***/ }),

/***/ "./node_modules/enquire.js/src/QueryHandler.js":
/*!*****************************************************!*\
  !*** ./node_modules/enquire.js/src/QueryHandler.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/**
 * Delegate to handle a media query being matched and unmatched.
 *
 * @param {object} options
 * @param {function} options.match callback for when the media query is matched
 * @param {function} [options.unmatch] callback for when the media query is unmatched
 * @param {function} [options.setup] one-time callback triggered the first time a query is matched
 * @param {boolean} [options.deferSetup=false] should the setup callback be run immediately, rather than first time query is matched?
 * @constructor
 */
function QueryHandler(options) {
    this.options = options;
    !options.deferSetup && this.setup();
}

QueryHandler.prototype = {

    constructor : QueryHandler,

    /**
     * coordinates setup of the handler
     *
     * @function
     */
    setup : function() {
        if(this.options.setup) {
            this.options.setup();
        }
        this.initialised = true;
    },

    /**
     * coordinates setup and triggering of the handler
     *
     * @function
     */
    on : function() {
        !this.initialised && this.setup();
        this.options.match && this.options.match();
    },

    /**
     * coordinates the unmatch event for the handler
     *
     * @function
     */
    off : function() {
        this.options.unmatch && this.options.unmatch();
    },

    /**
     * called when a handler is to be destroyed.
     * delegates to the destroy or unmatch callbacks, depending on availability.
     *
     * @function
     */
    destroy : function() {
        this.options.destroy ? this.options.destroy() : this.off();
    },

    /**
     * determines equality by reference.
     * if object is supplied compare options, if function, compare match callback
     *
     * @function
     * @param {object || function} [target] the target for comparison
     */
    equals : function(target) {
        return this.options === target || this.options.match === target;
    }

};

module.exports = QueryHandler;


/***/ }),

/***/ "./node_modules/enquire.js/src/Util.js":
/*!*********************************************!*\
  !*** ./node_modules/enquire.js/src/Util.js ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/**
 * Helper function for iterating over a collection
 *
 * @param collection
 * @param fn
 */
function each(collection, fn) {
    var i      = 0,
        length = collection.length,
        cont;

    for(i; i < length; i++) {
        cont = fn(collection[i], i);
        if(cont === false) {
            break; //allow early exit
        }
    }
}

/**
 * Helper function for determining whether target object is an array
 *
 * @param target the object under test
 * @return {Boolean} true if array, false otherwise
 */
function isArray(target) {
    return Object.prototype.toString.apply(target) === '[object Array]';
}

/**
 * Helper function for determining whether target object is a function
 *
 * @param target the object under test
 * @return {Boolean} true if function, false otherwise
 */
function isFunction(target) {
    return typeof target === 'function';
}

module.exports = {
    isFunction : isFunction,
    isArray : isArray,
    each : each
};


/***/ }),

/***/ "./node_modules/enquire.js/src/index.js":
/*!**********************************************!*\
  !*** ./node_modules/enquire.js/src/index.js ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var MediaQueryDispatch = __webpack_require__(/*! ./MediaQueryDispatch */ "./node_modules/enquire.js/src/MediaQueryDispatch.js");
module.exports = new MediaQueryDispatch();


/***/ }),

/***/ "./node_modules/json2mq/index.js":
/*!***************************************!*\
  !*** ./node_modules/json2mq/index.js ***!
  \***************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var camel2hyphen = __webpack_require__(/*! string-convert/camel2hyphen */ "./node_modules/string-convert/camel2hyphen.js");

var isDimension = function (feature) {
  var re = /[height|width]$/;
  return re.test(feature);
};

var obj2mq = function (obj) {
  var mq = '';
  var features = Object.keys(obj);
  features.forEach(function (feature, index) {
    var value = obj[feature];
    feature = camel2hyphen(feature);
    // Add px to dimension features
    if (isDimension(feature) && typeof value === 'number') {
      value = value + 'px';
    }
    if (value === true) {
      mq += feature;
    } else if (value === false) {
      mq += 'not ' + feature;
    } else {
      mq += '(' + feature + ': ' + value + ')';
    }
    if (index < features.length-1) {
      mq += ' and '
    }
  });
  return mq;
};

var json2mq = function (query) {
  var mq = '';
  if (typeof query === 'string') {
    return query;
  }
  // Handling array of media queries
  if (query instanceof Array) {
    query.forEach(function (q, index) {
      mq += obj2mq(q);
      if (index < query.length-1) {
        mq += ', '
      }
    });
    return mq;
  }
  // Handling single media query
  return obj2mq(query);
};

module.exports = json2mq;

/***/ }),

/***/ "./node_modules/lodash.debounce/index.js":
/*!***********************************************!*\
  !*** ./node_modules/lodash.debounce/index.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(global) {/**
 * lodash (Custom Build) <https://lodash.com/>
 * Build: `lodash modularize exports="npm" -o ./`
 * Copyright jQuery Foundation and other contributors <https://jquery.org/>
 * Released under MIT license <https://lodash.com/license>
 * Based on Underscore.js 1.8.3 <http://underscorejs.org/LICENSE>
 * Copyright Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
 */

/** Used as the `TypeError` message for "Functions" methods. */
var FUNC_ERROR_TEXT = 'Expected a function';

/** Used as references for various `Number` constants. */
var NAN = 0 / 0;

/** `Object#toString` result references. */
var symbolTag = '[object Symbol]';

/** Used to match leading and trailing whitespace. */
var reTrim = /^\s+|\s+$/g;

/** Used to detect bad signed hexadecimal string values. */
var reIsBadHex = /^[-+]0x[0-9a-f]+$/i;

/** Used to detect binary string values. */
var reIsBinary = /^0b[01]+$/i;

/** Used to detect octal string values. */
var reIsOctal = /^0o[0-7]+$/i;

/** Built-in method references without a dependency on `root`. */
var freeParseInt = parseInt;

/** Detect free variable `global` from Node.js. */
var freeGlobal = typeof global == 'object' && global && global.Object === Object && global;

/** Detect free variable `self`. */
var freeSelf = typeof self == 'object' && self && self.Object === Object && self;

/** Used as a reference to the global object. */
var root = freeGlobal || freeSelf || Function('return this')();

/** Used for built-in method references. */
var objectProto = Object.prototype;

/**
 * Used to resolve the
 * [`toStringTag`](http://ecma-international.org/ecma-262/7.0/#sec-object.prototype.tostring)
 * of values.
 */
var objectToString = objectProto.toString;

/* Built-in method references for those with the same name as other `lodash` methods. */
var nativeMax = Math.max,
    nativeMin = Math.min;

/**
 * Gets the timestamp of the number of milliseconds that have elapsed since
 * the Unix epoch (1 January 1970 00:00:00 UTC).
 *
 * @static
 * @memberOf _
 * @since 2.4.0
 * @category Date
 * @returns {number} Returns the timestamp.
 * @example
 *
 * _.defer(function(stamp) {
 *   console.log(_.now() - stamp);
 * }, _.now());
 * // => Logs the number of milliseconds it took for the deferred invocation.
 */
var now = function() {
  return root.Date.now();
};

/**
 * Creates a debounced function that delays invoking `func` until after `wait`
 * milliseconds have elapsed since the last time the debounced function was
 * invoked. The debounced function comes with a `cancel` method to cancel
 * delayed `func` invocations and a `flush` method to immediately invoke them.
 * Provide `options` to indicate whether `func` should be invoked on the
 * leading and/or trailing edge of the `wait` timeout. The `func` is invoked
 * with the last arguments provided to the debounced function. Subsequent
 * calls to the debounced function return the result of the last `func`
 * invocation.
 *
 * **Note:** If `leading` and `trailing` options are `true`, `func` is
 * invoked on the trailing edge of the timeout only if the debounced function
 * is invoked more than once during the `wait` timeout.
 *
 * If `wait` is `0` and `leading` is `false`, `func` invocation is deferred
 * until to the next tick, similar to `setTimeout` with a timeout of `0`.
 *
 * See [David Corbacho's article](https://css-tricks.com/debouncing-throttling-explained-examples/)
 * for details over the differences between `_.debounce` and `_.throttle`.
 *
 * @static
 * @memberOf _
 * @since 0.1.0
 * @category Function
 * @param {Function} func The function to debounce.
 * @param {number} [wait=0] The number of milliseconds to delay.
 * @param {Object} [options={}] The options object.
 * @param {boolean} [options.leading=false]
 *  Specify invoking on the leading edge of the timeout.
 * @param {number} [options.maxWait]
 *  The maximum time `func` is allowed to be delayed before it's invoked.
 * @param {boolean} [options.trailing=true]
 *  Specify invoking on the trailing edge of the timeout.
 * @returns {Function} Returns the new debounced function.
 * @example
 *
 * // Avoid costly calculations while the window size is in flux.
 * jQuery(window).on('resize', _.debounce(calculateLayout, 150));
 *
 * // Invoke `sendMail` when clicked, debouncing subsequent calls.
 * jQuery(element).on('click', _.debounce(sendMail, 300, {
 *   'leading': true,
 *   'trailing': false
 * }));
 *
 * // Ensure `batchLog` is invoked once after 1 second of debounced calls.
 * var debounced = _.debounce(batchLog, 250, { 'maxWait': 1000 });
 * var source = new EventSource('/stream');
 * jQuery(source).on('message', debounced);
 *
 * // Cancel the trailing debounced invocation.
 * jQuery(window).on('popstate', debounced.cancel);
 */
function debounce(func, wait, options) {
  var lastArgs,
      lastThis,
      maxWait,
      result,
      timerId,
      lastCallTime,
      lastInvokeTime = 0,
      leading = false,
      maxing = false,
      trailing = true;

  if (typeof func != 'function') {
    throw new TypeError(FUNC_ERROR_TEXT);
  }
  wait = toNumber(wait) || 0;
  if (isObject(options)) {
    leading = !!options.leading;
    maxing = 'maxWait' in options;
    maxWait = maxing ? nativeMax(toNumber(options.maxWait) || 0, wait) : maxWait;
    trailing = 'trailing' in options ? !!options.trailing : trailing;
  }

  function invokeFunc(time) {
    var args = lastArgs,
        thisArg = lastThis;

    lastArgs = lastThis = undefined;
    lastInvokeTime = time;
    result = func.apply(thisArg, args);
    return result;
  }

  function leadingEdge(time) {
    // Reset any `maxWait` timer.
    lastInvokeTime = time;
    // Start the timer for the trailing edge.
    timerId = setTimeout(timerExpired, wait);
    // Invoke the leading edge.
    return leading ? invokeFunc(time) : result;
  }

  function remainingWait(time) {
    var timeSinceLastCall = time - lastCallTime,
        timeSinceLastInvoke = time - lastInvokeTime,
        result = wait - timeSinceLastCall;

    return maxing ? nativeMin(result, maxWait - timeSinceLastInvoke) : result;
  }

  function shouldInvoke(time) {
    var timeSinceLastCall = time - lastCallTime,
        timeSinceLastInvoke = time - lastInvokeTime;

    // Either this is the first call, activity has stopped and we're at the
    // trailing edge, the system time has gone backwards and we're treating
    // it as the trailing edge, or we've hit the `maxWait` limit.
    return (lastCallTime === undefined || (timeSinceLastCall >= wait) ||
      (timeSinceLastCall < 0) || (maxing && timeSinceLastInvoke >= maxWait));
  }

  function timerExpired() {
    var time = now();
    if (shouldInvoke(time)) {
      return trailingEdge(time);
    }
    // Restart the timer.
    timerId = setTimeout(timerExpired, remainingWait(time));
  }

  function trailingEdge(time) {
    timerId = undefined;

    // Only invoke if we have `lastArgs` which means `func` has been
    // debounced at least once.
    if (trailing && lastArgs) {
      return invokeFunc(time);
    }
    lastArgs = lastThis = undefined;
    return result;
  }

  function cancel() {
    if (timerId !== undefined) {
      clearTimeout(timerId);
    }
    lastInvokeTime = 0;
    lastArgs = lastCallTime = lastThis = timerId = undefined;
  }

  function flush() {
    return timerId === undefined ? result : trailingEdge(now());
  }

  function debounced() {
    var time = now(),
        isInvoking = shouldInvoke(time);

    lastArgs = arguments;
    lastThis = this;
    lastCallTime = time;

    if (isInvoking) {
      if (timerId === undefined) {
        return leadingEdge(lastCallTime);
      }
      if (maxing) {
        // Handle invocations in a tight loop.
        timerId = setTimeout(timerExpired, wait);
        return invokeFunc(lastCallTime);
      }
    }
    if (timerId === undefined) {
      timerId = setTimeout(timerExpired, wait);
    }
    return result;
  }
  debounced.cancel = cancel;
  debounced.flush = flush;
  return debounced;
}

/**
 * Checks if `value` is the
 * [language type](http://www.ecma-international.org/ecma-262/7.0/#sec-ecmascript-language-types)
 * of `Object`. (e.g. arrays, functions, objects, regexes, `new Number(0)`, and `new String('')`)
 *
 * @static
 * @memberOf _
 * @since 0.1.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is an object, else `false`.
 * @example
 *
 * _.isObject({});
 * // => true
 *
 * _.isObject([1, 2, 3]);
 * // => true
 *
 * _.isObject(_.noop);
 * // => true
 *
 * _.isObject(null);
 * // => false
 */
function isObject(value) {
  var type = typeof value;
  return !!value && (type == 'object' || type == 'function');
}

/**
 * Checks if `value` is object-like. A value is object-like if it's not `null`
 * and has a `typeof` result of "object".
 *
 * @static
 * @memberOf _
 * @since 4.0.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is object-like, else `false`.
 * @example
 *
 * _.isObjectLike({});
 * // => true
 *
 * _.isObjectLike([1, 2, 3]);
 * // => true
 *
 * _.isObjectLike(_.noop);
 * // => false
 *
 * _.isObjectLike(null);
 * // => false
 */
function isObjectLike(value) {
  return !!value && typeof value == 'object';
}

/**
 * Checks if `value` is classified as a `Symbol` primitive or object.
 *
 * @static
 * @memberOf _
 * @since 4.0.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is a symbol, else `false`.
 * @example
 *
 * _.isSymbol(Symbol.iterator);
 * // => true
 *
 * _.isSymbol('abc');
 * // => false
 */
function isSymbol(value) {
  return typeof value == 'symbol' ||
    (isObjectLike(value) && objectToString.call(value) == symbolTag);
}

/**
 * Converts `value` to a number.
 *
 * @static
 * @memberOf _
 * @since 4.0.0
 * @category Lang
 * @param {*} value The value to process.
 * @returns {number} Returns the number.
 * @example
 *
 * _.toNumber(3.2);
 * // => 3.2
 *
 * _.toNumber(Number.MIN_VALUE);
 * // => 5e-324
 *
 * _.toNumber(Infinity);
 * // => Infinity
 *
 * _.toNumber('3.2');
 * // => 3.2
 */
function toNumber(value) {
  if (typeof value == 'number') {
    return value;
  }
  if (isSymbol(value)) {
    return NAN;
  }
  if (isObject(value)) {
    var other = typeof value.valueOf == 'function' ? value.valueOf() : value;
    value = isObject(other) ? (other + '') : other;
  }
  if (typeof value != 'string') {
    return value === 0 ? value : +value;
  }
  value = value.replace(reTrim, '');
  var isBinary = reIsBinary.test(value);
  return (isBinary || reIsOctal.test(value))
    ? freeParseInt(value.slice(2), isBinary ? 2 : 8)
    : (reIsBadHex.test(value) ? NAN : +value);
}

module.exports = debounce;

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./../webpack/buildin/global.js */ "./node_modules/webpack/buildin/global.js")))

/***/ }),

/***/ "./node_modules/react-slick/lib/arrows.js":
/*!************************************************!*\
  !*** ./node_modules/react-slick/lib/arrows.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.NextArrow = exports.PrevArrow = void 0;

var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));

var _classnames = _interopRequireDefault(__webpack_require__(/*! classnames */ "./node_modules/classnames/index.js"));

var _innerSliderUtils = __webpack_require__(/*! ./utils/innerSliderUtils */ "./node_modules/react-slick/lib/utils/innerSliderUtils.js");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(source, true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(source).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var PrevArrow =
/*#__PURE__*/
function (_React$PureComponent) {
  _inherits(PrevArrow, _React$PureComponent);

  function PrevArrow() {
    _classCallCheck(this, PrevArrow);

    return _possibleConstructorReturn(this, _getPrototypeOf(PrevArrow).apply(this, arguments));
  }

  _createClass(PrevArrow, [{
    key: "clickHandler",
    value: function clickHandler(options, e) {
      if (e) {
        e.preventDefault();
      }

      this.props.clickHandler(options, e);
    }
  }, {
    key: "render",
    value: function render() {
      var prevClasses = {
        "slick-arrow": true,
        "slick-prev": true
      };
      var prevHandler = this.clickHandler.bind(this, {
        message: "previous"
      });

      if (!this.props.infinite && (this.props.currentSlide === 0 || this.props.slideCount <= this.props.slidesToShow)) {
        prevClasses["slick-disabled"] = true;
        prevHandler = null;
      }

      var prevArrowProps = {
        key: "0",
        "data-role": "none",
        className: (0, _classnames["default"])(prevClasses),
        style: {
          display: "block"
        },
        onClick: prevHandler
      };
      var customProps = {
        currentSlide: this.props.currentSlide,
        slideCount: this.props.slideCount
      };
      var prevArrow;

      if (this.props.prevArrow) {
        prevArrow = _react["default"].cloneElement(this.props.prevArrow, _objectSpread({}, prevArrowProps, {}, customProps));
      } else {
        prevArrow = _react["default"].createElement("button", _extends({
          key: "0",
          type: "button"
        }, prevArrowProps), " ", "Previous");
      }

      return prevArrow;
    }
  }]);

  return PrevArrow;
}(_react["default"].PureComponent);

exports.PrevArrow = PrevArrow;

var NextArrow =
/*#__PURE__*/
function (_React$PureComponent2) {
  _inherits(NextArrow, _React$PureComponent2);

  function NextArrow() {
    _classCallCheck(this, NextArrow);

    return _possibleConstructorReturn(this, _getPrototypeOf(NextArrow).apply(this, arguments));
  }

  _createClass(NextArrow, [{
    key: "clickHandler",
    value: function clickHandler(options, e) {
      if (e) {
        e.preventDefault();
      }

      this.props.clickHandler(options, e);
    }
  }, {
    key: "render",
    value: function render() {
      var nextClasses = {
        "slick-arrow": true,
        "slick-next": true
      };
      var nextHandler = this.clickHandler.bind(this, {
        message: "next"
      });

      if (!(0, _innerSliderUtils.canGoNext)(this.props)) {
        nextClasses["slick-disabled"] = true;
        nextHandler = null;
      }

      var nextArrowProps = {
        key: "1",
        "data-role": "none",
        className: (0, _classnames["default"])(nextClasses),
        style: {
          display: "block"
        },
        onClick: nextHandler
      };
      var customProps = {
        currentSlide: this.props.currentSlide,
        slideCount: this.props.slideCount
      };
      var nextArrow;

      if (this.props.nextArrow) {
        nextArrow = _react["default"].cloneElement(this.props.nextArrow, _objectSpread({}, nextArrowProps, {}, customProps));
      } else {
        nextArrow = _react["default"].createElement("button", _extends({
          key: "1",
          type: "button"
        }, nextArrowProps), " ", "Next");
      }

      return nextArrow;
    }
  }]);

  return NextArrow;
}(_react["default"].PureComponent);

exports.NextArrow = NextArrow;

/***/ }),

/***/ "./node_modules/react-slick/lib/default-props.js":
/*!*******************************************************!*\
  !*** ./node_modules/react-slick/lib/default-props.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

var defaultProps = {
  accessibility: true,
  adaptiveHeight: false,
  afterChange: null,
  appendDots: function appendDots(dots) {
    return _react["default"].createElement("ul", {
      style: {
        display: "block"
      }
    }, dots);
  },
  arrows: true,
  autoplay: false,
  autoplaySpeed: 3000,
  beforeChange: null,
  centerMode: false,
  centerPadding: "50px",
  className: "",
  cssEase: "ease",
  customPaging: function customPaging(i) {
    return _react["default"].createElement("button", null, i + 1);
  },
  dots: false,
  dotsClass: "slick-dots",
  draggable: true,
  easing: "linear",
  edgeFriction: 0.35,
  fade: false,
  focusOnSelect: false,
  infinite: true,
  initialSlide: 0,
  lazyLoad: null,
  nextArrow: null,
  onEdge: null,
  onInit: null,
  onLazyLoadError: null,
  onReInit: null,
  pauseOnDotsHover: false,
  pauseOnFocus: false,
  pauseOnHover: true,
  prevArrow: null,
  responsive: null,
  rows: 1,
  rtl: false,
  slide: "div",
  slidesPerRow: 1,
  slidesToScroll: 1,
  slidesToShow: 1,
  speed: 500,
  swipe: true,
  swipeEvent: null,
  swipeToSlide: false,
  touchMove: true,
  touchThreshold: 5,
  useCSS: true,
  useTransform: true,
  variableWidth: false,
  vertical: false,
  waitForAnimate: true
};
var _default = defaultProps;
exports["default"] = _default;

/***/ }),

/***/ "./node_modules/react-slick/lib/dots.js":
/*!**********************************************!*\
  !*** ./node_modules/react-slick/lib/dots.js ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Dots = void 0;

var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));

var _classnames = _interopRequireDefault(__webpack_require__(/*! classnames */ "./node_modules/classnames/index.js"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(source, true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(source).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var getDotCount = function getDotCount(spec) {
  var dots;

  if (spec.infinite) {
    dots = Math.ceil(spec.slideCount / spec.slidesToScroll);
  } else {
    dots = Math.ceil((spec.slideCount - spec.slidesToShow) / spec.slidesToScroll) + 1;
  }

  return dots;
};

var Dots =
/*#__PURE__*/
function (_React$PureComponent) {
  _inherits(Dots, _React$PureComponent);

  function Dots() {
    _classCallCheck(this, Dots);

    return _possibleConstructorReturn(this, _getPrototypeOf(Dots).apply(this, arguments));
  }

  _createClass(Dots, [{
    key: "clickHandler",
    value: function clickHandler(options, e) {
      // In Autoplay the focus stays on clicked button even after transition
      // to next slide. That only goes away by click somewhere outside
      e.preventDefault();
      this.props.clickHandler(options);
    }
  }, {
    key: "render",
    value: function render() {
      var _this = this;

      var dotCount = getDotCount({
        slideCount: this.props.slideCount,
        slidesToScroll: this.props.slidesToScroll,
        slidesToShow: this.props.slidesToShow,
        infinite: this.props.infinite
      }); // Apply join & split to Array to pre-fill it for IE8
      //
      // Credit: http://stackoverflow.com/a/13735425/1849458

      var _this$props = this.props,
          onMouseEnter = _this$props.onMouseEnter,
          onMouseOver = _this$props.onMouseOver,
          onMouseLeave = _this$props.onMouseLeave;
      var mouseEvents = {
        onMouseEnter: onMouseEnter,
        onMouseOver: onMouseOver,
        onMouseLeave: onMouseLeave
      };
      var dots = Array.apply(null, Array(dotCount + 1).join("0").split("")).map(function (x, i) {
        var leftBound = i * _this.props.slidesToScroll;
        var rightBound = i * _this.props.slidesToScroll + (_this.props.slidesToScroll - 1);
        var className = (0, _classnames["default"])({
          "slick-active": _this.props.currentSlide >= leftBound && _this.props.currentSlide <= rightBound
        });
        var dotOptions = {
          message: "dots",
          index: i,
          slidesToScroll: _this.props.slidesToScroll,
          currentSlide: _this.props.currentSlide
        };

        var onClick = _this.clickHandler.bind(_this, dotOptions);

        return _react["default"].createElement("li", {
          key: i,
          className: className
        }, _react["default"].cloneElement(_this.props.customPaging(i), {
          onClick: onClick
        }));
      });
      return _react["default"].cloneElement(this.props.appendDots(dots), _objectSpread({
        className: this.props.dotsClass
      }, mouseEvents));
    }
  }]);

  return Dots;
}(_react["default"].PureComponent);

exports.Dots = Dots;

/***/ }),

/***/ "./node_modules/react-slick/lib/index.js":
/*!***********************************************!*\
  !*** ./node_modules/react-slick/lib/index.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _slider = _interopRequireDefault(__webpack_require__(/*! ./slider */ "./node_modules/react-slick/lib/slider.js"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

var _default = _slider["default"];
exports["default"] = _default;

/***/ }),

/***/ "./node_modules/react-slick/lib/initial-state.js":
/*!*******************************************************!*\
  !*** ./node_modules/react-slick/lib/initial-state.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;
var initialState = {
  animating: false,
  autoplaying: null,
  currentDirection: 0,
  currentLeft: null,
  currentSlide: 0,
  direction: 1,
  dragging: false,
  edgeDragged: false,
  initialized: false,
  lazyLoadedList: [],
  listHeight: null,
  listWidth: null,
  scrolling: false,
  slideCount: null,
  slideHeight: null,
  slideWidth: null,
  swipeLeft: null,
  swiped: false,
  // used by swipeEvent. differentites between touch and swipe.
  swiping: false,
  touchObject: {
    startX: 0,
    startY: 0,
    curX: 0,
    curY: 0
  },
  trackStyle: {},
  trackWidth: 0
};
var _default = initialState;
exports["default"] = _default;

/***/ }),

/***/ "./node_modules/react-slick/lib/inner-slider.js":
/*!******************************************************!*\
  !*** ./node_modules/react-slick/lib/inner-slider.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.InnerSlider = void 0;

var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));

var _reactDom = _interopRequireDefault(__webpack_require__(/*! react-dom */ "react-dom"));

var _initialState = _interopRequireDefault(__webpack_require__(/*! ./initial-state */ "./node_modules/react-slick/lib/initial-state.js"));

var _lodash = _interopRequireDefault(__webpack_require__(/*! lodash.debounce */ "./node_modules/lodash.debounce/index.js"));

var _classnames = _interopRequireDefault(__webpack_require__(/*! classnames */ "./node_modules/classnames/index.js"));

var _innerSliderUtils = __webpack_require__(/*! ./utils/innerSliderUtils */ "./node_modules/react-slick/lib/utils/innerSliderUtils.js");

var _track = __webpack_require__(/*! ./track */ "./node_modules/react-slick/lib/track.js");

var _dots = __webpack_require__(/*! ./dots */ "./node_modules/react-slick/lib/dots.js");

var _arrows = __webpack_require__(/*! ./arrows */ "./node_modules/react-slick/lib/arrows.js");

var _resizeObserverPolyfill = _interopRequireDefault(__webpack_require__(/*! resize-observer-polyfill */ "./node_modules/resize-observer-polyfill/dist/ResizeObserver.es.js"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }

function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(source, true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(source).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var InnerSlider =
/*#__PURE__*/
function (_React$Component) {
  _inherits(InnerSlider, _React$Component);

  function InnerSlider(props) {
    var _this;

    _classCallCheck(this, InnerSlider);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(InnerSlider).call(this, props));

    _defineProperty(_assertThisInitialized(_this), "listRefHandler", function (ref) {
      return _this.list = ref;
    });

    _defineProperty(_assertThisInitialized(_this), "trackRefHandler", function (ref) {
      return _this.track = ref;
    });

    _defineProperty(_assertThisInitialized(_this), "adaptHeight", function () {
      if (_this.props.adaptiveHeight && _this.list) {
        var elem = _this.list.querySelector("[data-index=\"".concat(_this.state.currentSlide, "\"]"));

        _this.list.style.height = (0, _innerSliderUtils.getHeight)(elem) + "px";
      }
    });

    _defineProperty(_assertThisInitialized(_this), "UNSAFE_componentWillMount", function () {
      _this.ssrInit();

      _this.props.onInit && _this.props.onInit();

      if (_this.props.lazyLoad) {
        var slidesToLoad = (0, _innerSliderUtils.getOnDemandLazySlides)(_objectSpread({}, _this.props, {}, _this.state));

        if (slidesToLoad.length > 0) {
          _this.setState(function (prevState) {
            return {
              lazyLoadedList: prevState.lazyLoadedList.concat(slidesToLoad)
            };
          });

          if (_this.props.onLazyLoad) {
            _this.props.onLazyLoad(slidesToLoad);
          }
        }
      }
    });

    _defineProperty(_assertThisInitialized(_this), "componentDidMount", function () {
      var spec = _objectSpread({
        listRef: _this.list,
        trackRef: _this.track
      }, _this.props);

      _this.updateState(spec, true, function () {
        _this.adaptHeight();

        _this.props.autoplay && _this.autoPlay("update");
      });

      if (_this.props.lazyLoad === "progressive") {
        _this.lazyLoadTimer = setInterval(_this.progressiveLazyLoad, 1000);
      }

      _this.ro = new _resizeObserverPolyfill["default"](function () {
        if (_this.state.animating) {
          _this.onWindowResized(false); // don't set trackStyle hence don't break animation


          _this.callbackTimers.push(setTimeout(function () {
            return _this.onWindowResized();
          }, _this.props.speed));
        } else {
          _this.onWindowResized();
        }
      });

      _this.ro.observe(_this.list);

      Array.prototype.forEach.call(document.querySelectorAll(".slick-slide"), function (slide) {
        slide.onfocus = _this.props.pauseOnFocus ? _this.onSlideFocus : null;
        slide.onblur = _this.props.pauseOnFocus ? _this.onSlideBlur : null;
      }); // To support server-side rendering

      if (!window) {
        return;
      }

      if (window.addEventListener) {
        window.addEventListener("resize", _this.onWindowResized);
      } else {
        window.attachEvent("onresize", _this.onWindowResized);
      }
    });

    _defineProperty(_assertThisInitialized(_this), "componentWillUnmount", function () {
      if (_this.animationEndCallback) {
        clearTimeout(_this.animationEndCallback);
      }

      if (_this.lazyLoadTimer) {
        clearInterval(_this.lazyLoadTimer);
      }

      if (_this.callbackTimers.length) {
        _this.callbackTimers.forEach(function (timer) {
          return clearTimeout(timer);
        });

        _this.callbackTimers = [];
      }

      if (window.addEventListener) {
        window.removeEventListener("resize", _this.onWindowResized);
      } else {
        window.detachEvent("onresize", _this.onWindowResized);
      }

      if (_this.autoplayTimer) {
        clearInterval(_this.autoplayTimer);
      }
    });

    _defineProperty(_assertThisInitialized(_this), "UNSAFE_componentWillReceiveProps", function (nextProps) {
      var spec = _objectSpread({
        listRef: _this.list,
        trackRef: _this.track
      }, nextProps, {}, _this.state);

      var setTrackStyle = false;

      for (var _i = 0, _Object$keys = Object.keys(_this.props); _i < _Object$keys.length; _i++) {
        var key = _Object$keys[_i];

        if (!nextProps.hasOwnProperty(key)) {
          setTrackStyle = true;
          break;
        }

        if (_typeof(nextProps[key]) === "object" || typeof nextProps[key] === "function") {
          continue;
        }

        if (nextProps[key] !== _this.props[key]) {
          setTrackStyle = true;
          break;
        }
      }

      _this.updateState(spec, setTrackStyle, function () {
        if (_this.state.currentSlide >= _react["default"].Children.count(nextProps.children)) {
          _this.changeSlide({
            message: "index",
            index: _react["default"].Children.count(nextProps.children) - nextProps.slidesToShow,
            currentSlide: _this.state.currentSlide
          });
        }

        if (nextProps.autoplay) {
          _this.autoPlay("update");
        } else {
          _this.pause("paused");
        }
      });
    });

    _defineProperty(_assertThisInitialized(_this), "componentDidUpdate", function () {
      _this.checkImagesLoad();

      _this.props.onReInit && _this.props.onReInit();

      if (_this.props.lazyLoad) {
        var slidesToLoad = (0, _innerSliderUtils.getOnDemandLazySlides)(_objectSpread({}, _this.props, {}, _this.state));

        if (slidesToLoad.length > 0) {
          _this.setState(function (prevState) {
            return {
              lazyLoadedList: prevState.lazyLoadedList.concat(slidesToLoad)
            };
          });

          if (_this.props.onLazyLoad) {
            _this.props.onLazyLoad(slidesToLoad);
          }
        }
      } // if (this.props.onLazyLoad) {
      //   this.props.onLazyLoad([leftMostSlide])
      // }


      _this.adaptHeight();
    });

    _defineProperty(_assertThisInitialized(_this), "onWindowResized", function (setTrackStyle) {
      if (_this.debouncedResize) _this.debouncedResize.cancel();
      _this.debouncedResize = (0, _lodash["default"])(function () {
        return _this.resizeWindow(setTrackStyle);
      }, 50);

      _this.debouncedResize();
    });

    _defineProperty(_assertThisInitialized(_this), "resizeWindow", function () {
      var setTrackStyle = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
      if (!_reactDom["default"].findDOMNode(_this.track)) return;

      var spec = _objectSpread({
        listRef: _this.list,
        trackRef: _this.track
      }, _this.props, {}, _this.state);

      _this.updateState(spec, setTrackStyle, function () {
        if (_this.props.autoplay) _this.autoPlay("update");else _this.pause("paused");
      }); // animating state should be cleared while resizing, otherwise autoplay stops working


      _this.setState({
        animating: false
      });

      clearTimeout(_this.animationEndCallback);
      delete _this.animationEndCallback;
    });

    _defineProperty(_assertThisInitialized(_this), "updateState", function (spec, setTrackStyle, callback) {
      var updatedState = (0, _innerSliderUtils.initializedState)(spec);
      spec = _objectSpread({}, spec, {}, updatedState, {
        slideIndex: updatedState.currentSlide
      });
      var targetLeft = (0, _innerSliderUtils.getTrackLeft)(spec);
      spec = _objectSpread({}, spec, {
        left: targetLeft
      });
      var trackStyle = (0, _innerSliderUtils.getTrackCSS)(spec);

      if (setTrackStyle || _react["default"].Children.count(_this.props.children) !== _react["default"].Children.count(spec.children)) {
        updatedState["trackStyle"] = trackStyle;
      }

      _this.setState(updatedState, callback);
    });

    _defineProperty(_assertThisInitialized(_this), "ssrInit", function () {
      if (_this.props.variableWidth) {
        var _trackWidth = 0,
            _trackLeft = 0;
        var childrenWidths = [];
        var preClones = (0, _innerSliderUtils.getPreClones)(_objectSpread({}, _this.props, {}, _this.state, {
          slideCount: _this.props.children.length
        }));
        var postClones = (0, _innerSliderUtils.getPostClones)(_objectSpread({}, _this.props, {}, _this.state, {
          slideCount: _this.props.children.length
        }));

        _this.props.children.forEach(function (child) {
          childrenWidths.push(child.props.style.width);
          _trackWidth += child.props.style.width;
        });

        for (var i = 0; i < preClones; i++) {
          _trackLeft += childrenWidths[childrenWidths.length - 1 - i];
          _trackWidth += childrenWidths[childrenWidths.length - 1 - i];
        }

        for (var _i2 = 0; _i2 < postClones; _i2++) {
          _trackWidth += childrenWidths[_i2];
        }

        for (var _i3 = 0; _i3 < _this.state.currentSlide; _i3++) {
          _trackLeft += childrenWidths[_i3];
        }

        var _trackStyle = {
          width: _trackWidth + "px",
          left: -_trackLeft + "px"
        };

        if (_this.props.centerMode) {
          var currentWidth = "".concat(childrenWidths[_this.state.currentSlide], "px");
          _trackStyle.left = "calc(".concat(_trackStyle.left, " + (100% - ").concat(currentWidth, ") / 2 ) ");
        }

        _this.setState({
          trackStyle: _trackStyle
        });

        return;
      }

      var childrenCount = _react["default"].Children.count(_this.props.children);

      var spec = _objectSpread({}, _this.props, {}, _this.state, {
        slideCount: childrenCount
      });

      var slideCount = (0, _innerSliderUtils.getPreClones)(spec) + (0, _innerSliderUtils.getPostClones)(spec) + childrenCount;
      var trackWidth = 100 / _this.props.slidesToShow * slideCount;
      var slideWidth = 100 / slideCount;
      var trackLeft = -slideWidth * ((0, _innerSliderUtils.getPreClones)(spec) + _this.state.currentSlide) * trackWidth / 100;

      if (_this.props.centerMode) {
        trackLeft += (100 - slideWidth * trackWidth / 100) / 2;
      }

      var trackStyle = {
        width: trackWidth + "%",
        left: trackLeft + "%"
      };

      _this.setState({
        slideWidth: slideWidth + "%",
        trackStyle: trackStyle
      });
    });

    _defineProperty(_assertThisInitialized(_this), "checkImagesLoad", function () {
      var images = document.querySelectorAll(".slick-slide img");
      var imagesCount = images.length,
          loadedCount = 0;
      Array.prototype.forEach.call(images, function (image) {
        var handler = function handler() {
          return ++loadedCount && loadedCount >= imagesCount && _this.onWindowResized();
        };

        if (!image.onclick) {
          image.onclick = function () {
            return image.parentNode.focus();
          };
        } else {
          var prevClickHandler = image.onclick;

          image.onclick = function () {
            prevClickHandler();
            image.parentNode.focus();
          };
        }

        if (!image.onload) {
          if (_this.props.lazyLoad) {
            image.onload = function () {
              _this.adaptHeight();

              _this.callbackTimers.push(setTimeout(_this.onWindowResized, _this.props.speed));
            };
          } else {
            image.onload = handler;

            image.onerror = function () {
              handler();
              _this.props.onLazyLoadError && _this.props.onLazyLoadError();
            };
          }
        }
      });
    });

    _defineProperty(_assertThisInitialized(_this), "progressiveLazyLoad", function () {
      var slidesToLoad = [];

      var spec = _objectSpread({}, _this.props, {}, _this.state);

      for (var index = _this.state.currentSlide; index < _this.state.slideCount + (0, _innerSliderUtils.getPostClones)(spec); index++) {
        if (_this.state.lazyLoadedList.indexOf(index) < 0) {
          slidesToLoad.push(index);
          break;
        }
      }

      for (var _index = _this.state.currentSlide - 1; _index >= -(0, _innerSliderUtils.getPreClones)(spec); _index--) {
        if (_this.state.lazyLoadedList.indexOf(_index) < 0) {
          slidesToLoad.push(_index);
          break;
        }
      }

      if (slidesToLoad.length > 0) {
        _this.setState(function (state) {
          return {
            lazyLoadedList: state.lazyLoadedList.concat(slidesToLoad)
          };
        });

        if (_this.props.onLazyLoad) {
          _this.props.onLazyLoad(slidesToLoad);
        }
      } else {
        if (_this.lazyLoadTimer) {
          clearInterval(_this.lazyLoadTimer);
          delete _this.lazyLoadTimer;
        }
      }
    });

    _defineProperty(_assertThisInitialized(_this), "slideHandler", function (index) {
      var dontAnimate = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      var _this$props = _this.props,
          asNavFor = _this$props.asNavFor,
          beforeChange = _this$props.beforeChange,
          onLazyLoad = _this$props.onLazyLoad,
          speed = _this$props.speed,
          afterChange = _this$props.afterChange; // capture currentslide before state is updated

      var currentSlide = _this.state.currentSlide;

      var _slideHandler = (0, _innerSliderUtils.slideHandler)(_objectSpread({
        index: index
      }, _this.props, {}, _this.state, {
        trackRef: _this.track,
        useCSS: _this.props.useCSS && !dontAnimate
      })),
          state = _slideHandler.state,
          nextState = _slideHandler.nextState;

      if (!state) return;
      beforeChange && beforeChange(currentSlide, state.currentSlide);
      var slidesToLoad = state.lazyLoadedList.filter(function (value) {
        return _this.state.lazyLoadedList.indexOf(value) < 0;
      });
      onLazyLoad && slidesToLoad.length > 0 && onLazyLoad(slidesToLoad);

      _this.setState(state, function () {
        asNavFor && asNavFor.innerSlider.slideHandler(index);
        if (!nextState) return;
        _this.animationEndCallback = setTimeout(function () {
          var animating = nextState.animating,
              firstBatch = _objectWithoutProperties(nextState, ["animating"]);

          _this.setState(firstBatch, function () {
            _this.callbackTimers.push(setTimeout(function () {
              return _this.setState({
                animating: animating
              });
            }, 10));

            afterChange && afterChange(state.currentSlide);
            delete _this.animationEndCallback;
          });
        }, speed);
      });
    });

    _defineProperty(_assertThisInitialized(_this), "changeSlide", function (options) {
      var dontAnimate = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

      var spec = _objectSpread({}, _this.props, {}, _this.state);

      var targetSlide = (0, _innerSliderUtils.changeSlide)(spec, options);
      if (targetSlide !== 0 && !targetSlide) return;

      if (dontAnimate === true) {
        _this.slideHandler(targetSlide, dontAnimate);
      } else {
        _this.slideHandler(targetSlide);
      }
    });

    _defineProperty(_assertThisInitialized(_this), "clickHandler", function (e) {
      if (_this.clickable === false) {
        e.stopPropagation();
        e.preventDefault();
      }

      _this.clickable = true;
    });

    _defineProperty(_assertThisInitialized(_this), "keyHandler", function (e) {
      var dir = (0, _innerSliderUtils.keyHandler)(e, _this.props.accessibility, _this.props.rtl);
      dir !== "" && _this.changeSlide({
        message: dir
      });
    });

    _defineProperty(_assertThisInitialized(_this), "selectHandler", function (options) {
      _this.changeSlide(options);
    });

    _defineProperty(_assertThisInitialized(_this), "disableBodyScroll", function () {
      var preventDefault = function preventDefault(e) {
        e = e || window.event;
        if (e.preventDefault) e.preventDefault();
        e.returnValue = false;
      };

      window.ontouchmove = preventDefault;
    });

    _defineProperty(_assertThisInitialized(_this), "enableBodyScroll", function () {
      window.ontouchmove = null;
    });

    _defineProperty(_assertThisInitialized(_this), "swipeStart", function (e) {
      if (_this.props.verticalSwiping) {
        _this.disableBodyScroll();
      }

      var state = (0, _innerSliderUtils.swipeStart)(e, _this.props.swipe, _this.props.draggable);
      state !== "" && _this.setState(state);
    });

    _defineProperty(_assertThisInitialized(_this), "swipeMove", function (e) {
      var state = (0, _innerSliderUtils.swipeMove)(e, _objectSpread({}, _this.props, {}, _this.state, {
        trackRef: _this.track,
        listRef: _this.list,
        slideIndex: _this.state.currentSlide
      }));
      if (!state) return;

      if (state["swiping"]) {
        _this.clickable = false;
      }

      _this.setState(state);
    });

    _defineProperty(_assertThisInitialized(_this), "swipeEnd", function (e) {
      var state = (0, _innerSliderUtils.swipeEnd)(e, _objectSpread({}, _this.props, {}, _this.state, {
        trackRef: _this.track,
        listRef: _this.list,
        slideIndex: _this.state.currentSlide
      }));
      if (!state) return;
      var triggerSlideHandler = state["triggerSlideHandler"];
      delete state["triggerSlideHandler"];

      _this.setState(state);

      if (triggerSlideHandler === undefined) return;

      _this.slideHandler(triggerSlideHandler);

      if (_this.props.verticalSwiping) {
        _this.enableBodyScroll();
      }
    });

    _defineProperty(_assertThisInitialized(_this), "slickPrev", function () {
      // this and fellow methods are wrapped in setTimeout
      // to make sure initialize setState has happened before
      // any of such methods are called
      _this.callbackTimers.push(setTimeout(function () {
        return _this.changeSlide({
          message: "previous"
        });
      }, 0));
    });

    _defineProperty(_assertThisInitialized(_this), "slickNext", function () {
      _this.callbackTimers.push(setTimeout(function () {
        return _this.changeSlide({
          message: "next"
        });
      }, 0));
    });

    _defineProperty(_assertThisInitialized(_this), "slickGoTo", function (slide) {
      var dontAnimate = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      slide = Number(slide);
      if (isNaN(slide)) return "";

      _this.callbackTimers.push(setTimeout(function () {
        return _this.changeSlide({
          message: "index",
          index: slide,
          currentSlide: _this.state.currentSlide
        }, dontAnimate);
      }, 0));
    });

    _defineProperty(_assertThisInitialized(_this), "play", function () {
      var nextIndex;

      if (_this.props.rtl) {
        nextIndex = _this.state.currentSlide - _this.props.slidesToScroll;
      } else {
        if ((0, _innerSliderUtils.canGoNext)(_objectSpread({}, _this.props, {}, _this.state))) {
          nextIndex = _this.state.currentSlide + _this.props.slidesToScroll;
        } else {
          return false;
        }
      }

      _this.slideHandler(nextIndex);
    });

    _defineProperty(_assertThisInitialized(_this), "autoPlay", function (playType) {
      if (_this.autoplayTimer) {
        clearInterval(_this.autoplayTimer);
      }

      var autoplaying = _this.state.autoplaying;

      if (playType === "update") {
        if (autoplaying === "hovered" || autoplaying === "focused" || autoplaying === "paused") {
          return;
        }
      } else if (playType === "leave") {
        if (autoplaying === "paused" || autoplaying === "focused") {
          return;
        }
      } else if (playType === "blur") {
        if (autoplaying === "paused" || autoplaying === "hovered") {
          return;
        }
      }

      _this.autoplayTimer = setInterval(_this.play, _this.props.autoplaySpeed + 50);

      _this.setState({
        autoplaying: "playing"
      });
    });

    _defineProperty(_assertThisInitialized(_this), "pause", function (pauseType) {
      if (_this.autoplayTimer) {
        clearInterval(_this.autoplayTimer);
        _this.autoplayTimer = null;
      }

      var autoplaying = _this.state.autoplaying;

      if (pauseType === "paused") {
        _this.setState({
          autoplaying: "paused"
        });
      } else if (pauseType === "focused") {
        if (autoplaying === "hovered" || autoplaying === "playing") {
          _this.setState({
            autoplaying: "focused"
          });
        }
      } else {
        // pauseType  is 'hovered'
        if (autoplaying === "playing") {
          _this.setState({
            autoplaying: "hovered"
          });
        }
      }
    });

    _defineProperty(_assertThisInitialized(_this), "onDotsOver", function () {
      return _this.props.autoplay && _this.pause("hovered");
    });

    _defineProperty(_assertThisInitialized(_this), "onDotsLeave", function () {
      return _this.props.autoplay && _this.state.autoplaying === "hovered" && _this.autoPlay("leave");
    });

    _defineProperty(_assertThisInitialized(_this), "onTrackOver", function () {
      return _this.props.autoplay && _this.pause("hovered");
    });

    _defineProperty(_assertThisInitialized(_this), "onTrackLeave", function () {
      return _this.props.autoplay && _this.state.autoplaying === "hovered" && _this.autoPlay("leave");
    });

    _defineProperty(_assertThisInitialized(_this), "onSlideFocus", function () {
      return _this.props.autoplay && _this.pause("focused");
    });

    _defineProperty(_assertThisInitialized(_this), "onSlideBlur", function () {
      return _this.props.autoplay && _this.state.autoplaying === "focused" && _this.autoPlay("blur");
    });

    _defineProperty(_assertThisInitialized(_this), "render", function () {
      var className = (0, _classnames["default"])("slick-slider", _this.props.className, {
        "slick-vertical": _this.props.vertical,
        "slick-initialized": true
      });

      var spec = _objectSpread({}, _this.props, {}, _this.state);

      var trackProps = (0, _innerSliderUtils.extractObject)(spec, ["fade", "cssEase", "speed", "infinite", "centerMode", "focusOnSelect", "currentSlide", "lazyLoad", "lazyLoadedList", "rtl", "slideWidth", "slideHeight", "listHeight", "vertical", "slidesToShow", "slidesToScroll", "slideCount", "trackStyle", "variableWidth", "unslick", "centerPadding"]);
      var pauseOnHover = _this.props.pauseOnHover;
      trackProps = _objectSpread({}, trackProps, {
        onMouseEnter: pauseOnHover ? _this.onTrackOver : null,
        onMouseLeave: pauseOnHover ? _this.onTrackLeave : null,
        onMouseOver: pauseOnHover ? _this.onTrackOver : null,
        focusOnSelect: _this.props.focusOnSelect ? _this.selectHandler : null
      });
      var dots;

      if (_this.props.dots === true && _this.state.slideCount >= _this.props.slidesToShow) {
        var dotProps = (0, _innerSliderUtils.extractObject)(spec, ["dotsClass", "slideCount", "slidesToShow", "currentSlide", "slidesToScroll", "clickHandler", "children", "customPaging", "infinite", "appendDots"]);
        var pauseOnDotsHover = _this.props.pauseOnDotsHover;
        dotProps = _objectSpread({}, dotProps, {
          clickHandler: _this.changeSlide,
          onMouseEnter: pauseOnDotsHover ? _this.onDotsLeave : null,
          onMouseOver: pauseOnDotsHover ? _this.onDotsOver : null,
          onMouseLeave: pauseOnDotsHover ? _this.onDotsLeave : null
        });
        dots = _react["default"].createElement(_dots.Dots, dotProps);
      }

      var prevArrow, nextArrow;
      var arrowProps = (0, _innerSliderUtils.extractObject)(spec, ["infinite", "centerMode", "currentSlide", "slideCount", "slidesToShow", "prevArrow", "nextArrow"]);
      arrowProps.clickHandler = _this.changeSlide;

      if (_this.props.arrows) {
        prevArrow = _react["default"].createElement(_arrows.PrevArrow, arrowProps);
        nextArrow = _react["default"].createElement(_arrows.NextArrow, arrowProps);
      }

      var verticalHeightStyle = null;

      if (_this.props.vertical) {
        verticalHeightStyle = {
          height: _this.state.listHeight
        };
      }

      var centerPaddingStyle = null;

      if (_this.props.vertical === false) {
        if (_this.props.centerMode === true) {
          centerPaddingStyle = {
            padding: "0px " + _this.props.centerPadding
          };
        }
      } else {
        if (_this.props.centerMode === true) {
          centerPaddingStyle = {
            padding: _this.props.centerPadding + " 0px"
          };
        }
      }

      var listStyle = _objectSpread({}, verticalHeightStyle, {}, centerPaddingStyle);

      var touchMove = _this.props.touchMove;
      var listProps = {
        className: "slick-list",
        style: listStyle,
        onClick: _this.clickHandler,
        onMouseDown: touchMove ? _this.swipeStart : null,
        onMouseMove: _this.state.dragging && touchMove ? _this.swipeMove : null,
        onMouseUp: touchMove ? _this.swipeEnd : null,
        onMouseLeave: _this.state.dragging && touchMove ? _this.swipeEnd : null,
        onTouchStart: touchMove ? _this.swipeStart : null,
        onTouchMove: _this.state.dragging && touchMove ? _this.swipeMove : null,
        onTouchEnd: touchMove ? _this.swipeEnd : null,
        onTouchCancel: _this.state.dragging && touchMove ? _this.swipeEnd : null,
        onKeyDown: _this.props.accessibility ? _this.keyHandler : null
      };
      var innerSliderProps = {
        className: className,
        dir: "ltr",
        style: _this.props.style
      };

      if (_this.props.unslick) {
        listProps = {
          className: "slick-list"
        };
        innerSliderProps = {
          className: className
        };
      }

      return _react["default"].createElement("div", innerSliderProps, !_this.props.unslick ? prevArrow : "", _react["default"].createElement("div", _extends({
        ref: _this.listRefHandler
      }, listProps), _react["default"].createElement(_track.Track, _extends({
        ref: _this.trackRefHandler
      }, trackProps), _this.props.children)), !_this.props.unslick ? nextArrow : "", !_this.props.unslick ? dots : "");
    });

    _this.list = null;
    _this.track = null;
    _this.state = _objectSpread({}, _initialState["default"], {
      currentSlide: _this.props.initialSlide,
      slideCount: _react["default"].Children.count(_this.props.children)
    });
    _this.callbackTimers = [];
    _this.clickable = true;
    _this.debouncedResize = null;
    return _this;
  }

  return InnerSlider;
}(_react["default"].Component);

exports.InnerSlider = InnerSlider;

/***/ }),

/***/ "./node_modules/react-slick/lib/slider.js":
/*!************************************************!*\
  !*** ./node_modules/react-slick/lib/slider.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));

var _innerSlider = __webpack_require__(/*! ./inner-slider */ "./node_modules/react-slick/lib/inner-slider.js");

var _json2mq = _interopRequireDefault(__webpack_require__(/*! json2mq */ "./node_modules/json2mq/index.js"));

var _defaultProps = _interopRequireDefault(__webpack_require__(/*! ./default-props */ "./node_modules/react-slick/lib/default-props.js"));

var _innerSliderUtils = __webpack_require__(/*! ./utils/innerSliderUtils */ "./node_modules/react-slick/lib/utils/innerSliderUtils.js");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(source, true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(source).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var enquire = (0, _innerSliderUtils.canUseDOM)() && __webpack_require__(/*! enquire.js */ "./node_modules/enquire.js/src/index.js");

var Slider =
/*#__PURE__*/
function (_React$Component) {
  _inherits(Slider, _React$Component);

  function Slider(props) {
    var _this;

    _classCallCheck(this, Slider);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(Slider).call(this, props));

    _defineProperty(_assertThisInitialized(_this), "innerSliderRefHandler", function (ref) {
      return _this.innerSlider = ref;
    });

    _defineProperty(_assertThisInitialized(_this), "slickPrev", function () {
      return _this.innerSlider.slickPrev();
    });

    _defineProperty(_assertThisInitialized(_this), "slickNext", function () {
      return _this.innerSlider.slickNext();
    });

    _defineProperty(_assertThisInitialized(_this), "slickGoTo", function (slide) {
      var dontAnimate = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      return _this.innerSlider.slickGoTo(slide, dontAnimate);
    });

    _defineProperty(_assertThisInitialized(_this), "slickPause", function () {
      return _this.innerSlider.pause("paused");
    });

    _defineProperty(_assertThisInitialized(_this), "slickPlay", function () {
      return _this.innerSlider.autoPlay("play");
    });

    _this.state = {
      breakpoint: null
    };
    _this._responsiveMediaHandlers = [];
    return _this;
  }

  _createClass(Slider, [{
    key: "media",
    value: function media(query, handler) {
      // javascript handler for  css media query
      enquire.register(query, handler);

      this._responsiveMediaHandlers.push({
        query: query,
        handler: handler
      });
    } // handles responsive breakpoints

  }, {
    key: "UNSAFE_componentWillMount",
    value: function UNSAFE_componentWillMount() {
      var _this2 = this;

      // performance monitoring
      //if (process.env.NODE_ENV !== 'production') {
      //const { whyDidYouUpdate } = require('why-did-you-update')
      //whyDidYouUpdate(React)
      //}
      if (this.props.responsive) {
        var breakpoints = this.props.responsive.map(function (breakpt) {
          return breakpt.breakpoint;
        }); // sort them in increasing order of their numerical value

        breakpoints.sort(function (x, y) {
          return x - y;
        });
        breakpoints.forEach(function (breakpoint, index) {
          // media query for each breakpoint
          var bQuery;

          if (index === 0) {
            bQuery = (0, _json2mq["default"])({
              minWidth: 0,
              maxWidth: breakpoint
            });
          } else {
            bQuery = (0, _json2mq["default"])({
              minWidth: breakpoints[index - 1] + 1,
              maxWidth: breakpoint
            });
          } // when not using server side rendering


          (0, _innerSliderUtils.canUseDOM)() && _this2.media(bQuery, function () {
            _this2.setState({
              breakpoint: breakpoint
            });
          });
        }); // Register media query for full screen. Need to support resize from small to large
        // convert javascript object to media query string

        var query = (0, _json2mq["default"])({
          minWidth: breakpoints.slice(-1)[0]
        });
        (0, _innerSliderUtils.canUseDOM)() && this.media(query, function () {
          _this2.setState({
            breakpoint: null
          });
        });
      }
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      this._responsiveMediaHandlers.forEach(function (obj) {
        enquire.unregister(obj.query, obj.handler);
      });
    }
  }, {
    key: "render",
    value: function render() {
      var _this3 = this;

      var settings;
      var newProps;

      if (this.state.breakpoint) {
        newProps = this.props.responsive.filter(function (resp) {
          return resp.breakpoint === _this3.state.breakpoint;
        });
        settings = newProps[0].settings === "unslick" ? "unslick" : _objectSpread({}, _defaultProps["default"], {}, this.props, {}, newProps[0].settings);
      } else {
        settings = _objectSpread({}, _defaultProps["default"], {}, this.props);
      } // force scrolling by one if centerMode is on


      if (settings.centerMode) {
        if (settings.slidesToScroll > 1 && "development" !== "production") {
          console.warn("slidesToScroll should be equal to 1 in centerMode, you are using ".concat(settings.slidesToScroll));
        }

        settings.slidesToScroll = 1;
      } // force showing one slide and scrolling by one if the fade mode is on


      if (settings.fade) {
        if (settings.slidesToShow > 1 && "development" !== "production") {
          console.warn("slidesToShow should be equal to 1 when fade is true, you're using ".concat(settings.slidesToShow));
        }

        if (settings.slidesToScroll > 1 && "development" !== "production") {
          console.warn("slidesToScroll should be equal to 1 when fade is true, you're using ".concat(settings.slidesToScroll));
        }

        settings.slidesToShow = 1;
        settings.slidesToScroll = 1;
      } // makes sure that children is an array, even when there is only 1 child


      var children = _react["default"].Children.toArray(this.props.children); // Children may contain false or null, so we should filter them
      // children may also contain string filled with spaces (in certain cases where we use jsx strings)


      children = children.filter(function (child) {
        if (typeof child === "string") {
          return !!child.trim();
        }

        return !!child;
      }); // rows and slidesPerRow logic is handled here

      if (settings.variableWidth && (settings.rows > 1 || settings.slidesPerRow > 1)) {
        console.warn("variableWidth is not supported in case of rows > 1 or slidesPerRow > 1");
        settings.variableWidth = false;
      }

      var newChildren = [];
      var currentWidth = null;

      for (var i = 0; i < children.length; i += settings.rows * settings.slidesPerRow) {
        var newSlide = [];

        for (var j = i; j < i + settings.rows * settings.slidesPerRow; j += settings.slidesPerRow) {
          var row = [];

          for (var k = j; k < j + settings.slidesPerRow; k += 1) {
            if (settings.variableWidth && children[k].props.style) {
              currentWidth = children[k].props.style.width;
            }

            if (k >= children.length) break;
            row.push(_react["default"].cloneElement(children[k], {
              key: 100 * i + 10 * j + k,
              tabIndex: -1,
              style: {
                width: "".concat(100 / settings.slidesPerRow, "%"),
                display: "inline-block"
              }
            }));
          }

          newSlide.push(_react["default"].createElement("div", {
            key: 10 * i + j
          }, row));
        }

        if (settings.variableWidth) {
          newChildren.push(_react["default"].createElement("div", {
            key: i,
            style: {
              width: currentWidth
            }
          }, newSlide));
        } else {
          newChildren.push(_react["default"].createElement("div", {
            key: i
          }, newSlide));
        }
      }

      if (settings === "unslick") {
        var className = "regular slider " + (this.props.className || "");
        return _react["default"].createElement("div", {
          className: className
        }, newChildren);
      } else if (newChildren.length <= settings.slidesToShow) {
        settings.unslick = true;
      }

      return _react["default"].createElement(_innerSlider.InnerSlider, _extends({
        style: this.props.style,
        ref: this.innerSliderRefHandler
      }, settings), newChildren);
    }
  }]);

  return Slider;
}(_react["default"].Component);

exports["default"] = Slider;

/***/ }),

/***/ "./node_modules/react-slick/lib/track.js":
/*!***********************************************!*\
  !*** ./node_modules/react-slick/lib/track.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Track = void 0;

var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));

var _classnames = _interopRequireDefault(__webpack_require__(/*! classnames */ "./node_modules/classnames/index.js"));

var _innerSliderUtils = __webpack_require__(/*! ./utils/innerSliderUtils */ "./node_modules/react-slick/lib/utils/innerSliderUtils.js");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(source, true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(source).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

// given specifications/props for a slide, fetch all the classes that need to be applied to the slide
var getSlideClasses = function getSlideClasses(spec) {
  var slickActive, slickCenter, slickCloned;
  var centerOffset, index;

  if (spec.rtl) {
    index = spec.slideCount - 1 - spec.index;
  } else {
    index = spec.index;
  }

  slickCloned = index < 0 || index >= spec.slideCount;

  if (spec.centerMode) {
    centerOffset = Math.floor(spec.slidesToShow / 2);
    slickCenter = (index - spec.currentSlide) % spec.slideCount === 0;

    if (index > spec.currentSlide - centerOffset - 1 && index <= spec.currentSlide + centerOffset) {
      slickActive = true;
    }
  } else {
    slickActive = spec.currentSlide <= index && index < spec.currentSlide + spec.slidesToShow;
  }

  var slickCurrent = index === spec.currentSlide;
  return {
    "slick-slide": true,
    "slick-active": slickActive,
    "slick-center": slickCenter,
    "slick-cloned": slickCloned,
    "slick-current": slickCurrent // dubious in case of RTL

  };
};

var getSlideStyle = function getSlideStyle(spec) {
  var style = {};

  if (spec.variableWidth === undefined || spec.variableWidth === false) {
    style.width = spec.slideWidth;
  }

  if (spec.fade) {
    style.position = "relative";

    if (spec.vertical) {
      style.top = -spec.index * parseInt(spec.slideHeight);
    } else {
      style.left = -spec.index * parseInt(spec.slideWidth);
    }

    style.opacity = spec.currentSlide === spec.index ? 1 : 0;
    style.transition = "opacity " + spec.speed + "ms " + spec.cssEase + ", " + "visibility " + spec.speed + "ms " + spec.cssEase;
    style.WebkitTransition = "opacity " + spec.speed + "ms " + spec.cssEase + ", " + "visibility " + spec.speed + "ms " + spec.cssEase;
  }

  return style;
};

var getKey = function getKey(child, fallbackKey) {
  return child.key || fallbackKey;
};

var renderSlides = function renderSlides(spec) {
  var key;
  var slides = [];
  var preCloneSlides = [];
  var postCloneSlides = [];

  var childrenCount = _react["default"].Children.count(spec.children);

  var startIndex = (0, _innerSliderUtils.lazyStartIndex)(spec);
  var endIndex = (0, _innerSliderUtils.lazyEndIndex)(spec);

  _react["default"].Children.forEach(spec.children, function (elem, index) {
    var child;
    var childOnClickOptions = {
      message: "children",
      index: index,
      slidesToScroll: spec.slidesToScroll,
      currentSlide: spec.currentSlide
    }; // in case of lazyLoad, whether or not we want to fetch the slide

    if (!spec.lazyLoad || spec.lazyLoad && spec.lazyLoadedList.indexOf(index) >= 0) {
      child = elem;
    } else {
      child = _react["default"].createElement("div", null);
    }

    var childStyle = getSlideStyle(_objectSpread({}, spec, {
      index: index
    }));
    var slideClass = child.props.className || "";
    var slideClasses = getSlideClasses(_objectSpread({}, spec, {
      index: index
    })); // push a cloned element of the desired slide

    slides.push(_react["default"].cloneElement(child, {
      key: "original" + getKey(child, index),
      "data-index": index,
      className: (0, _classnames["default"])(slideClasses, slideClass),
      tabIndex: "-1",
      "aria-hidden": !slideClasses["slick-active"],
      style: _objectSpread({
        outline: "none"
      }, child.props.style || {}, {}, childStyle),
      onClick: function onClick(e) {
        child.props && child.props.onClick && child.props.onClick(e);

        if (spec.focusOnSelect) {
          spec.focusOnSelect(childOnClickOptions);
        }
      }
    })); // if slide needs to be precloned or postcloned

    if (spec.infinite && spec.fade === false) {
      var preCloneNo = childrenCount - index;

      if (preCloneNo <= (0, _innerSliderUtils.getPreClones)(spec) && childrenCount !== spec.slidesToShow) {
        key = -preCloneNo;

        if (key >= startIndex) {
          child = elem;
        }

        slideClasses = getSlideClasses(_objectSpread({}, spec, {
          index: key
        }));
        preCloneSlides.push(_react["default"].cloneElement(child, {
          key: "precloned" + getKey(child, key),
          "data-index": key,
          tabIndex: "-1",
          className: (0, _classnames["default"])(slideClasses, slideClass),
          "aria-hidden": !slideClasses["slick-active"],
          style: _objectSpread({}, child.props.style || {}, {}, childStyle),
          onClick: function onClick(e) {
            child.props && child.props.onClick && child.props.onClick(e);

            if (spec.focusOnSelect) {
              spec.focusOnSelect(childOnClickOptions);
            }
          }
        }));
      }

      if (childrenCount !== spec.slidesToShow) {
        key = childrenCount + index;

        if (key < endIndex) {
          child = elem;
        }

        slideClasses = getSlideClasses(_objectSpread({}, spec, {
          index: key
        }));
        postCloneSlides.push(_react["default"].cloneElement(child, {
          key: "postcloned" + getKey(child, key),
          "data-index": key,
          tabIndex: "-1",
          className: (0, _classnames["default"])(slideClasses, slideClass),
          "aria-hidden": !slideClasses["slick-active"],
          style: _objectSpread({}, child.props.style || {}, {}, childStyle),
          onClick: function onClick(e) {
            child.props && child.props.onClick && child.props.onClick(e);

            if (spec.focusOnSelect) {
              spec.focusOnSelect(childOnClickOptions);
            }
          }
        }));
      }
    }
  });

  if (spec.rtl) {
    return preCloneSlides.concat(slides, postCloneSlides).reverse();
  } else {
    return preCloneSlides.concat(slides, postCloneSlides);
  }
};

var Track =
/*#__PURE__*/
function (_React$PureComponent) {
  _inherits(Track, _React$PureComponent);

  function Track() {
    _classCallCheck(this, Track);

    return _possibleConstructorReturn(this, _getPrototypeOf(Track).apply(this, arguments));
  }

  _createClass(Track, [{
    key: "render",
    value: function render() {
      var slides = renderSlides(this.props);
      var _this$props = this.props,
          onMouseEnter = _this$props.onMouseEnter,
          onMouseOver = _this$props.onMouseOver,
          onMouseLeave = _this$props.onMouseLeave;
      var mouseEvents = {
        onMouseEnter: onMouseEnter,
        onMouseOver: onMouseOver,
        onMouseLeave: onMouseLeave
      };
      return _react["default"].createElement("div", _extends({
        className: "slick-track",
        style: this.props.trackStyle
      }, mouseEvents), slides);
    }
  }]);

  return Track;
}(_react["default"].PureComponent);

exports.Track = Track;

/***/ }),

/***/ "./node_modules/react-slick/lib/utils/innerSliderUtils.js":
/*!****************************************************************!*\
  !*** ./node_modules/react-slick/lib/utils/innerSliderUtils.js ***!
  \****************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.canUseDOM = exports.slidesOnLeft = exports.slidesOnRight = exports.siblingDirection = exports.getTotalSlides = exports.getPostClones = exports.getPreClones = exports.getTrackLeft = exports.getTrackAnimateCSS = exports.getTrackCSS = exports.checkSpecKeys = exports.getSlideCount = exports.checkNavigable = exports.getNavigableIndexes = exports.swipeEnd = exports.swipeMove = exports.swipeStart = exports.keyHandler = exports.changeSlide = exports.slideHandler = exports.initializedState = exports.extractObject = exports.canGoNext = exports.getSwipeDirection = exports.getHeight = exports.getWidth = exports.lazySlidesOnRight = exports.lazySlidesOnLeft = exports.lazyEndIndex = exports.lazyStartIndex = exports.getRequiredLazySlides = exports.getOnDemandLazySlides = void 0;

var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));

var _reactDom = _interopRequireDefault(__webpack_require__(/*! react-dom */ "react-dom"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(source, true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(source).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var getOnDemandLazySlides = function getOnDemandLazySlides(spec) {
  var onDemandSlides = [];
  var startIndex = lazyStartIndex(spec);
  var endIndex = lazyEndIndex(spec);

  for (var slideIndex = startIndex; slideIndex < endIndex; slideIndex++) {
    if (spec.lazyLoadedList.indexOf(slideIndex) < 0) {
      onDemandSlides.push(slideIndex);
    }
  }

  return onDemandSlides;
}; // return list of slides that need to be present


exports.getOnDemandLazySlides = getOnDemandLazySlides;

var getRequiredLazySlides = function getRequiredLazySlides(spec) {
  var requiredSlides = [];
  var startIndex = lazyStartIndex(spec);
  var endIndex = lazyEndIndex(spec);

  for (var slideIndex = startIndex; slideIndex < endIndex; slideIndex++) {
    requiredSlides.push(slideIndex);
  }

  return requiredSlides;
}; // startIndex that needs to be present


exports.getRequiredLazySlides = getRequiredLazySlides;

var lazyStartIndex = function lazyStartIndex(spec) {
  return spec.currentSlide - lazySlidesOnLeft(spec);
};

exports.lazyStartIndex = lazyStartIndex;

var lazyEndIndex = function lazyEndIndex(spec) {
  return spec.currentSlide + lazySlidesOnRight(spec);
};

exports.lazyEndIndex = lazyEndIndex;

var lazySlidesOnLeft = function lazySlidesOnLeft(spec) {
  return spec.centerMode ? Math.floor(spec.slidesToShow / 2) + (parseInt(spec.centerPadding) > 0 ? 1 : 0) : 0;
};

exports.lazySlidesOnLeft = lazySlidesOnLeft;

var lazySlidesOnRight = function lazySlidesOnRight(spec) {
  return spec.centerMode ? Math.floor((spec.slidesToShow - 1) / 2) + 1 + (parseInt(spec.centerPadding) > 0 ? 1 : 0) : spec.slidesToShow;
}; // get width of an element


exports.lazySlidesOnRight = lazySlidesOnRight;

var getWidth = function getWidth(elem) {
  return elem && elem.offsetWidth || 0;
};

exports.getWidth = getWidth;

var getHeight = function getHeight(elem) {
  return elem && elem.offsetHeight || 0;
};

exports.getHeight = getHeight;

var getSwipeDirection = function getSwipeDirection(touchObject) {
  var verticalSwiping = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
  var xDist, yDist, r, swipeAngle;
  xDist = touchObject.startX - touchObject.curX;
  yDist = touchObject.startY - touchObject.curY;
  r = Math.atan2(yDist, xDist);
  swipeAngle = Math.round(r * 180 / Math.PI);

  if (swipeAngle < 0) {
    swipeAngle = 360 - Math.abs(swipeAngle);
  }

  if (swipeAngle <= 45 && swipeAngle >= 0 || swipeAngle <= 360 && swipeAngle >= 315) {
    return "left";
  }

  if (swipeAngle >= 135 && swipeAngle <= 225) {
    return "right";
  }

  if (verticalSwiping === true) {
    if (swipeAngle >= 35 && swipeAngle <= 135) {
      return "up";
    } else {
      return "down";
    }
  }

  return "vertical";
}; // whether or not we can go next


exports.getSwipeDirection = getSwipeDirection;

var canGoNext = function canGoNext(spec) {
  var canGo = true;

  if (!spec.infinite) {
    if (spec.centerMode && spec.currentSlide >= spec.slideCount - 1) {
      canGo = false;
    } else if (spec.slideCount <= spec.slidesToShow || spec.currentSlide >= spec.slideCount - spec.slidesToShow) {
      canGo = false;
    }
  }

  return canGo;
}; // given an object and a list of keys, return new object with given keys


exports.canGoNext = canGoNext;

var extractObject = function extractObject(spec, keys) {
  var newObject = {};
  keys.forEach(function (key) {
    return newObject[key] = spec[key];
  });
  return newObject;
}; // get initialized state


exports.extractObject = extractObject;

var initializedState = function initializedState(spec) {
  // spec also contains listRef, trackRef
  var slideCount = _react["default"].Children.count(spec.children);

  var listWidth = Math.ceil(getWidth(_reactDom["default"].findDOMNode(spec.listRef)));
  var trackWidth = Math.ceil(getWidth(_reactDom["default"].findDOMNode(spec.trackRef)));
  var slideWidth;

  if (!spec.vertical) {
    var centerPaddingAdj = spec.centerMode && parseInt(spec.centerPadding) * 2;

    if (typeof spec.centerPadding === "string" && spec.centerPadding.slice(-1) === "%") {
      centerPaddingAdj *= listWidth / 100;
    }

    slideWidth = Math.ceil((listWidth - centerPaddingAdj) / spec.slidesToShow);
  } else {
    slideWidth = listWidth;
  }

  var slideHeight = _reactDom["default"].findDOMNode(spec.listRef) && getHeight(_reactDom["default"].findDOMNode(spec.listRef).querySelector('[data-index="0"]'));
  var listHeight = slideHeight * spec.slidesToShow;
  var currentSlide = spec.currentSlide === undefined ? spec.initialSlide : spec.currentSlide;

  if (spec.rtl && spec.currentSlide === undefined) {
    currentSlide = slideCount - 1 - spec.initialSlide;
  }

  var lazyLoadedList = spec.lazyLoadedList || [];
  var slidesToLoad = getOnDemandLazySlides({
    currentSlide: currentSlide,
    lazyLoadedList: lazyLoadedList
  }, spec);
  lazyLoadedList.concat(slidesToLoad);
  var state = {
    slideCount: slideCount,
    slideWidth: slideWidth,
    listWidth: listWidth,
    trackWidth: trackWidth,
    currentSlide: currentSlide,
    slideHeight: slideHeight,
    listHeight: listHeight,
    lazyLoadedList: lazyLoadedList
  };

  if (spec.autoplaying === null && spec.autoplay) {
    state["autoplaying"] = "playing";
  }

  return state;
};

exports.initializedState = initializedState;

var slideHandler = function slideHandler(spec) {
  var waitForAnimate = spec.waitForAnimate,
      animating = spec.animating,
      fade = spec.fade,
      infinite = spec.infinite,
      index = spec.index,
      slideCount = spec.slideCount,
      lazyLoadedList = spec.lazyLoadedList,
      lazyLoad = spec.lazyLoad,
      currentSlide = spec.currentSlide,
      centerMode = spec.centerMode,
      slidesToScroll = spec.slidesToScroll,
      slidesToShow = spec.slidesToShow,
      useCSS = spec.useCSS;
  if (waitForAnimate && animating) return {};
  var animationSlide = index,
      finalSlide,
      animationLeft,
      finalLeft;
  var state = {},
      nextState = {};

  if (fade) {
    if (!infinite && (index < 0 || index >= slideCount)) return {};

    if (index < 0) {
      animationSlide = index + slideCount;
    } else if (index >= slideCount) {
      animationSlide = index - slideCount;
    }

    if (lazyLoad && lazyLoadedList.indexOf(animationSlide) < 0) {
      lazyLoadedList.push(animationSlide);
    }

    state = {
      animating: true,
      currentSlide: animationSlide,
      lazyLoadedList: lazyLoadedList
    };
    nextState = {
      animating: false
    };
  } else {
    finalSlide = animationSlide;

    if (animationSlide < 0) {
      finalSlide = animationSlide + slideCount;
      if (!infinite) finalSlide = 0;else if (slideCount % slidesToScroll !== 0) finalSlide = slideCount - slideCount % slidesToScroll;
    } else if (!canGoNext(spec) && animationSlide > currentSlide) {
      animationSlide = finalSlide = currentSlide;
    } else if (centerMode && animationSlide >= slideCount) {
      animationSlide = infinite ? slideCount : slideCount - 1;
      finalSlide = infinite ? 0 : slideCount - 1;
    } else if (animationSlide >= slideCount) {
      finalSlide = animationSlide - slideCount;
      if (!infinite) finalSlide = slideCount - slidesToShow;else if (slideCount % slidesToScroll !== 0) finalSlide = 0;
    }

    animationLeft = getTrackLeft(_objectSpread({}, spec, {
      slideIndex: animationSlide
    }));
    finalLeft = getTrackLeft(_objectSpread({}, spec, {
      slideIndex: finalSlide
    }));

    if (!infinite) {
      if (animationLeft === finalLeft) animationSlide = finalSlide;
      animationLeft = finalLeft;
    }

    lazyLoad && lazyLoadedList.concat(getOnDemandLazySlides(_objectSpread({}, spec, {
      currentSlide: animationSlide
    })));

    if (!useCSS) {
      state = {
        currentSlide: finalSlide,
        trackStyle: getTrackCSS(_objectSpread({}, spec, {
          left: finalLeft
        })),
        lazyLoadedList: lazyLoadedList
      };
    } else {
      state = {
        animating: true,
        currentSlide: finalSlide,
        trackStyle: getTrackAnimateCSS(_objectSpread({}, spec, {
          left: animationLeft
        })),
        lazyLoadedList: lazyLoadedList
      };
      nextState = {
        animating: false,
        currentSlide: finalSlide,
        trackStyle: getTrackCSS(_objectSpread({}, spec, {
          left: finalLeft
        })),
        swipeLeft: null
      };
    }
  }

  return {
    state: state,
    nextState: nextState
  };
};

exports.slideHandler = slideHandler;

var changeSlide = function changeSlide(spec, options) {
  var indexOffset, previousInt, slideOffset, unevenOffset, targetSlide;
  var slidesToScroll = spec.slidesToScroll,
      slidesToShow = spec.slidesToShow,
      slideCount = spec.slideCount,
      currentSlide = spec.currentSlide,
      lazyLoad = spec.lazyLoad,
      infinite = spec.infinite;
  unevenOffset = slideCount % slidesToScroll !== 0;
  indexOffset = unevenOffset ? 0 : (slideCount - currentSlide) % slidesToScroll;

  if (options.message === "previous") {
    slideOffset = indexOffset === 0 ? slidesToScroll : slidesToShow - indexOffset;
    targetSlide = currentSlide - slideOffset;

    if (lazyLoad && !infinite) {
      previousInt = currentSlide - slideOffset;
      targetSlide = previousInt === -1 ? slideCount - 1 : previousInt;
    }
  } else if (options.message === "next") {
    slideOffset = indexOffset === 0 ? slidesToScroll : indexOffset;
    targetSlide = currentSlide + slideOffset;

    if (lazyLoad && !infinite) {
      targetSlide = (currentSlide + slidesToScroll) % slideCount + indexOffset;
    }
  } else if (options.message === "dots") {
    // Click on dots
    targetSlide = options.index * options.slidesToScroll;

    if (targetSlide === options.currentSlide) {
      return null;
    }
  } else if (options.message === "children") {
    // Click on the slides
    targetSlide = options.index;

    if (targetSlide === options.currentSlide) {
      return null;
    }

    if (infinite) {
      var direction = siblingDirection(_objectSpread({}, spec, {
        targetSlide: targetSlide
      }));

      if (targetSlide > options.currentSlide && direction === "left") {
        targetSlide = targetSlide - slideCount;
      } else if (targetSlide < options.currentSlide && direction === "right") {
        targetSlide = targetSlide + slideCount;
      }
    }
  } else if (options.message === "index") {
    targetSlide = Number(options.index);

    if (targetSlide === options.currentSlide) {
      return null;
    }
  }

  return targetSlide;
};

exports.changeSlide = changeSlide;

var keyHandler = function keyHandler(e, accessibility, rtl) {
  if (e.target.tagName.match("TEXTAREA|INPUT|SELECT") || !accessibility) return "";
  if (e.keyCode === 37) return rtl ? "next" : "previous";
  if (e.keyCode === 39) return rtl ? "previous" : "next";
  return "";
};

exports.keyHandler = keyHandler;

var swipeStart = function swipeStart(e, swipe, draggable) {
  e.target.tagName === "IMG" && e.preventDefault();
  if (!swipe || !draggable && e.type.indexOf("mouse") !== -1) return "";
  return {
    dragging: true,
    touchObject: {
      startX: e.touches ? e.touches[0].pageX : e.clientX,
      startY: e.touches ? e.touches[0].pageY : e.clientY,
      curX: e.touches ? e.touches[0].pageX : e.clientX,
      curY: e.touches ? e.touches[0].pageY : e.clientY
    }
  };
};

exports.swipeStart = swipeStart;

var swipeMove = function swipeMove(e, spec) {
  // spec also contains, trackRef and slideIndex
  var scrolling = spec.scrolling,
      animating = spec.animating,
      vertical = spec.vertical,
      swipeToSlide = spec.swipeToSlide,
      verticalSwiping = spec.verticalSwiping,
      rtl = spec.rtl,
      currentSlide = spec.currentSlide,
      edgeFriction = spec.edgeFriction,
      edgeDragged = spec.edgeDragged,
      onEdge = spec.onEdge,
      swiped = spec.swiped,
      swiping = spec.swiping,
      slideCount = spec.slideCount,
      slidesToScroll = spec.slidesToScroll,
      infinite = spec.infinite,
      touchObject = spec.touchObject,
      swipeEvent = spec.swipeEvent,
      listHeight = spec.listHeight,
      listWidth = spec.listWidth;
  if (scrolling) return;
  if (animating) return e.preventDefault();
  if (vertical && swipeToSlide && verticalSwiping) e.preventDefault();
  var swipeLeft,
      state = {};
  var curLeft = getTrackLeft(spec);
  touchObject.curX = e.touches ? e.touches[0].pageX : e.clientX;
  touchObject.curY = e.touches ? e.touches[0].pageY : e.clientY;
  touchObject.swipeLength = Math.round(Math.sqrt(Math.pow(touchObject.curX - touchObject.startX, 2)));
  var verticalSwipeLength = Math.round(Math.sqrt(Math.pow(touchObject.curY - touchObject.startY, 2)));

  if (!verticalSwiping && !swiping && verticalSwipeLength > 10) {
    return {
      scrolling: true
    };
  }

  if (verticalSwiping) touchObject.swipeLength = verticalSwipeLength;
  var positionOffset = (!rtl ? 1 : -1) * (touchObject.curX > touchObject.startX ? 1 : -1);
  if (verticalSwiping) positionOffset = touchObject.curY > touchObject.startY ? 1 : -1;
  var dotCount = Math.ceil(slideCount / slidesToScroll);
  var swipeDirection = getSwipeDirection(spec.touchObject, verticalSwiping);
  var touchSwipeLength = touchObject.swipeLength;

  if (!infinite) {
    if (currentSlide === 0 && swipeDirection === "right" || currentSlide + 1 >= dotCount && swipeDirection === "left" || !canGoNext(spec) && swipeDirection === "left") {
      touchSwipeLength = touchObject.swipeLength * edgeFriction;

      if (edgeDragged === false && onEdge) {
        onEdge(swipeDirection);
        state["edgeDragged"] = true;
      }
    }
  }

  if (!swiped && swipeEvent) {
    swipeEvent(swipeDirection);
    state["swiped"] = true;
  }

  if (!vertical) {
    if (!rtl) {
      swipeLeft = curLeft + touchSwipeLength * positionOffset;
    } else {
      swipeLeft = curLeft - touchSwipeLength * positionOffset;
    }
  } else {
    swipeLeft = curLeft + touchSwipeLength * (listHeight / listWidth) * positionOffset;
  }

  if (verticalSwiping) {
    swipeLeft = curLeft + touchSwipeLength * positionOffset;
  }

  state = _objectSpread({}, state, {
    touchObject: touchObject,
    swipeLeft: swipeLeft,
    trackStyle: getTrackCSS(_objectSpread({}, spec, {
      left: swipeLeft
    }))
  });

  if (Math.abs(touchObject.curX - touchObject.startX) < Math.abs(touchObject.curY - touchObject.startY) * 0.8) {
    return state;
  }

  if (touchObject.swipeLength > 10) {
    state["swiping"] = true;
    e.preventDefault();
  }

  return state;
};

exports.swipeMove = swipeMove;

var swipeEnd = function swipeEnd(e, spec) {
  var dragging = spec.dragging,
      swipe = spec.swipe,
      touchObject = spec.touchObject,
      listWidth = spec.listWidth,
      touchThreshold = spec.touchThreshold,
      verticalSwiping = spec.verticalSwiping,
      listHeight = spec.listHeight,
      currentSlide = spec.currentSlide,
      swipeToSlide = spec.swipeToSlide,
      scrolling = spec.scrolling,
      onSwipe = spec.onSwipe;

  if (!dragging) {
    if (swipe) e.preventDefault();
    return {};
  }

  var minSwipe = verticalSwiping ? listHeight / touchThreshold : listWidth / touchThreshold;
  var swipeDirection = getSwipeDirection(touchObject, verticalSwiping); // reset the state of touch related state variables.

  var state = {
    dragging: false,
    edgeDragged: false,
    scrolling: false,
    swiping: false,
    swiped: false,
    swipeLeft: null,
    touchObject: {}
  };

  if (scrolling) {
    return state;
  }

  if (!touchObject.swipeLength) {
    return state;
  }

  if (touchObject.swipeLength > minSwipe) {
    e.preventDefault();

    if (onSwipe) {
      onSwipe(swipeDirection);
    }

    var slideCount, newSlide;

    switch (swipeDirection) {
      case "left":
      case "up":
        newSlide = currentSlide + getSlideCount(spec);
        slideCount = swipeToSlide ? checkNavigable(spec, newSlide) : newSlide;
        state["currentDirection"] = 0;
        break;

      case "right":
      case "down":
        newSlide = currentSlide - getSlideCount(spec);
        slideCount = swipeToSlide ? checkNavigable(spec, newSlide) : newSlide;
        state["currentDirection"] = 1;
        break;

      default:
        slideCount = currentSlide;
    }

    state["triggerSlideHandler"] = slideCount;
  } else {
    // Adjust the track back to it's original position.
    var currentLeft = getTrackLeft(spec);
    state["trackStyle"] = getTrackAnimateCSS(_objectSpread({}, spec, {
      left: currentLeft
    }));
  }

  return state;
};

exports.swipeEnd = swipeEnd;

var getNavigableIndexes = function getNavigableIndexes(spec) {
  var max = spec.infinite ? spec.slideCount * 2 : spec.slideCount;
  var breakpoint = spec.infinite ? spec.slidesToShow * -1 : 0;
  var counter = spec.infinite ? spec.slidesToShow * -1 : 0;
  var indexes = [];

  while (breakpoint < max) {
    indexes.push(breakpoint);
    breakpoint = counter + spec.slidesToScroll;
    counter += Math.min(spec.slidesToScroll, spec.slidesToShow);
  }

  return indexes;
};

exports.getNavigableIndexes = getNavigableIndexes;

var checkNavigable = function checkNavigable(spec, index) {
  var navigables = getNavigableIndexes(spec);
  var prevNavigable = 0;

  if (index > navigables[navigables.length - 1]) {
    index = navigables[navigables.length - 1];
  } else {
    for (var n in navigables) {
      if (index < navigables[n]) {
        index = prevNavigable;
        break;
      }

      prevNavigable = navigables[n];
    }
  }

  return index;
};

exports.checkNavigable = checkNavigable;

var getSlideCount = function getSlideCount(spec) {
  var centerOffset = spec.centerMode ? spec.slideWidth * Math.floor(spec.slidesToShow / 2) : 0;

  if (spec.swipeToSlide) {
    var swipedSlide;

    var slickList = _reactDom["default"].findDOMNode(spec.listRef);

    var slides = slickList.querySelectorAll(".slick-slide");
    Array.from(slides).every(function (slide) {
      if (!spec.vertical) {
        if (slide.offsetLeft - centerOffset + getWidth(slide) / 2 > spec.swipeLeft * -1) {
          swipedSlide = slide;
          return false;
        }
      } else {
        if (slide.offsetTop + getHeight(slide) / 2 > spec.swipeLeft * -1) {
          swipedSlide = slide;
          return false;
        }
      }

      return true;
    });

    if (!swipedSlide) {
      return 0;
    }

    var currentIndex = spec.rtl === true ? spec.slideCount - spec.currentSlide : spec.currentSlide;
    var slidesTraversed = Math.abs(swipedSlide.dataset.index - currentIndex) || 1;
    return slidesTraversed;
  } else {
    return spec.slidesToScroll;
  }
};

exports.getSlideCount = getSlideCount;

var checkSpecKeys = function checkSpecKeys(spec, keysArray) {
  return keysArray.reduce(function (value, key) {
    return value && spec.hasOwnProperty(key);
  }, true) ? null : console.error("Keys Missing:", spec);
};

exports.checkSpecKeys = checkSpecKeys;

var getTrackCSS = function getTrackCSS(spec) {
  checkSpecKeys(spec, ["left", "variableWidth", "slideCount", "slidesToShow", "slideWidth"]);
  var trackWidth, trackHeight;
  var trackChildren = spec.slideCount + 2 * spec.slidesToShow;

  if (!spec.vertical) {
    trackWidth = getTotalSlides(spec) * spec.slideWidth;
  } else {
    trackHeight = trackChildren * spec.slideHeight;
  }

  var style = {
    opacity: 1,
    transition: "",
    WebkitTransition: ""
  };

  if (spec.useTransform) {
    var WebkitTransform = !spec.vertical ? "translate3d(" + spec.left + "px, 0px, 0px)" : "translate3d(0px, " + spec.left + "px, 0px)";
    var transform = !spec.vertical ? "translate3d(" + spec.left + "px, 0px, 0px)" : "translate3d(0px, " + spec.left + "px, 0px)";
    var msTransform = !spec.vertical ? "translateX(" + spec.left + "px)" : "translateY(" + spec.left + "px)";
    style = _objectSpread({}, style, {
      WebkitTransform: WebkitTransform,
      transform: transform,
      msTransform: msTransform
    });
  } else {
    if (spec.vertical) {
      style["top"] = spec.left;
    } else {
      style["left"] = spec.left;
    }
  }

  if (spec.fade) style = {
    opacity: 1
  };
  if (trackWidth) style.width = trackWidth;
  if (trackHeight) style.height = trackHeight; // Fallback for IE8

  if (window && !window.addEventListener && window.attachEvent) {
    if (!spec.vertical) {
      style.marginLeft = spec.left + "px";
    } else {
      style.marginTop = spec.left + "px";
    }
  }

  return style;
};

exports.getTrackCSS = getTrackCSS;

var getTrackAnimateCSS = function getTrackAnimateCSS(spec) {
  checkSpecKeys(spec, ["left", "variableWidth", "slideCount", "slidesToShow", "slideWidth", "speed", "cssEase"]);
  var style = getTrackCSS(spec); // useCSS is true by default so it can be undefined

  if (spec.useTransform) {
    style.WebkitTransition = "-webkit-transform " + spec.speed + "ms " + spec.cssEase;
    style.transition = "transform " + spec.speed + "ms " + spec.cssEase;
  } else {
    if (spec.vertical) {
      style.transition = "top " + spec.speed + "ms " + spec.cssEase;
    } else {
      style.transition = "left " + spec.speed + "ms " + spec.cssEase;
    }
  }

  return style;
};

exports.getTrackAnimateCSS = getTrackAnimateCSS;

var getTrackLeft = function getTrackLeft(spec) {
  if (spec.unslick) {
    return 0;
  }

  checkSpecKeys(spec, ["slideIndex", "trackRef", "infinite", "centerMode", "slideCount", "slidesToShow", "slidesToScroll", "slideWidth", "listWidth", "variableWidth", "slideHeight"]);
  var slideIndex = spec.slideIndex,
      trackRef = spec.trackRef,
      infinite = spec.infinite,
      centerMode = spec.centerMode,
      slideCount = spec.slideCount,
      slidesToShow = spec.slidesToShow,
      slidesToScroll = spec.slidesToScroll,
      slideWidth = spec.slideWidth,
      listWidth = spec.listWidth,
      variableWidth = spec.variableWidth,
      slideHeight = spec.slideHeight,
      fade = spec.fade,
      vertical = spec.vertical;
  var slideOffset = 0;
  var targetLeft;
  var targetSlide;
  var verticalOffset = 0;

  if (fade || spec.slideCount === 1) {
    return 0;
  }

  var slidesToOffset = 0;

  if (infinite) {
    slidesToOffset = -getPreClones(spec); // bring active slide to the beginning of visual area
    // if next scroll doesn't have enough children, just reach till the end of original slides instead of shifting slidesToScroll children

    if (slideCount % slidesToScroll !== 0 && slideIndex + slidesToScroll > slideCount) {
      slidesToOffset = -(slideIndex > slideCount ? slidesToShow - (slideIndex - slideCount) : slideCount % slidesToScroll);
    } // shift current slide to center of the frame


    if (centerMode) {
      slidesToOffset += parseInt(slidesToShow / 2);
    }
  } else {
    if (slideCount % slidesToScroll !== 0 && slideIndex + slidesToScroll > slideCount) {
      slidesToOffset = slidesToShow - slideCount % slidesToScroll;
    }

    if (centerMode) {
      slidesToOffset = parseInt(slidesToShow / 2);
    }
  }

  slideOffset = slidesToOffset * slideWidth;
  verticalOffset = slidesToOffset * slideHeight;

  if (!vertical) {
    targetLeft = slideIndex * slideWidth * -1 + slideOffset;
  } else {
    targetLeft = slideIndex * slideHeight * -1 + verticalOffset;
  }

  if (variableWidth === true) {
    var targetSlideIndex;

    var trackElem = _reactDom["default"].findDOMNode(trackRef);

    targetSlideIndex = slideIndex + getPreClones(spec);
    targetSlide = trackElem && trackElem.childNodes[targetSlideIndex];
    targetLeft = targetSlide ? targetSlide.offsetLeft * -1 : 0;

    if (centerMode === true) {
      targetSlideIndex = infinite ? slideIndex + getPreClones(spec) : slideIndex;
      targetSlide = trackElem && trackElem.children[targetSlideIndex];
      targetLeft = 0;

      for (var slide = 0; slide < targetSlideIndex; slide++) {
        targetLeft -= trackElem && trackElem.children[slide] && trackElem.children[slide].offsetWidth;
      }

      targetLeft -= parseInt(spec.centerPadding);
      targetLeft += targetSlide && (listWidth - targetSlide.offsetWidth) / 2;
    }
  }

  return targetLeft;
};

exports.getTrackLeft = getTrackLeft;

var getPreClones = function getPreClones(spec) {
  if (spec.unslick || !spec.infinite) {
    return 0;
  }

  if (spec.variableWidth) {
    return spec.slideCount;
  }

  return spec.slidesToShow + (spec.centerMode ? 1 : 0);
};

exports.getPreClones = getPreClones;

var getPostClones = function getPostClones(spec) {
  if (spec.unslick || !spec.infinite) {
    return 0;
  }

  return spec.slideCount;
};

exports.getPostClones = getPostClones;

var getTotalSlides = function getTotalSlides(spec) {
  return spec.slideCount === 1 ? 1 : getPreClones(spec) + spec.slideCount + getPostClones(spec);
};

exports.getTotalSlides = getTotalSlides;

var siblingDirection = function siblingDirection(spec) {
  if (spec.targetSlide > spec.currentSlide) {
    if (spec.targetSlide > spec.currentSlide + slidesOnRight(spec)) {
      return "left";
    }

    return "right";
  } else {
    if (spec.targetSlide < spec.currentSlide - slidesOnLeft(spec)) {
      return "right";
    }

    return "left";
  }
};

exports.siblingDirection = siblingDirection;

var slidesOnRight = function slidesOnRight(_ref) {
  var slidesToShow = _ref.slidesToShow,
      centerMode = _ref.centerMode,
      rtl = _ref.rtl,
      centerPadding = _ref.centerPadding;

  // returns no of slides on the right of active slide
  if (centerMode) {
    var right = (slidesToShow - 1) / 2 + 1;
    if (parseInt(centerPadding) > 0) right += 1;
    if (rtl && slidesToShow % 2 === 0) right += 1;
    return right;
  }

  if (rtl) {
    return 0;
  }

  return slidesToShow - 1;
};

exports.slidesOnRight = slidesOnRight;

var slidesOnLeft = function slidesOnLeft(_ref2) {
  var slidesToShow = _ref2.slidesToShow,
      centerMode = _ref2.centerMode,
      rtl = _ref2.rtl,
      centerPadding = _ref2.centerPadding;

  // returns no of slides on the left of active slide
  if (centerMode) {
    var left = (slidesToShow - 1) / 2 + 1;
    if (parseInt(centerPadding) > 0) left += 1;
    if (!rtl && slidesToShow % 2 === 0) left += 1;
    return left;
  }

  if (rtl) {
    return slidesToShow - 1;
  }

  return 0;
};

exports.slidesOnLeft = slidesOnLeft;

var canUseDOM = function canUseDOM() {
  return !!(typeof window !== "undefined" && window.document && window.document.createElement);
};

exports.canUseDOM = canUseDOM;

/***/ }),

/***/ "./node_modules/resize-observer-polyfill/dist/ResizeObserver.es.js":
/*!*************************************************************************!*\
  !*** ./node_modules/resize-observer-polyfill/dist/ResizeObserver.es.js ***!
  \*************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function(global) {/**
 * A collection of shims that provide minimal functionality of the ES6 collections.
 *
 * These implementations are not meant to be used outside of the ResizeObserver
 * modules as they cover only a limited range of use cases.
 */
/* eslint-disable require-jsdoc, valid-jsdoc */
var MapShim = (function () {
    if (typeof Map !== 'undefined') {
        return Map;
    }
    /**
     * Returns index in provided array that matches the specified key.
     *
     * @param {Array<Array>} arr
     * @param {*} key
     * @returns {number}
     */
    function getIndex(arr, key) {
        var result = -1;
        arr.some(function (entry, index) {
            if (entry[0] === key) {
                result = index;
                return true;
            }
            return false;
        });
        return result;
    }
    return /** @class */ (function () {
        function class_1() {
            this.__entries__ = [];
        }
        Object.defineProperty(class_1.prototype, "size", {
            /**
             * @returns {boolean}
             */
            get: function () {
                return this.__entries__.length;
            },
            enumerable: true,
            configurable: true
        });
        /**
         * @param {*} key
         * @returns {*}
         */
        class_1.prototype.get = function (key) {
            var index = getIndex(this.__entries__, key);
            var entry = this.__entries__[index];
            return entry && entry[1];
        };
        /**
         * @param {*} key
         * @param {*} value
         * @returns {void}
         */
        class_1.prototype.set = function (key, value) {
            var index = getIndex(this.__entries__, key);
            if (~index) {
                this.__entries__[index][1] = value;
            }
            else {
                this.__entries__.push([key, value]);
            }
        };
        /**
         * @param {*} key
         * @returns {void}
         */
        class_1.prototype.delete = function (key) {
            var entries = this.__entries__;
            var index = getIndex(entries, key);
            if (~index) {
                entries.splice(index, 1);
            }
        };
        /**
         * @param {*} key
         * @returns {void}
         */
        class_1.prototype.has = function (key) {
            return !!~getIndex(this.__entries__, key);
        };
        /**
         * @returns {void}
         */
        class_1.prototype.clear = function () {
            this.__entries__.splice(0);
        };
        /**
         * @param {Function} callback
         * @param {*} [ctx=null]
         * @returns {void}
         */
        class_1.prototype.forEach = function (callback, ctx) {
            if (ctx === void 0) { ctx = null; }
            for (var _i = 0, _a = this.__entries__; _i < _a.length; _i++) {
                var entry = _a[_i];
                callback.call(ctx, entry[1], entry[0]);
            }
        };
        return class_1;
    }());
})();

/**
 * Detects whether window and document objects are available in current environment.
 */
var isBrowser = typeof window !== 'undefined' && typeof document !== 'undefined' && window.document === document;

// Returns global object of a current environment.
var global$1 = (function () {
    if (typeof global !== 'undefined' && global.Math === Math) {
        return global;
    }
    if (typeof self !== 'undefined' && self.Math === Math) {
        return self;
    }
    if (typeof window !== 'undefined' && window.Math === Math) {
        return window;
    }
    // eslint-disable-next-line no-new-func
    return Function('return this')();
})();

/**
 * A shim for the requestAnimationFrame which falls back to the setTimeout if
 * first one is not supported.
 *
 * @returns {number} Requests' identifier.
 */
var requestAnimationFrame$1 = (function () {
    if (typeof requestAnimationFrame === 'function') {
        // It's required to use a bounded function because IE sometimes throws
        // an "Invalid calling object" error if rAF is invoked without the global
        // object on the left hand side.
        return requestAnimationFrame.bind(global$1);
    }
    return function (callback) { return setTimeout(function () { return callback(Date.now()); }, 1000 / 60); };
})();

// Defines minimum timeout before adding a trailing call.
var trailingTimeout = 2;
/**
 * Creates a wrapper function which ensures that provided callback will be
 * invoked only once during the specified delay period.
 *
 * @param {Function} callback - Function to be invoked after the delay period.
 * @param {number} delay - Delay after which to invoke callback.
 * @returns {Function}
 */
function throttle (callback, delay) {
    var leadingCall = false, trailingCall = false, lastCallTime = 0;
    /**
     * Invokes the original callback function and schedules new invocation if
     * the "proxy" was called during current request.
     *
     * @returns {void}
     */
    function resolvePending() {
        if (leadingCall) {
            leadingCall = false;
            callback();
        }
        if (trailingCall) {
            proxy();
        }
    }
    /**
     * Callback invoked after the specified delay. It will further postpone
     * invocation of the original function delegating it to the
     * requestAnimationFrame.
     *
     * @returns {void}
     */
    function timeoutCallback() {
        requestAnimationFrame$1(resolvePending);
    }
    /**
     * Schedules invocation of the original function.
     *
     * @returns {void}
     */
    function proxy() {
        var timeStamp = Date.now();
        if (leadingCall) {
            // Reject immediately following calls.
            if (timeStamp - lastCallTime < trailingTimeout) {
                return;
            }
            // Schedule new call to be in invoked when the pending one is resolved.
            // This is important for "transitions" which never actually start
            // immediately so there is a chance that we might miss one if change
            // happens amids the pending invocation.
            trailingCall = true;
        }
        else {
            leadingCall = true;
            trailingCall = false;
            setTimeout(timeoutCallback, delay);
        }
        lastCallTime = timeStamp;
    }
    return proxy;
}

// Minimum delay before invoking the update of observers.
var REFRESH_DELAY = 20;
// A list of substrings of CSS properties used to find transition events that
// might affect dimensions of observed elements.
var transitionKeys = ['top', 'right', 'bottom', 'left', 'width', 'height', 'size', 'weight'];
// Check if MutationObserver is available.
var mutationObserverSupported = typeof MutationObserver !== 'undefined';
/**
 * Singleton controller class which handles updates of ResizeObserver instances.
 */
var ResizeObserverController = /** @class */ (function () {
    /**
     * Creates a new instance of ResizeObserverController.
     *
     * @private
     */
    function ResizeObserverController() {
        /**
         * Indicates whether DOM listeners have been added.
         *
         * @private {boolean}
         */
        this.connected_ = false;
        /**
         * Tells that controller has subscribed for Mutation Events.
         *
         * @private {boolean}
         */
        this.mutationEventsAdded_ = false;
        /**
         * Keeps reference to the instance of MutationObserver.
         *
         * @private {MutationObserver}
         */
        this.mutationsObserver_ = null;
        /**
         * A list of connected observers.
         *
         * @private {Array<ResizeObserverSPI>}
         */
        this.observers_ = [];
        this.onTransitionEnd_ = this.onTransitionEnd_.bind(this);
        this.refresh = throttle(this.refresh.bind(this), REFRESH_DELAY);
    }
    /**
     * Adds observer to observers list.
     *
     * @param {ResizeObserverSPI} observer - Observer to be added.
     * @returns {void}
     */
    ResizeObserverController.prototype.addObserver = function (observer) {
        if (!~this.observers_.indexOf(observer)) {
            this.observers_.push(observer);
        }
        // Add listeners if they haven't been added yet.
        if (!this.connected_) {
            this.connect_();
        }
    };
    /**
     * Removes observer from observers list.
     *
     * @param {ResizeObserverSPI} observer - Observer to be removed.
     * @returns {void}
     */
    ResizeObserverController.prototype.removeObserver = function (observer) {
        var observers = this.observers_;
        var index = observers.indexOf(observer);
        // Remove observer if it's present in registry.
        if (~index) {
            observers.splice(index, 1);
        }
        // Remove listeners if controller has no connected observers.
        if (!observers.length && this.connected_) {
            this.disconnect_();
        }
    };
    /**
     * Invokes the update of observers. It will continue running updates insofar
     * it detects changes.
     *
     * @returns {void}
     */
    ResizeObserverController.prototype.refresh = function () {
        var changesDetected = this.updateObservers_();
        // Continue running updates if changes have been detected as there might
        // be future ones caused by CSS transitions.
        if (changesDetected) {
            this.refresh();
        }
    };
    /**
     * Updates every observer from observers list and notifies them of queued
     * entries.
     *
     * @private
     * @returns {boolean} Returns "true" if any observer has detected changes in
     *      dimensions of it's elements.
     */
    ResizeObserverController.prototype.updateObservers_ = function () {
        // Collect observers that have active observations.
        var activeObservers = this.observers_.filter(function (observer) {
            return observer.gatherActive(), observer.hasActive();
        });
        // Deliver notifications in a separate cycle in order to avoid any
        // collisions between observers, e.g. when multiple instances of
        // ResizeObserver are tracking the same element and the callback of one
        // of them changes content dimensions of the observed target. Sometimes
        // this may result in notifications being blocked for the rest of observers.
        activeObservers.forEach(function (observer) { return observer.broadcastActive(); });
        return activeObservers.length > 0;
    };
    /**
     * Initializes DOM listeners.
     *
     * @private
     * @returns {void}
     */
    ResizeObserverController.prototype.connect_ = function () {
        // Do nothing if running in a non-browser environment or if listeners
        // have been already added.
        if (!isBrowser || this.connected_) {
            return;
        }
        // Subscription to the "Transitionend" event is used as a workaround for
        // delayed transitions. This way it's possible to capture at least the
        // final state of an element.
        document.addEventListener('transitionend', this.onTransitionEnd_);
        window.addEventListener('resize', this.refresh);
        if (mutationObserverSupported) {
            this.mutationsObserver_ = new MutationObserver(this.refresh);
            this.mutationsObserver_.observe(document, {
                attributes: true,
                childList: true,
                characterData: true,
                subtree: true
            });
        }
        else {
            document.addEventListener('DOMSubtreeModified', this.refresh);
            this.mutationEventsAdded_ = true;
        }
        this.connected_ = true;
    };
    /**
     * Removes DOM listeners.
     *
     * @private
     * @returns {void}
     */
    ResizeObserverController.prototype.disconnect_ = function () {
        // Do nothing if running in a non-browser environment or if listeners
        // have been already removed.
        if (!isBrowser || !this.connected_) {
            return;
        }
        document.removeEventListener('transitionend', this.onTransitionEnd_);
        window.removeEventListener('resize', this.refresh);
        if (this.mutationsObserver_) {
            this.mutationsObserver_.disconnect();
        }
        if (this.mutationEventsAdded_) {
            document.removeEventListener('DOMSubtreeModified', this.refresh);
        }
        this.mutationsObserver_ = null;
        this.mutationEventsAdded_ = false;
        this.connected_ = false;
    };
    /**
     * "Transitionend" event handler.
     *
     * @private
     * @param {TransitionEvent} event
     * @returns {void}
     */
    ResizeObserverController.prototype.onTransitionEnd_ = function (_a) {
        var _b = _a.propertyName, propertyName = _b === void 0 ? '' : _b;
        // Detect whether transition may affect dimensions of an element.
        var isReflowProperty = transitionKeys.some(function (key) {
            return !!~propertyName.indexOf(key);
        });
        if (isReflowProperty) {
            this.refresh();
        }
    };
    /**
     * Returns instance of the ResizeObserverController.
     *
     * @returns {ResizeObserverController}
     */
    ResizeObserverController.getInstance = function () {
        if (!this.instance_) {
            this.instance_ = new ResizeObserverController();
        }
        return this.instance_;
    };
    /**
     * Holds reference to the controller's instance.
     *
     * @private {ResizeObserverController}
     */
    ResizeObserverController.instance_ = null;
    return ResizeObserverController;
}());

/**
 * Defines non-writable/enumerable properties of the provided target object.
 *
 * @param {Object} target - Object for which to define properties.
 * @param {Object} props - Properties to be defined.
 * @returns {Object} Target object.
 */
var defineConfigurable = (function (target, props) {
    for (var _i = 0, _a = Object.keys(props); _i < _a.length; _i++) {
        var key = _a[_i];
        Object.defineProperty(target, key, {
            value: props[key],
            enumerable: false,
            writable: false,
            configurable: true
        });
    }
    return target;
});

/**
 * Returns the global object associated with provided element.
 *
 * @param {Object} target
 * @returns {Object}
 */
var getWindowOf = (function (target) {
    // Assume that the element is an instance of Node, which means that it
    // has the "ownerDocument" property from which we can retrieve a
    // corresponding global object.
    var ownerGlobal = target && target.ownerDocument && target.ownerDocument.defaultView;
    // Return the local global object if it's not possible extract one from
    // provided element.
    return ownerGlobal || global$1;
});

// Placeholder of an empty content rectangle.
var emptyRect = createRectInit(0, 0, 0, 0);
/**
 * Converts provided string to a number.
 *
 * @param {number|string} value
 * @returns {number}
 */
function toFloat(value) {
    return parseFloat(value) || 0;
}
/**
 * Extracts borders size from provided styles.
 *
 * @param {CSSStyleDeclaration} styles
 * @param {...string} positions - Borders positions (top, right, ...)
 * @returns {number}
 */
function getBordersSize(styles) {
    var positions = [];
    for (var _i = 1; _i < arguments.length; _i++) {
        positions[_i - 1] = arguments[_i];
    }
    return positions.reduce(function (size, position) {
        var value = styles['border-' + position + '-width'];
        return size + toFloat(value);
    }, 0);
}
/**
 * Extracts paddings sizes from provided styles.
 *
 * @param {CSSStyleDeclaration} styles
 * @returns {Object} Paddings box.
 */
function getPaddings(styles) {
    var positions = ['top', 'right', 'bottom', 'left'];
    var paddings = {};
    for (var _i = 0, positions_1 = positions; _i < positions_1.length; _i++) {
        var position = positions_1[_i];
        var value = styles['padding-' + position];
        paddings[position] = toFloat(value);
    }
    return paddings;
}
/**
 * Calculates content rectangle of provided SVG element.
 *
 * @param {SVGGraphicsElement} target - Element content rectangle of which needs
 *      to be calculated.
 * @returns {DOMRectInit}
 */
function getSVGContentRect(target) {
    var bbox = target.getBBox();
    return createRectInit(0, 0, bbox.width, bbox.height);
}
/**
 * Calculates content rectangle of provided HTMLElement.
 *
 * @param {HTMLElement} target - Element for which to calculate the content rectangle.
 * @returns {DOMRectInit}
 */
function getHTMLElementContentRect(target) {
    // Client width & height properties can't be
    // used exclusively as they provide rounded values.
    var clientWidth = target.clientWidth, clientHeight = target.clientHeight;
    // By this condition we can catch all non-replaced inline, hidden and
    // detached elements. Though elements with width & height properties less
    // than 0.5 will be discarded as well.
    //
    // Without it we would need to implement separate methods for each of
    // those cases and it's not possible to perform a precise and performance
    // effective test for hidden elements. E.g. even jQuery's ':visible' filter
    // gives wrong results for elements with width & height less than 0.5.
    if (!clientWidth && !clientHeight) {
        return emptyRect;
    }
    var styles = getWindowOf(target).getComputedStyle(target);
    var paddings = getPaddings(styles);
    var horizPad = paddings.left + paddings.right;
    var vertPad = paddings.top + paddings.bottom;
    // Computed styles of width & height are being used because they are the
    // only dimensions available to JS that contain non-rounded values. It could
    // be possible to utilize the getBoundingClientRect if only it's data wasn't
    // affected by CSS transformations let alone paddings, borders and scroll bars.
    var width = toFloat(styles.width), height = toFloat(styles.height);
    // Width & height include paddings and borders when the 'border-box' box
    // model is applied (except for IE).
    if (styles.boxSizing === 'border-box') {
        // Following conditions are required to handle Internet Explorer which
        // doesn't include paddings and borders to computed CSS dimensions.
        //
        // We can say that if CSS dimensions + paddings are equal to the "client"
        // properties then it's either IE, and thus we don't need to subtract
        // anything, or an element merely doesn't have paddings/borders styles.
        if (Math.round(width + horizPad) !== clientWidth) {
            width -= getBordersSize(styles, 'left', 'right') + horizPad;
        }
        if (Math.round(height + vertPad) !== clientHeight) {
            height -= getBordersSize(styles, 'top', 'bottom') + vertPad;
        }
    }
    // Following steps can't be applied to the document's root element as its
    // client[Width/Height] properties represent viewport area of the window.
    // Besides, it's as well not necessary as the <html> itself neither has
    // rendered scroll bars nor it can be clipped.
    if (!isDocumentElement(target)) {
        // In some browsers (only in Firefox, actually) CSS width & height
        // include scroll bars size which can be removed at this step as scroll
        // bars are the only difference between rounded dimensions + paddings
        // and "client" properties, though that is not always true in Chrome.
        var vertScrollbar = Math.round(width + horizPad) - clientWidth;
        var horizScrollbar = Math.round(height + vertPad) - clientHeight;
        // Chrome has a rather weird rounding of "client" properties.
        // E.g. for an element with content width of 314.2px it sometimes gives
        // the client width of 315px and for the width of 314.7px it may give
        // 314px. And it doesn't happen all the time. So just ignore this delta
        // as a non-relevant.
        if (Math.abs(vertScrollbar) !== 1) {
            width -= vertScrollbar;
        }
        if (Math.abs(horizScrollbar) !== 1) {
            height -= horizScrollbar;
        }
    }
    return createRectInit(paddings.left, paddings.top, width, height);
}
/**
 * Checks whether provided element is an instance of the SVGGraphicsElement.
 *
 * @param {Element} target - Element to be checked.
 * @returns {boolean}
 */
var isSVGGraphicsElement = (function () {
    // Some browsers, namely IE and Edge, don't have the SVGGraphicsElement
    // interface.
    if (typeof SVGGraphicsElement !== 'undefined') {
        return function (target) { return target instanceof getWindowOf(target).SVGGraphicsElement; };
    }
    // If it's so, then check that element is at least an instance of the
    // SVGElement and that it has the "getBBox" method.
    // eslint-disable-next-line no-extra-parens
    return function (target) { return (target instanceof getWindowOf(target).SVGElement &&
        typeof target.getBBox === 'function'); };
})();
/**
 * Checks whether provided element is a document element (<html>).
 *
 * @param {Element} target - Element to be checked.
 * @returns {boolean}
 */
function isDocumentElement(target) {
    return target === getWindowOf(target).document.documentElement;
}
/**
 * Calculates an appropriate content rectangle for provided html or svg element.
 *
 * @param {Element} target - Element content rectangle of which needs to be calculated.
 * @returns {DOMRectInit}
 */
function getContentRect(target) {
    if (!isBrowser) {
        return emptyRect;
    }
    if (isSVGGraphicsElement(target)) {
        return getSVGContentRect(target);
    }
    return getHTMLElementContentRect(target);
}
/**
 * Creates rectangle with an interface of the DOMRectReadOnly.
 * Spec: https://drafts.fxtf.org/geometry/#domrectreadonly
 *
 * @param {DOMRectInit} rectInit - Object with rectangle's x/y coordinates and dimensions.
 * @returns {DOMRectReadOnly}
 */
function createReadOnlyRect(_a) {
    var x = _a.x, y = _a.y, width = _a.width, height = _a.height;
    // If DOMRectReadOnly is available use it as a prototype for the rectangle.
    var Constr = typeof DOMRectReadOnly !== 'undefined' ? DOMRectReadOnly : Object;
    var rect = Object.create(Constr.prototype);
    // Rectangle's properties are not writable and non-enumerable.
    defineConfigurable(rect, {
        x: x, y: y, width: width, height: height,
        top: y,
        right: x + width,
        bottom: height + y,
        left: x
    });
    return rect;
}
/**
 * Creates DOMRectInit object based on the provided dimensions and the x/y coordinates.
 * Spec: https://drafts.fxtf.org/geometry/#dictdef-domrectinit
 *
 * @param {number} x - X coordinate.
 * @param {number} y - Y coordinate.
 * @param {number} width - Rectangle's width.
 * @param {number} height - Rectangle's height.
 * @returns {DOMRectInit}
 */
function createRectInit(x, y, width, height) {
    return { x: x, y: y, width: width, height: height };
}

/**
 * Class that is responsible for computations of the content rectangle of
 * provided DOM element and for keeping track of it's changes.
 */
var ResizeObservation = /** @class */ (function () {
    /**
     * Creates an instance of ResizeObservation.
     *
     * @param {Element} target - Element to be observed.
     */
    function ResizeObservation(target) {
        /**
         * Broadcasted width of content rectangle.
         *
         * @type {number}
         */
        this.broadcastWidth = 0;
        /**
         * Broadcasted height of content rectangle.
         *
         * @type {number}
         */
        this.broadcastHeight = 0;
        /**
         * Reference to the last observed content rectangle.
         *
         * @private {DOMRectInit}
         */
        this.contentRect_ = createRectInit(0, 0, 0, 0);
        this.target = target;
    }
    /**
     * Updates content rectangle and tells whether it's width or height properties
     * have changed since the last broadcast.
     *
     * @returns {boolean}
     */
    ResizeObservation.prototype.isActive = function () {
        var rect = getContentRect(this.target);
        this.contentRect_ = rect;
        return (rect.width !== this.broadcastWidth ||
            rect.height !== this.broadcastHeight);
    };
    /**
     * Updates 'broadcastWidth' and 'broadcastHeight' properties with a data
     * from the corresponding properties of the last observed content rectangle.
     *
     * @returns {DOMRectInit} Last observed content rectangle.
     */
    ResizeObservation.prototype.broadcastRect = function () {
        var rect = this.contentRect_;
        this.broadcastWidth = rect.width;
        this.broadcastHeight = rect.height;
        return rect;
    };
    return ResizeObservation;
}());

var ResizeObserverEntry = /** @class */ (function () {
    /**
     * Creates an instance of ResizeObserverEntry.
     *
     * @param {Element} target - Element that is being observed.
     * @param {DOMRectInit} rectInit - Data of the element's content rectangle.
     */
    function ResizeObserverEntry(target, rectInit) {
        var contentRect = createReadOnlyRect(rectInit);
        // According to the specification following properties are not writable
        // and are also not enumerable in the native implementation.
        //
        // Property accessors are not being used as they'd require to define a
        // private WeakMap storage which may cause memory leaks in browsers that
        // don't support this type of collections.
        defineConfigurable(this, { target: target, contentRect: contentRect });
    }
    return ResizeObserverEntry;
}());

var ResizeObserverSPI = /** @class */ (function () {
    /**
     * Creates a new instance of ResizeObserver.
     *
     * @param {ResizeObserverCallback} callback - Callback function that is invoked
     *      when one of the observed elements changes it's content dimensions.
     * @param {ResizeObserverController} controller - Controller instance which
     *      is responsible for the updates of observer.
     * @param {ResizeObserver} callbackCtx - Reference to the public
     *      ResizeObserver instance which will be passed to callback function.
     */
    function ResizeObserverSPI(callback, controller, callbackCtx) {
        /**
         * Collection of resize observations that have detected changes in dimensions
         * of elements.
         *
         * @private {Array<ResizeObservation>}
         */
        this.activeObservations_ = [];
        /**
         * Registry of the ResizeObservation instances.
         *
         * @private {Map<Element, ResizeObservation>}
         */
        this.observations_ = new MapShim();
        if (typeof callback !== 'function') {
            throw new TypeError('The callback provided as parameter 1 is not a function.');
        }
        this.callback_ = callback;
        this.controller_ = controller;
        this.callbackCtx_ = callbackCtx;
    }
    /**
     * Starts observing provided element.
     *
     * @param {Element} target - Element to be observed.
     * @returns {void}
     */
    ResizeObserverSPI.prototype.observe = function (target) {
        if (!arguments.length) {
            throw new TypeError('1 argument required, but only 0 present.');
        }
        // Do nothing if current environment doesn't have the Element interface.
        if (typeof Element === 'undefined' || !(Element instanceof Object)) {
            return;
        }
        if (!(target instanceof getWindowOf(target).Element)) {
            throw new TypeError('parameter 1 is not of type "Element".');
        }
        var observations = this.observations_;
        // Do nothing if element is already being observed.
        if (observations.has(target)) {
            return;
        }
        observations.set(target, new ResizeObservation(target));
        this.controller_.addObserver(this);
        // Force the update of observations.
        this.controller_.refresh();
    };
    /**
     * Stops observing provided element.
     *
     * @param {Element} target - Element to stop observing.
     * @returns {void}
     */
    ResizeObserverSPI.prototype.unobserve = function (target) {
        if (!arguments.length) {
            throw new TypeError('1 argument required, but only 0 present.');
        }
        // Do nothing if current environment doesn't have the Element interface.
        if (typeof Element === 'undefined' || !(Element instanceof Object)) {
            return;
        }
        if (!(target instanceof getWindowOf(target).Element)) {
            throw new TypeError('parameter 1 is not of type "Element".');
        }
        var observations = this.observations_;
        // Do nothing if element is not being observed.
        if (!observations.has(target)) {
            return;
        }
        observations.delete(target);
        if (!observations.size) {
            this.controller_.removeObserver(this);
        }
    };
    /**
     * Stops observing all elements.
     *
     * @returns {void}
     */
    ResizeObserverSPI.prototype.disconnect = function () {
        this.clearActive();
        this.observations_.clear();
        this.controller_.removeObserver(this);
    };
    /**
     * Collects observation instances the associated element of which has changed
     * it's content rectangle.
     *
     * @returns {void}
     */
    ResizeObserverSPI.prototype.gatherActive = function () {
        var _this = this;
        this.clearActive();
        this.observations_.forEach(function (observation) {
            if (observation.isActive()) {
                _this.activeObservations_.push(observation);
            }
        });
    };
    /**
     * Invokes initial callback function with a list of ResizeObserverEntry
     * instances collected from active resize observations.
     *
     * @returns {void}
     */
    ResizeObserverSPI.prototype.broadcastActive = function () {
        // Do nothing if observer doesn't have active observations.
        if (!this.hasActive()) {
            return;
        }
        var ctx = this.callbackCtx_;
        // Create ResizeObserverEntry instance for every active observation.
        var entries = this.activeObservations_.map(function (observation) {
            return new ResizeObserverEntry(observation.target, observation.broadcastRect());
        });
        this.callback_.call(ctx, entries, ctx);
        this.clearActive();
    };
    /**
     * Clears the collection of active observations.
     *
     * @returns {void}
     */
    ResizeObserverSPI.prototype.clearActive = function () {
        this.activeObservations_.splice(0);
    };
    /**
     * Tells whether observer has active observations.
     *
     * @returns {boolean}
     */
    ResizeObserverSPI.prototype.hasActive = function () {
        return this.activeObservations_.length > 0;
    };
    return ResizeObserverSPI;
}());

// Registry of internal observers. If WeakMap is not available use current shim
// for the Map collection as it has all required methods and because WeakMap
// can't be fully polyfilled anyway.
var observers = typeof WeakMap !== 'undefined' ? new WeakMap() : new MapShim();
/**
 * ResizeObserver API. Encapsulates the ResizeObserver SPI implementation
 * exposing only those methods and properties that are defined in the spec.
 */
var ResizeObserver = /** @class */ (function () {
    /**
     * Creates a new instance of ResizeObserver.
     *
     * @param {ResizeObserverCallback} callback - Callback that is invoked when
     *      dimensions of the observed elements change.
     */
    function ResizeObserver(callback) {
        if (!(this instanceof ResizeObserver)) {
            throw new TypeError('Cannot call a class as a function.');
        }
        if (!arguments.length) {
            throw new TypeError('1 argument required, but only 0 present.');
        }
        var controller = ResizeObserverController.getInstance();
        var observer = new ResizeObserverSPI(callback, controller, this);
        observers.set(this, observer);
    }
    return ResizeObserver;
}());
// Expose public methods of ResizeObserver.
[
    'observe',
    'unobserve',
    'disconnect'
].forEach(function (method) {
    ResizeObserver.prototype[method] = function () {
        var _a;
        return (_a = observers.get(this))[method].apply(_a, arguments);
    };
});

var index = (function () {
    // Export existing implementation if available.
    if (typeof global$1.ResizeObserver !== 'undefined') {
        return global$1.ResizeObserver;
    }
    return ResizeObserver;
})();

/* harmony default export */ __webpack_exports__["default"] = (index);

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./../../webpack/buildin/global.js */ "./node_modules/webpack/buildin/global.js")))

/***/ }),

/***/ "./node_modules/slick-carousel/slick/ajax-loader.gif":
/*!***********************************************************!*\
  !*** ./node_modules/slick-carousel/slick/ajax-loader.gif ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = "../images/ajax-loader.gif";

/***/ }),

/***/ "./node_modules/slick-carousel/slick/fonts/slick.eot":
/*!***********************************************************!*\
  !*** ./node_modules/slick-carousel/slick/fonts/slick.eot ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = "data:application/vnd.ms-fontobject;base64,AAgAAGQHAAABAAIAAAAAAAIABQkAAAAAAAABAJABAAAAAExQAQAAgCAAAAAAAAAAAAAAAAEAAAAAAAAATxDE8AAAAAAAAAAAAAAAAAAAAAAAAAoAcwBsAGkAYwBrAAAADgBSAGUAZwB1AGwAYQByAAAAFgBWAGUAcgBzAGkAbwBuACAAMQAuADAAAAAKAHMAbABpAGMAawAAAAAAAAEAAAANAIAAAwBQRkZUTW3RyK8AAAdIAAAAHEdERUYANAAGAAAHKAAAACBPUy8yT/b9sgAAAVgAAABWY21hcCIPRb0AAAHIAAABYmdhc3D//wADAAAHIAAAAAhnbHlmP5u2YAAAAzwAAAIsaGVhZAABMfsAAADcAAAANmhoZWED5QIFAAABFAAAACRobXR4BkoASgAAAbAAAAAWbG9jYQD2AaIAAAMsAAAAEG1heHAASwBHAAABOAAAACBuYW1lBSeBwgAABWgAAAFucG9zdC+zMgMAAAbYAAAARQABAAAAAQAA8MQQT18PPPUACwIAAAAAAM9xeH8AAAAAz3F4fwAlACUB2wHbAAAACAACAAAAAAAAAAEAAAHbAAAALgIAAAAAAAHbAAEAAAAAAAAAAAAAAAAAAAAEAAEAAAAHAEQAAgAAAAAAAgAAAAEAAQAAAEAAAAAAAAAAAQIAAZAABQAIAUwBZgAAAEcBTAFmAAAA9QAZAIQAAAIABQkAAAAAAACAAAABAAAAIAAAAAAAAAAAUGZFZABAAGEhkgHg/+AALgHb/9sAAAABAAAAAAAAAgAAAAAAAAACAAAAAgAAJQAlACUAJQAAAAAAAwAAAAMAAAAcAAEAAAAAAFwAAwABAAAAHAAEAEAAAAAMAAgAAgAEAAAAYSAiIZAhkv//AAAAAABhICIhkCGS//8AAP+l3+PedN5xAAEAAAAAAAAAAAAAAAAAAAEGAAABAAAAAAAAAAECAAAAAgAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABGAIwAsAEWAAIAJQAlAdsB2wAYACwAAD8BNjQvASYjIg8BBhUUHwEHBhUUHwEWMzI2FAcGBwYiJyYnJjQ3Njc2MhcWF/GCBgaCBQcIBR0GBldXBgYdBQgH7x0eMjB8MDIeHR0eMjB8MDIecYIGDgaCBQUeBQcJBFhYBAkHBR4F0nwwMh4dHR4yMHwwMh4dHR4yAAAAAgAlACUB2wHbABgALAAAJTc2NTQvATc2NTQvASYjIg8BBhQfARYzMjYUBwYHBiInJicmNDc2NzYyFxYXASgdBgZXVwYGHQUIBwWCBgaCBQcIuB0eMjB8MDIeHR0eMjB8MDIecR4FBwkEWFgECQcFHgUFggYOBoIF0nwwMh4dHR4yMHwwMh4dHR4yAAABACUAJQHbAdsAEwAAABQHBgcGIicmJyY0NzY3NjIXFhcB2x0eMjB8MDIeHR0eMjB8MDIeAT58MDIeHR0eMjB8MDIeHR0eMgABACUAJQHbAdsAQwAAARUUBisBIicmPwEmIyIHBgcGBwYUFxYXFhcWMzI3Njc2MzIfARYVFAcGBwYjIicmJyYnJjQ3Njc2NzYzMhcWFzc2FxYB2woIgAsGBQkoKjodHBwSFAwLCwwUEhwcHSIeIBMGAQQDJwMCISspNC8mLBobFBERFBsaLCYvKicpHSUIDAsBt4AICgsLCScnCwwUEhwcOhwcEhQMCw8OHAMDJwMDAgQnFBQRFBsaLCZeJiwaGxQRDxEcJQgEBgAAAAAAAAwAlgABAAAAAAABAAUADAABAAAAAAACAAcAIgABAAAAAAADACEAbgABAAAAAAAEAAUAnAABAAAAAAAFAAsAugABAAAAAAAGAAUA0gADAAEECQABAAoAAAADAAEECQACAA4AEgADAAEECQADAEIAKgADAAEECQAEAAoAkAADAAEECQAFABYAogADAAEECQAGAAoAxgBzAGwAaQBjAGsAAHNsaWNrAABSAGUAZwB1AGwAYQByAABSZWd1bGFyAABGAG8AbgB0AEYAbwByAGcAZQAgADIALgAwACAAOgAgAHMAbABpAGMAawAgADoAIAAxADQALQA0AC0AMgAwADEANAAARm9udEZvcmdlIDIuMCA6IHNsaWNrIDogMTQtNC0yMDE0AABzAGwAaQBjAGsAAHNsaWNrAABWAGUAcgBzAGkAbwBuACAAMQAuADAAAFZlcnNpb24gMS4wAABzAGwAaQBjAGsAAHNsaWNrAAAAAAIAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAABwAAAAEAAgECAQMAhwBECmFycm93cmlnaHQJYXJyb3dsZWZ0AAAAAAAAAf//AAIAAQAAAA4AAAAYAAAAAAACAAEAAwAGAAEABAAAAAIAAAAAAAEAAAAAzu7XsAAAAADPcXh/AAAAAM9xeH8="

/***/ }),

/***/ "./node_modules/slick-carousel/slick/fonts/slick.svg":
/*!***********************************************************!*\
  !*** ./node_modules/slick-carousel/slick/fonts/slick.svg ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = "data:image/svg+xml;base64,bW9kdWxlLmV4cG9ydHMgPSAiLi4vaW1hZ2VzL3NsaWNrLnN2ZyI7"

/***/ }),

/***/ "./node_modules/slick-carousel/slick/fonts/slick.ttf":
/*!***********************************************************!*\
  !*** ./node_modules/slick-carousel/slick/fonts/slick.ttf ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = "data:font/ttf;base64,AAEAAAANAIAAAwBQRkZUTW3RyK8AAAdIAAAAHEdERUYANAAGAAAHKAAAACBPUy8yT/b9sgAAAVgAAABWY21hcCIPRb0AAAHIAAABYmdhc3D//wADAAAHIAAAAAhnbHlmP5u2YAAAAzwAAAIsaGVhZAABMfsAAADcAAAANmhoZWED5QIFAAABFAAAACRobXR4BkoASgAAAbAAAAAWbG9jYQD2AaIAAAMsAAAAEG1heHAASwBHAAABOAAAACBuYW1lBSeBwgAABWgAAAFucG9zdC+zMgMAAAbYAAAARQABAAAAAQAA8MQQT18PPPUACwIAAAAAAM9xeH8AAAAAz3F4fwAlACUB2wHbAAAACAACAAAAAAAAAAEAAAHbAAAALgIAAAAAAAHbAAEAAAAAAAAAAAAAAAAAAAAEAAEAAAAHAEQAAgAAAAAAAgAAAAEAAQAAAEAAAAAAAAAAAQIAAZAABQAIAUwBZgAAAEcBTAFmAAAA9QAZAIQAAAIABQkAAAAAAACAAAABAAAAIAAAAAAAAAAAUGZFZABAAGEhkgHg/+AALgHb/9sAAAABAAAAAAAAAgAAAAAAAAACAAAAAgAAJQAlACUAJQAAAAAAAwAAAAMAAAAcAAEAAAAAAFwAAwABAAAAHAAEAEAAAAAMAAgAAgAEAAAAYSAiIZAhkv//AAAAAABhICIhkCGS//8AAP+l3+PedN5xAAEAAAAAAAAAAAAAAAAAAAEGAAABAAAAAAAAAAECAAAAAgAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABGAIwAsAEWAAIAJQAlAdsB2wAYACwAAD8BNjQvASYjIg8BBhUUHwEHBhUUHwEWMzI2FAcGBwYiJyYnJjQ3Njc2MhcWF/GCBgaCBQcIBR0GBldXBgYdBQgH7x0eMjB8MDIeHR0eMjB8MDIecYIGDgaCBQUeBQcJBFhYBAkHBR4F0nwwMh4dHR4yMHwwMh4dHR4yAAAAAgAlACUB2wHbABgALAAAJTc2NTQvATc2NTQvASYjIg8BBhQfARYzMjYUBwYHBiInJicmNDc2NzYyFxYXASgdBgZXVwYGHQUIBwWCBgaCBQcIuB0eMjB8MDIeHR0eMjB8MDIecR4FBwkEWFgECQcFHgUFggYOBoIF0nwwMh4dHR4yMHwwMh4dHR4yAAABACUAJQHbAdsAEwAAABQHBgcGIicmJyY0NzY3NjIXFhcB2x0eMjB8MDIeHR0eMjB8MDIeAT58MDIeHR0eMjB8MDIeHR0eMgABACUAJQHbAdsAQwAAARUUBisBIicmPwEmIyIHBgcGBwYUFxYXFhcWMzI3Njc2MzIfARYVFAcGBwYjIicmJyYnJjQ3Njc2NzYzMhcWFzc2FxYB2woIgAsGBQkoKjodHBwSFAwLCwwUEhwcHSIeIBMGAQQDJwMCISspNC8mLBobFBERFBsaLCYvKicpHSUIDAsBt4AICgsLCScnCwwUEhwcOhwcEhQMCw8OHAMDJwMDAgQnFBQRFBsaLCZeJiwaGxQRDxEcJQgEBgAAAAAAAAwAlgABAAAAAAABAAUADAABAAAAAAACAAcAIgABAAAAAAADACEAbgABAAAAAAAEAAUAnAABAAAAAAAFAAsAugABAAAAAAAGAAUA0gADAAEECQABAAoAAAADAAEECQACAA4AEgADAAEECQADAEIAKgADAAEECQAEAAoAkAADAAEECQAFABYAogADAAEECQAGAAoAxgBzAGwAaQBjAGsAAHNsaWNrAABSAGUAZwB1AGwAYQByAABSZWd1bGFyAABGAG8AbgB0AEYAbwByAGcAZQAgADIALgAwACAAOgAgAHMAbABpAGMAawAgADoAIAAxADQALQA0AC0AMgAwADEANAAARm9udEZvcmdlIDIuMCA6IHNsaWNrIDogMTQtNC0yMDE0AABzAGwAaQBjAGsAAHNsaWNrAABWAGUAcgBzAGkAbwBuACAAMQAuADAAAFZlcnNpb24gMS4wAABzAGwAaQBjAGsAAHNsaWNrAAAAAAIAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAABwAAAAEAAgECAQMAhwBECmFycm93cmlnaHQJYXJyb3dsZWZ0AAAAAAAAAf//AAIAAQAAAA4AAAAYAAAAAAACAAEAAwAGAAEABAAAAAIAAAAAAAEAAAAAzu7XsAAAAADPcXh/AAAAAM9xeH8="

/***/ }),

/***/ "./node_modules/slick-carousel/slick/fonts/slick.woff":
/*!************************************************************!*\
  !*** ./node_modules/slick-carousel/slick/fonts/slick.woff ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = "data:application/font-woff;base64,d09GRk9UVE8AAAVkAAsAAAAAB1wAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAABDRkYgAAABCAAAAi4AAAKbH/pWDkZGVE0AAAM4AAAAGgAAABxt0civR0RFRgAAA1QAAAAcAAAAIAAyAARPUy8yAAADcAAAAFIAAABgUBj/rmNtYXAAAAPEAAAAUAAAAWIiC0SwaGVhZAAABBQAAAAuAAAANgABMftoaGVhAAAERAAAABwAAAAkA+UCA2htdHgAAARgAAAADgAAAA4ESgBKbWF4cAAABHAAAAAGAAAABgAFUABuYW1lAAAEeAAAANwAAAFuBSeBwnBvc3QAAAVUAAAAEAAAACAAAwABeJw9ks9vEkEUx2cpWyeUoFYgNkHi2Wt7N3rVm3cTs3UVLC4LxIWEQvi1P3i7O1tYLJDAmlgKGEhQrsajf0j7J3jYTXrQWUrMJG+++b55n5e8NwwKBhHDMLv5kxT3ATEBxKBn3qOAl9zxHgb1MAPhHQgHkyF08Gr/L8B/Eb6zWnmCJ7AJVLubQOheArXvJ1A4EXi6j4I+Zg9F0QFKvsnlBCmXeve+sFEnb/nCptdtQ4QYhVFRAT1HrF8UQK/RL/SbmUbclsvGVFXRZKDHUE38cc4qpkbAAsuwiImvro+ufcfaOIQ6szlrmjRJDaKZKnbjN3GWKIbiIzRFUfCffuxxKOL+3LDlDVvx2TdxN84qZEsnhNBa6pgm2dAsnzbLsETdsmRFxUeHV4e+I2/ptN8TyqV8T3Dt29t7EYOuajVIw2y1Wy3M86w0zg/Fz2IvawmQAUHOVrPVfLkoScVynsqsTG0MGUs4z55nh3mnOJa+li+rl9WpPIcFfDubDeaDC+fLBdYN3QADzLauGfj4B6sZmq6CCpqmtSvF0qlUl2qf5AJIUCSlTqlb7lUG+LRfGzZGzZEyBgccMu6MuqPecNDvD4Y9Kjtj4gD+DsvKVMTcMdtqtZtmkzQstQvYje7Syep0PDSAhSOeHYXYWThEF//A/0YvYV1fSQtpKU5STtrhbQ444OtpKSWJIg3pOg8cBs7maTY1EZf07aq+hjWs7IWzdCYTGhb2CtZ47x+Uhx28AAB4nGNgYGBkAIJz765vANHnCyvqYTQAWnkHswAAeJxjYGRgYOADYgkGEGBiYARCFjAG8RgABHYAN3icY2BmYmCcwMDKwMHow5jGwMDgDqW/MkgytDAwMDGwcjKAQQMDAyOQUmCAgoA01xQGB4ZExUmMD/4/YNBjvP3/NgNEDQPjbbBKBQZGADfLDgsAAHicY2BgYGaAYBkGRgYQiAHyGMF8FgYHIM3DwMHABGQzMCQqKClOUJz0/z9YHRLv/+L7D+8V3cuHmgAHjGwM6ELUByxUMIOZCmbgAAA5LQ8XeJxjYGRgYABiO68w73h+m68M3EwMIHC+sKIeTqsyqDLeZrwN5HIwgKUB/aYJUgAAeJxjYGRgYLzNwMCgx8QAAkA2IwMqYAIAMGIB7QIAAAACAAAlACUAJQAlAAAAAFAAAAUAAHicbY49asNAEIU/2ZJDfkiRIvXapUFCEqpcptABUrg3ZhEiQoKVfY9UqVLlGDlADpAT5e16IUWysMz3hjfzBrjjjQT/EjKpCy+4YhN5yZoxcirPe+SMWz4jr6S+5UzSa3VuwpTnBfc8RF7yxDZyKs9r5IxHPiKv1P9iZqDnyAvMQ39UecbScVb/gJO03Xk4CFom3XYK1clhMdQUlKo7/d9NF13RkIdfy+MV7TSe2sl11tRFaXYmJKpWTd7kdVnJ8veevZKc+n3I93t9Jnvr5n4aTVWU/0z9AI2qMkV4nGNgZkAGjAxoAAAAjgAF"

/***/ }),

/***/ "./node_modules/slick-carousel/slick/slick-theme.css":
/*!***********************************************************!*\
  !*** ./node_modules/slick-carousel/slick/slick-theme.css ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(/*! !../../css-loader/dist/cjs.js!./slick-theme.css */ "./node_modules/css-loader/dist/cjs.js!./node_modules/slick-carousel/slick/slick-theme.css");
if(typeof content === 'string') content = [[module.i, content, '']];
// Prepare cssTransformation
var transform;

var options = {}
options.transform = transform
// add the styles to the DOM
var update = __webpack_require__(/*! ../../style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {}

/***/ }),

/***/ "./node_modules/slick-carousel/slick/slick.css":
/*!*****************************************************!*\
  !*** ./node_modules/slick-carousel/slick/slick.css ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(/*! !../../css-loader/dist/cjs.js!./slick.css */ "./node_modules/css-loader/dist/cjs.js!./node_modules/slick-carousel/slick/slick.css");
if(typeof content === 'string') content = [[module.i, content, '']];
// Prepare cssTransformation
var transform;

var options = {}
options.transform = transform
// add the styles to the DOM
var update = __webpack_require__(/*! ../../style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {}

/***/ }),

/***/ "./node_modules/string-convert/camel2hyphen.js":
/*!*****************************************************!*\
  !*** ./node_modules/string-convert/camel2hyphen.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

var camel2hyphen = function (str) {
  return str
          .replace(/[A-Z]/g, function (match) {
            return '-' + match.toLowerCase();
          })
          .toLowerCase();
};

module.exports = camel2hyphen;

/***/ }),

/***/ "./node_modules/style-loader/lib/addStyles.js":
/*!****************************************************!*\
  !*** ./node_modules/style-loader/lib/addStyles.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/

var stylesInDom = {};

var	memoize = function (fn) {
	var memo;

	return function () {
		if (typeof memo === "undefined") memo = fn.apply(this, arguments);
		return memo;
	};
};

var isOldIE = memoize(function () {
	// Test for IE <= 9 as proposed by Browserhacks
	// @see http://browserhacks.com/#hack-e71d8692f65334173fee715c222cb805
	// Tests for existence of standard globals is to allow style-loader
	// to operate correctly into non-standard environments
	// @see https://github.com/webpack-contrib/style-loader/issues/177
	return window && document && document.all && !window.atob;
});

var getElement = (function (fn) {
	var memo = {};

	return function(selector) {
		if (typeof memo[selector] === "undefined") {
			memo[selector] = fn.call(this, selector);
		}

		return memo[selector]
	};
})(function (target) {
	return document.querySelector(target)
});

var singleton = null;
var	singletonCounter = 0;
var	stylesInsertedAtTop = [];

var	fixUrls = __webpack_require__(/*! ./urls */ "./node_modules/style-loader/lib/urls.js");

module.exports = function(list, options) {
	if (typeof DEBUG !== "undefined" && DEBUG) {
		if (typeof document !== "object") throw new Error("The style-loader cannot be used in a non-browser environment");
	}

	options = options || {};

	options.attrs = typeof options.attrs === "object" ? options.attrs : {};

	// Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
	// tags it will allow on a page
	if (!options.singleton) options.singleton = isOldIE();

	// By default, add <style> tags to the <head> element
	if (!options.insertInto) options.insertInto = "head";

	// By default, add <style> tags to the bottom of the target
	if (!options.insertAt) options.insertAt = "bottom";

	var styles = listToStyles(list, options);

	addStylesToDom(styles, options);

	return function update (newList) {
		var mayRemove = [];

		for (var i = 0; i < styles.length; i++) {
			var item = styles[i];
			var domStyle = stylesInDom[item.id];

			domStyle.refs--;
			mayRemove.push(domStyle);
		}

		if(newList) {
			var newStyles = listToStyles(newList, options);
			addStylesToDom(newStyles, options);
		}

		for (var i = 0; i < mayRemove.length; i++) {
			var domStyle = mayRemove[i];

			if(domStyle.refs === 0) {
				for (var j = 0; j < domStyle.parts.length; j++) domStyle.parts[j]();

				delete stylesInDom[domStyle.id];
			}
		}
	};
};

function addStylesToDom (styles, options) {
	for (var i = 0; i < styles.length; i++) {
		var item = styles[i];
		var domStyle = stylesInDom[item.id];

		if(domStyle) {
			domStyle.refs++;

			for(var j = 0; j < domStyle.parts.length; j++) {
				domStyle.parts[j](item.parts[j]);
			}

			for(; j < item.parts.length; j++) {
				domStyle.parts.push(addStyle(item.parts[j], options));
			}
		} else {
			var parts = [];

			for(var j = 0; j < item.parts.length; j++) {
				parts.push(addStyle(item.parts[j], options));
			}

			stylesInDom[item.id] = {id: item.id, refs: 1, parts: parts};
		}
	}
}

function listToStyles (list, options) {
	var styles = [];
	var newStyles = {};

	for (var i = 0; i < list.length; i++) {
		var item = list[i];
		var id = options.base ? item[0] + options.base : item[0];
		var css = item[1];
		var media = item[2];
		var sourceMap = item[3];
		var part = {css: css, media: media, sourceMap: sourceMap};

		if(!newStyles[id]) styles.push(newStyles[id] = {id: id, parts: [part]});
		else newStyles[id].parts.push(part);
	}

	return styles;
}

function insertStyleElement (options, style) {
	var target = getElement(options.insertInto)

	if (!target) {
		throw new Error("Couldn't find a style target. This probably means that the value for the 'insertInto' parameter is invalid.");
	}

	var lastStyleElementInsertedAtTop = stylesInsertedAtTop[stylesInsertedAtTop.length - 1];

	if (options.insertAt === "top") {
		if (!lastStyleElementInsertedAtTop) {
			target.insertBefore(style, target.firstChild);
		} else if (lastStyleElementInsertedAtTop.nextSibling) {
			target.insertBefore(style, lastStyleElementInsertedAtTop.nextSibling);
		} else {
			target.appendChild(style);
		}
		stylesInsertedAtTop.push(style);
	} else if (options.insertAt === "bottom") {
		target.appendChild(style);
	} else {
		throw new Error("Invalid value for parameter 'insertAt'. Must be 'top' or 'bottom'.");
	}
}

function removeStyleElement (style) {
	if (style.parentNode === null) return false;
	style.parentNode.removeChild(style);

	var idx = stylesInsertedAtTop.indexOf(style);
	if(idx >= 0) {
		stylesInsertedAtTop.splice(idx, 1);
	}
}

function createStyleElement (options) {
	var style = document.createElement("style");

	options.attrs.type = "text/css";

	addAttrs(style, options.attrs);
	insertStyleElement(options, style);

	return style;
}

function createLinkElement (options) {
	var link = document.createElement("link");

	options.attrs.type = "text/css";
	options.attrs.rel = "stylesheet";

	addAttrs(link, options.attrs);
	insertStyleElement(options, link);

	return link;
}

function addAttrs (el, attrs) {
	Object.keys(attrs).forEach(function (key) {
		el.setAttribute(key, attrs[key]);
	});
}

function addStyle (obj, options) {
	var style, update, remove, result;

	// If a transform function was defined, run it on the css
	if (options.transform && obj.css) {
	    result = options.transform(obj.css);

	    if (result) {
	    	// If transform returns a value, use that instead of the original css.
	    	// This allows running runtime transformations on the css.
	    	obj.css = result;
	    } else {
	    	// If the transform function returns a falsy value, don't add this css.
	    	// This allows conditional loading of css
	    	return function() {
	    		// noop
	    	};
	    }
	}

	if (options.singleton) {
		var styleIndex = singletonCounter++;

		style = singleton || (singleton = createStyleElement(options));

		update = applyToSingletonTag.bind(null, style, styleIndex, false);
		remove = applyToSingletonTag.bind(null, style, styleIndex, true);

	} else if (
		obj.sourceMap &&
		typeof URL === "function" &&
		typeof URL.createObjectURL === "function" &&
		typeof URL.revokeObjectURL === "function" &&
		typeof Blob === "function" &&
		typeof btoa === "function"
	) {
		style = createLinkElement(options);
		update = updateLink.bind(null, style, options);
		remove = function () {
			removeStyleElement(style);

			if(style.href) URL.revokeObjectURL(style.href);
		};
	} else {
		style = createStyleElement(options);
		update = applyToTag.bind(null, style);
		remove = function () {
			removeStyleElement(style);
		};
	}

	update(obj);

	return function updateStyle (newObj) {
		if (newObj) {
			if (
				newObj.css === obj.css &&
				newObj.media === obj.media &&
				newObj.sourceMap === obj.sourceMap
			) {
				return;
			}

			update(obj = newObj);
		} else {
			remove();
		}
	};
}

var replaceText = (function () {
	var textStore = [];

	return function (index, replacement) {
		textStore[index] = replacement;

		return textStore.filter(Boolean).join('\n');
	};
})();

function applyToSingletonTag (style, index, remove, obj) {
	var css = remove ? "" : obj.css;

	if (style.styleSheet) {
		style.styleSheet.cssText = replaceText(index, css);
	} else {
		var cssNode = document.createTextNode(css);
		var childNodes = style.childNodes;

		if (childNodes[index]) style.removeChild(childNodes[index]);

		if (childNodes.length) {
			style.insertBefore(cssNode, childNodes[index]);
		} else {
			style.appendChild(cssNode);
		}
	}
}

function applyToTag (style, obj) {
	var css = obj.css;
	var media = obj.media;

	if(media) {
		style.setAttribute("media", media)
	}

	if(style.styleSheet) {
		style.styleSheet.cssText = css;
	} else {
		while(style.firstChild) {
			style.removeChild(style.firstChild);
		}

		style.appendChild(document.createTextNode(css));
	}
}

function updateLink (link, options, obj) {
	var css = obj.css;
	var sourceMap = obj.sourceMap;

	/*
		If convertToAbsoluteUrls isn't defined, but sourcemaps are enabled
		and there is no publicPath defined then lets turn convertToAbsoluteUrls
		on by default.  Otherwise default to the convertToAbsoluteUrls option
		directly
	*/
	var autoFixUrls = options.convertToAbsoluteUrls === undefined && sourceMap;

	if (options.convertToAbsoluteUrls || autoFixUrls) {
		css = fixUrls(css);
	}

	if (sourceMap) {
		// http://stackoverflow.com/a/26603875
		css += "\n/*# sourceMappingURL=data:application/json;base64," + btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))) + " */";
	}

	var blob = new Blob([css], { type: "text/css" });

	var oldSrc = link.href;

	link.href = URL.createObjectURL(blob);

	if(oldSrc) URL.revokeObjectURL(oldSrc);
}


/***/ }),

/***/ "./node_modules/style-loader/lib/urls.js":
/*!***********************************************!*\
  !*** ./node_modules/style-loader/lib/urls.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports) {


/**
 * When source maps are enabled, `style-loader` uses a link element with a data-uri to
 * embed the css on the page. This breaks all relative urls because now they are relative to a
 * bundle instead of the current page.
 *
 * One solution is to only use full urls, but that may be impossible.
 *
 * Instead, this function "fixes" the relative urls to be absolute according to the current page location.
 *
 * A rudimentary test suite is located at `test/fixUrls.js` and can be run via the `npm test` command.
 *
 */

module.exports = function (css) {
  // get current location
  var location = typeof window !== "undefined" && window.location;

  if (!location) {
    throw new Error("fixUrls requires window.location");
  }

	// blank or null?
	if (!css || typeof css !== "string") {
	  return css;
  }

  var baseUrl = location.protocol + "//" + location.host;
  var currentDir = baseUrl + location.pathname.replace(/\/[^\/]*$/, "/");

	// convert each url(...)
	/*
	This regular expression is just a way to recursively match brackets within
	a string.

	 /url\s*\(  = Match on the word "url" with any whitespace after it and then a parens
	   (  = Start a capturing group
	     (?:  = Start a non-capturing group
	         [^)(]  = Match anything that isn't a parentheses
	         |  = OR
	         \(  = Match a start parentheses
	             (?:  = Start another non-capturing groups
	                 [^)(]+  = Match anything that isn't a parentheses
	                 |  = OR
	                 \(  = Match a start parentheses
	                     [^)(]*  = Match anything that isn't a parentheses
	                 \)  = Match a end parentheses
	             )  = End Group
              *\) = Match anything and then a close parens
          )  = Close non-capturing group
          *  = Match anything
       )  = Close capturing group
	 \)  = Match a close parens

	 /gi  = Get all matches, not the first.  Be case insensitive.
	 */
	var fixedCss = css.replace(/url\s*\(((?:[^)(]|\((?:[^)(]+|\([^)(]*\))*\))*)\)/gi, function(fullMatch, origUrl) {
		// strip quotes (if they exist)
		var unquotedOrigUrl = origUrl
			.trim()
			.replace(/^"(.*)"$/, function(o, $1){ return $1; })
			.replace(/^'(.*)'$/, function(o, $1){ return $1; });

		// already a full url? no change
		if (/^(#|data:|http:\/\/|https:\/\/|file:\/\/\/)/i.test(unquotedOrigUrl)) {
		  return fullMatch;
		}

		// convert the url to a full url
		var newUrl;

		if (unquotedOrigUrl.indexOf("//") === 0) {
		  	//TODO: should we add protocol?
			newUrl = unquotedOrigUrl;
		} else if (unquotedOrigUrl.indexOf("/") === 0) {
			// path should be relative to the base url
			newUrl = baseUrl + unquotedOrigUrl; // already starts with '/'
		} else {
			// path should be relative to current directory
			newUrl = currentDir + unquotedOrigUrl.replace(/^\.\//, ""); // Strip leading './'
		}

		// send back the fixed url(...)
		return "url(" + JSON.stringify(newUrl) + ")";
	});

	// send back the fixed css
	return fixedCss;
};


/***/ }),

/***/ "./node_modules/webpack/buildin/global.js":
/*!***********************************!*\
  !*** (webpack)/buildin/global.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports) {

var g;

// This works in non-strict mode
g = (function() {
	return this;
})();

try {
	// This works if eval is allowed (see CSP)
	g = g || new Function("return this")();
} catch (e) {
	// This works if the window reference is available
	if (typeof window === "object") g = window;
}

// g can still be undefined, but nothing to do about it...
// We return undefined, instead of nothing here, so it's
// easier to handle this case. if(!global) { ...}

module.exports = g;


/***/ }),

/***/ "lodash":
/*!*************************!*\
  !*** external "lodash" ***!
  \*************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = lodash;

/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = React;

/***/ }),

/***/ "react-dom":
/*!***************************!*\
  !*** external "ReactDOM" ***!
  \***************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = ReactDOM;

/***/ })

/******/ });
//# sourceMappingURL=blocks-editor.js.map