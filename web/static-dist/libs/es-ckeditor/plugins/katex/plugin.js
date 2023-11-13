'use strict';

(function () {
  function KatexWidgetDefinition(editor) {
    var katexClass = 'data-tex';
    var katexDisplayAttr = 'data-display';
    var _delimiters = editor.config.katexDelimiters || [['\\(', '\\)', false], ['\\[', '\\]', true], ['$$', '$$', true], ['$', '$', false]];
    var parseMath = function (value) {
      if (!value) {
        return {
          expr: '',
          delimiter: null,
          displayMode: false
        };
      }

      value = value.trim();

      var delimiter = _delimiters.find(function (d) {
        return value.startsWith(d[0]) && value.endsWith(d[1]);
      });

      if (delimiter) {
        var expr = value.substring(delimiter[0].length, value.length - delimiter[1].length).trim();
        var displayMode = !!delimiter[2];
        return {
          expr: expr,
          delimiter: delimiter,
          displayMode: displayMode
        };
      }

      return {
        expr: value,
        delimiter: null,
        displayMode: false
      };
    };

    return {
      inline: true,
      button: 'Math',
      pathName: 'math',
      dialog: 'katexEdit',
      mask: true,
      requiredContent: 'span[' + katexClass + ']',
      allowedContent: 'span[!' + katexClass + ',' + katexDisplayAttr + ']',
      styleableElements: 'span',
      template: '<span ' + katexClass + '=""></span>',
      parts: {
        span: 'span'
      },
      defaults: {
        math: '',
        displayMode: false,
      },
      _delimiters: _delimiters,
      _katexOptions: Object.assign({
        output: 'html',
        throwOnError: false
      }, editor.config.katexOptions),
      renderKatexHtml: function (math) {
        var html;
        var katex = window.katex;

        if (katex) {
          var _this$parseMath = this.parseMath(math),
              expr = _this$parseMath.expr,
              displayMode = this.data.displayMode;

          var options = Object.assign({}, this._katexOptions, {
            displayMode: displayMode
          });
          html = katex.renderToString(expr || '(empty)', options);
        } else {
          var state = editor._.katex.state;
          var content = CKEDITOR.tools.htmlEncode(math);
          html = "<span data-no-katex=\"".concat(state, "\">").concat(content, "</span>");
        }

        return html;
      },
      renderKatex: function () {
        editor.fire('lockSnapshot');

        try {
          var html = this.renderKatexHtml(this.data.math);
          this.parts.span.setHtml(html);
        } finally {
          editor.fire('unlockSnapshot');
        }
      },
      parseMath: parseMath,
      generateMath: function (expr, displayMode) {
        this.data.displayMode = displayMode;
        var delimiter = this._delimiters.find(function (d) {
          return displayMode === !!d[2];
        });

        if (!delimiter) {
          throw new Error('Unable to find math delimiter with displayMode=' + displayMode);
        }

        return delimiter[0] + expr.trim() + delimiter[1];
      },
      data: function () {
        var parsed = this.parseMath(this.data.math);
        var math = parsed.expr;
        this.data.math = math;
        var displayMode = this.data.displayMode;

        // var _this$parseMath2 = this.parseMath(math),
        //     displayMode = _this$parseMath2.displayMode;

        this.wrapper.setAttribute('data-math', math);
        this.wrapper.setAttribute('data-math-display-mode', displayMode);
        this.renderKatex();
      },
      upcast: function (el, data) {
        if (!(el.name === 'span' && el.attributes[katexClass])) {
          return null;
        }

        var math = CKEDITOR.tools.htmlDecode(el.attributes[katexClass]);
        var displayMode = el.attributes[katexDisplayAttr] != null;

        var _this$parseMath3 = parseMath(math),
            expr = _this$parseMath3.expr;
        if (!expr) {
          return null;
        }

        data.math = math;
        data.displayMode = displayMode;
        return el;
      },
      downcast: function (el) {
        el.attributes[katexClass] = CKEDITOR.tools.htmlEncode(this.data.math);
        if (this.data.displayMode) {
          el.attributes[katexDisplayAttr] = "";
        } else {
          delete el.attributes[katexDisplayAttr];
        }
        el.children[0].replaceWith(new CKEDITOR.htmlParser.text(""));
        return el;
      }
    };
  }

  CKEDITOR.plugins.add('katex', {
    requires: 'widget,dialog',
    lang: 'en,zh,zh-cn',
    icons: 'katex',
    hidpi: true,
    onLoad: function onLoad() {
      CKEDITOR.dtd.$removeEmpty['span'] = false;
      CKEDITOR.dialog.add('katexEdit', this.path + 'dialogs/edit.js');
      CKEDITOR.addCss("\n        .cke_widget_katex[data-math-display-mode=\"true\"] {\n          display: block;\n          text-align: center;\n        }\n\n        .cke_widget_katex > .cke_widget_element {\n          display: inline-block;\n          overflow: hidden;\n          vertical-align: bottom;\n        }\n\n        .cke_widget_katex[data-math-display-mode=\"true\"] > .cke_widget_element {\n          display: block;\n        }\n\n        .cke_widget_katex .katex-error {\n          font-size: 12px;\n          color: #666 !important;\n        }\n\n        .cke_widget_katex .katex-error::before {\n          content: attr(title);\n\n          display: block;\n          margin: 0 0 6px;\n          padding: 0 0 3px;\n\n          border-bottom: 1px dotted;\n          color: #cc0000;\n        }\n      ");
      var isBlockBoundaryOrig = CKEDITOR.dom.element.prototype.isBlockBoundary;

      CKEDITOR.dom.element.prototype.isBlockBoundary = function () {
        var result = isBlockBoundaryOrig.apply(this, arguments);

        try {
          var isFalseBlock = result && this.getAscendant(function (x) {
            return x.data && x.data('widget') === 'katex';
          });

          if (isFalseBlock) {
            return false;
          }
        } catch (e) {
          console.error(e);
        }

        return result;
      };
    },
    init: function(editor) {
      editor._.katex = {
        state: 'loading'
      };
      editor.widgets.add('katex', KatexWidgetDefinition(editor));
      this.loadLib(editor);
    },
    loadLib: function(editor) {
      // "addContentsCss" doesn't affect inline editor by design, and also it is
      // available only with the "wysiwygarea" plugin.
      // https://ckeditor.com/docs/ckeditor4/latest/guide/plugin_sdk_styles.html#inline-editor
      // https://ckeditor.com/docs/ckeditor4/latest/api/CKEDITOR_editor.html#method-addContentsCss
      if (editor.elementMode === CKEDITOR.ELEMENT_MODE_INLINE) {
        this.attachLibCssToTheDocument(editor);
      } else if (editor.addContentsCss) {
        var katexLibCss = this.getLibCss(editor);
        editor.addContentsCss(katexLibCss);
      }

      if ('katex' in window) {
        editor._.katex.state = 'loaded';
      } else {
        var katexLibJs = this.getLibJs(editor);
        CKEDITOR.scriptLoader.load(katexLibJs, function (success) {
          editor._.katex.state = success ? 'loaded' : 'loaderror';
          var katexWidgets = Object.values(editor.widgets.instances).filter(function (w) {
            return w.name === 'katex';
          });
          katexWidgets.forEach(function (w) {
            return w.renderKatex();
          });
          var activeDialog = CKEDITOR.dialog.getCurrent();

          if (activeDialog && activeDialog.getName() === 'katexEdit') {
            activeDialog.updatePreview();
          }
        });
      }
    },
    attachLibCssToTheDocument: function(editor, callback) {
      var katexLibCss = this.getLibCss(editor);
      var exists = !!document.head.querySelector("link[href=\"".concat(katexLibCss, "\"]"));

      if (!exists) {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = katexLibCss;

        if (typeof callback === 'function') {
          link.onload = function () {
            return callback(true);
          };

          link.onerror = function () {
            return callback(false);
          };
        }

        document.head.appendChild(link);
      }
    },
    getLibCss: function(editor) {
      var path = editor.config.katexLibCss;

      if (!path) {
        throw new Error('ckeditor.config.katexLibCss must be defined');
      }

      return path;
    },
    getLibJs: function(editor) {
      var path = editor.config.katexLibJs;

      if (!path) {
        throw new Error('ckeditor.config.katexLibJs must be defined');
      }

      return path;
    }
  });
})();