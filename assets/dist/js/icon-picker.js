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
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/src/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/src/index.js":
/*!*****************************!*\
  !*** ./assets/src/index.js ***!
  \*****************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _sortable_iconpicker__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./sortable-iconpicker */ "./assets/src/sortable-iconpicker/index.js");


/***/ }),

/***/ "./assets/src/sortable-iconpicker/index.js":
/*!*************************************************!*\
  !*** ./assets/src/sortable-iconpicker/index.js ***!
  \*************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);



/**
 * @link https://stackoverflow.com/a/46500027/5351316
 */
var _window = window,
    $ = _window.jQuery;

__webpack_require__(/*! @fonticonpicker/fonticonpicker */ "./node_modules/@fonticonpicker/fonticonpicker/dist/js/jquery.fonticonpicker.min.js")(jQuery);

var sn;

var socialNetwork = /*#__PURE__*/function () {
  function socialNetwork(instance) {
    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, socialNetwork);

    if (instance instanceof jQuery) {
      this.instance = instance;
      this.slug = this.instance.find('input.cn-brandicon');
      this.icon = this.instance.find('i[class^="cn-brandicon"]');
      this.backgroundColor = this.instance.find('input.cn-brandicon-background-color');
      this.hoverBackgroundColor = this.instance.find('input.cn-brandicon-hover-background-color');
      this.backgroundTransparent = this.instance.find('input.cn-brandicon-background-transparent');
      this.foregroundColor = this.instance.find('input.cn-brandicon-foreground-color');
      this.hoverForegroundColor = this.instance.find('input.cn-brandicon-hover-foreground-color');
    }
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(socialNetwork, [{
    key: "getBackgroundColor",
    value: function getBackgroundColor() {
      var iconColor = brandicons.color(this.getSlug());

      if (this.backgroundColor instanceof jQuery && this.backgroundColor.val()) {
        iconColor = this.backgroundColor.val();
      }

      return iconColor;
    }
  }, {
    key: "setBackgroundColor",
    value: function setBackgroundColor(value) {
      if (this.backgroundColor instanceof jQuery) {
        this.backgroundColor.val(value); // 'transparent' === value ? this.backgroundTransparent.val( '1' ) : this.backgroundTransparent.val( '0' );

        this.writeStyle();
      }
    }
  }, {
    key: "setBackgroundTransparent",
    value: function setBackgroundTransparent(value) {
      if (this.backgroundTransparent instanceof jQuery) {
        this.backgroundTransparent.val(value);
        this.writeStyle();
      }
    }
  }, {
    key: "isBackgroundTransparent",
    value: function isBackgroundTransparent() {
      if (this.backgroundTransparent instanceof jQuery) {
        return '1' === this.backgroundTransparent.val();
      }

      return false;
    }
  }, {
    key: "getForegroundColor",
    value: function getForegroundColor() {
      var iconColor = '#FFFFFF';

      if (this.foregroundColor instanceof jQuery && this.foregroundColor.val()) {
        iconColor = this.foregroundColor.val();
      }

      return iconColor;
    }
  }, {
    key: "setForegroundColor",
    value: function setForegroundColor(value) {
      if (this.foregroundColor instanceof jQuery) {
        this.foregroundColor.val(value);
        this.writeStyle();
      }
    }
  }, {
    key: "getHoverBackgroundColor",
    value: function getHoverBackgroundColor() {
      var iconColor = brandicons.color(this.getSlug());

      if (this.hoverBackgroundColor instanceof jQuery && this.hoverBackgroundColor.val()) {
        iconColor = this.hoverBackgroundColor.val();
      }

      return iconColor;
    }
  }, {
    key: "setHoverBackgroundColor",
    value: function setHoverBackgroundColor(value) {
      if (this.hoverBackgroundColor instanceof jQuery) {
        this.hoverBackgroundColor.val(value); // 'transparent' === value ? this.backgroundTransparent.val( '1' ) : this.backgroundTransparent.val( '0' );

        this.writeStyle();
      }
    }
  }, {
    key: "getHoverForegroundColor",
    value: function getHoverForegroundColor() {
      var iconColor = '#FFFFFF';

      if (this.hoverForegroundColor instanceof jQuery && this.hoverForegroundColor.val()) {
        iconColor = this.hoverForegroundColor.val();
      }

      return iconColor;
    }
  }, {
    key: "setHoverForegroundColor",
    value: function setHoverForegroundColor(value) {
      if (this.hoverForegroundColor instanceof jQuery) {
        this.hoverForegroundColor.val(value);
        this.writeStyle();
      }
    }
  }, {
    key: "setIcon",
    value: function setIcon(value) {
      if (this.icon instanceof jQuery) {
        this.setSlug(socialNetwork.classNameToSlug(value));
        this.icon.removeClass().addClass('cn-brandicon-size-24').addClass(value);
      }
    }
  }, {
    key: "getSlug",
    value: function getSlug() {
      if (this.slug instanceof jQuery) {
        return this.slug.val();
      }
    }
  }, {
    key: "setSlug",
    value: function setSlug(slug) {
      if (this.slug instanceof jQuery) {
        return this.slug.val(slug);
      }
    }
  }, {
    key: "getClassname",
    value: function getClassname() {
      if (this.slug instanceof jQuery) {
        return 'cn-brandicon-' + this.getSlug();
      }
    }
  }, {
    key: "writeStyle",
    value: function writeStyle() {
      var backgroundColor = this.getBackgroundColor();
      var backgroundHoverColor = this.getHoverBackgroundColor();
      var foregroundColor = this.getForegroundColor();
      var foregroundHoverColor = this.getHoverForegroundColor();

      if (this.isBackgroundTransparent()) {
        backgroundColor = 'transparent';
        backgroundHoverColor = 'transparent';
      }

      this.icon.attr('style', "--color: " + foregroundColor + '; background-color: ' + backgroundColor);
      /**
       * Since the hover color can not be set with an inline style, use the mouseenter/mouseleave events.
       *
       * Use CSS variable to in an inline style to set the hover colors.
       * @link https://stackoverflow.com/a/49618941/5351316
       */

      this.icon.mouseenter(function () {
        $(this).attr('style', '--color: ' + foregroundHoverColor + '; background-color: ' + backgroundHoverColor);
      }).mouseleave(function () {
        $(this).attr('style', "--color: " + foregroundColor + '; background-color: ' + backgroundColor);
      });
    }
  }], [{
    key: "classNameToSlug",
    value: function classNameToSlug(value) {
      return value.replace('cn-brandicon-', '');
    }
  }, {
    key: "slugToClassName",
    value: function slugToClassName(value) {
      return 'cn-brandicon-' + value;
    }
  }]);

  return socialNetwork;
}();

var brandicons = {
  icons: [],
  add: function add(item) {
    var color = 'rgb(0, 0, 0)';

    if (item.icon.attrs.length && 'fill' in item.icon.attrs[0]) {
      color = item.icon.attrs[0].fill;
    }

    this.icons[item.properties.name] = {
      color: color
    };
  },
  get: function get(slug) {
    if (slug in this.icons) {
      return this.icons[slug];
    }

    return false;
  },
  color: function color(slug) {
    var _color = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'rgb(0, 0, 0)';

    var icon = this.get(slug);

    if (false !== icon) {
      _color = icon.color;
    }

    return _color;
  }
}; // Init the font icon picker.

var e9_element = $('#e9_element').fontIconPicker({
  emptyIcon: false,
  theme: 'fip-darkgrey'
}).on('change', function () {
  var input = $(this);
  var value = input.val();

  if (sn instanceof socialNetwork) {
    // sn.setSlug( socialNetwork.classNameToSlug( value ) );
    sn.setIcon(value);
  }
});

var initModal = function initModal() {
  var modal = $('#cn-social-network-icon-settings-modal'); // initialize the dialog

  modal.dialog({
    title: 'Social Network Icons Settings',
    dialogClass: 'wp-dialog',
    autoOpen: false,
    draggable: false,
    width: 'auto',
    minHeight: 600,
    minWidth: 386,
    modal: true,
    resizable: false,
    closeOnEscape: true,
    position: {
      my: 'center',
      at: 'center',
      of: window
    },
    open: function open() {
      // close dialog by clicking the overlay behind it
      $('.ui-widget-overlay').bind('click', function () {
        $('#cn-social-network-icon-settings-modal').dialog('close');
      });
    },
    create: function create() {
      // style fix for WordPress admin
      $('.ui-dialog-titlebar-close').addClass('ui-button');
    }
  }); // Bind a button to open the dialog.

  $('.cn-fieldset-social-networks').on('click', 'a.cn-social-network-icon-setting-button', function (e) {
    e.preventDefault();
    sn = new socialNetwork($(this).parent()); // Set the icon to be selected in the font icon picker.

    e9_element.setIcon(sn.getClassname()); // Init the icon background color picker.

    var iconBackgroundColorPicker = $('#cn-icon-background-color').wpColorPicker({
      change: function change(event, ui) {
        // let hex = ui.color.toString();
        sn.setBackgroundColor(ui.color.toString());
      }
    }); // Init the icon background hover color.

    var iconHoverBackgroundColorPicker = $('#cn-icon-hover-background-color').wpColorPicker({
      change: function change(event, ui) {
        // let hex = ui.color.toString();
        sn.setHoverBackgroundColor(ui.color.toString());
      }
    }); // Set the transparent background checkbox state.

    if (sn.isBackgroundTransparent()) {
      $('#cn-icon-background-transparent').prop('checked', true);
    }
    /**
     * Bind event to set transparent color or background colors based on whether the checkbox is enabled or not.
     *
     * To prevent the change event from being attached more than once, remove it before adding it again
     * using a namespace.
     *
     * @link https://stackoverflow.com/a/1558382/5351316
     */


    $('#cn-icon-background-transparent').off('change.transparent').on('change.transparent', function () {
      var checkbox = $(this);

      if (checkbox.is(':checked')) {
        // sn.setBackgroundColor( 'transparent' );
        // sn.setHoverBackgroundColor( 'transparent' );
        sn.setBackgroundTransparent('1');
      } else {
        // sn.setBackgroundColor( iconBackgroundColorPicker.wpColorPicker( 'color' ) );
        // sn.setHoverBackgroundColor( iconHoverBackgroundColorPicker.wpColorPicker( 'color' ) );
        sn.setBackgroundTransparent('0');
      }
    }); // Init the icon foreground color picker.

    var iconForegroundColorPicker = $('#cn-icon-foreground-color').wpColorPicker({
      change: function change(event, ui) {
        // let hex = ui.color.toString();
        sn.setForegroundColor(ui.color.toString());
      }
    }); // Init the icon foreground hover color.

    var iconHoverForegroundColorPicker = $('#cn-icon-hover-foreground-color').wpColorPicker({
      change: function change(event, ui) {
        // let hex = ui.color.toString();
        sn.setHoverForegroundColor(ui.color.toString());
      }
    }); // Set the color pickers to the saved color values before the modal is opened.

    iconBackgroundColorPicker.wpColorPicker('color', sn.getBackgroundColor());
    iconHoverBackgroundColorPicker.wpColorPicker('color', sn.getHoverBackgroundColor());
    iconForegroundColorPicker.wpColorPicker('color', sn.getForegroundColor());
    iconHoverForegroundColorPicker.wpColorPicker('color', sn.getHoverForegroundColor()); // Open the icon settings modal.

    modal.dialog('open');
  });
};

$(document).ready(function () {
  // Get the JSON file
  $.ajax({
    url: cnBase.url + 'assets/vendor/icomoon-brands/selection.json',
    type: 'GET',
    dataType: 'json'
  }).done(function (response) {
    // Get the class prefix
    var classPrefix = response.preferences.fontPref.prefix,
        icomoon_json_icons = [],
        icomoon_json_search = []; // For each icon

    $.each(response.icons, function (i, v) {
      brandicons.add(v); // Set the source

      icomoon_json_icons.push(classPrefix + v.properties.name); // Create and set the search source

      if (v.icon && v.icon.tags && v.icon.tags.length) {
        icomoon_json_search.push(v.properties.name + ' ' + v.icon.tags.join(' '));
      } else {
        icomoon_json_search.push(v.properties.name);
      }
    }); // console.log( icomoon_json_icons );
    // Set new fonts on fontIconPicker

    e9_element.setIcons(icomoon_json_icons, icomoon_json_search); // Init the modal.

    initModal();
  }).fail(function () {
    console.log('error fetching selection.json');
  });
});

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

/***/ "./node_modules/@fonticonpicker/fonticonpicker/dist/js/jquery.fonticonpicker.min.js":
/*!******************************************************************************************!*\
  !*** ./node_modules/@fonticonpicker/fonticonpicker/dist/js/jquery.fonticonpicker.min.js ***!
  \******************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/**
 *  jQuery fontIconPicker - 3.1.1
 *
 *  An icon picker built on top of font icons and jQuery
 *
 *  http://codeb.it/fontIconPicker
 *
 *  @author Alessandro Benoit & Swashata Ghosh
 *  @license MIT License
 *
 * {@link https://github.com/micc83/fontIconPicker}
 */
!function(t,e){ true?module.exports=e(__webpack_require__(/*! jquery */ "jquery")):undefined}(this,function(t){"use strict";function e(t){return(e="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function i(t){return function(t){if(Array.isArray(t)){for(var e=0,i=new Array(t.length);e<t.length;e++)i[e]=t[e];return i}}(t)||function(t){if(Symbol.iterator in Object(t)||"[object Arguments]"===Object.prototype.toString.call(t))return Array.from(t)}(t)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance")}()}var s={theme:"fip-grey",source:!1,emptyIcon:!0,emptyIconValue:"",autoClose:!0,iconsPerPage:20,hasSearch:!0,searchSource:!1,appendTo:"self",useAttribute:!1,attributeName:"data-icon",convertToHex:!0,allCategoryText:"From all categories",unCategorizedText:"Uncategorized",iconGenerator:null,windowDebounceDelay:150,searchPlaceholder:"Search Icons"},n=t=t&&t.hasOwnProperty("default")?t.default:t,o=0;function r(t,e){this.element=n(t),this.settings=n.extend({},s,e),this.settings.emptyIcon&&this.settings.iconsPerPage--,this.iconPicker=n("<div/>",{class:"icons-selector",style:"position: relative",html:this._getPickerTemplate(),attr:{"data-fip-origin":this.element.attr("id")}}),this.iconContainer=this.iconPicker.find(".fip-icons-container"),this.searchIcon=this.iconPicker.find(".selector-search i"),this.selectorPopup=this.iconPicker.find(".selector-popup-wrap"),this.selectorButton=this.iconPicker.find(".selector-button"),this.iconsSearched=[],this.isSearch=!1,this.totalPage=1,this.currentPage=1,this.currentIcon=!1,this.iconsCount=0,this.open=!1,this.guid=o++,this.eventNameSpace=".fontIconPicker".concat(o),this.searchValues=[],this.availableCategoriesSearch=[],this.triggerEvent=null,this.backupSource=[],this.backupSearch=[],this.isCategorized=!1,this.selectCategory=this.iconPicker.find(".icon-category-select"),this.selectedCategory=!1,this.availableCategories=[],this.unCategorizedKey=null,this.init()}function c(t){return!(!(e=t).fn||(!e.fn||!e.fn.fontIconPicker)&&(e.fn.fontIconPicker=function(t){var i=this;return this.each(function(){e.data(this,"fontIconPicker")||e.data(this,"fontIconPicker",new r(this,t))}),this.setIcons=function(){var t=arguments.length>0&&void 0!==arguments[0]&&arguments[0],s=arguments.length>1&&void 0!==arguments[1]&&arguments[1];i.each(function(){e.data(this,"fontIconPicker").setIcons(t,s)})},this.setIcon=function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"";i.each(function(){e.data(this,"fontIconPicker").setIcon(t)})},this.destroyPicker=function(){i.each(function(){e.data(this,"fontIconPicker")&&(e.data(this,"fontIconPicker").destroy(),e.removeData(this,"fontIconPicker"))})},this.refreshPicker=function(s){s||(s=t),i.destroyPicker(),i.each(function(){e.data(this,"fontIconPicker")||e.data(this,"fontIconPicker",new r(this,s))})},this.repositionPicker=function(){i.each(function(){e.data(this,"fontIconPicker").resetPosition()})},this.setPage=function(t){i.each(function(){e.data(this,"fontIconPicker").setPage(t)})},this},0));var e}r.prototype={init:function(){this.iconPicker.addClass(this.settings.theme),this.iconPicker.css({left:-9999}).appendTo("body");var t=this.iconPicker.outerHeight(),e=this.iconPicker.outerWidth();this.iconPicker.css({left:""}),this.element.before(this.iconPicker),this.element.css({visibility:"hidden",top:0,position:"relative",zIndex:"-1",left:"-"+e+"px",display:"inline-block",height:t+"px",width:e+"px",padding:"0",margin:"0 -"+e+"px 0 0",border:"0 none",verticalAlign:"top",float:"none"}),this.element.is("select")||(this.triggerEvent="input"),!this.settings.source&&this.element.is("select")?this._populateSourceFromSelect():this._initSourceIndex(),this._loadCategories(),this._loadIcons(),this._initDropDown(),this._initCategoryChanger(),this._initPagination(),this._initIconSearch(),this._initIconSelect(),this._initAutoClose(),this._initFixOnResize()},setIcons:function(t,e){this.settings.source=Array.isArray(t)?i(t):n.extend({},t),this.settings.searchSource=Array.isArray(e)?i(e):n.extend({},e),this._initSourceIndex(),this._loadCategories(),this._resetSearch(),this._loadIcons()},setIcon:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"";this._setSelectedIcon(t)},destroy:function(){this.iconPicker.off().remove(),this.element.css({visibility:"",top:"",position:"",zIndex:"",left:"",display:"",height:"",width:"",padding:"",margin:"",border:"",verticalAlign:"",float:""}),n(window).off("resize"+this.eventNameSpace),n("html").off("click"+this.eventNameSpace)},resetPosition:function(){this._fixOnResize()},setPage:function(t){"first"==t&&(t=1),"last"==t&&(t=this.totalPage),t=parseInt(t,10),isNaN(t)&&(t=1),t>this.totalPage&&(t=this.totalPage),1>t&&(t=1),this.currentPage=t,this._renderIconContainer()},_initFixOnResize:function(){var t,e,i,s=this;n(window).on("resize"+this.eventNameSpace,(t=function(){s._fixOnResize()},e=this.settings.windowDebounceDelay,function(){var s=this,n=arguments;clearTimeout(i),i=setTimeout(function(){return t.apply(s,n)},e)}))},_initAutoClose:function(){var t=this;this.settings.autoClose&&n("html").on("click"+this.eventNameSpace,function(e){var i=e.target;t.selectorPopup.has(i).length||t.selectorPopup.is(i)||t.iconPicker.has(i).length||t.iconPicker.is(i)||t.open&&t._toggleIconSelector()})},_initIconSelect:function(){var t=this;this.selectorPopup.on("click",".fip-box",function(e){var i=n(e.currentTarget);t._setSelectedIcon(i.attr("data-fip-value")),t._toggleIconSelector()})},_initIconSearch:function(){var t=this;this.selectorPopup.on("input",".icons-search-input",function(e){var i=n(e.currentTarget).val();""!==i?(t.searchIcon.removeClass("fip-icon-search"),t.searchIcon.addClass("fip-icon-cancel"),t.isSearch=!0,t.currentPage=1,t.iconsSearched=[],n.grep(t.searchValues,function(e,s){if(0<=e.toLowerCase().search(i.toLowerCase()))return t.iconsSearched[t.iconsSearched.length]=t.settings.source[s],!0}),t._renderIconContainer()):t._resetSearch()}),this.selectorPopup.on("click",".selector-search .fip-icon-cancel",function(){t.selectorPopup.find(".icons-search-input").focus(),t._resetSearch()})},_initPagination:function(){var t=this;this.selectorPopup.on("click",".selector-arrow-right",function(e){t.currentPage<t.totalPage&&(t.currentPage=t.currentPage+1,t._renderIconContainer())}),this.selectorPopup.on("click",".selector-arrow-left",function(e){1<t.currentPage&&(t.currentPage=t.currentPage-1,t._renderIconContainer())})},_initCategoryChanger:function(){var t=this;this.selectorPopup.on("change keyup",".icon-category-select",function(e){if(!1===t.isCategorized)return!1;var i=n(e.currentTarget),s=i.val();if("all"===i.val())t.settings.source=t.backupSource,t.searchValues=t.backupSearch;else{var o=parseInt(s,10);t.availableCategories[o]&&(t.settings.source=t.availableCategories[o],t.searchValues=t.availableCategoriesSearch[o])}t._resetSearch(),t._loadIcons()})},_initDropDown:function(){var t=this;this.selectorButton.on("click",function(e){t._toggleIconSelector()})},_getPickerTemplate:function(){return'\n<div class="selector" data-fip-origin="'.concat(this.element.attr("id"),'">\n\t<span class="selected-icon">\n\t\t<i class="fip-icon-block"></i>\n\t</span>\n\t<span class="selector-button">\n\t\t<i class="fip-icon-down-dir"></i>\n\t</span>\n</div>\n<div class="selector-popup-wrap" data-fip-origin="').concat(this.element.attr("id"),'">\n\t<div class="selector-popup" style="display: none;"> ').concat(this.settings.hasSearch?'<div class="selector-search">\n\t\t\t<input type="text" name="" value="" placeholder="'.concat(this.settings.searchPlaceholder,'" class="icons-search-input"/>\n\t\t\t<i class="fip-icon-search"></i>\n\t\t</div>'):"",'\n\t\t<div class="selector-category">\n\t\t\t<select name="" class="icon-category-select" style="display: none"></select>\n\t\t</div>\n\t\t<div class="fip-icons-container"></div>\n\t\t<div class="selector-footer" style="display:none;">\n\t\t\t<span class="selector-pages">1/2</span>\n\t\t\t<span class="selector-arrows">\n\t\t\t\t<span class="selector-arrow-left" style="display:none;">\n\t\t\t\t\t<i class="fip-icon-left-dir"></i>\n\t\t\t\t</span>\n\t\t\t\t<span class="selector-arrow-right">\n\t\t\t\t\t<i class="fip-icon-right-dir"></i>\n\t\t\t\t</span>\n\t\t\t</span>\n\t\t</div>\n\t</div>\n</div>')},_initSourceIndex:function(){if("object"===e(this.settings.source)){if(Array.isArray(this.settings.source))this.isCategorized=!1,this.selectCategory.html("").hide(),this.settings.source=n.map(this.settings.source,function(t,e){return"function"==typeof t.toString?t.toString():t}),Array.isArray(this.settings.searchSource)?this.searchValues=n.map(this.settings.searchSource,function(t,e){return"function"==typeof t.toString?t.toString():t}):this.searchValues=this.settings.source.slice(0);else{var t=n.extend(!0,{},this.settings.source);for(var i in this.settings.source=[],this.searchValues=[],this.availableCategoriesSearch=[],this.selectedCategory=!1,this.availableCategories=[],this.unCategorizedKey=null,this.isCategorized=!0,this.selectCategory.html(""),t){var s=this.availableCategories.length,o=n("<option />");for(var r in o.attr("value",s),o.html(i),this.selectCategory.append(o),this.availableCategories[s]=[],this.availableCategoriesSearch[s]=[],t[i]){var c=t[i][r],a=this.settings.searchSource&&this.settings.searchSource[i]&&this.settings.searchSource[i][r]?this.settings.searchSource[i][r]:c;"function"==typeof c.toString&&(c=c.toString()),c&&c!==this.settings.emptyIconValue&&(this.settings.source.push(c),this.availableCategories[s].push(c),this.searchValues.push(a),this.availableCategoriesSearch[s].push(a))}}}this.backupSource=this.settings.source.slice(0),this.backupSearch=this.searchValues.slice(0)}},_populateSourceFromSelect:function(){var t=this;this.settings.source=[],this.settings.searchSource=[],this.element.find("optgroup").length?(this.isCategorized=!0,this.element.find("optgroup").each(function(e,i){var s=t.availableCategories.length,o=n("<option />");o.attr("value",s),o.html(n(i).attr("label")),t.selectCategory.append(o),t.availableCategories[s]=[],t.availableCategoriesSearch[s]=[],n(i).find("option").each(function(e,i){var o=n(i).val(),r=n(i).html();o&&o!==t.settings.emptyIconValue&&(t.settings.source.push(o),t.availableCategories[s].push(o),t.searchValues.push(r),t.availableCategoriesSearch[s].push(r))})}),this.element.find("> option").length&&this.element.find("> option").each(function(e,i){var s=n(i).val(),o=n(i).html();if(!s||""===s||s==t.settings.emptyIconValue)return!0;null===t.unCategorizedKey&&(t.unCategorizedKey=t.availableCategories.length,t.availableCategories[t.unCategorizedKey]=[],t.availableCategoriesSearch[t.unCategorizedKey]=[],n("<option />").attr("value",t.unCategorizedKey).html(t.settings.unCategorizedText).appendTo(t.selectCategory)),t.settings.source.push(s),t.availableCategories[t.unCategorizedKey].push(s),t.searchValues.push(o),t.availableCategoriesSearch[t.unCategorizedKey].push(o)})):this.element.find("option").each(function(e,i){var s=n(i).val(),o=n(i).html();s&&(t.settings.source.push(s),t.searchValues.push(o))}),this.backupSource=this.settings.source.slice(0),this.backupSearch=this.searchValues.slice(0)},_loadCategories:function(){!1!==this.isCategorized&&(n('<option value="all">'+this.settings.allCategoryText+"</option>").prependTo(this.selectCategory),this.selectCategory.show().val("all").trigger("change"))},_loadIcons:function(){this.iconContainer.html('<i class="fip-icon-spin3 animate-spin loading"></i>'),Array.isArray(this.settings.source)&&this._renderIconContainer()},_iconGenerator:function(t){return"function"==typeof this.settings.iconGenerator?this.settings.iconGenerator(t):"<i "+(this.settings.useAttribute?this.settings.attributeName+'="'+(this.settings.convertToHex?"&#x"+parseInt(t,10).toString(16)+";":t)+'"':'class="'+t+'"')+"></i>"},_renderIconContainer:function(){var t,e=this,i=[];if(i=this.isSearch?this.iconsSearched:this.settings.source,this.iconsCount=i.length,this.totalPage=Math.ceil(this.iconsCount/this.settings.iconsPerPage),1<this.totalPage?(this.selectorPopup.find(".selector-footer").show(),this.currentPage<this.totalPage?this.selectorPopup.find(".selector-arrow-right").show():this.selectorPopup.find(".selector-arrow-right").hide(),1<this.currentPage?this.selectorPopup.find(".selector-arrow-left").show():this.selectorPopup.find(".selector-arrow-left").hide()):this.selectorPopup.find(".selector-footer").hide(),this.selectorPopup.find(".selector-pages").html(this.currentPage+"/"+this.totalPage+" <em>("+this.iconsCount+")</em>"),t=(this.currentPage-1)*this.settings.iconsPerPage,this.settings.emptyIcon)this.iconContainer.html('<span class="fip-box" data-fip-value="fip-icon-block"><i class="fip-icon-block"></i></span>');else{if(1>i.length)return void this.iconContainer.html('<span class="icons-picker-error" data-fip-value="fip-icon-block"><i class="fip-icon-block"></i></span>');this.iconContainer.html("")}i=i.slice(t,t+this.settings.iconsPerPage);for(var s,o=function(t,i){var s=i;n.grep(e.settings.source,n.proxy(function(t,e){return t===i&&(s=this.searchValues[e],!0)},e)),n("<span/>",{html:e._iconGenerator(i),attr:{"data-fip-value":i},class:"fip-box",title:s}).appendTo(e.iconContainer)},r=0;s=i[r++];)o(0,s);if(this.settings.emptyIcon||this.element.val()&&-1!==n.inArray(this.element.val(),this.settings.source))if(-1===n.inArray(this.element.val(),this.settings.source))this._setSelectedIcon("");else{var c=this.element.val();c===this.settings.emptyIconValue&&(c="fip-icon-block"),this._setSelectedIcon(c)}else this._setSelectedIcon(i[0])},_setHighlightedIcon:function(){this.iconContainer.find(".current-icon").removeClass("current-icon"),this.currentIcon&&this.iconContainer.find('[data-fip-value="'+this.currentIcon+'"]').addClass("current-icon")},_setSelectedIcon:function(t){"fip-icon-block"===t&&(t="");var e=this.iconPicker.find(".selected-icon");""===t?e.html('<i class="fip-icon-block"></i>'):e.html(this._iconGenerator(t));var i=this.element.val();this.element.val(""===t?this.settings.emptyIconValue:t),i!==t&&(this.element.trigger("change"),null!==this.triggerEvent&&this.element.trigger(this.triggerEvent)),this.currentIcon=t,this._setHighlightedIcon()},_repositionIconSelector:function(){var t=this.iconPicker.offset(),e=t.top+this.iconPicker.outerHeight(!0),i=t.left;this.selectorPopup.css({left:i,top:e})},_fixWindowOverflow:function(){var t=this.selectorPopup.find(".selector-popup").is(":visible");t||this.selectorPopup.find(".selector-popup").show();var e=this.selectorPopup.outerWidth(),i=n(window).width(),s=this.selectorPopup.offset().left,o="self"==this.settings.appendTo?this.selectorPopup.parent().offset():n(this.settings.appendTo).offset();if(t||this.selectorPopup.find(".selector-popup").hide(),s+e>i-20){var r=this.selectorButton.offset().left+this.selectorButton.outerWidth(),c=Math.floor(r-e-1);0>c?this.selectorPopup.css({left:i-20-e-o.left}):this.selectorPopup.css({left:c})}},_fixOnResize:function(){"self"!==this.settings.appendTo&&this._repositionIconSelector(),this._fixWindowOverflow()},_toggleIconSelector:function(){this.open=this.open?0:1,this.open&&("self"!==this.settings.appendTo&&(this.selectorPopup.appendTo(this.settings.appendTo).css({zIndex:1e3}).addClass("icons-selector "+this.settings.theme),this._repositionIconSelector()),this._fixWindowOverflow()),this.selectorPopup.find(".selector-popup").slideToggle(300,n.proxy(function(){this.iconPicker.find(".selector-button i").toggleClass("fip-icon-down-dir"),this.iconPicker.find(".selector-button i").toggleClass("fip-icon-up-dir"),this.open?this.selectorPopup.find(".icons-search-input").trigger("focus").trigger("select"):this.selectorPopup.appendTo(this.iconPicker).css({left:"",top:"",zIndex:""}).removeClass("icons-selector "+this.settings.theme)},this))},_resetSearch:function(){this.selectorPopup.find(".icons-search-input").val(""),this.searchIcon.removeClass("fip-icon-cancel"),this.searchIcon.addClass("fip-icon-search"),this.currentPage=1,this.isSearch=!1,this._renderIconContainer()}},t&&t.fn&&c(t);return function(t){return c(t)}});


/***/ }),

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ })

/******/ });
//# sourceMappingURL=icon-picker.js.map