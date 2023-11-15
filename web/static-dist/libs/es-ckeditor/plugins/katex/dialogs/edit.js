'use strict';

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _wrapNativeSuper(Class) { var _cache = typeof Map === "function" ? new Map() : undefined; _wrapNativeSuper = function _wrapNativeSuper(Class) { if (Class === null || !_isNativeFunction(Class)) return Class; if (typeof Class !== "function") { throw new TypeError("Super expression must either be null or a function"); } if (typeof _cache !== "undefined") { if (_cache.has(Class)) return _cache.get(Class); _cache.set(Class, Wrapper); } function Wrapper() { return _construct(Class, arguments, _getPrototypeOf(this).constructor); } Wrapper.prototype = Object.create(Class.prototype, { constructor: { value: Wrapper, enumerable: false, writable: true, configurable: true } }); return _setPrototypeOf(Wrapper, Class); }; return _wrapNativeSuper(Class); }

function isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

function _construct(Parent, args, Class) { if (isNativeReflectConstruct()) { _construct = Reflect.construct; } else { _construct = function _construct(Parent, args, Class) { var a = [null]; a.push.apply(a, args); var Constructor = Function.bind.apply(Parent, a); var instance = new Constructor(); if (Class) _setPrototypeOf(instance, Class.prototype); return instance; }; } return _construct.apply(null, arguments); }

function _isNativeFunction(fn) { return Function.toString.call(fn).indexOf("[native code]") !== -1; }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

(function () {
  CKEDITOR.dialog.add('katexEdit', function (editor) {
    var lang = editor.lang.katex;
    var DialogDefinition = {
      title: lang.title,
      minWidth: 400,
      minHeight: 100,
      onLoad: function onLoad() {
        // Required for preview. Unfortunately, @font-face within shadow dom
        // doesn't work. https://bugs.chromium.org/p/chromium/issues/detail?id=336876
        var plugin = CKEDITOR.plugins.registered.katex;
        plugin.attachLibCssToTheDocument(editor);
        this.getMath = this.definition.getMath.bind(this);
        this.updatePreview = this.definition.updatePreview.bind(this);
      },
      getMath: function getMath(widget) {
        var expr = this.getValueOf('main', 'expr');
        var displayModeOption = this.getValueOf('main', 'displayMode');
        var displayMode = displayModeOption === 'true';
        var math = widget.generateMath(expr, displayMode);
        return math;
      },
      updatePreview: function updatePreview() {
        var preview = this.getContentElement('main', 'preview');
        preview.updatePreview();
      },
      contents: [{
        id: 'main',
        elements: [{
          id: 'expr',
          type: 'textarea',
          label: lang.editArea,
          required: true,
          validate: CKEDITOR.dialog.validate.notEmpty(lang.editAreaEmpty),
          onLoad: function onLoad() {
            var area = this.getInputElement();
            var dialog = this.getDialog();
            area.on('input', function () {
              dialog.updatePreview();
            });
            area.$.spellcheck = false;
          },
          setup: function setup(widget) {
            var _widget$parseMath = widget.parseMath(widget.data.math),
                expr = _widget$parseMath.expr;

            this.setValue(expr);
          },
          commit: function commit(widget) {
            var math = this.getDialog().getMath(widget);
            var displayModeOption = this.getDialog().getValueOf('main', 'displayMode');
            var displayMode = displayModeOption === 'true';
            widget.setData('math', math);
            widget.setData('displayMode', displayMode);
          }
        }, {
          id: 'displayMode',
          type: 'radio',
          items: [[lang.inlineMode, 'false'], [lang.displayMode, 'true']],
          label: '',
          labelLayout: 'horizontal',
          widths: [0, 100],
          onChange: function onChange() {
            var prevValue = this.getInitValue();

            if (!prevValue) {
              // Ignore first call when dialog is opening
              // (init value == 'default' option).
              return;
            }

            this.getDialog().updatePreview();
          },
          setup: function setup(widget) {
            var displayMode = widget.data.displayMode;

            this.setValue(displayMode.toString());
          },
          commit: function commit(widget) {
            var math = this.getDialog().getMath(widget);
            widget.setData('math', math);
          }
        }, {
          id: 'doc',
          type: 'html',
          html: ""
        }, {
          id: 'preview',
          type: 'html',
          html: "\n            <style>\n              .cke_dialog cke-katex-preview {\n                display: flex;\n                align-items: center;\n                justify-content: center;\n\n                min-height: 2em;\n                max-width: 90vw;\n                overflow-x: auto;\n                overflow-y: hidden;\n\n                white-space: normal;\n                font-size: 16px;\n              }\n            </style>\n\n            <cke-katex-preview editor=\"".concat(editor.name, "\"></cke-katex-preview>\n          "),
          onLoad: function onLoad() {
            var el = this.getElement();
            var katexPreview = el.findOne('cke-katex-preview');

            if ('customElements' in window) {
              if (!customElements.get('cke-katex-preview')) {
                customElements.define('cke-katex-preview', CKEKatexPreview);
              }
            } else {
              el.setAttribute('hidden', '');
            }

            this._katexPreview = katexPreview;
          },
          setup: function setup(widget) {
            this._widget = widget;

            this._katexPreview.setAttribute('widget', widget.id);

            this._katexPreview.setAttribute('math', widget.data.math);
          },
          updatePreview: function updatePreview() {
            var widget = this._widget;
            var katexPreview = this._katexPreview;

            if (widget && katexPreview) {
              var math = this.getDialog().getMath(widget);
              katexPreview.setAttribute('math', math);
            }
          }
        }]
      }]
    };
    return DialogDefinition;
  });

  var CKEKatexPreview =
  /*#__PURE__*/
  function (_HTMLElement) {
    _inherits(CKEKatexPreview, _HTMLElement);

    function CKEKatexPreview() {
      var _this;

      _classCallCheck(this, CKEKatexPreview);

      _this = _possibleConstructorReturn(this, _getPrototypeOf(CKEKatexPreview).call(this));

      _this.attachShadow({
        mode: 'open'
      });

      _this.shadowRoot.innerHTML = "\n        <style>\n          :host {\n            display: inline-block;\n          }\n\n          .container[data-loading] {\n            display: none;\n          }\n\n          .katex-error {\n            font-size: 12px;\n            color: #666 !important;\n          }\n\n          .katex-error::before {\n            content: attr(title);\n\n            display: block;\n            margin: 0 0 6px;\n            padding: 0 0 3px;\n\n            border-bottom: 1px dotted;\n            color: #cc0000;\n          }\n        </style>\n        <div class=\"container\" data-loading></div>\n      ";

      var editorId = _this.getAttribute('editor');

      var editor = editorId && CKEDITOR.instances[editorId];

      if (!editor) {
        throw new Error('editor must be defined');
      }

      var plugin = CKEDITOR.plugins.registered.katex;

      if (!plugin) {
        throw new Error('katex plugin must be registered');
      }

      _this._editor = editor;
      _this._widget = null;
      _this._container = _this.shadowRoot.querySelector('.container');

      var onLoaded = function onLoaded() {
        delete _this._container.dataset.loading;
      };

      var link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = plugin.getLibCss(editor);
      link.onload = onLoaded;
      link.onerror = onLoaded;

      _this.shadowRoot.appendChild(link);

      return _this;
    }

    _createClass(CKEKatexPreview, [{
      key: "attributeChangedCallback",
      value: function attributeChangedCallback(name, oldValue, newValue) {
        if (name === 'widget') {
          var widgetId = newValue;
          this._widget = this._editor.widgets.instances[widgetId];
        } else if (name === 'math') {
          this._renderKatex(newValue);
        }
      }
    }, {
      key: "_renderKatex",
      value: function _renderKatex(math) {
        var widget = this._widget;
        var html = math && widget ? widget.renderKatexHtml(math) : '';
        this._container.innerHTML = html;
      }
    }], [{
      key: "observedAttributes",
      get: function get() {
        return ['math', 'widget'];
      }
    }]);

    return CKEKatexPreview;
  }(_wrapNativeSuper(HTMLElement));
})();