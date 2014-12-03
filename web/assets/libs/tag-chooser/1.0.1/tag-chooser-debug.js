define("tag-chooser/1.0.1/tag-chooser-debug", ["jquery"], function(require, exports, module) {
  // define(function(require, exports, module) {
  var Widget = require("arale-widget/1.2.0/widget-debug");
  var Overlay = require("arale-overlay/1.2.0/overlay-debug");
  var AutoComplete = require("arale-autocomplete/1.4.1/autocomplete-debug");
  var TagChooser = Widget.extend({
    attrs: {
      sourceUrl: '',
      queryUrl: '',
      matchUrl: '',
      choosedTags: {},
      maxTagNum: 10,
      maxTagMessage: '最多只能选择{{num}}个',
      existedMessage: '{{name}}已添加，不能重复添加',
      alwaysShow: false
    },
    _choosedTagsNum: 0,
    _tagOverlay: null,
    _autocomplete: null,
    events: {
      'click .tag-item': '_onClickTagItem',
      'click [data-role=dropdown-trigger]': '_onClickDropdown',
      'click [data-role=tag-remove]': '_onClickTagRemove',
      'blur [data-role=tag-input]': '_onBlurTagInput'
    },
    setup: function() {
      this._initDorpdownOverlay();
      this._initChoosedTags();
      this._initAutocomplete();
      this.on('maxlimit', function() {
        var message = this.get('maxTagMessage').replace(/\{\{num\}\}/g, this.get('maxTagNum'));
        this._showError(message);
      });
      this.on('existed', function(tag) {
        var message = this.get('existedMessage').replace(/\{\{name\}\}/g, tag.name);
        this._showError(message);
      });
    },
    showDropdown: function() {
      this._tagOverlay.show();
      var self = this;
      if (self._sourceDataInited) {
        this._hideError();
        self.trigger('change', self.get('choosedTags'));
        return;
      }
      $.get(this.get('sourceUrl'),{_t:$.now()}, function(html) {
        self.$('[data-role=dropdown-content]').html(html);
        self._refreshDropdownChoosedTags();
        self._sourceDataInited = true;
        self.trigger('change', self.get('choosedTags'));
      });
    },
    hideDropdown: function() {
      this._tagOverlay.hide();
    },
    removeTag: function(id) {
      this._removeTag(id);
      this.trigger('change', this.get('choosedTags'));
    },

    resetTags: function(ids) {
      var self = this;
      $.each(this.get('choosedTags'), function(i, tag) {
        self.removeTag(tag.id);
      });

      $.getJSON(this.get('queryUrl'), {
        ids: ids
      }, function(tags) {
        $.each(tags, function(i, tag) {
          self.addTag(tag);
        });
      });

    },

    addTag: function(newTag) {
      var maxTagNum = this.get('maxTagNum');
      var choosedTags = this.get('choosedTags');
      if (this._choosedTagsNum >= maxTagNum) {
        this.trigger('maxlimit');
        return;
      }
      if (this.hasTag(newTag.id)) {
        this.trigger('existed', newTag);
        return;
      }
      this.get('choosedTags')[newTag.id] = newTag;
      this._choosedTagsNum++;
      // 在已选区域新增标签
      var $newTag = this.$('.choosed-tag-template').clone().removeClass('choosed-tag-template');
      $newTag.data(newTag).addClass('choosed-tag-' + newTag.id);
      $newTag.find('.tag-name-placeholder').html(newTag.name);
      this.$('.tagchooser-choosed').append($newTag).show();
      this._renderAddTagDropdownView(newTag);
      // 插入标签后，重新计算浮层的位置。
      if (this._tagOverlay.get('visible')) {
        this._tagOverlay._setPosition();
      }
      this.trigger('change', this.get('choosedTags'));
    },
    hasTag: function(id) {
      return !!(this.get('choosedTags')[id]);
    },
    getHeight: function() {
      return this.element.height() + this.$('.tagchooser-dropdown').height();
    },
    _onClickTagRemove: function(e) {
      this.removeTag($(e.currentTarget).parents('.choosed-tag').data('id'));
    },
    _onClickTagItem: function(e) {
      var $item = $(e.currentTarget);
      var self = this;
      if (this.get('maxTagNum') > 1) {
        if ($item.hasClass('tag-item-choosed')) {
          this.removeTag($item.data('id'));
        } else {
          this.addTag($item.data());
        }
      } else {
        if (!this.hasTag($item.data('id'))) {
          $.each(this.get('choosedTags'), function(i, tag) {
            self._removeTag(tag.id);
          });
          this.addTag($item.data());
        }
        this.hideDropdown();
      }
    },
    _initAutocomplete: function() {
      var autocomplete = new AutoComplete({
        trigger: this.$('[data-role=tag-input]'),
        dataSource: this.get('matchUrl'),
        width: this.$('[data-role=tag-input]').width(),
        selectFirst: true,
        submitOnEnter: false,
        zIndex: 3000
      }).render();
      var self = this;
      autocomplete.on('itemSelected', function(data, item) {
        self.$('[data-role=tag-input]').val('');
        self.addTag({
          id: data.value,
          name: data.label
        });
      });
    },
    _onClickDropdown: function(e) {
      if (this._tagOverlay.get('visible')) {
        this.hideDropdown();
      } else {
        this.showDropdown();
      }
    },
    _onBlurTagInput: function(e) {
      $(e.currentTarget).val('');
    },
    _showError: function(message) {
      var self = this;
      message = '<span class="text-danger">' + message + '</span>';
      if (this._tagOverlay.get('visible')) {
        this.element.find('[data-role=dropdown-error]').html(message).removeClass('hide');
      } else {
        this.element.find('[data-role=input-error]').html(message).removeClass('hide');
      }
      setTimeout(function() {
        self._hideError();
      }, 3000);
    },
    _hideError: function() {
      this.element.find('[data-role=dropdown-error]').html('').addClass('hide');
      this.element.find('[data-role=input-error]').html('').addClass('hide');
    },
    _initDorpdownOverlay: function() {
      var overlayY = this.$('.input-group').height();
      var overlayWidth = this.$('.input-group').width();
      var overlay = new Overlay({
        element: this.$('.tagchooser-dropdown'),
        width: overlayWidth,
        align: {
          baseElement: this.$('.input-group'),
          baseXY: [0, overlayY]
        }
      });
      this._tagOverlay = overlay;
      if (!this.get('alwaysShow')) {
        overlay._blurHide([overlay.element, this.$('[data-role=dropdown-trigger]')]);
      } else {
        this.$('[data-role=dropdown-trigger]').click();
      }
    },
    _initChoosedTags: function() {
      var tags = this.get('choosedTags');
      this.set('choosedTags', {});
      if (!$.isArray(tags) || tags.length == 0) {
        return;
      }
      var self = this;
      $.getJSON(this.get('queryUrl'), {
        ids: tags
      }, function(tags) {
        $.each(tags, function(i, tag) {
          self.addTag(tag);
        });
      });
    },
    _refreshDropdownChoosedTags: function() {
      var self = this;
      $.each(this.get('choosedTags'), function(i, tag) {
        self.$('.tagchooser-dropdown').find('.tag-item-' + tag.id).addClass('tag-item-choosed');
      });
    },
    // 移除Tag，但不发送change事件
    _removeTag: function(id) {
      delete this.get('choosedTags')[id];
      this._choosedTagsNum--;
      this.$('.tagchooser-choosed').find('.choosed-tag-' + id).remove();
      if (this._choosedTagsNum == 0) {
        this.$('.tagchooser-choosed').hide();
      }
      this._renderRemoveTagDropdownView(id);
      if (this._tagOverlay.get('visible')) {
        this._tagOverlay._setPosition();
      }
    },
    _renderAddTagDropdownView: function(newTag) {
      // 更新下拉框中的选中状态
      this.$('.tagchooser-dropdown').find('.tag-item-' + newTag.id).addClass('tag-item-choosed');
    },
    _renderRemoveTagDropdownView: function(id) {
      this.$('.tagchooser-dropdown').find('.tag-item-' + id).removeClass('tag-item-choosed');
    }
  });
  module.exports = TagChooser;
  // });
});
define("arale-widget/1.2.0/widget-debug", ["jquery"], function(require, exports, module) {
  module.exports = require("arale-widget/1.2.0/src/widget-debug")
});
define("arale-widget/1.2.0/src/widget-debug", ["jquery"], function(require, exports, module) {
  // Widget
  // ---------
  // Widget 是与 DOM 元素相关联的非工具类组件，主要负责 View 层的管理。
  // Widget 组件具有四个要素：描述状态的 attributes 和 properties，描述行为的 events
  // 和 methods。Widget 基类约定了这四要素创建时的基本流程和最佳实践。
  var Base = require("arale-base/1.2.0/base-debug")
  var $ = require('jquery')
  var DAParser = require("arale-widget/1.2.0/src/daparser-debug")
  var AutoRender = require("arale-widget/1.2.0/src/auto-render-debug")
  var DELEGATE_EVENT_NS = '.delegate-events-'
  var ON_RENDER = '_onRender'
  var DATA_WIDGET_CID = 'data-widget-cid'
    // 所有初始化过的 Widget 实例
  var cachedInstances = {}
  var Widget = Base.extend({
      // config 中的这些键值会直接添加到实例上，转换成 properties
      propsInAttrs: ['initElement', 'element', 'events'],
      // 与 widget 关联的 DOM 元素
      element: null,
      // 事件代理，格式为：
      //   {
      //     'mousedown .title': 'edit',
      //     'click {{attrs.saveButton}}': 'save'
      //     'click .open': function(ev) { ... }
      //   }
      events: null,
      // 属性列表
      attrs: {
        // 基本属性
        id: null,
        className: null,
        style: null,
        // 默认模板
        template: '<div></div>',
        // 默认数据模型
        model: null,
        // 组件的默认父节点
        parentNode: document.body
      },
      // 初始化方法，确定组件创建时的基本流程：
      // 初始化 attrs --》 初始化 props --》 初始化 events --》 子类的初始化
      initialize: function(config) {
        this.cid = uniqueCid()
          // 初始化 attrs
        var dataAttrsConfig = this._parseDataAttrsConfig(config)
        Widget.superclass.initialize.call(this, config ? $.extend(dataAttrsConfig, config) : dataAttrsConfig)
          // 初始化 props
        this.parseElement()
        this.initProps()
          // 初始化 events
        this.delegateEvents()
          // 子类自定义的初始化
        this.setup()
          // 保存实例信息
        this._stamp()
          // 是否由 template 初始化
        this._isTemplate = !(config && config.element)
      },
      // 解析通过 data-attr 设置的 api
      _parseDataAttrsConfig: function(config) {
        var element, dataAttrsConfig
        if (config) {
          element = config.initElement ? $(config.initElement) : $(config.element)
        }
        // 解析 data-api 时，只考虑用户传入的 element，不考虑来自继承或从模板构建的
        if (element && element[0] && !AutoRender.isDataApiOff(element)) {
          dataAttrsConfig = DAParser.parseElement(element)
        }
        return dataAttrsConfig
      },
      // 构建 this.element
      parseElement: function() {
        var element = this.element
        if (element) {
          this.element = $(element)
        }
        // 未传入 element 时，从 template 构建
        else if (this.get('template')) {
          this.parseElementFromTemplate()
        }
        // 如果对应的 DOM 元素不存在，则报错
        if (!this.element || !this.element[0]) {
          throw new Error('element is invalid')
        }
      },
      // 从模板中构建 this.element
      parseElementFromTemplate: function() {
        this.element = $(this.get('template'))
      },
      // 负责 properties 的初始化，提供给子类覆盖
      initProps: function() {},
      // 注册事件代理
      delegateEvents: function(element, events, handler) {
        var argus = trimRightUndefine(Array.prototype.slice.call(arguments));
        // widget.delegateEvents()
        if (argus.length === 0) {
          events = getEvents(this)
          element = this.element
        }
        // widget.delegateEvents({
        //   'click p': 'fn1',
        //   'click li': 'fn2'
        // })
        else if (argus.length === 1) {
          events = element
          element = this.element
        }
        // widget.delegateEvents('click p', function(ev) { ... })
        else if (argus.length === 2) {
          handler = events
          events = element
          element = this.element
        }
        // widget.delegateEvents(element, 'click p', function(ev) { ... })
        else {
          element || (element = this.element)
          this._delegateElements || (this._delegateElements = [])
          this._delegateElements.push($(element))
        }
        // 'click p' => {'click p': handler}
        if (isString(events) && isFunction(handler)) {
          var o = {}
          o[events] = handler
          events = o
        }
        // key 为 'event selector'
        for (var key in events) {
          if (!events.hasOwnProperty(key)) continue
          var args = parseEventKey(key, this)
          var eventType = args.type
          var selector = args.selector;
          (function(handler, widget) {
            var callback = function(ev) {
                if (isFunction(handler)) {
                  handler.call(widget, ev)
                } else {
                  widget[handler](ev)
                }
              }
              // delegate
            if (selector) {
              $(element).on(eventType, selector, callback)
            }
            // normal bind
            // 分开写是为了兼容 zepto，zepto 的判断不如 jquery 强劲有力
            else {
              $(element).on(eventType, callback)
            }
          })(events[key], this)
        }
        return this
      },
      // 卸载事件代理
      undelegateEvents: function(element, eventKey) {
        var argus = trimRightUndefine(Array.prototype.slice.call(arguments));
        if (!eventKey) {
          eventKey = element
          element = null
        }
        // 卸载所有
        // .undelegateEvents()
        if (argus.length === 0) {
          var type = DELEGATE_EVENT_NS + this.cid
          this.element && this.element.off(type)
            // 卸载所有外部传入的 element
          if (this._delegateElements) {
            for (var de in this._delegateElements) {
              if (!this._delegateElements.hasOwnProperty(de)) continue
              this._delegateElements[de].off(type)
            }
          }
        } else {
          var args = parseEventKey(eventKey, this)
            // 卸载 this.element
            // .undelegateEvents(events)
          if (!element) {
            this.element && this.element.off(args.type, args.selector)
          }
          // 卸载外部 element
          // .undelegateEvents(element, events)
          else {
            $(element).off(args.type, args.selector)
          }
        }
        return this
      },
      // 提供给子类覆盖的初始化方法
      setup: function() {},
      // 将 widget 渲染到页面上
      // 渲染不仅仅包括插入到 DOM 树中，还包括样式渲染等
      // 约定：子类覆盖时，需保持 `return this`
      render: function() {
        // 让渲染相关属性的初始值生效，并绑定到 change 事件
        if (!this.rendered) {
          this._renderAndBindAttrs()
          this.rendered = true
        }
        // 插入到文档流中
        var parentNode = this.get('parentNode')
        if (parentNode && !isInDocument(this.element[0])) {
          // 隔离样式，添加统一的命名空间
          // https://github.com/aliceui/aliceui.org/issues/9
          var outerBoxClass = this.constructor.outerBoxClass
          if (outerBoxClass) {
            var outerBox = this._outerBox = $('<div></div>').addClass(outerBoxClass)
            outerBox.append(this.element).appendTo(parentNode)
          } else {
            this.element.appendTo(parentNode)
          }
        }
        return this
      },
      // 让属性的初始值生效，并绑定到 change:attr 事件上
      _renderAndBindAttrs: function() {
        var widget = this
        var attrs = widget.attrs
        for (var attr in attrs) {
          if (!attrs.hasOwnProperty(attr)) continue
          var m = ON_RENDER + ucfirst(attr)
          if (this[m]) {
            var val = this.get(attr)
              // 让属性的初始值生效。注：默认空值不触发
            if (!isEmptyAttrValue(val)) {
              this[m](val, undefined, attr)
            }
            // 将 _onRenderXx 自动绑定到 change:xx 事件上
            (function(m) {
              widget.on('change:' + attr, function(val, prev, key) {
                widget[m](val, prev, key)
              })
            })(m)
          }
        }
      },
      _onRenderId: function(val) {
        this.element.attr('id', val)
      },
      _onRenderClassName: function(val) {
        this.element.addClass(val)
      },
      _onRenderStyle: function(val) {
        this.element.css(val)
      },
      // 让 element 与 Widget 实例建立关联
      _stamp: function() {
        var cid = this.cid;
        (this.initElement || this.element).attr(DATA_WIDGET_CID, cid)
        cachedInstances[cid] = this
      },
      // 在 this.element 内寻找匹配节点
      $: function(selector) {
        return this.element.find(selector)
      },
      destroy: function() {
        this.undelegateEvents()
        delete cachedInstances[this.cid]
          // For memory leak
        if (this.element && this._isTemplate) {
          this.element.off()
            // 如果是 widget 生成的 element 则去除
          if (this._outerBox) {
            this._outerBox.remove()
          } else {
            this.element.remove()
          }
        }
        this.element = null
        Widget.superclass.destroy.call(this)
      }
    })
    // For memory leak
  $(window).unload(function() {
      for (var cid in cachedInstances) {
        cachedInstances[cid].destroy()
      }
    })
    // 查询与 selector 匹配的第一个 DOM 节点，得到与该 DOM 节点相关联的 Widget 实例
  Widget.query = function(selector) {
    var element = $(selector).eq(0)
    var cid
    element && (cid = element.attr(DATA_WIDGET_CID))
    return cachedInstances[cid]
  }
  Widget.autoRender = AutoRender.autoRender
  Widget.autoRenderAll = AutoRender.autoRenderAll
  Widget.StaticsWhiteList = ['autoRender']
  module.exports = Widget
    // Helpers
    // ------
  var toString = Object.prototype.toString
  var cidCounter = 0

  function uniqueCid() {
    return 'widget-' + cidCounter++
  }

  function isString(val) {
    return toString.call(val) === '[object String]'
  }

  function isFunction(val) {
      return toString.call(val) === '[object Function]'
    }
    // Zepto 上没有 contains 方法
  var contains = $.contains || function(a, b) {
    //noinspection JSBitwiseOperatorUsage
    return !!(a.compareDocumentPosition(b) & 16)
  }

  function isInDocument(element) {
    return contains(document.documentElement, element)
  }

  function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.substring(1)
  }
  var EVENT_KEY_SPLITTER = /^(\S+)\s*(.*)$/
  var EXPRESSION_FLAG = /{{([^}]+)}}/g
  var INVALID_SELECTOR = 'INVALID_SELECTOR'

  function getEvents(widget) {
    if (isFunction(widget.events)) {
      widget.events = widget.events()
    }
    return widget.events
  }

  function parseEventKey(eventKey, widget) {
      var match = eventKey.match(EVENT_KEY_SPLITTER)
      var eventType = match[1] + DELEGATE_EVENT_NS + widget.cid
        // 当没有 selector 时，需要设置为 undefined，以使得 zepto 能正确转换为 bind
      var selector = match[2] || undefined
      if (selector && selector.indexOf('{{') > -1) {
        selector = parseExpressionInEventKey(selector, widget)
      }
      return {
        type: eventType,
        selector: selector
      }
    }
    // 解析 eventKey 中的 {{xx}}, {{yy}}
  function parseExpressionInEventKey(selector, widget) {
      return selector.replace(EXPRESSION_FLAG, function(m, name) {
        var parts = name.split('.')
        var point = widget,
          part
        while (part = parts.shift()) {
          if (point === widget.attrs) {
            point = widget.get(part)
          } else {
            point = point[part]
          }
        }
        // 已经是 className，比如来自 dataset 的
        if (isString(point)) {
          return point
        }
        // 不能识别的，返回无效标识
        return INVALID_SELECTOR
      })
    }
    // 对于 attrs 的 value 来说，以下值都认为是空值： null, undefined
  function isEmptyAttrValue(o) {
    return o == null || o === undefined
  }

  function trimRightUndefine(argus) {
    for (var i = argus.length - 1; i >= 0; i--) {
      if (argus[i] === undefined) {
        argus.pop();
      } else {
        break;
      }
    }
    return argus;
  }
});
define("arale-base/1.2.0/base-debug", [], function(require, exports, module) {
  module.exports = require("arale-base/1.2.0/src/base-debug");
});
define("arale-base/1.2.0/src/base-debug", [], function(require, exports, module) {
  // Base
  // ---------
  // Base 是一个基础类，提供 Class、Events、Attrs 和 Aspect 支持。
  var Class = require("arale-class/1.2.0/class-debug");
  var Events = require("arale-events/1.2.0/events-debug");
  var Aspect = require("arale-base/1.2.0/src/aspect-debug");
  var Attribute = require("arale-base/1.2.0/src/attribute-debug");
  module.exports = Class.create({
    Implements: [Events, Aspect, Attribute],
    initialize: function(config) {
      this.initAttrs(config);
      // Automatically register `this._onChangeAttr` method as
      // a `change:attr` event handler.
      parseEventsFromInstance(this, this.attrs);
    },
    destroy: function() {
      this.off();
      for (var p in this) {
        if (this.hasOwnProperty(p)) {
          delete this[p];
        }
      }
      // Destroy should be called only once, generate a fake destroy after called
      // https://github.com/aralejs/widget/issues/50
      this.destroy = function() {};
    }
  });

  function parseEventsFromInstance(host, attrs) {
    for (var attr in attrs) {
      if (attrs.hasOwnProperty(attr)) {
        var m = '_onChange' + ucfirst(attr);
        if (host[m]) {
          host.on('change:' + attr, host[m]);
        }
      }
    }
  }

  function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.substring(1);
  }
});
define("arale-class/1.2.0/class-debug", [], function(require, exports, module) {
  // Class
  // -----------------
  // Thanks to:
  //  - http://mootools.net/docs/core/Class/Class
  //  - http://ejohn.org/blog/simple-javascript-inheritance/
  //  - https://github.com/ded/klass
  //  - http://documentcloud.github.com/backbone/#Model-extend
  //  - https://github.com/joyent/node/blob/master/lib/util.js
  //  - https://github.com/kissyteam/kissy/blob/master/src/seed/src/kissy.js
  // The base Class implementation.
  function Class(o) {
    // Convert existed function to Class.
    if (!(this instanceof Class) && isFunction(o)) {
      return classify(o)
    }
  }
  module.exports = Class
    // Create a new Class.
    //
    //  var SuperPig = Class.create({
    //    Extends: Animal,
    //    Implements: Flyable,
    //    initialize: function() {
    //      SuperPig.superclass.initialize.apply(this, arguments)
    //    },
    //    Statics: {
    //      COLOR: 'red'
    //    }
    // })
    //
  Class.create = function(parent, properties) {
    if (!isFunction(parent)) {
      properties = parent
      parent = null
    }
    properties || (properties = {})
    parent || (parent = properties.Extends || Class)
    properties.Extends = parent
      // The created class constructor
    function SubClass() {
        // Call the parent constructor.
        parent.apply(this, arguments)
          // Only call initialize in self constructor.
        if (this.constructor === SubClass && this.initialize) {
          this.initialize.apply(this, arguments)
        }
      }
      // Inherit class (static) properties from parent.
    if (parent !== Class) {
      mix(SubClass, parent, parent.StaticsWhiteList)
    }
    // Add instance properties to the subclass.
    implement.call(SubClass, properties)
      // Make subclass extendable.
    return classify(SubClass)
  }

  function implement(properties) {
      var key, value
      for (key in properties) {
        value = properties[key]
        if (Class.Mutators.hasOwnProperty(key)) {
          Class.Mutators[key].call(this, value)
        } else {
          this.prototype[key] = value
        }
      }
    }
    // Create a sub Class based on `Class`.
  Class.extend = function(properties) {
    properties || (properties = {})
    properties.Extends = this
    return Class.create(properties)
  }

  function classify(cls) {
      cls.extend = Class.extend
      cls.implement = implement
      return cls
    }
    // Mutators define special properties.
  Class.Mutators = {
      'Extends': function(parent) {
        var existed = this.prototype
        var proto = createProto(parent.prototype)
          // Keep existed properties.
        mix(proto, existed)
          // Enforce the constructor to be what we expect.
        proto.constructor = this
          // Set the prototype chain to inherit from `parent`.
        this.prototype = proto
          // Set a convenience property in case the parent's prototype is
          // needed later.
        this.superclass = parent.prototype
      },
      'Implements': function(items) {
        isArray(items) || (items = [items])
        var proto = this.prototype,
          item
        while (item = items.shift()) {
          mix(proto, item.prototype || item)
        }
      },
      'Statics': function(staticProperties) {
        mix(this, staticProperties)
      }
    }
    // Shared empty constructor function to aid in prototype-chain creation.
  function Ctor() {}
    // See: http://jsperf.com/object-create-vs-new-ctor
  var createProto = Object.__proto__ ? function(proto) {
      return {
        __proto__: proto
      }
    } : function(proto) {
      Ctor.prototype = proto
      return new Ctor()
    }
    // Helpers
    // ------------
  function mix(r, s, wl) {
    // Copy "all" properties including inherited ones.
    for (var p in s) {
      if (s.hasOwnProperty(p)) {
        if (wl && indexOf(wl, p) === -1) continue
          // 在 iPhone 1 代等设备的 Safari 中，prototype 也会被枚举出来，需排除
        if (p !== 'prototype') {
          r[p] = s[p]
        }
      }
    }
  }
  var toString = Object.prototype.toString
  var isArray = Array.isArray || function(val) {
    return toString.call(val) === '[object Array]'
  }
  var isFunction = function(val) {
    return toString.call(val) === '[object Function]'
  }
  var indexOf = Array.prototype.indexOf ? function(arr, item) {
    return arr.indexOf(item)
  } : function(arr, item) {
    for (var i = 0, len = arr.length; i < len; i++) {
      if (arr[i] === item) {
        return i
      }
    }
    return -1
  }
});
define("arale-events/1.2.0/events-debug", [], function(require, exports, module) {
  // Events
  // -----------------
  // Thanks to:
  //  - https://github.com/documentcloud/backbone/blob/master/backbone.js
  //  - https://github.com/joyent/node/blob/master/lib/events.js
  // Regular expression used to split event strings
  var eventSplitter = /\s+/
    // A module that can be mixed in to *any object* in order to provide it
    // with custom events. You may bind with `on` or remove with `off` callback
    // functions to an event; `trigger`-ing an event fires all callbacks in
    // succession.
    //
    //     var object = new Events();
    //     object.on('expand', function(){ alert('expanded'); });
    //     object.trigger('expand');
    //
  function Events() {}
    // Bind one or more space separated events, `events`, to a `callback`
    // function. Passing `"all"` will bind the callback to all events fired.
  Events.prototype.on = function(events, callback, context) {
    var cache, event, list
    if (!callback) return this
    cache = this.__events || (this.__events = {})
    events = events.split(eventSplitter)
    while (event = events.shift()) {
      list = cache[event] || (cache[event] = [])
      list.push(callback, context)
    }
    return this
  }
  Events.prototype.once = function(events, callback, context) {
      var that = this
      var cb = function() {
        that.off(events, cb)
        callback.apply(context || that, arguments)
      }
      return this.on(events, cb, context)
    }
    // Remove one or many callbacks. If `context` is null, removes all callbacks
    // with that function. If `callback` is null, removes all callbacks for the
    // event. If `events` is null, removes all bound callbacks for all events.
  Events.prototype.off = function(events, callback, context) {
      var cache, event, list, i
        // No events, or removing *all* events.
      if (!(cache = this.__events)) return this
      if (!(events || callback || context)) {
        delete this.__events
        return this
      }
      events = events ? events.split(eventSplitter) : keys(cache)
        // Loop through the callback list, splicing where appropriate.
      while (event = events.shift()) {
        list = cache[event]
        if (!list) continue
        if (!(callback || context)) {
          delete cache[event]
          continue
        }
        for (i = list.length - 2; i >= 0; i -= 2) {
          if (!(callback && list[i] !== callback || context && list[i + 1] !== context)) {
            list.splice(i, 2)
          }
        }
      }
      return this
    }
    // Trigger one or many events, firing all bound callbacks. Callbacks are
    // passed the same arguments as `trigger` is, apart from the event name
    // (unless you're listening on `"all"`, which will cause your callback to
    // receive the true name of the event as the first argument).
  Events.prototype.trigger = function(events) {
    var cache, event, all, list, i, len, rest = [],
      args, returned = true;
    if (!(cache = this.__events)) return this
    events = events.split(eventSplitter)
      // Fill up `rest` with the callback arguments.  Since we're only copying
      // the tail of `arguments`, a loop is much faster than Array#slice.
    for (i = 1, len = arguments.length; i < len; i++) {
      rest[i - 1] = arguments[i]
    }
    // For each event, walk through the list of callbacks twice, first to
    // trigger the event, then to trigger any `"all"` callbacks.
    while (event = events.shift()) {
      // Copy callback lists to prevent modification.
      if (all = cache.all) all = all.slice()
      if (list = cache[event]) list = list.slice()
        // Execute event callbacks except one named "all"
      if (event !== 'all') {
        returned = triggerEvents(list, rest, this) && returned
      }
      // Execute "all" callbacks.
      returned = triggerEvents(all, [event].concat(rest), this) && returned
    }
    return returned
  }
  Events.prototype.emit = Events.prototype.trigger
    // Helpers
    // -------
  var keys = Object.keys
  if (!keys) {
    keys = function(o) {
      var result = []
      for (var name in o) {
        if (o.hasOwnProperty(name)) {
          result.push(name)
        }
      }
      return result
    }
  }
  // Mix `Events` to object instance or Class function.
  Events.mixTo = function(receiver) {
      receiver = isFunction(receiver) ? receiver.prototype : receiver
      var proto = Events.prototype
      var event = new Events
      for (var key in proto) {
        if (proto.hasOwnProperty(key)) {
          copyProto(key)
        }
      }

      function copyProto(key) {
        receiver[key] = function() {
          proto[key].apply(event, Array.prototype.slice.call(arguments))
          return this
        }
      }
    }
    // Execute callbacks
  function triggerEvents(list, args, context) {
    var pass = true
    if (list) {
      var i = 0,
        l = list.length,
        a1 = args[0],
        a2 = args[1],
        a3 = args[2]
        // call is faster than apply, optimize less than 3 argu
        // http://blog.csdn.net/zhengyinhui100/article/details/7837127
      switch (args.length) {
        case 0:
          for (; i < l; i += 2) {
            pass = list[i].call(list[i + 1] || context) !== false && pass
          }
          break;
        case 1:
          for (; i < l; i += 2) {
            pass = list[i].call(list[i + 1] || context, a1) !== false && pass
          }
          break;
        case 2:
          for (; i < l; i += 2) {
            pass = list[i].call(list[i + 1] || context, a1, a2) !== false && pass
          }
          break;
        case 3:
          for (; i < l; i += 2) {
            pass = list[i].call(list[i + 1] || context, a1, a2, a3) !== false && pass
          }
          break;
        default:
          for (; i < l; i += 2) {
            pass = list[i].apply(list[i + 1] || context, args) !== false && pass
          }
          break;
      }
    }
    // trigger will return false if one of the callbacks return false
    return pass;
  }

  function isFunction(func) {
    return Object.prototype.toString.call(func) === '[object Function]'
  }
  module.exports = Events
});
define("arale-base/1.2.0/src/aspect-debug", [], function(require, exports, module) {
  // Aspect
  // ---------------------
  // Thanks to:
  //  - http://yuilibrary.com/yui/docs/api/classes/Do.html
  //  - http://code.google.com/p/jquery-aop/
  //  - http://lazutkin.com/blog/2008/may/18/aop-aspect-javascript-dojo/
  // 在指定方法执行前，先执行 callback
  exports.before = function(methodName, callback, context) {
    return weave.call(this, 'before', methodName, callback, context);
  };
  // 在指定方法执行后，再执行 callback
  exports.after = function(methodName, callback, context) {
    return weave.call(this, 'after', methodName, callback, context);
  };
  // Helpers
  // -------
  var eventSplitter = /\s+/;

  function weave(when, methodName, callback, context) {
    var names = methodName.split(eventSplitter);
    var name, method;
    while (name = names.shift()) {
      method = getMethod(this, name);
      if (!method.__isAspected) {
        wrap.call(this, name);
      }
      this.on(when + ':' + name, callback, context);
    }
    return this;
  }

  function getMethod(host, methodName) {
    var method = host[methodName];
    if (!method) {
      throw new Error('Invalid method name: ' + methodName);
    }
    return method;
  }

  function wrap(methodName) {
    var old = this[methodName];
    this[methodName] = function() {
      var args = Array.prototype.slice.call(arguments);
      var beforeArgs = ['before:' + methodName].concat(args);
      // prevent if trigger return false
      if (this.trigger.apply(this, beforeArgs) === false) return;
      var ret = old.apply(this, arguments);
      var afterArgs = ['after:' + methodName, ret].concat(args);
      this.trigger.apply(this, afterArgs);
      return ret;
    };
    this[methodName].__isAspected = true;
  }
});
define("arale-base/1.2.0/src/attribute-debug", [], function(require, exports, module) {
  // Attribute
  // -----------------
  // Thanks to:
  //  - http://documentcloud.github.com/backbone/#Model
  //  - http://yuilibrary.com/yui/docs/api/classes/AttributeCore.html
  //  - https://github.com/berzniz/backbone.getters.setters
  // 负责 attributes 的初始化
  // attributes 是与实例相关的状态信息，可读可写，发生变化时，会自动触发相关事件
  exports.initAttrs = function(config) {
    // initAttrs 是在初始化时调用的，默认情况下实例上肯定没有 attrs，不存在覆盖问题
    var attrs = this.attrs = {};
    // Get all inherited attributes.
    var specialProps = this.propsInAttrs || [];
    mergeInheritedAttrs(attrs, this, specialProps);
    // Merge user-specific attributes from config.
    if (config) {
      mergeUserValue(attrs, config);
    }
    // 对于有 setter 的属性，要用初始值 set 一下，以保证关联属性也一同初始化
    setSetterAttrs(this, attrs, config);
    // Convert `on/before/afterXxx` config to event handler.
    parseEventsFromAttrs(this, attrs);
    // 将 this.attrs 上的 special properties 放回 this 上
    copySpecialProps(specialProps, this, attrs, true);
  };
  // Get the value of an attribute.
  exports.get = function(key) {
    var attr = this.attrs[key] || {};
    var val = attr.value;
    return attr.getter ? attr.getter.call(this, val, key) : val;
  };
  // Set a hash of model attributes on the object, firing `"change"` unless
  // you choose to silence it.
  exports.set = function(key, val, options) {
    var attrs = {};
    // set("key", val, options)
    if (isString(key)) {
      attrs[key] = val;
    }
    // set({ "key": val, "key2": val2 }, options)
    else {
      attrs = key;
      options = val;
    }
    options || (options = {});
    var silent = options.silent;
    var override = options.override;
    var now = this.attrs;
    var changed = this.__changedAttrs || (this.__changedAttrs = {});
    for (key in attrs) {
      if (!attrs.hasOwnProperty(key)) continue;
      var attr = now[key] || (now[key] = {});
      val = attrs[key];
      if (attr.readOnly) {
        throw new Error('This attribute is readOnly: ' + key);
      }
      // invoke setter
      if (attr.setter) {
        val = attr.setter.call(this, val, key);
      }
      // 获取设置前的 prev 值
      var prev = this.get(key);
      // 获取需要设置的 val 值
      // 如果设置了 override 为 true，表示要强制覆盖，就不去 merge 了
      // 都为对象时，做 merge 操作，以保留 prev 上没有覆盖的值
      if (!override && isPlainObject(prev) && isPlainObject(val)) {
        val = merge(merge({}, prev), val);
      }
      // set finally
      now[key].value = val;
      // invoke change event
      // 初始化时对 set 的调用，不触发任何事件
      if (!this.__initializingAttrs && !isEqual(prev, val)) {
        if (silent) {
          changed[key] = [val, prev];
        } else {
          this.trigger('change:' + key, val, prev, key);
        }
      }
    }
    return this;
  };
  // Call this method to manually fire a `"change"` event for triggering
  // a `"change:attribute"` event for each changed attribute.
  exports.change = function() {
    var changed = this.__changedAttrs;
    if (changed) {
      for (var key in changed) {
        if (changed.hasOwnProperty(key)) {
          var args = changed[key];
          this.trigger('change:' + key, args[0], args[1], key);
        }
      }
      delete this.__changedAttrs;
    }
    return this;
  };
  // for test
  exports._isPlainObject = isPlainObject;
  // Helpers
  // -------
  var toString = Object.prototype.toString;
  var hasOwn = Object.prototype.hasOwnProperty;
  /**
   * Detect the JScript [[DontEnum]] bug:
   * In IE < 9 an objects own properties, shadowing non-enumerable ones, are
   * made non-enumerable as well.
   * https://github.com/bestiejs/lodash/blob/7520066fc916e205ef84cb97fbfe630d7c154158/lodash.js#L134-L144
   */
  /** Detect if own properties are iterated after inherited properties (IE < 9) */
  var iteratesOwnLast;
  (function() {
    var props = [];

    function Ctor() {
      this.x = 1;
    }
    Ctor.prototype = {
      'valueOf': 1,
      'y': 1
    };
    for (var prop in new Ctor()) {
      props.push(prop);
    }
    iteratesOwnLast = props[0] !== 'x';
  }());
  var isArray = Array.isArray || function(val) {
    return toString.call(val) === '[object Array]';
  };

  function isString(val) {
    return toString.call(val) === '[object String]';
  }

  function isFunction(val) {
    return toString.call(val) === '[object Function]';
  }

  function isWindow(o) {
    return o != null && o == o.window;
  }

  function isPlainObject(o) {
    // Must be an Object.
    // Because of IE, we also have to check the presence of the constructor
    // property. Make sure that DOM nodes and window objects don't
    // pass through, as well
    if (!o || toString.call(o) !== "[object Object]" || o.nodeType || isWindow(o)) {
      return false;
    }
    try {
      // Not own constructor property must be Object
      if (o.constructor && !hasOwn.call(o, "constructor") && !hasOwn.call(o.constructor.prototype, "isPrototypeOf")) {
        return false;
      }
    } catch (e) {
      // IE8,9 Will throw exceptions on certain host objects #9897
      return false;
    }
    var key;
    // Support: IE<9
    // Handle iteration over inherited properties before own properties.
    // http://bugs.jquery.com/ticket/12199
    if (iteratesOwnLast) {
      for (key in o) {
        return hasOwn.call(o, key);
      }
    }
    // Own properties are enumerated firstly, so to speed up,
    // if last one is own, then all properties are own.
    for (key in o) {}
    return key === undefined || hasOwn.call(o, key);
  }

  function isEmptyObject(o) {
    if (!o || toString.call(o) !== "[object Object]" || o.nodeType || isWindow(o) || !o.hasOwnProperty) {
      return false;
    }
    for (var p in o) {
      if (o.hasOwnProperty(p)) return false;
    }
    return true;
  }

  function merge(receiver, supplier) {
      var key, value;
      for (key in supplier) {
        if (supplier.hasOwnProperty(key)) {
          receiver[key] = cloneValue(supplier[key], receiver[key]);
        }
      }
      return receiver;
    }
    // 只 clone 数组和 plain object，其他的保持不变
  function cloneValue(value, prev) {
    if (isArray(value)) {
      value = value.slice();
    } else if (isPlainObject(value)) {
      isPlainObject(prev) || (prev = {});
      value = merge(prev, value);
    }
    return value;
  }
  var keys = Object.keys;
  if (!keys) {
    keys = function(o) {
      var result = [];
      for (var name in o) {
        if (o.hasOwnProperty(name)) {
          result.push(name);
        }
      }
      return result;
    };
  }

  function mergeInheritedAttrs(attrs, instance, specialProps) {
    var inherited = [];
    var proto = instance.constructor.prototype;
    while (proto) {
      // 不要拿到 prototype 上的
      if (!proto.hasOwnProperty('attrs')) {
        proto.attrs = {};
      }
      // 将 proto 上的特殊 properties 放到 proto.attrs 上，以便合并
      copySpecialProps(specialProps, proto.attrs, proto);
      // 为空时不添加
      if (!isEmptyObject(proto.attrs)) {
        inherited.unshift(proto.attrs);
      }
      // 向上回溯一级
      proto = proto.constructor.superclass;
    }
    // Merge and clone default values to instance.
    for (var i = 0, len = inherited.length; i < len; i++) {
      mergeAttrs(attrs, normalize(inherited[i]));
    }
  }

  function mergeUserValue(attrs, config) {
    mergeAttrs(attrs, normalize(config, true), true);
  }

  function copySpecialProps(specialProps, receiver, supplier, isAttr2Prop) {
    for (var i = 0, len = specialProps.length; i < len; i++) {
      var key = specialProps[i];
      if (supplier.hasOwnProperty(key)) {
        receiver[key] = isAttr2Prop ? receiver.get(key) : supplier[key];
      }
    }
  }
  var EVENT_PATTERN = /^(on|before|after)([A-Z].*)$/;
  var EVENT_NAME_PATTERN = /^(Change)?([A-Z])(.*)/;

  function parseEventsFromAttrs(host, attrs) {
      for (var key in attrs) {
        if (attrs.hasOwnProperty(key)) {
          var value = attrs[key].value,
            m;
          if (isFunction(value) && (m = key.match(EVENT_PATTERN))) {
            host[m[1]](getEventName(m[2]), value);
            delete attrs[key];
          }
        }
      }
    }
    // Converts `Show` to `show` and `ChangeTitle` to `change:title`
  function getEventName(name) {
    var m = name.match(EVENT_NAME_PATTERN);
    var ret = m[1] ? 'change:' : '';
    ret += m[2].toLowerCase() + m[3];
    return ret;
  }

  function setSetterAttrs(host, attrs, config) {
    var options = {
      silent: true
    };
    host.__initializingAttrs = true;
    for (var key in config) {
      if (config.hasOwnProperty(key)) {
        if (attrs[key].setter) {
          host.set(key, config[key], options);
        }
      }
    }
    delete host.__initializingAttrs;
  }
  var ATTR_SPECIAL_KEYS = ['value', 'getter', 'setter', 'readOnly'];
  // normalize `attrs` to
  //
  //   {
  //      value: 'xx',
  //      getter: fn,
  //      setter: fn,
  //      readOnly: boolean
  //   }
  //
  function normalize(attrs, isUserValue) {
    var newAttrs = {};
    for (var key in attrs) {
      var attr = attrs[key];
      if (!isUserValue && isPlainObject(attr) && hasOwnProperties(attr, ATTR_SPECIAL_KEYS)) {
        newAttrs[key] = attr;
        continue;
      }
      newAttrs[key] = {
        value: attr
      };
    }
    return newAttrs;
  }
  var ATTR_OPTIONS = ['setter', 'getter', 'readOnly'];
  // 专用于 attrs 的 merge 方法
  function mergeAttrs(attrs, inheritedAttrs, isUserValue) {
    var key, value;
    var attr;
    for (key in inheritedAttrs) {
      if (inheritedAttrs.hasOwnProperty(key)) {
        value = inheritedAttrs[key];
        attr = attrs[key];
        if (!attr) {
          attr = attrs[key] = {};
        }
        // 从严谨上来说，遍历 ATTR_SPECIAL_KEYS 更好
        // 从性能来说，直接 人肉赋值 更快
        // 这里还是选择 性能优先
        // 只有 value 要复制原值，其他的直接覆盖即可
        (value['value'] !== undefined) && (attr['value'] = cloneValue(value['value'], attr['value']));
        // 如果是用户赋值，只要考虑value
        if (isUserValue) continue;
        for (var i in ATTR_OPTIONS) {
          var option = ATTR_OPTIONS[i];
          if (value[option] !== undefined) {
            attr[option] = value[option];
          }
        }
      }
    }
    return attrs;
  }

  function hasOwnProperties(object, properties) {
      for (var i = 0, len = properties.length; i < len; i++) {
        if (object.hasOwnProperty(properties[i])) {
          return true;
        }
      }
      return false;
    }
    // 对于 attrs 的 value 来说，以下值都认为是空值： null, undefined, '', [], {}
  function isEmptyAttrValue(o) {
      return o == null || // null, undefined
        (isString(o) || isArray(o)) && o.length === 0 || // '', []
        isEmptyObject(o); // {}
    }
    // 判断属性值 a 和 b 是否相等，注意仅适用于属性值的判断，非普适的 === 或 == 判断。
  function isEqual(a, b) {
    if (a === b) return true;
    if (isEmptyAttrValue(a) && isEmptyAttrValue(b)) return true;
    // Compare `[[Class]]` names.
    var className = toString.call(a);
    if (className != toString.call(b)) return false;
    switch (className) {
      // Strings, numbers, dates, and booleans are compared by value.
      case '[object String]':
        // Primitives and their corresponding object wrappers are
        // equivalent; thus, `"5"` is equivalent to `new String("5")`.
        return a == String(b);
      case '[object Number]':
        // `NaN`s are equivalent, but non-reflexive. An `equal`
        // comparison is performed for other numeric values.
        return a != +a ? b != +b : (a == 0 ? 1 / a == 1 / b : a == +b);
      case '[object Date]':
      case '[object Boolean]':
        // Coerce dates and booleans to numeric primitive values.
        // Dates are compared by their millisecond representations.
        // Note that invalid dates with millisecond representations
        // of `NaN` are not equivalent.
        return +a == +b;
        // RegExps are compared by their source patterns and flags.
      case '[object RegExp]':
        return a.source == b.source && a.global == b.global && a.multiline == b.multiline && a.ignoreCase == b.ignoreCase;
        // 简单判断数组包含的 primitive 值是否相等
      case '[object Array]':
        var aString = a.toString();
        var bString = b.toString();
        // 只要包含非 primitive 值，为了稳妥起见，都返回 false
        return aString.indexOf('[object') === -1 && bString.indexOf('[object') === -1 && aString === bString;
    }
    if (typeof a != 'object' || typeof b != 'object') return false;
    // 简单判断两个对象是否相等，只判断第一层
    if (isPlainObject(a) && isPlainObject(b)) {
      // 键值不相等，立刻返回 false
      if (!isEqual(keys(a), keys(b))) {
        return false;
      }
      // 键相同，但有值不等，立刻返回 false
      for (var p in a) {
        if (a[p] !== b[p]) return false;
      }
      return true;
    }
    // 其他情况返回 false, 以避免误判导致 change 事件没发生
    return false;
  }
});
define("arale-widget/1.2.0/src/daparser-debug", ["jquery"], function(require, exports, module) {
  // DAParser
  // --------
  // data api 解析器，提供对单个 element 的解析，可用来初始化页面中的所有 Widget 组件。
  var $ = require('jquery')
    // 得到某个 DOM 元素的 dataset
  exports.parseElement = function(element, raw) {
      element = $(element)[0]
      var dataset = {}
        // ref: https://developer.mozilla.org/en/DOM/element.dataset
      if (element.dataset) {
        // 转换成普通对象
        dataset = $.extend({}, element.dataset)
      } else {
        var attrs = element.attributes
        for (var i = 0, len = attrs.length; i < len; i++) {
          var attr = attrs[i]
          var name = attr.name
          if (name.indexOf('data-') === 0) {
            name = camelCase(name.substring(5))
            dataset[name] = attr.value
          }
        }
      }
      return raw === true ? dataset : normalizeValues(dataset)
    }
    // Helpers
    // ------
  var RE_DASH_WORD = /-([a-z])/g
  var JSON_LITERAL_PATTERN = /^\s*[\[{].*[\]}]\s*$/
  var parseJSON = this.JSON ? JSON.parse : $.parseJSON
    // 仅处理字母开头的，其他情况转换为小写："data-x-y-123-_A" --> xY-123-_a
  function camelCase(str) {
      return str.toLowerCase().replace(RE_DASH_WORD, function(all, letter) {
        return (letter + '').toUpperCase()
      })
    }
    // 解析并归一化配置中的值
  function normalizeValues(data) {
      for (var key in data) {
        if (data.hasOwnProperty(key)) {
          var val = data[key]
          if (typeof val !== 'string') continue
          if (JSON_LITERAL_PATTERN.test(val)) {
            val = val.replace(/'/g, '"')
            data[key] = normalizeValues(parseJSON(val))
          } else {
            data[key] = normalizeValue(val)
          }
        }
      }
      return data
    }
    // 将 'false' 转换为 false
    // 'true' 转换为 true
    // '3253.34' 转换为 3253.34
  function normalizeValue(val) {
    if (val.toLowerCase() === 'false') {
      val = false
    } else if (val.toLowerCase() === 'true') {
      val = true
    } else if (/\d/.test(val) && /[^a-z]/i.test(val)) {
      var number = parseFloat(val)
      if (number + '' === val) {
        val = number
      }
    }
    return val
  }
});
define("arale-widget/1.2.0/src/auto-render-debug", ["jquery"], function(require, exports, module) {
  var $ = require('jquery')
  var DATA_WIDGET_AUTO_RENDERED = 'data-widget-auto-rendered'
    // 自动渲染接口，子类可根据自己的初始化逻辑进行覆盖
  exports.autoRender = function(config) {
      return new this(config).render()
    }
    // 根据 data-widget 属性，自动渲染所有开启了 data-api 的 widget 组件
  exports.autoRenderAll = function(root, callback) {
    if (typeof root === 'function') {
      callback = root
      root = null
    }
    root = $(root || document.body)
    var modules = []
    var elements = []
    root.find('[data-widget]').each(function(i, element) {
      if (!exports.isDataApiOff(element)) {
        modules.push(element.getAttribute('data-widget').toLowerCase())
        elements.push(element)
      }
    })
    if (modules.length) {
      seajs.use(modules, function() {
        for (var i = 0; i < arguments.length; i++) {
          var SubWidget = arguments[i]
          var element = $(elements[i])
            // 已经渲染过
          if (element.attr(DATA_WIDGET_AUTO_RENDERED)) continue
          var config = {
            initElement: element,
            renderType: 'auto'
          };
          // data-widget-role 是指将当前的 DOM 作为 role 的属性去实例化，默认的 role 为 element
          var role = element.attr('data-widget-role')
          config[role ? role : 'element'] = element
            // 调用自动渲染接口
          SubWidget.autoRender && SubWidget.autoRender(config)
            // 标记已经渲染过
          element.attr(DATA_WIDGET_AUTO_RENDERED, 'true')
        }
        // 在所有自动渲染完成后，执行回调
        callback && callback()
      })
    }
  }
  var isDefaultOff = $(document.body).attr('data-api') === 'off'
    // 是否没开启 data-api
  exports.isDataApiOff = function(element) {
    var elementDataApi = $(element).attr('data-api')
      // data-api 默认开启，关闭只有两种方式：
      //  1. element 上有 data-api="off"，表示关闭单个
      //  2. document.body 上有 data-api="off"，表示关闭所有
    return elementDataApi === 'off' || (elementDataApi !== 'on' && isDefaultOff)
  }
});
define("arale-overlay/1.2.0/overlay-debug", ["jquery"], function(require, exports, module) {
  module.exports = require("arale-overlay/1.2.0/src/overlay-debug");
  module.exports.Mask = require("arale-overlay/1.2.0/src/mask-debug");
});
define("arale-overlay/1.2.0/src/overlay-debug", ["jquery"], function(require, exports, module) {
  var $ = require("jquery"),
    Position = require("position/1.1.0/index-debug"),
    Shim = require("arale-iframe-shim/1.1.0/index-debug"),
    Widget = require("arale-widget/1.2.0/widget-debug");
  // Overlay
  // -------
  // Overlay 组件的核心特点是可定位（Positionable）和可层叠（Stackable）
  // 是一切悬浮类 UI 组件的基类
  var Overlay = Widget.extend({
    attrs: {
      // 基本属性
      width: null,
      height: null,
      zIndex: 2000,
      visible: false,
      // 定位配置
      align: {
        // element 的定位点，默认为左上角
        selfXY: [0, 0],
        // 基准定位元素，默认为当前可视区域
        baseElement: Position.VIEWPORT,
        // 基准定位元素的定位点，默认为左上角
        baseXY: [0, 0]
      },
      // 父元素
      parentNode: document.body
    },
    show: function() {
      // 若从未渲染，则调用 render
      if (!this.rendered) {
        this.render();
      }
      this.set('visible', true);
      return this;
    },
    hide: function() {
      this.set('visible', false);
      return this;
    },
    setup: function() {
      var that = this;
      // 加载 iframe 遮罩层并与 overlay 保持同步
      this._setupShim();
      // 窗口resize时，重新定位浮层
      this._setupResize();
      this.after('render', function() {
        var _pos = this.element.css('position');
        if (_pos === 'static' || _pos === 'relative') {
          this.element.css({
            position: 'absolute',
            left: '-9999px',
            top: '-9999px'
          });
        }
      });
      // 统一在显示之后重新设定位置
      this.after('show', function() {
        that._setPosition();
      });
    },
    destroy: function() {
      // 销毁两个静态数组中的实例
      erase(this, Overlay.allOverlays);
      erase(this, Overlay.blurOverlays);
      return Overlay.superclass.destroy.call(this);
    },
    // 进行定位
    _setPosition: function(align) {
      // 不在文档流中，定位无效
      if (!isInDocument(this.element[0])) return;
      align || (align = this.get('align'));
      // 如果align为空，表示不需要使用js对齐
      if (!align) return;
      var isHidden = this.element.css('display') === 'none';
      // 在定位时，为避免元素高度不定，先显示出来
      if (isHidden) {
        this.element.css({
          visibility: 'hidden',
          display: 'block'
        });
      }
      Position.pin({
        element: this.element,
        x: align.selfXY[0],
        y: align.selfXY[1]
      }, {
        element: align.baseElement,
        x: align.baseXY[0],
        y: align.baseXY[1]
      });
      // 定位完成后，还原
      if (isHidden) {
        this.element.css({
          visibility: '',
          display: 'none'
        });
      }
      return this;
    },
    // 加载 iframe 遮罩层并与 overlay 保持同步
    _setupShim: function() {
      var shim = new Shim(this.element);
      // 在隐藏和设置位置后，要重新定位
      // 显示后会设置位置，所以不用绑定 shim.sync
      this.after('hide _setPosition', shim.sync, shim);
      // 除了 parentNode 之外的其他属性发生变化时，都触发 shim 同步
      var attrs = ['width', 'height'];
      for (var attr in attrs) {
        if (attrs.hasOwnProperty(attr)) {
          this.on('change:' + attr, shim.sync, shim);
        }
      }
      // 在销魂自身前要销毁 shim
      this.before('destroy', shim.destroy, shim);
    },
    // resize窗口时重新定位浮层，用这个方法收集所有浮层实例
    _setupResize: function() {
      Overlay.allOverlays.push(this);
    },
    // 除了 element 和 relativeElements，点击 body 后都会隐藏 element
    _blurHide: function(arr) {
      arr = $.makeArray(arr);
      arr.push(this.element);
      this._relativeElements = arr;
      Overlay.blurOverlays.push(this);
    },
    // 用于 set 属性后的界面更新
    _onRenderWidth: function(val) {
      this.element.css('width', val);
    },
    _onRenderHeight: function(val) {
      this.element.css('height', val);
    },
    _onRenderZIndex: function(val) {
      this.element.css('zIndex', val);
    },
    _onRenderAlign: function(val) {
      this._setPosition(val);
    },
    _onRenderVisible: function(val) {
      this.element[val ? 'show' : 'hide']();
    }
  });
  // 绑定 blur 隐藏事件
  Overlay.blurOverlays = [];
  $(document).on('click', function(e) {
    hideBlurOverlays(e);
  });
  // 绑定 resize 重新定位事件
  var timeout;
  var winWidth = $(window).width();
  var winHeight = $(window).height();
  Overlay.allOverlays = [];
  $(window).resize(function() {
    timeout && clearTimeout(timeout);
    timeout = setTimeout(function() {
      var winNewWidth = $(window).width();
      var winNewHeight = $(window).height();
      // IE678 莫名其妙触发 resize
      // http://stackoverflow.com/questions/1852751/window-resize-event-firing-in-internet-explorer
      if (winWidth !== winNewWidth || winHeight !== winNewHeight) {
        $(Overlay.allOverlays).each(function(i, item) {
          // 当实例为空或隐藏时，不处理
          if (!item || !item.get('visible')) {
            return;
          }
          item._setPosition();
        });
      }
      winWidth = winNewWidth;
      winHeight = winNewHeight;
    }, 80);
  });
  module.exports = Overlay;
  // Helpers
  // -------
  function isInDocument(element) {
    return $.contains(document.documentElement, element);
  }

  function hideBlurOverlays(e) {
      $(Overlay.blurOverlays).each(function(index, item) {
        // 当实例为空或隐藏时，不处理
        if (!item || !item.get('visible')) {
          return;
        }
        // 遍历 _relativeElements ，当点击的元素落在这些元素上时，不处理
        for (var i = 0; i < item._relativeElements.length; i++) {
          var el = $(item._relativeElements[i])[0];
          if (el === e.target || $.contains(el, e.target)) {
            return;
          }
        }
        // 到这里，判断触发了元素的 blur 事件，隐藏元素
        item.hide();
      });
    }
    // 从数组中删除对应元素
  function erase(target, array) {
    for (var i = 0; i < array.length; i++) {
      if (target === array[i]) {
        array.splice(i, 1);
        return array;
      }
    }
  }
});
define("position/1.1.0/index-debug", ["jquery"], function(require, exports, module) {
  // Position
  // --------
  // 定位工具组件，将一个 DOM 节点相对对另一个 DOM 节点进行定位操作。
  // 代码易改，人生难得
  var Position = exports,
    VIEWPORT = {
      _id: 'VIEWPORT',
      nodeType: 1
    },
    $ = require('jquery'),
    isPinFixed = false,
    ua = (window.navigator.userAgent || "").toLowerCase(),
    isIE6 = ua.indexOf("msie 6") !== -1;
  // 将目标元素相对于基准元素进行定位
  // 这是 Position 的基础方法，接收两个参数，分别描述了目标元素和基准元素的定位点
  Position.pin = function(pinObject, baseObject) {
    // 将两个参数转换成标准定位对象 { element: a, x: 0, y: 0 }
    pinObject = normalize(pinObject);
    baseObject = normalize(baseObject);
    // if pinObject.element is not present
    // https://github.com/aralejs/position/pull/11
    if (pinObject.element === VIEWPORT || pinObject.element._id === 'VIEWPORT') {
      return;
    }
    // 设定目标元素的 position 为绝对定位
    // 若元素的初始 position 不为 absolute，会影响元素的 display、宽高等属性
    var pinElement = $(pinObject.element);
    if (pinElement.css('position') !== 'fixed' || isIE6) {
      pinElement.css('position', 'absolute');
      isPinFixed = false;
    } else {
      // 定位 fixed 元素的标志位，下面有特殊处理
      isPinFixed = true;
    }
    // 将位置属性归一化为数值
    // 注：必须放在上面这句 `css('position', 'absolute')` 之后，
    //    否则获取的宽高有可能不对
    posConverter(pinObject);
    posConverter(baseObject);
    var parentOffset = getParentOffset(pinElement);
    var baseOffset = baseObject.offset();
    // 计算目标元素的位置
    var top = baseOffset.top + baseObject.y - pinObject.y - parentOffset.top;
    var left = baseOffset.left + baseObject.x - pinObject.x - parentOffset.left;
    // 定位目标元素
    pinElement.css({
      left: left,
      top: top
    });
  };
  // 将目标元素相对于基准元素进行居中定位
  // 接受两个参数，分别为目标元素和定位的基准元素，都是 DOM 节点类型
  Position.center = function(pinElement, baseElement) {
    Position.pin({
      element: pinElement,
      x: '50%',
      y: '50%'
    }, {
      element: baseElement,
      x: '50%',
      y: '50%'
    });
  };
  // 这是当前可视区域的伪 DOM 节点
  // 需要相对于当前可视区域定位时，可传入此对象作为 element 参数
  Position.VIEWPORT = VIEWPORT;
  // Helpers
  // -------
  // 将参数包装成标准的定位对象，形似 { element: a, x: 0, y: 0 }
  function normalize(posObject) {
      posObject = toElement(posObject) || {};
      if (posObject.nodeType) {
        posObject = {
          element: posObject
        };
      }
      var element = toElement(posObject.element) || VIEWPORT;
      if (element.nodeType !== 1) {
        throw new Error('posObject.element is invalid.');
      }
      var result = {
        element: element,
        x: posObject.x || 0,
        y: posObject.y || 0
      };
      // config 的深度克隆会替换掉 Position.VIEWPORT, 导致直接比较为 false
      var isVIEWPORT = (element === VIEWPORT || element._id === 'VIEWPORT');
      // 归一化 offset
      result.offset = function() {
        // 若定位 fixed 元素，则父元素的 offset 没有意义
        if (isPinFixed) {
          return {
            left: 0,
            top: 0
          };
        } else if (isVIEWPORT) {
          return {
            left: $(document).scrollLeft(),
            top: $(document).scrollTop()
          };
        } else {
          return getOffset($(element)[0]);
        }
      };
      // 归一化 size, 含 padding 和 border
      result.size = function() {
        var el = isVIEWPORT ? $(window) : $(element);
        return {
          width: el.outerWidth(),
          height: el.outerHeight()
        };
      };
      return result;
    }
    // 对 x, y 两个参数为 left|center|right|%|px 时的处理，全部处理为纯数字
  function posConverter(pinObject) {
      pinObject.x = xyConverter(pinObject.x, pinObject, 'width');
      pinObject.y = xyConverter(pinObject.y, pinObject, 'height');
    }
    // 处理 x, y 值，都转化为数字
  function xyConverter(x, pinObject, type) {
      // 先转成字符串再说！好处理
      x = x + '';
      // 处理 px
      x = x.replace(/px/gi, '');
      // 处理 alias
      if (/\D/.test(x)) {
        x = x.replace(/(?:top|left)/gi, '0%').replace(/center/gi, '50%').replace(/(?:bottom|right)/gi, '100%');
      }
      // 将百分比转为像素值
      if (x.indexOf('%') !== -1) {
        //支持小数
        x = x.replace(/(\d+(?:\.\d+)?)%/gi, function(m, d) {
          return pinObject.size()[type] * (d / 100.0);
        });
      }
      // 处理类似 100%+20px 的情况
      if (/[+\-*\/]/.test(x)) {
        try {
          // eval 会影响压缩
          // new Function 方法效率高于 for 循环拆字符串的方法
          // 参照：http://jsperf.com/eval-newfunction-for
          x = (new Function('return ' + x))();
        } catch (e) {
          throw new Error('Invalid position value: ' + x);
        }
      }
      // 转回为数字
      return numberize(x);
    }
    // 获取 offsetParent 的位置
  function getParentOffset(element) {
    var parent = element.offsetParent();
    // IE7 下，body 子节点的 offsetParent 为 html 元素，其 offset 为
    // { top: 2, left: 2 }，会导致定位差 2 像素，所以这里将 parent
    // 转为 document.body
    if (parent[0] === document.documentElement) {
      parent = $(document.body);
    }
    // 修正 ie6 下 absolute 定位不准的 bug
    if (isIE6) {
      parent.css('zoom', 1);
    }
    // 获取 offsetParent 的 offset
    var offset;
    // 当 offsetParent 为 body，
    // 而且 body 的 position 是 static 时
    // 元素并不按照 body 来定位，而是按 document 定位
    // http://jsfiddle.net/afc163/hN9Tc/2/
    // 因此这里的偏移值直接设为 0 0
    if (parent[0] === document.body && parent.css('position') === 'static') {
      offset = {
        top: 0,
        left: 0
      };
    } else {
      offset = getOffset(parent[0]);
    }
    // 根据基准元素 offsetParent 的 border 宽度，来修正 offsetParent 的基准位置
    offset.top += numberize(parent.css('border-top-width'));
    offset.left += numberize(parent.css('border-left-width'));
    return offset;
  }

  function numberize(s) {
    return parseFloat(s, 10) || 0;
  }

  function toElement(element) {
      return $(element)[0];
    }
    // fix jQuery 1.7.2 offset
    // document.body 的 position 是 absolute 或 relative 时
    // jQuery.offset 方法无法正确获取 body 的偏移值
    //   -> http://jsfiddle.net/afc163/gMAcp/
    // jQuery 1.9.1 已经修正了这个问题
    //   -> http://jsfiddle.net/afc163/gMAcp/1/
    // 这里先实现一份
    // 参照 kissy 和 jquery 1.9.1
    //   -> https://github.com/kissyteam/kissy/blob/master/src/dom/sub-modules/base/src/base/offset.js#L366
    //   -> https://github.com/jquery/jquery/blob/1.9.1/src/offset.js#L28
  function getOffset(element) {
    var box = element.getBoundingClientRect(),
      docElem = document.documentElement;
    // < ie8 不支持 win.pageXOffset, 则使用 docElem.scrollLeft
    return {
      left: box.left + (window.pageXOffset || docElem.scrollLeft) - (docElem.clientLeft || document.body.clientLeft || 0),
      top: box.top + (window.pageYOffset || docElem.scrollTop) - (docElem.clientTop || document.body.clientTop || 0)
    };
  }
});
define("arale-iframe-shim/1.1.0/index-debug", ["jquery"], function(require, exports, module) {
  var $ = require('jquery');
  var Position = require("position/1.1.0/index-debug");
  var isIE6 = (window.navigator.userAgent || '').toLowerCase().indexOf('msie 6') !== -1;
  // target 是需要添加垫片的目标元素，可以传 `DOM Element` 或 `Selector`
  function Shim(target) {
      // 如果选择器选了多个 DOM，则只取第一个
      this.target = $(target).eq(0);
    }
    // 根据目标元素计算 iframe 的显隐、宽高、定位
  Shim.prototype.sync = function() {
    var target = this.target;
    var iframe = this.iframe;
    // 如果未传 target 则不处理
    if (!target.length) return this;
    var height = target.outerHeight();
    var width = target.outerWidth();
    // 如果目标元素隐藏，则 iframe 也隐藏
    // jquery 判断宽高同时为 0 才算隐藏，这里判断宽高其中一个为 0 就隐藏
    // http://api.jquery.com/hidden-selector/
    if (!height || !width || target.is(':hidden')) {
      iframe && iframe.hide();
    } else {
      // 第一次显示时才创建：as lazy as possible
      iframe || (iframe = this.iframe = createIframe(target));
      iframe.css({
        'height': height,
        'width': width
      });
      Position.pin(iframe[0], target[0]);
      iframe.show();
    }
    return this;
  };
  // 销毁 iframe 等
  Shim.prototype.destroy = function() {
    if (this.iframe) {
      this.iframe.remove();
      delete this.iframe;
    }
    delete this.target;
  };
  if (isIE6) {
    module.exports = Shim;
  } else {
    // 除了 IE6 都返回空函数
    function Noop() {}
    Noop.prototype.sync = function() {
      return this
    };
    Noop.prototype.destroy = Noop;
    module.exports = Noop;
  }
  // Helpers
  // 在 target 之前创建 iframe，这样就没有 z-index 问题
  // iframe 永远在 target 下方
  function createIframe(target) {
    var css = {
      display: 'none',
      border: 'none',
      opacity: 0,
      position: 'absolute'
    };
    // 如果 target 存在 zIndex 则设置
    var zIndex = target.css('zIndex');
    if (zIndex && zIndex > 0) {
      css.zIndex = zIndex - 1;
    }
    return $('<iframe>', {
      src: 'javascript:\'\'', // 不加的话，https 下会弹警告
      frameborder: 0,
      css: css
    }).insertBefore(target);
  }
});
define("arale-overlay/1.2.0/src/mask-debug", ["jquery"], function(require, exports, module) {
  var $ = require("jquery"),
    Overlay = require("arale-overlay/1.2.0/src/overlay-debug"),
    ua = (window.navigator.userAgent || "").toLowerCase(),
    isIE6 = ua.indexOf("msie 6") !== -1,
    body = $(document.body),
    doc = $(document);
  // Mask
  // ----------
  // 全屏遮罩层组件
  var Mask = Overlay.extend({
    attrs: {
      width: isIE6 ? doc.outerWidth(true) : '100%',
      height: isIE6 ? doc.outerHeight(true) : '100%',
      className: 'ui-mask',
      opacity: 0.2,
      backgroundColor: '#000',
      style: {
        position: isIE6 ? 'absolute' : 'fixed',
        top: 0,
        left: 0
      },
      align: {
        // undefined 表示相对于当前可视范围定位
        baseElement: isIE6 ? body : undefined
      }
    },
    show: function() {
      if (isIE6) {
        this.set('width', doc.outerWidth(true));
        this.set('height', doc.outerHeight(true));
      }
      return Mask.superclass.show.call(this);
    },
    _onRenderBackgroundColor: function(val) {
      this.element.css('backgroundColor', val);
    },
    _onRenderOpacity: function(val) {
      this.element.css('opacity', val);
    }
  });
  // 单例
  module.exports = new Mask();
});
define("arale-autocomplete/1.4.1/autocomplete-debug", ["jquery"], function(require, exports, module) {
  module.exports = require("arale-autocomplete/1.4.1/src/autocomplete-debug");
});
define("arale-autocomplete/1.4.1/src/autocomplete-debug", ["jquery"], function(require, exports, module) {
  var $ = require('jquery');
  var Overlay = require("arale-overlay/1.2.0/overlay-debug");
  var Templatable = require("arale-templatable/0.10.0/src/templatable-debug");
  var DataSource = require("arale-autocomplete/1.4.1/src/data-source-debug");
  var Filter = require("arale-autocomplete/1.4.1/src/filter-debug");
  var Input = require("arale-autocomplete/1.4.1/src/input-debug");
  var IE678 = /\bMSIE [678]\.0\b/.test(navigator.userAgent);
  var template = require("arale-autocomplete/1.4.1/src/autocomplete-debug.handlebars");
  var AutoComplete = Overlay.extend({
    Implements: Templatable,
    attrs: {
      // 触发元素
      trigger: null,
      classPrefix: 'ui-select',
      align: {
        baseXY: [0, '100%']
      },
      submitOnEnter: true,
      // 回车是否会提交表单
      dataSource: { //数据源，支持 Array, URL, Object, Function
        value: [],
        getter: function(val) {
          var that = this;
          if ($.isFunction(val)) {
            return function() {
              return val.apply(that, arguments);
            };
          }
          return val;
        }
      },
      locator: 'data',
      // 输出过滤
      filter: null,
      disabled: false,
      selectFirst: false,
      delay: 100,
      // 以下为模板相关
      model: {
        value: {
          items: []
        },
        getter: function(val) {
          val.classPrefix || (val.classPrefix = this.get('classPrefix'));
          return val;
        }
      },
      template: template,
      footer: '',
      header: '',
      html: '{{{label}}}',
      // 以下仅为组件使用
      selectedIndex: null,
      data: []
    },
    events: {
      'mousedown [data-role=items]': '_handleMouseDown',
      'click [data-role=item]': '_handleSelection',
      'mouseenter [data-role=item]': '_handleMouseMove',
      'mouseleave [data-role=item]': '_handleMouseMove'
    },
    templateHelpers: {
      // 将匹配的高亮文字加上 hl 的样式
      highlightItem: highlightItem,
      include: include
    },
    parseElement: function() {
      var that = this;
      this.templatePartials || (this.templatePartials = {});
      $.each(['header', 'footer', 'html'], function(index, item) {
        that.templatePartials[item] = that.get(item);
      });
      AutoComplete.superclass.parseElement.call(this);
    },
    setup: function() {
      AutoComplete.superclass.setup.call(this);
      this._isOpen = false;
      this._initInput(); // 初始化输入框
      this._initDataSource(); // 初始化数据源
      this._initFilter(); // 初始化过滤器
      this._bindHandle(); // 绑定事件
      this._blurHide([$(this.get('trigger'))]);
      this._tweakAlignDefaultValue();
      this.on('indexChanged', function(index) {
        // scroll current item into view
        //this.currentItem.scrollIntoView();
        var containerHeight = parseInt(this.get('height'), 10);
        if (!containerHeight) return;
        var itemHeight = this.items.parent().height() / this.items.length,
          itemTop = Math.max(0, itemHeight * (index + 1) - containerHeight);
        this.element.children().scrollTop(itemTop);
      });
    },
    show: function() {
      this._isOpen = true;
      // 无数据则不显示
      if (this._isEmpty()) return;
      AutoComplete.superclass.show.call(this);
    },
    hide: function() {
      // 隐藏的时候取消请求或回调
      if (this._timeout) clearTimeout(this._timeout);
      this.dataSource.abort();
      this._hide();
    },
    destroy: function() {
      this._clear();
      if (this.input) {
        this.input.destroy();
        this.input = null;
      }
      AutoComplete.superclass.destroy.call(this);
    },
    // Public Methods
    // --------------
    selectItem: function(index) {
      if (this.items) {
        if (index && this.items.length > index && index >= -1) {
          this.set('selectedIndex', index);
        }
        this._handleSelection();
      }
    },
    setInputValue: function(val) {
      this.input.setValue(val);
    },
    // Private Methods
    // ---------------
    // 数据源返回，过滤数据
    _filterData: function(data) {
      var filter = this.get('filter'),
        locator = this.get('locator');
      // 获取目标数据
      data = locateResult(locator, data);
      // 进行过滤
      data = filter.call(this, normalize(data), this.input.get('query'));
      this.set('data', data);
    },
    // 通过数据渲染模板
    _onRenderData: function(data) {
      data || (data = []);
      // 渲染下拉
      this.set('model', {
        items: data,
        query: this.input.get('query'),
        length: data.length
      });
      this.renderPartial();
      // 初始化下拉的状态
      this.items = this.$('[data-role=items]').children();
      if (this.get('selectFirst')) {
        this.set('selectedIndex', 0);
      }
      // 选中后会修改 input 的值并触发下一次渲染，但第二次渲染的结果不应该显示出来。
      this._isOpen && this.show();
    },
    // 键盘控制上下移动
    _onRenderSelectedIndex: function(index) {
      var hoverClass = this.get('classPrefix') + '-item-hover';
      this.items && this.items.removeClass(hoverClass);
      // -1 什么都不选
      if (index === -1) return;
      this.items.eq(index).addClass(hoverClass);
      this.trigger('indexChanged', index, this.lastIndex);
      this.lastIndex = index;
    },
    // 初始化
    // ------------
    _initDataSource: function() {
      this.dataSource = new DataSource({
        source: this.get('dataSource')
      });
    },
    _initInput: function() {
      this.input = new Input({
        element: this.get('trigger'),
        delay: this.get('delay')
      });
    },
    _initFilter: function() {
      var filter = this.get('filter');
      filter = initFilter(filter, this.dataSource);
      this.set('filter', filter);
    },
    // 事件绑定
    // ------------
    _bindHandle: function() {
      this.dataSource.on('data', this._filterData, this);
      this.input.on('blur', this.hide, this).on('focus', this._handleFocus, this).on('keyEnter', this._handleSelection, this).on('keyEsc', this.hide, this).on('keyUp keyDown', this.show, this).on('keyUp keyDown', this._handleStep, this).on('queryChanged', this._clear, this).on('queryChanged', this._hide, this).on('queryChanged', this._handleQueryChange, this).on('queryChanged', this.show, this);
      this.after('hide', function() {
        this.set('selectedIndex', -1);
      });
      // 选中后隐藏浮层
      this.on('itemSelected', function() {
        this._hide();
      });
    },
    // 选中的处理器
    // 1. 鼠标点击触发
    // 2. 回车触发
    // 3. selectItem 触发
    _handleSelection: function(e) {
      if (!this.items) return;
      var isMouse = e ? e.type === 'click' : false;
      var index = isMouse ? this.items.index(e.currentTarget) : this.get('selectedIndex');
      var item = this.items.eq(index);
      var data = this.get('data')[index];
      if (index >= 0 && item && data) {
        this.input.setValue(data.label);
        this.set('selectedIndex', index, {
          silent: true
        });
        // 是否阻止回车提交表单
        if (e && !isMouse && !this.get('submitOnEnter')) e.preventDefault();
        this.trigger('itemSelected', data, item);
      }
    },
    _handleFocus: function() {
      this._isOpen = true;
    },
    _handleMouseMove: function(e) {
      var hoverClass = this.get('classPrefix') + '-item-hover';
      this.items.removeClass(hoverClass);
      if (e.type === 'mouseenter') {
        var index = this.items.index(e.currentTarget);
        this.set('selectedIndex', index, {
          silent: true
        });
        this.items.eq(index).addClass(hoverClass);
      }
    },
    _handleMouseDown: function(e) {
      if (IE678) {
        var trigger = this.input.get('element')[0];
        trigger.onbeforedeactivate = function() {
          window.event.returnValue = false;
          trigger.onbeforedeactivate = null;
        };
      }
      e.preventDefault();
    },
    _handleStep: function(e) {
      e.preventDefault();
      this.get('visible') && this._step(e.type === 'keyUp' ? -1 : 1);
    },
    _handleQueryChange: function(val, prev) {
      if (this.get('disabled')) return;
      this.dataSource.abort();
      this.dataSource.getData(val);
    },
    // 选项上下移动
    _step: function(direction) {
      var currentIndex = this.get('selectedIndex');
      if (direction === -1) { // 反向
        if (currentIndex > -1) {
          this.set('selectedIndex', currentIndex - 1);
        } else {
          this.set('selectedIndex', this.items.length - 1);
        }
      } else if (direction === 1) { // 正向
        if (currentIndex < this.items.length - 1) {
          this.set('selectedIndex', currentIndex + 1);
        } else {
          this.set('selectedIndex', -1);
        }
      }
    },
    _clear: function() {
      this.$('[data-role=items]').empty();
      this.set('selectedIndex', -1);
      delete this.items;
      delete this.lastIndex;
    },
    _hide: function() {
      this._isOpen = false;
      AutoComplete.superclass.hide.call(this);
    },
    _isEmpty: function() {
      var data = this.get('data');
      return !(data && data.length > 0);
    },
    // 调整 align 属性的默认值
    _tweakAlignDefaultValue: function() {
      var align = this.get('align');
      align.baseElement = this.get('trigger');
      this.set('align', align);
    }
  });
  module.exports = AutoComplete;

  function isString(str) {
    return Object.prototype.toString.call(str) === '[object String]';
  }

  function isObject(obj) {
      return Object.prototype.toString.call(obj) === '[object Object]';
    }
    // 通过 locator 找到 data 中的某个属性的值
    // 1. locator 支持 function，函数返回值为结果
    // 2. locator 支持 string，而且支持点操作符寻址
    //     data {
    //       a: {
    //         b: 'c'
    //       }
    //     }
    //     locator 'a.b'
    // 最后的返回值为 c
  function locateResult(locator, data) {
      if (locator) {
        if ($.isFunction(locator)) {
          return locator.call(this, data);
        } else if (!$.isArray(data) && isString(locator)) {
          var s = locator.split('.'),
            p = data;
          while (s.length) {
            var v = s.shift();
            if (!p[v]) {
              break;
            }
            p = p[v];
          }
          return p;
        }
      }
      return data;
    }
    // 标准格式，不匹配则忽略
    //
    //   {
    //     label: '', 显示的字段
    //     value: '', 匹配的字段
    //     alias: []  其他匹配的字段
    //   }
  function normalize(data) {
      var result = [];
      $.each(data, function(index, item) {
        if (isString(item)) {
          result.push({
            label: item,
            value: item,
            alias: []
          });
        } else if (isObject(item)) {
          if (!item.value && !item.label) return;
          item.value || (item.value = item.label);
          item.label || (item.label = item.value);
          item.alias || (item.alias = []);
          result.push(item);
        }
      });
      return result;
    }
    // 初始化 filter
    // 支持的格式
    //   1. null: 使用默认的 startsWith
    //   2. string: 从 Filter 中找，如果不存在则用 default
    //   3. function: 自定义
  function initFilter(filter, dataSource) {
    // 字符串
    if (isString(filter)) {
      // 从组件内置的 FILTER 获取
      if (Filter[filter]) {
        filter = Filter[filter];
      } else {
        filter = Filter['default'];
      }
    }
    // 非函数为默认值
    else if (!$.isFunction(filter)) {
      // 异步请求的时候不需要过滤器
      if (dataSource.get('type') === 'url') {
        filter = Filter['default'];
      } else {
        filter = Filter['startsWith'];
      }
    }
    return filter;
  }

  function include(options) {
    var context = {};
    mergeContext(this);
    mergeContext(options.hash);
    return options.fn(context);

    function mergeContext(obj) {
      for (var k in obj) context[k] = obj[k];
    }
  }

  function highlightItem(label) {
    var index = this.highlightIndex,
      classPrefix = this.parent ? this.parent.classPrefix : '',
      cursor = 0,
      v = label || this.label || '',
      h = '';
    if ($.isArray(index)) {
      for (var i = 0, l = index.length; i < l; i++) {
        var j = index[i],
          start, length;
        if ($.isArray(j)) {
          start = j[0];
          length = j[1] - j[0];
        } else {
          start = j;
          length = 1;
        }
        if (start > cursor) {
          h += v.substring(cursor, start);
        }
        if (start < v.length) {
          var className = classPrefix ? ('class="' + classPrefix + '-item-hl"') : '';
          h += '<span ' + className + '>' + v.substr(start, length) + '</span>';
        }
        cursor = start + length;
        if (cursor >= v.length) {
          break;
        }
      }
      if (v.length > cursor) {
        h += v.substring(cursor, v.length);
      }
      return h;
    }
    return v;
  }
});
define("arale-templatable/0.10.0/src/templatable-debug", ["jquery"], function(require, exports, module) {
  var $ = require('jquery');
  var Handlebars = require("handlebars/1.3.0/dist/cjs/handlebars-debug")['default'];
  var compiledTemplates = {};
  // 提供 Template 模板支持，默认引擎是 Handlebars
  module.exports = {
    // Handlebars 的 helpers
    templateHelpers: null,
    // Handlebars 的 partials
    templatePartials: null,
    // template 对应的 DOM-like object
    templateObject: null,
    // 根据配置的模板和传入的数据，构建 this.element 和 templateElement
    parseElementFromTemplate: function() {
      // template 支持 id 选择器
      var t, template = this.get('template');
      if (/^#/.test(template) && (t = document.getElementById(template.substring(1)))) {
        template = t.innerHTML;
        this.set('template', template);
      }
      this.templateObject = convertTemplateToObject(template);
      this.element = $(this.compile());
    },
    // 编译模板，混入数据，返回 html 结果
    compile: function(template, model) {
      template || (template = this.get('template'));
      model || (model = this.get('model')) || (model = {});
      if (model.toJSON) {
        model = model.toJSON();
      }
      // handlebars runtime，注意 partials 也需要预编译
      if (isFunction(template)) {
        return template(model, {
          helpers: this.templateHelpers,
          partials: precompile(this.templatePartials)
        });
      } else {
        var helpers = this.templateHelpers;
        var partials = this.templatePartials;
        var helper, partial;
        // 注册 helpers
        if (helpers) {
          for (helper in helpers) {
            if (helpers.hasOwnProperty(helper)) {
              Handlebars.registerHelper(helper, helpers[helper]);
            }
          }
        }
        // 注册 partials
        if (partials) {
          for (partial in partials) {
            if (partials.hasOwnProperty(partial)) {
              Handlebars.registerPartial(partial, partials[partial]);
            }
          }
        }
        var compiledTemplate = compiledTemplates[template];
        if (!compiledTemplate) {
          compiledTemplate = compiledTemplates[template] = Handlebars.compile(template);
        }
        // 生成 html
        var html = compiledTemplate(model);
        // 卸载 helpers
        if (helpers) {
          for (helper in helpers) {
            if (helpers.hasOwnProperty(helper)) {
              delete Handlebars.helpers[helper];
            }
          }
        }
        // 卸载 partials
        if (partials) {
          for (partial in partials) {
            if (partials.hasOwnProperty(partial)) {
              delete Handlebars.partials[partial];
            }
          }
        }
        return html;
      }
    },
    // 刷新 selector 指定的局部区域
    renderPartial: function(selector) {
      if (this.templateObject) {
        var template = convertObjectToTemplate(this.templateObject, selector);
        if (template) {
          if (selector) {
            this.$(selector).html(this.compile(template));
          } else {
            this.element.html(this.compile(template));
          }
        } else {
          this.element.html(this.compile());
        }
      }
      // 如果 template 已经编译过了，templateObject 不存在
      else {
        var all = $(this.compile());
        var selected = all.find(selector);
        if (selected.length) {
          this.$(selector).html(selected.html());
        } else {
          this.element.html(all.html());
        }
      }
      return this;
    }
  };
  // Helpers
  // -------
  var _compile = Handlebars.compile;
  Handlebars.compile = function(template) {
    return isFunction(template) ? template : _compile.call(Handlebars, template);
  };
  // 将 template 字符串转换成对应的 DOM-like object
  function convertTemplateToObject(template) {
      return isFunction(template) ? null : $(encode(template));
    }
    // 根据 selector 得到 DOM-like template object，并转换为 template 字符串
  function convertObjectToTemplate(templateObject, selector) {
    if (!templateObject) return;
    var element;
    if (selector) {
      element = templateObject.find(selector);
      if (element.length === 0) {
        throw new Error('Invalid template selector: ' + selector);
      }
    } else {
      element = templateObject;
    }
    return decode(element.html());
  }

  function encode(template) {
    return template
      // 替换 {{xxx}} 为 <!-- {{xxx}} -->
      .replace(/({[^}]+}})/g, '<!--$1-->')
      // 替换 src="{{xxx}}" 为 data-TEMPLATABLE-src="{{xxx}}"
      .replace(/\s(src|href)\s*=\s*(['"])(.*?\{.+?)\2/g, ' data-templatable-$1=$2$3$2');
  }

  function decode(template) {
    return template.replace(/(?:<|&lt;)!--({{[^}]+}})--(?:>|&gt;)/g, '$1').replace(/data-templatable-/ig, '');
  }

  function isFunction(obj) {
    return typeof obj === "function";
  }

  function precompile(partials) {
    if (!partials) return {};
    var result = {};
    for (var name in partials) {
      var partial = partials[name];
      result[name] = isFunction(partial) ? partial : Handlebars.compile(partial);
    }
    return result;
  };
  // 调用 renderPartial 时，Templatable 对模板有一个约束：
  // ** template 自身必须是有效的 html 代码片段**，比如
  //   1. 代码闭合
  //   2. 嵌套符合规范
  //
  // 总之，要保证在 template 里，将 `{{...}}` 转换成注释后，直接 innerHTML 插入到
  // DOM 中，浏览器不会自动增加一些东西。比如：
  //
  // tbody 里没有 tr：
  //  `<table><tbody>{{#each items}}<td>{{this}}</td>{{/each}}</tbody></table>`
  //
  // 标签不闭合：
  //  `<div><span>{{name}}</div>`
});
define("handlebars/1.3.0/dist/cjs/handlebars-debug", [], function(require, exports, module) {
  "use strict";
  /*globals Handlebars: true */
  var Handlebars = require("handlebars/1.3.0/dist/cjs/handlebars.runtime-debug")["default"];
  // Compiler imports
  var AST = require("handlebars/1.3.0/dist/cjs/handlebars/compiler/ast-debug")["default"];
  var Parser = require("handlebars/1.3.0/dist/cjs/handlebars/compiler/base-debug").parser;
  var parse = require("handlebars/1.3.0/dist/cjs/handlebars/compiler/base-debug").parse;
  var Compiler = require("handlebars/1.3.0/dist/cjs/handlebars/compiler/compiler-debug").Compiler;
  var compile = require("handlebars/1.3.0/dist/cjs/handlebars/compiler/compiler-debug").compile;
  var precompile = require("handlebars/1.3.0/dist/cjs/handlebars/compiler/compiler-debug").precompile;
  var JavaScriptCompiler = require("handlebars/1.3.0/dist/cjs/handlebars/compiler/javascript-compiler-debug")["default"];
  var _create = Handlebars.create;
  var create = function() {
    var hb = _create();
    hb.compile = function(input, options) {
      return compile(input, options, hb);
    };
    hb.precompile = function(input, options) {
      return precompile(input, options, hb);
    };
    hb.AST = AST;
    hb.Compiler = Compiler;
    hb.JavaScriptCompiler = JavaScriptCompiler;
    hb.Parser = Parser;
    hb.parse = parse;
    return hb;
  };
  Handlebars = create();
  Handlebars.create = create;
  exports["default"] = Handlebars;
});
define("handlebars/1.3.0/dist/cjs/handlebars.runtime-debug", [], function(require, exports, module) {
  "use strict";
  /*globals Handlebars: true */
  var base = require("handlebars/1.3.0/dist/cjs/handlebars/base-debug");
  // Each of these augment the Handlebars object. No need to setup here.
  // (This is done to easily share code between commonjs and browse envs)
  var SafeString = require("handlebars/1.3.0/dist/cjs/handlebars/safe-string-debug")["default"];
  var Exception = require("handlebars/1.3.0/dist/cjs/handlebars/exception-debug")["default"];
  var Utils = require("handlebars/1.3.0/dist/cjs/handlebars/utils-debug");
  var runtime = require("handlebars/1.3.0/dist/cjs/handlebars/runtime-debug");
  // For compatibility and usage outside of module systems, make the Handlebars object a namespace
  var create = function() {
    var hb = new base.HandlebarsEnvironment();
    Utils.extend(hb, base);
    hb.SafeString = SafeString;
    hb.Exception = Exception;
    hb.Utils = Utils;
    hb.VM = runtime;
    hb.template = function(spec) {
      return runtime.template(spec, hb);
    };
    return hb;
  };
  var Handlebars = create();
  Handlebars.create = create;
  exports["default"] = Handlebars;
});
define("handlebars/1.3.0/dist/cjs/handlebars/base-debug", [], function(require, exports, module) {
  "use strict";
  var Utils = require("handlebars/1.3.0/dist/cjs/handlebars/utils-debug");
  var Exception = require("handlebars/1.3.0/dist/cjs/handlebars/exception-debug")["default"];
  var VERSION = "1.3.0";
  exports.VERSION = VERSION;
  var COMPILER_REVISION = 4;
  exports.COMPILER_REVISION = COMPILER_REVISION;
  var REVISION_CHANGES = {
    1: '<= 1.0.rc.2', // 1.0.rc.2 is actually rev2 but doesn't report it
    2: '== 1.0.0-rc.3',
    3: '== 1.0.0-rc.4',
    4: '>= 1.0.0'
  };
  exports.REVISION_CHANGES = REVISION_CHANGES;
  var isArray = Utils.isArray,
    isFunction = Utils.isFunction,
    toString = Utils.toString,
    objectType = '[object Object]';

  function HandlebarsEnvironment(helpers, partials) {
    this.helpers = helpers || {};
    this.partials = partials || {};
    registerDefaultHelpers(this);
  }
  exports.HandlebarsEnvironment = HandlebarsEnvironment;
  HandlebarsEnvironment.prototype = {
    constructor: HandlebarsEnvironment,
    logger: logger,
    log: log,
    registerHelper: function(name, fn, inverse) {
      if (toString.call(name) === objectType) {
        if (inverse || fn) {
          throw new Exception('Arg not supported with multiple helpers');
        }
        Utils.extend(this.helpers, name);
      } else {
        if (inverse) {
          fn.not = inverse;
        }
        this.helpers[name] = fn;
      }
    },
    registerPartial: function(name, str) {
      if (toString.call(name) === objectType) {
        Utils.extend(this.partials, name);
      } else {
        this.partials[name] = str;
      }
    }
  };

  function registerDefaultHelpers(instance) {
    instance.registerHelper('helperMissing', function(arg) {
      if (arguments.length === 2) {
        return undefined;
      } else {
        throw new Exception("Missing helper: '" + arg + "'");
      }
    });
    instance.registerHelper('blockHelperMissing', function(context, options) {
      var inverse = options.inverse || function() {},
        fn = options.fn;
      if (isFunction(context)) {
        context = context.call(this);
      }
      if (context === true) {
        return fn(this);
      } else if (context === false || context == null) {
        return inverse(this);
      } else if (isArray(context)) {
        if (context.length > 0) {
          return instance.helpers.each(context, options);
        } else {
          return inverse(this);
        }
      } else {
        return fn(context);
      }
    });
    instance.registerHelper('each', function(context, options) {
      var fn = options.fn,
        inverse = options.inverse;
      var i = 0,
        ret = "",
        data;
      if (isFunction(context)) {
        context = context.call(this);
      }
      if (options.data) {
        data = createFrame(options.data);
      }
      if (context && typeof context === 'object') {
        if (isArray(context)) {
          for (var j = context.length; i < j; i++) {
            if (data) {
              data.index = i;
              data.first = (i === 0);
              data.last = (i === (context.length - 1));
            }
            ret = ret + fn(context[i], {
              data: data
            });
          }
        } else {
          for (var key in context) {
            if (context.hasOwnProperty(key)) {
              if (data) {
                data.key = key;
                data.index = i;
                data.first = (i === 0);
              }
              ret = ret + fn(context[key], {
                data: data
              });
              i++;
            }
          }
        }
      }
      if (i === 0) {
        ret = inverse(this);
      }
      return ret;
    });
    instance.registerHelper('if', function(conditional, options) {
      if (isFunction(conditional)) {
        conditional = conditional.call(this);
      }
      // Default behavior is to render the positive path if the value is truthy and not empty.
      // The `includeZero` option may be set to treat the condtional as purely not empty based on the
      // behavior of isEmpty. Effectively this determines if 0 is handled by the positive path or negative.
      if ((!options.hash.includeZero && !conditional) || Utils.isEmpty(conditional)) {
        return options.inverse(this);
      } else {
        return options.fn(this);
      }
    });
    instance.registerHelper('unless', function(conditional, options) {
      return instance.helpers['if'].call(this, conditional, {
        fn: options.inverse,
        inverse: options.fn,
        hash: options.hash
      });
    });
    instance.registerHelper('with', function(context, options) {
      if (isFunction(context)) {
        context = context.call(this);
      }
      if (!Utils.isEmpty(context)) return options.fn(context);
    });
    instance.registerHelper('log', function(context, options) {
      var level = options.data && options.data.level != null ? parseInt(options.data.level, 10) : 1;
      instance.log(level, context);
    });
  }
  var logger = {
    methodMap: {
      0: 'debug',
      1: 'info',
      2: 'warn',
      3: 'error'
    },
    // State enum
    DEBUG: 0,
    INFO: 1,
    WARN: 2,
    ERROR: 3,
    level: 3,
    // can be overridden in the host environment
    log: function(level, obj) {
      if (logger.level <= level) {
        var method = logger.methodMap[level];
        if (typeof console !== 'undefined' && console[method]) {
          console[method].call(console, obj);
        }
      }
    }
  };
  exports.logger = logger;

  function log(level, obj) {
    logger.log(level, obj);
  }
  exports.log = log;
  var createFrame = function(object) {
    var obj = {};
    Utils.extend(obj, object);
    return obj;
  };
  exports.createFrame = createFrame;
});
define("handlebars/1.3.0/dist/cjs/handlebars/utils-debug", [], function(require, exports, module) {
  "use strict";
  /*jshint -W004 */
  var SafeString = require("handlebars/1.3.0/dist/cjs/handlebars/safe-string-debug")["default"];
  var escape = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#x27;",
    "`": "&#x60;"
  };
  var badChars = /[&<>"'`]/g;
  var possible = /[&<>"'`]/;

  function escapeChar(chr) {
    return escape[chr] || "&amp;";
  }

  function extend(obj, value) {
    for (var key in value) {
      if (Object.prototype.hasOwnProperty.call(value, key)) {
        obj[key] = value[key];
      }
    }
  }
  exports.extend = extend;
  var toString = Object.prototype.toString;
  exports.toString = toString;
  // Sourced from lodash
  // https://github.com/bestiejs/lodash/blob/master/LICENSE.txt
  var isFunction = function(value) {
    return typeof value === 'function';
  };
  // fallback for older versions of Chrome and Safari
  if (isFunction(/x/)) {
    isFunction = function(value) {
      return typeof value === 'function' && toString.call(value) === '[object Function]';
    };
  }
  var isFunction;
  exports.isFunction = isFunction;
  var isArray = Array.isArray || function(value) {
    return (value && typeof value === 'object') ? toString.call(value) === '[object Array]' : false;
  };
  exports.isArray = isArray;

  function escapeExpression(string) {
    // don't escape SafeStrings, since they're already safe
    if (string instanceof SafeString) {
      return string.toString();
    } else if (!string && string !== 0) {
      return "";
    }
    // Force a string conversion as this will be done by the append regardless and
    // the regex test will do this transparently behind the scenes, causing issues if
    // an object's to string has escaped characters in it.
    string = "" + string;
    if (!possible.test(string)) {
      return string;
    }
    return string.replace(badChars, escapeChar);
  }
  exports.escapeExpression = escapeExpression;

  function isEmpty(value) {
    if (!value && value !== 0) {
      return true;
    } else if (isArray(value) && value.length === 0) {
      return true;
    } else {
      return false;
    }
  }
  exports.isEmpty = isEmpty;
});
define("handlebars/1.3.0/dist/cjs/handlebars/safe-string-debug", [], function(require, exports, module) {
  "use strict";
  // Build out our basic SafeString type
  function SafeString(string) {
    this.string = string;
  }
  SafeString.prototype.toString = function() {
    return "" + this.string;
  };
  exports["default"] = SafeString;
});
define("handlebars/1.3.0/dist/cjs/handlebars/exception-debug", [], function(require, exports, module) {
  "use strict";
  var errorProps = ['description', 'fileName', 'lineNumber', 'message', 'name', 'number', 'stack'];

  function Exception(message, node) {
    var line;
    if (node && node.firstLine) {
      line = node.firstLine;
      message += ' - ' + line + ':' + node.firstColumn;
    }
    var tmp = Error.prototype.constructor.call(this, message);
    // Unfortunately errors are not enumerable in Chrome (at least), so `for prop in tmp` doesn't work.
    for (var idx = 0; idx < errorProps.length; idx++) {
      this[errorProps[idx]] = tmp[errorProps[idx]];
    }
    if (line) {
      this.lineNumber = line;
      this.column = node.firstColumn;
    }
  }
  Exception.prototype = new Error();
  exports["default"] = Exception;
});
define("handlebars/1.3.0/dist/cjs/handlebars/runtime-debug", [], function(require, exports, module) {
  "use strict";
  var Utils = require("handlebars/1.3.0/dist/cjs/handlebars/utils-debug");
  var Exception = require("handlebars/1.3.0/dist/cjs/handlebars/exception-debug")["default"];
  var COMPILER_REVISION = require("handlebars/1.3.0/dist/cjs/handlebars/base-debug").COMPILER_REVISION;
  var REVISION_CHANGES = require("handlebars/1.3.0/dist/cjs/handlebars/base-debug").REVISION_CHANGES;

  function checkRevision(compilerInfo) {
    var compilerRevision = compilerInfo && compilerInfo[0] || 1,
      currentRevision = COMPILER_REVISION;
    if (compilerRevision !== currentRevision) {
      if (compilerRevision < currentRevision) {
        var runtimeVersions = REVISION_CHANGES[currentRevision],
          compilerVersions = REVISION_CHANGES[compilerRevision];
        throw new Exception("Template was precompiled with an older version of Handlebars than the current runtime. " + "Please update your precompiler to a newer version (" + runtimeVersions + ") or downgrade your runtime to an older version (" + compilerVersions + ").");
      } else {
        // Use the embedded version info since the runtime doesn't know about this revision yet
        throw new Exception("Template was precompiled with a newer version of Handlebars than the current runtime. " + "Please update your runtime to a newer version (" + compilerInfo[1] + ").");
      }
    }
  }
  exports.checkRevision = checkRevision; // TODO: Remove this line and break up compilePartial
  function template(templateSpec, env) {
    if (!env) {
      throw new Exception("No environment passed to template");
    }
    // Note: Using env.VM references rather than local var references throughout this section to allow
    // for external users to override these as psuedo-supported APIs.
    var invokePartialWrapper = function(partial, name, context, helpers, partials, data) {
      var result = env.VM.invokePartial.apply(this, arguments);
      if (result != null) {
        return result;
      }
      if (env.compile) {
        var options = {
          helpers: helpers,
          partials: partials,
          data: data
        };
        partials[name] = env.compile(partial, {
          data: data !== undefined
        }, env);
        return partials[name](context, options);
      } else {
        throw new Exception("The partial " + name + " could not be compiled when running in runtime-only mode");
      }
    };
    // Just add water
    var container = {
      escapeExpression: Utils.escapeExpression,
      invokePartial: invokePartialWrapper,
      programs: [],
      program: function(i, fn, data) {
        var programWrapper = this.programs[i];
        if (data) {
          programWrapper = program(i, fn, data);
        } else if (!programWrapper) {
          programWrapper = this.programs[i] = program(i, fn);
        }
        return programWrapper;
      },
      merge: function(param, common) {
        var ret = param || common;
        if (param && common && (param !== common)) {
          ret = {};
          Utils.extend(ret, common);
          Utils.extend(ret, param);
        }
        return ret;
      },
      programWithDepth: env.VM.programWithDepth,
      noop: env.VM.noop,
      compilerInfo: null
    };
    return function(context, options) {
      options = options || {};
      var namespace = options.partial ? options : env,
        helpers,
        partials;
      if (!options.partial) {
        helpers = options.helpers;
        partials = options.partials;
      }
      var result = templateSpec.call(container, namespace, context, helpers, partials, options.data);
      if (!options.partial) {
        env.VM.checkRevision(container.compilerInfo);
      }
      return result;
    };
  }
  exports.template = template;

  function programWithDepth(i, fn, data /*, $depth */ ) {
    var args = Array.prototype.slice.call(arguments, 3);
    var prog = function(context, options) {
      options = options || {};
      return fn.apply(this, [context, options.data || data].concat(args));
    };
    prog.program = i;
    prog.depth = args.length;
    return prog;
  }
  exports.programWithDepth = programWithDepth;

  function program(i, fn, data) {
    var prog = function(context, options) {
      options = options || {};
      return fn(context, options.data || data);
    };
    prog.program = i;
    prog.depth = 0;
    return prog;
  }
  exports.program = program;

  function invokePartial(partial, name, context, helpers, partials, data) {
    var options = {
      partial: true,
      helpers: helpers,
      partials: partials,
      data: data
    };
    if (partial === undefined) {
      throw new Exception("The partial " + name + " could not be found");
    } else if (partial instanceof Function) {
      return partial(context, options);
    }
  }
  exports.invokePartial = invokePartial;

  function noop() {
    return "";
  }
  exports.noop = noop;
});
define("handlebars/1.3.0/dist/cjs/handlebars/compiler/ast-debug", [], function(require, exports, module) {
  "use strict";
  var Exception = require("handlebars/1.3.0/dist/cjs/handlebars/exception-debug")["default"];

  function LocationInfo(locInfo) {
    locInfo = locInfo || {};
    this.firstLine = locInfo.first_line;
    this.firstColumn = locInfo.first_column;
    this.lastColumn = locInfo.last_column;
    this.lastLine = locInfo.last_line;
  }
  var AST = {
    ProgramNode: function(statements, inverseStrip, inverse, locInfo) {
      var inverseLocationInfo, firstInverseNode;
      if (arguments.length === 3) {
        locInfo = inverse;
        inverse = null;
      } else if (arguments.length === 2) {
        locInfo = inverseStrip;
        inverseStrip = null;
      }
      LocationInfo.call(this, locInfo);
      this.type = "program";
      this.statements = statements;
      this.strip = {};
      if (inverse) {
        firstInverseNode = inverse[0];
        if (firstInverseNode) {
          inverseLocationInfo = {
            first_line: firstInverseNode.firstLine,
            last_line: firstInverseNode.lastLine,
            last_column: firstInverseNode.lastColumn,
            first_column: firstInverseNode.firstColumn
          };
          this.inverse = new AST.ProgramNode(inverse, inverseStrip, inverseLocationInfo);
        } else {
          this.inverse = new AST.ProgramNode(inverse, inverseStrip);
        }
        this.strip.right = inverseStrip.left;
      } else if (inverseStrip) {
        this.strip.left = inverseStrip.right;
      }
    },
    MustacheNode: function(rawParams, hash, open, strip, locInfo) {
      LocationInfo.call(this, locInfo);
      this.type = "mustache";
      this.strip = strip;
      // Open may be a string parsed from the parser or a passed boolean flag
      if (open != null && open.charAt) {
        // Must use charAt to support IE pre-10
        var escapeFlag = open.charAt(3) || open.charAt(2);
        this.escaped = escapeFlag !== '{' && escapeFlag !== '&';
      } else {
        this.escaped = !!open;
      }
      if (rawParams instanceof AST.SexprNode) {
        this.sexpr = rawParams;
      } else {
        // Support old AST API
        this.sexpr = new AST.SexprNode(rawParams, hash);
      }
      this.sexpr.isRoot = true;
      // Support old AST API that stored this info in MustacheNode
      this.id = this.sexpr.id;
      this.params = this.sexpr.params;
      this.hash = this.sexpr.hash;
      this.eligibleHelper = this.sexpr.eligibleHelper;
      this.isHelper = this.sexpr.isHelper;
    },
    SexprNode: function(rawParams, hash, locInfo) {
      LocationInfo.call(this, locInfo);
      this.type = "sexpr";
      this.hash = hash;
      var id = this.id = rawParams[0];
      var params = this.params = rawParams.slice(1);
      // a mustache is an eligible helper if:
      // * its id is simple (a single part, not `this` or `..`)
      var eligibleHelper = this.eligibleHelper = id.isSimple;
      // a mustache is definitely a helper if:
      // * it is an eligible helper, and
      // * it has at least one parameter or hash segment
      this.isHelper = eligibleHelper && (params.length || hash);
      // if a mustache is an eligible helper but not a definite
      // helper, it is ambiguous, and will be resolved in a later
      // pass or at runtime.
    },
    PartialNode: function(partialName, context, strip, locInfo) {
      LocationInfo.call(this, locInfo);
      this.type = "partial";
      this.partialName = partialName;
      this.context = context;
      this.strip = strip;
    },
    BlockNode: function(mustache, program, inverse, close, locInfo) {
      LocationInfo.call(this, locInfo);
      if (mustache.sexpr.id.original !== close.path.original) {
        throw new Exception(mustache.sexpr.id.original + " doesn't match " + close.path.original, this);
      }
      this.type = 'block';
      this.mustache = mustache;
      this.program = program;
      this.inverse = inverse;
      this.strip = {
        left: mustache.strip.left,
        right: close.strip.right
      };
      (program || inverse).strip.left = mustache.strip.right;
      (inverse || program).strip.right = close.strip.left;
      if (inverse && !program) {
        this.isInverse = true;
      }
    },
    ContentNode: function(string, locInfo) {
      LocationInfo.call(this, locInfo);
      this.type = "content";
      this.string = string;
    },
    HashNode: function(pairs, locInfo) {
      LocationInfo.call(this, locInfo);
      this.type = "hash";
      this.pairs = pairs;
    },
    IdNode: function(parts, locInfo) {
      LocationInfo.call(this, locInfo);
      this.type = "ID";
      var original = "",
        dig = [],
        depth = 0;
      for (var i = 0, l = parts.length; i < l; i++) {
        var part = parts[i].part;
        original += (parts[i].separator || '') + part;
        if (part === ".." || part === "." || part === "this") {
          if (dig.length > 0) {
            throw new Exception("Invalid path: " + original, this);
          } else if (part === "..") {
            depth++;
          } else {
            this.isScoped = true;
          }
        } else {
          dig.push(part);
        }
      }
      this.original = original;
      this.parts = dig;
      this.string = dig.join('.');
      this.depth = depth;
      // an ID is simple if it only has one part, and that part is not
      // `..` or `this`.
      this.isSimple = parts.length === 1 && !this.isScoped && depth === 0;
      this.stringModeValue = this.string;
    },
    PartialNameNode: function(name, locInfo) {
      LocationInfo.call(this, locInfo);
      this.type = "PARTIAL_NAME";
      this.name = name.original;
    },
    DataNode: function(id, locInfo) {
      LocationInfo.call(this, locInfo);
      this.type = "DATA";
      this.id = id;
    },
    StringNode: function(string, locInfo) {
      LocationInfo.call(this, locInfo);
      this.type = "STRING";
      this.original = this.string = this.stringModeValue = string;
    },
    IntegerNode: function(integer, locInfo) {
      LocationInfo.call(this, locInfo);
      this.type = "INTEGER";
      this.original = this.integer = integer;
      this.stringModeValue = Number(integer);
    },
    BooleanNode: function(bool, locInfo) {
      LocationInfo.call(this, locInfo);
      this.type = "BOOLEAN";
      this.bool = bool;
      this.stringModeValue = bool === "true";
    },
    CommentNode: function(comment, locInfo) {
      LocationInfo.call(this, locInfo);
      this.type = "comment";
      this.comment = comment;
    }
  };
  // Must be exported as an object rather than the root of the module as the jison lexer
  // most modify the object to operate properly.
  exports["default"] = AST;
});
define("handlebars/1.3.0/dist/cjs/handlebars/compiler/base-debug", [], function(require, exports, module) {
  "use strict";
  var parser = require("handlebars/1.3.0/dist/cjs/handlebars/compiler/parser-debug")["default"];
  var AST = require("handlebars/1.3.0/dist/cjs/handlebars/compiler/ast-debug")["default"];
  exports.parser = parser;

  function parse(input) {
    // Just return if an already-compile AST was passed in.
    if (input.constructor === AST.ProgramNode) {
      return input;
    }
    parser.yy = AST;
    return parser.parse(input);
  }
  exports.parse = parse;
});
define("handlebars/1.3.0/dist/cjs/handlebars/compiler/parser-debug", [], function(require, exports, module) {
  "use strict";
  /* jshint ignore:start */
  /* Jison generated parser */
  var handlebars = (function() {
    var parser = {
      trace: function trace() {},
      yy: {},
      symbols_: {
        "error": 2,
        "root": 3,
        "statements": 4,
        "EOF": 5,
        "program": 6,
        "simpleInverse": 7,
        "statement": 8,
        "openInverse": 9,
        "closeBlock": 10,
        "openBlock": 11,
        "mustache": 12,
        "partial": 13,
        "CONTENT": 14,
        "COMMENT": 15,
        "OPEN_BLOCK": 16,
        "sexpr": 17,
        "CLOSE": 18,
        "OPEN_INVERSE": 19,
        "OPEN_ENDBLOCK": 20,
        "path": 21,
        "OPEN": 22,
        "OPEN_UNESCAPED": 23,
        "CLOSE_UNESCAPED": 24,
        "OPEN_PARTIAL": 25,
        "partialName": 26,
        "partial_option0": 27,
        "sexpr_repetition0": 28,
        "sexpr_option0": 29,
        "dataName": 30,
        "param": 31,
        "STRING": 32,
        "INTEGER": 33,
        "BOOLEAN": 34,
        "OPEN_SEXPR": 35,
        "CLOSE_SEXPR": 36,
        "hash": 37,
        "hash_repetition_plus0": 38,
        "hashSegment": 39,
        "ID": 40,
        "EQUALS": 41,
        "DATA": 42,
        "pathSegments": 43,
        "SEP": 44,
        "$accept": 0,
        "$end": 1
      },
      terminals_: {
        2: "error",
        5: "EOF",
        14: "CONTENT",
        15: "COMMENT",
        16: "OPEN_BLOCK",
        18: "CLOSE",
        19: "OPEN_INVERSE",
        20: "OPEN_ENDBLOCK",
        22: "OPEN",
        23: "OPEN_UNESCAPED",
        24: "CLOSE_UNESCAPED",
        25: "OPEN_PARTIAL",
        32: "STRING",
        33: "INTEGER",
        34: "BOOLEAN",
        35: "OPEN_SEXPR",
        36: "CLOSE_SEXPR",
        40: "ID",
        41: "EQUALS",
        42: "DATA",
        44: "SEP"
      },
      productions_: [0, [3, 2],
        [3, 1],
        [6, 2],
        [6, 3],
        [6, 2],
        [6, 1],
        [6, 1],
        [6, 0],
        [4, 1],
        [4, 2],
        [8, 3],
        [8, 3],
        [8, 1],
        [8, 1],
        [8, 1],
        [8, 1],
        [11, 3],
        [9, 3],
        [10, 3],
        [12, 3],
        [12, 3],
        [13, 4],
        [7, 2],
        [17, 3],
        [17, 1],
        [31, 1],
        [31, 1],
        [31, 1],
        [31, 1],
        [31, 1],
        [31, 3],
        [37, 1],
        [39, 3],
        [26, 1],
        [26, 1],
        [26, 1],
        [30, 2],
        [21, 1],
        [43, 3],
        [43, 1],
        [27, 0],
        [27, 1],
        [28, 0],
        [28, 2],
        [29, 0],
        [29, 1],
        [38, 1],
        [38, 2]
      ],
      performAction: function anonymous(yytext, yyleng, yylineno, yy, yystate, $$, _$) {
        var $0 = $$.length - 1;
        switch (yystate) {
          case 1:
            return new yy.ProgramNode($$[$0 - 1], this._$);
            break;
          case 2:
            return new yy.ProgramNode([], this._$);
            break;
          case 3:
            this.$ = new yy.ProgramNode([], $$[$0 - 1], $$[$0], this._$);
            break;
          case 4:
            this.$ = new yy.ProgramNode($$[$0 - 2], $$[$0 - 1], $$[$0], this._$);
            break;
          case 5:
            this.$ = new yy.ProgramNode($$[$0 - 1], $$[$0], [], this._$);
            break;
          case 6:
            this.$ = new yy.ProgramNode($$[$0], this._$);
            break;
          case 7:
            this.$ = new yy.ProgramNode([], this._$);
            break;
          case 8:
            this.$ = new yy.ProgramNode([], this._$);
            break;
          case 9:
            this.$ = [$$[$0]];
            break;
          case 10:
            $$[$0 - 1].push($$[$0]);
            this.$ = $$[$0 - 1];
            break;
          case 11:
            this.$ = new yy.BlockNode($$[$0 - 2], $$[$0 - 1].inverse, $$[$0 - 1], $$[$0], this._$);
            break;
          case 12:
            this.$ = new yy.BlockNode($$[$0 - 2], $$[$0 - 1], $$[$0 - 1].inverse, $$[$0], this._$);
            break;
          case 13:
            this.$ = $$[$0];
            break;
          case 14:
            this.$ = $$[$0];
            break;
          case 15:
            this.$ = new yy.ContentNode($$[$0], this._$);
            break;
          case 16:
            this.$ = new yy.CommentNode($$[$0], this._$);
            break;
          case 17:
            this.$ = new yy.MustacheNode($$[$0 - 1], null, $$[$0 - 2], stripFlags($$[$0 - 2], $$[$0]), this._$);
            break;
          case 18:
            this.$ = new yy.MustacheNode($$[$0 - 1], null, $$[$0 - 2], stripFlags($$[$0 - 2], $$[$0]), this._$);
            break;
          case 19:
            this.$ = {
              path: $$[$0 - 1],
              strip: stripFlags($$[$0 - 2], $$[$0])
            };
            break;
          case 20:
            this.$ = new yy.MustacheNode($$[$0 - 1], null, $$[$0 - 2], stripFlags($$[$0 - 2], $$[$0]), this._$);
            break;
          case 21:
            this.$ = new yy.MustacheNode($$[$0 - 1], null, $$[$0 - 2], stripFlags($$[$0 - 2], $$[$0]), this._$);
            break;
          case 22:
            this.$ = new yy.PartialNode($$[$0 - 2], $$[$0 - 1], stripFlags($$[$0 - 3], $$[$0]), this._$);
            break;
          case 23:
            this.$ = stripFlags($$[$0 - 1], $$[$0]);
            break;
          case 24:
            this.$ = new yy.SexprNode([$$[$0 - 2]].concat($$[$0 - 1]), $$[$0], this._$);
            break;
          case 25:
            this.$ = new yy.SexprNode([$$[$0]], null, this._$);
            break;
          case 26:
            this.$ = $$[$0];
            break;
          case 27:
            this.$ = new yy.StringNode($$[$0], this._$);
            break;
          case 28:
            this.$ = new yy.IntegerNode($$[$0], this._$);
            break;
          case 29:
            this.$ = new yy.BooleanNode($$[$0], this._$);
            break;
          case 30:
            this.$ = $$[$0];
            break;
          case 31:
            $$[$0 - 1].isHelper = true;
            this.$ = $$[$0 - 1];
            break;
          case 32:
            this.$ = new yy.HashNode($$[$0], this._$);
            break;
          case 33:
            this.$ = [$$[$0 - 2], $$[$0]];
            break;
          case 34:
            this.$ = new yy.PartialNameNode($$[$0], this._$);
            break;
          case 35:
            this.$ = new yy.PartialNameNode(new yy.StringNode($$[$0], this._$), this._$);
            break;
          case 36:
            this.$ = new yy.PartialNameNode(new yy.IntegerNode($$[$0], this._$));
            break;
          case 37:
            this.$ = new yy.DataNode($$[$0], this._$);
            break;
          case 38:
            this.$ = new yy.IdNode($$[$0], this._$);
            break;
          case 39:
            $$[$0 - 2].push({
              part: $$[$0],
              separator: $$[$0 - 1]
            });
            this.$ = $$[$0 - 2];
            break;
          case 40:
            this.$ = [{
              part: $$[$0]
            }];
            break;
          case 43:
            this.$ = [];
            break;
          case 44:
            $$[$0 - 1].push($$[$0]);
            break;
          case 47:
            this.$ = [$$[$0]];
            break;
          case 48:
            $$[$0 - 1].push($$[$0]);
            break;
        }
      },
      table: [{
        3: 1,
        4: 2,
        5: [1, 3],
        8: 4,
        9: 5,
        11: 6,
        12: 7,
        13: 8,
        14: [1, 9],
        15: [1, 10],
        16: [1, 12],
        19: [1, 11],
        22: [1, 13],
        23: [1, 14],
        25: [1, 15]
      }, {
        1: [3]
      }, {
        5: [1, 16],
        8: 17,
        9: 5,
        11: 6,
        12: 7,
        13: 8,
        14: [1, 9],
        15: [1, 10],
        16: [1, 12],
        19: [1, 11],
        22: [1, 13],
        23: [1, 14],
        25: [1, 15]
      }, {
        1: [2, 2]
      }, {
        5: [2, 9],
        14: [2, 9],
        15: [2, 9],
        16: [2, 9],
        19: [2, 9],
        20: [2, 9],
        22: [2, 9],
        23: [2, 9],
        25: [2, 9]
      }, {
        4: 20,
        6: 18,
        7: 19,
        8: 4,
        9: 5,
        11: 6,
        12: 7,
        13: 8,
        14: [1, 9],
        15: [1, 10],
        16: [1, 12],
        19: [1, 21],
        20: [2, 8],
        22: [1, 13],
        23: [1, 14],
        25: [1, 15]
      }, {
        4: 20,
        6: 22,
        7: 19,
        8: 4,
        9: 5,
        11: 6,
        12: 7,
        13: 8,
        14: [1, 9],
        15: [1, 10],
        16: [1, 12],
        19: [1, 21],
        20: [2, 8],
        22: [1, 13],
        23: [1, 14],
        25: [1, 15]
      }, {
        5: [2, 13],
        14: [2, 13],
        15: [2, 13],
        16: [2, 13],
        19: [2, 13],
        20: [2, 13],
        22: [2, 13],
        23: [2, 13],
        25: [2, 13]
      }, {
        5: [2, 14],
        14: [2, 14],
        15: [2, 14],
        16: [2, 14],
        19: [2, 14],
        20: [2, 14],
        22: [2, 14],
        23: [2, 14],
        25: [2, 14]
      }, {
        5: [2, 15],
        14: [2, 15],
        15: [2, 15],
        16: [2, 15],
        19: [2, 15],
        20: [2, 15],
        22: [2, 15],
        23: [2, 15],
        25: [2, 15]
      }, {
        5: [2, 16],
        14: [2, 16],
        15: [2, 16],
        16: [2, 16],
        19: [2, 16],
        20: [2, 16],
        22: [2, 16],
        23: [2, 16],
        25: [2, 16]
      }, {
        17: 23,
        21: 24,
        30: 25,
        40: [1, 28],
        42: [1, 27],
        43: 26
      }, {
        17: 29,
        21: 24,
        30: 25,
        40: [1, 28],
        42: [1, 27],
        43: 26
      }, {
        17: 30,
        21: 24,
        30: 25,
        40: [1, 28],
        42: [1, 27],
        43: 26
      }, {
        17: 31,
        21: 24,
        30: 25,
        40: [1, 28],
        42: [1, 27],
        43: 26
      }, {
        21: 33,
        26: 32,
        32: [1, 34],
        33: [1, 35],
        40: [1, 28],
        43: 26
      }, {
        1: [2, 1]
      }, {
        5: [2, 10],
        14: [2, 10],
        15: [2, 10],
        16: [2, 10],
        19: [2, 10],
        20: [2, 10],
        22: [2, 10],
        23: [2, 10],
        25: [2, 10]
      }, {
        10: 36,
        20: [1, 37]
      }, {
        4: 38,
        8: 4,
        9: 5,
        11: 6,
        12: 7,
        13: 8,
        14: [1, 9],
        15: [1, 10],
        16: [1, 12],
        19: [1, 11],
        20: [2, 7],
        22: [1, 13],
        23: [1, 14],
        25: [1, 15]
      }, {
        7: 39,
        8: 17,
        9: 5,
        11: 6,
        12: 7,
        13: 8,
        14: [1, 9],
        15: [1, 10],
        16: [1, 12],
        19: [1, 21],
        20: [2, 6],
        22: [1, 13],
        23: [1, 14],
        25: [1, 15]
      }, {
        17: 23,
        18: [1, 40],
        21: 24,
        30: 25,
        40: [1, 28],
        42: [1, 27],
        43: 26
      }, {
        10: 41,
        20: [1, 37]
      }, {
        18: [1, 42]
      }, {
        18: [2, 43],
        24: [2, 43],
        28: 43,
        32: [2, 43],
        33: [2, 43],
        34: [2, 43],
        35: [2, 43],
        36: [2, 43],
        40: [2, 43],
        42: [2, 43]
      }, {
        18: [2, 25],
        24: [2, 25],
        36: [2, 25]
      }, {
        18: [2, 38],
        24: [2, 38],
        32: [2, 38],
        33: [2, 38],
        34: [2, 38],
        35: [2, 38],
        36: [2, 38],
        40: [2, 38],
        42: [2, 38],
        44: [1, 44]
      }, {
        21: 45,
        40: [1, 28],
        43: 26
      }, {
        18: [2, 40],
        24: [2, 40],
        32: [2, 40],
        33: [2, 40],
        34: [2, 40],
        35: [2, 40],
        36: [2, 40],
        40: [2, 40],
        42: [2, 40],
        44: [2, 40]
      }, {
        18: [1, 46]
      }, {
        18: [1, 47]
      }, {
        24: [1, 48]
      }, {
        18: [2, 41],
        21: 50,
        27: 49,
        40: [1, 28],
        43: 26
      }, {
        18: [2, 34],
        40: [2, 34]
      }, {
        18: [2, 35],
        40: [2, 35]
      }, {
        18: [2, 36],
        40: [2, 36]
      }, {
        5: [2, 11],
        14: [2, 11],
        15: [2, 11],
        16: [2, 11],
        19: [2, 11],
        20: [2, 11],
        22: [2, 11],
        23: [2, 11],
        25: [2, 11]
      }, {
        21: 51,
        40: [1, 28],
        43: 26
      }, {
        8: 17,
        9: 5,
        11: 6,
        12: 7,
        13: 8,
        14: [1, 9],
        15: [1, 10],
        16: [1, 12],
        19: [1, 11],
        20: [2, 3],
        22: [1, 13],
        23: [1, 14],
        25: [1, 15]
      }, {
        4: 52,
        8: 4,
        9: 5,
        11: 6,
        12: 7,
        13: 8,
        14: [1, 9],
        15: [1, 10],
        16: [1, 12],
        19: [1, 11],
        20: [2, 5],
        22: [1, 13],
        23: [1, 14],
        25: [1, 15]
      }, {
        14: [2, 23],
        15: [2, 23],
        16: [2, 23],
        19: [2, 23],
        20: [2, 23],
        22: [2, 23],
        23: [2, 23],
        25: [2, 23]
      }, {
        5: [2, 12],
        14: [2, 12],
        15: [2, 12],
        16: [2, 12],
        19: [2, 12],
        20: [2, 12],
        22: [2, 12],
        23: [2, 12],
        25: [2, 12]
      }, {
        14: [2, 18],
        15: [2, 18],
        16: [2, 18],
        19: [2, 18],
        20: [2, 18],
        22: [2, 18],
        23: [2, 18],
        25: [2, 18]
      }, {
        18: [2, 45],
        21: 56,
        24: [2, 45],
        29: 53,
        30: 60,
        31: 54,
        32: [1, 57],
        33: [1, 58],
        34: [1, 59],
        35: [1, 61],
        36: [2, 45],
        37: 55,
        38: 62,
        39: 63,
        40: [1, 64],
        42: [1, 27],
        43: 26
      }, {
        40: [1, 65]
      }, {
        18: [2, 37],
        24: [2, 37],
        32: [2, 37],
        33: [2, 37],
        34: [2, 37],
        35: [2, 37],
        36: [2, 37],
        40: [2, 37],
        42: [2, 37]
      }, {
        14: [2, 17],
        15: [2, 17],
        16: [2, 17],
        19: [2, 17],
        20: [2, 17],
        22: [2, 17],
        23: [2, 17],
        25: [2, 17]
      }, {
        5: [2, 20],
        14: [2, 20],
        15: [2, 20],
        16: [2, 20],
        19: [2, 20],
        20: [2, 20],
        22: [2, 20],
        23: [2, 20],
        25: [2, 20]
      }, {
        5: [2, 21],
        14: [2, 21],
        15: [2, 21],
        16: [2, 21],
        19: [2, 21],
        20: [2, 21],
        22: [2, 21],
        23: [2, 21],
        25: [2, 21]
      }, {
        18: [1, 66]
      }, {
        18: [2, 42]
      }, {
        18: [1, 67]
      }, {
        8: 17,
        9: 5,
        11: 6,
        12: 7,
        13: 8,
        14: [1, 9],
        15: [1, 10],
        16: [1, 12],
        19: [1, 11],
        20: [2, 4],
        22: [1, 13],
        23: [1, 14],
        25: [1, 15]
      }, {
        18: [2, 24],
        24: [2, 24],
        36: [2, 24]
      }, {
        18: [2, 44],
        24: [2, 44],
        32: [2, 44],
        33: [2, 44],
        34: [2, 44],
        35: [2, 44],
        36: [2, 44],
        40: [2, 44],
        42: [2, 44]
      }, {
        18: [2, 46],
        24: [2, 46],
        36: [2, 46]
      }, {
        18: [2, 26],
        24: [2, 26],
        32: [2, 26],
        33: [2, 26],
        34: [2, 26],
        35: [2, 26],
        36: [2, 26],
        40: [2, 26],
        42: [2, 26]
      }, {
        18: [2, 27],
        24: [2, 27],
        32: [2, 27],
        33: [2, 27],
        34: [2, 27],
        35: [2, 27],
        36: [2, 27],
        40: [2, 27],
        42: [2, 27]
      }, {
        18: [2, 28],
        24: [2, 28],
        32: [2, 28],
        33: [2, 28],
        34: [2, 28],
        35: [2, 28],
        36: [2, 28],
        40: [2, 28],
        42: [2, 28]
      }, {
        18: [2, 29],
        24: [2, 29],
        32: [2, 29],
        33: [2, 29],
        34: [2, 29],
        35: [2, 29],
        36: [2, 29],
        40: [2, 29],
        42: [2, 29]
      }, {
        18: [2, 30],
        24: [2, 30],
        32: [2, 30],
        33: [2, 30],
        34: [2, 30],
        35: [2, 30],
        36: [2, 30],
        40: [2, 30],
        42: [2, 30]
      }, {
        17: 68,
        21: 24,
        30: 25,
        40: [1, 28],
        42: [1, 27],
        43: 26
      }, {
        18: [2, 32],
        24: [2, 32],
        36: [2, 32],
        39: 69,
        40: [1, 70]
      }, {
        18: [2, 47],
        24: [2, 47],
        36: [2, 47],
        40: [2, 47]
      }, {
        18: [2, 40],
        24: [2, 40],
        32: [2, 40],
        33: [2, 40],
        34: [2, 40],
        35: [2, 40],
        36: [2, 40],
        40: [2, 40],
        41: [1, 71],
        42: [2, 40],
        44: [2, 40]
      }, {
        18: [2, 39],
        24: [2, 39],
        32: [2, 39],
        33: [2, 39],
        34: [2, 39],
        35: [2, 39],
        36: [2, 39],
        40: [2, 39],
        42: [2, 39],
        44: [2, 39]
      }, {
        5: [2, 22],
        14: [2, 22],
        15: [2, 22],
        16: [2, 22],
        19: [2, 22],
        20: [2, 22],
        22: [2, 22],
        23: [2, 22],
        25: [2, 22]
      }, {
        5: [2, 19],
        14: [2, 19],
        15: [2, 19],
        16: [2, 19],
        19: [2, 19],
        20: [2, 19],
        22: [2, 19],
        23: [2, 19],
        25: [2, 19]
      }, {
        36: [1, 72]
      }, {
        18: [2, 48],
        24: [2, 48],
        36: [2, 48],
        40: [2, 48]
      }, {
        41: [1, 71]
      }, {
        21: 56,
        30: 60,
        31: 73,
        32: [1, 57],
        33: [1, 58],
        34: [1, 59],
        35: [1, 61],
        40: [1, 28],
        42: [1, 27],
        43: 26
      }, {
        18: [2, 31],
        24: [2, 31],
        32: [2, 31],
        33: [2, 31],
        34: [2, 31],
        35: [2, 31],
        36: [2, 31],
        40: [2, 31],
        42: [2, 31]
      }, {
        18: [2, 33],
        24: [2, 33],
        36: [2, 33],
        40: [2, 33]
      }],
      defaultActions: {
        3: [2, 2],
        16: [2, 1],
        50: [2, 42]
      },
      parseError: function parseError(str, hash) {
        throw new Error(str);
      },
      parse: function parse(input) {
        var self = this,
          stack = [0],
          vstack = [null],
          lstack = [],
          table = this.table,
          yytext = "",
          yylineno = 0,
          yyleng = 0,
          recovering = 0,
          TERROR = 2,
          EOF = 1;
        this.lexer.setInput(input);
        this.lexer.yy = this.yy;
        this.yy.lexer = this.lexer;
        this.yy.parser = this;
        if (typeof this.lexer.yylloc == "undefined") this.lexer.yylloc = {};
        var yyloc = this.lexer.yylloc;
        lstack.push(yyloc);
        var ranges = this.lexer.options && this.lexer.options.ranges;
        if (typeof this.yy.parseError === "function") this.parseError = this.yy.parseError;

        function popStack(n) {
          stack.length = stack.length - 2 * n;
          vstack.length = vstack.length - n;
          lstack.length = lstack.length - n;
        }

        function lex() {
          var token;
          token = self.lexer.lex() || 1;
          if (typeof token !== "number") {
            token = self.symbols_[token] || token;
          }
          return token;
        }
        var symbol, preErrorSymbol, state, action, a, r, yyval = {},
          p, len, newState, expected;
        while (true) {
          state = stack[stack.length - 1];
          if (this.defaultActions[state]) {
            action = this.defaultActions[state];
          } else {
            if (symbol === null || typeof symbol == "undefined") {
              symbol = lex();
            }
            action = table[state] && table[state][symbol];
          }
          if (typeof action === "undefined" || !action.length || !action[0]) {
            var errStr = "";
            if (!recovering) {
              expected = [];
              for (p in table[state])
                if (this.terminals_[p] && p > 2) {
                  expected.push("'" + this.terminals_[p] + "'");
                }
              if (this.lexer.showPosition) {
                errStr = "Parse error on line " + (yylineno + 1) + ":\n" + this.lexer.showPosition() + "\nExpecting " + expected.join(", ") + ", got '" + (this.terminals_[symbol] || symbol) + "'";
              } else {
                errStr = "Parse error on line " + (yylineno + 1) + ": Unexpected " + (symbol == 1 ? "end of input" : "'" + (this.terminals_[symbol] || symbol) + "'");
              }
              this.parseError(errStr, {
                text: this.lexer.match,
                token: this.terminals_[symbol] || symbol,
                line: this.lexer.yylineno,
                loc: yyloc,
                expected: expected
              });
            }
          }
          if (action[0] instanceof Array && action.length > 1) {
            throw new Error("Parse Error: multiple actions possible at state: " + state + ", token: " + symbol);
          }
          switch (action[0]) {
            case 1:
              stack.push(symbol);
              vstack.push(this.lexer.yytext);
              lstack.push(this.lexer.yylloc);
              stack.push(action[1]);
              symbol = null;
              if (!preErrorSymbol) {
                yyleng = this.lexer.yyleng;
                yytext = this.lexer.yytext;
                yylineno = this.lexer.yylineno;
                yyloc = this.lexer.yylloc;
                if (recovering > 0) recovering--;
              } else {
                symbol = preErrorSymbol;
                preErrorSymbol = null;
              }
              break;
            case 2:
              len = this.productions_[action[1]][1];
              yyval.$ = vstack[vstack.length - len];
              yyval._$ = {
                first_line: lstack[lstack.length - (len || 1)].first_line,
                last_line: lstack[lstack.length - 1].last_line,
                first_column: lstack[lstack.length - (len || 1)].first_column,
                last_column: lstack[lstack.length - 1].last_column
              };
              if (ranges) {
                yyval._$.range = [lstack[lstack.length - (len || 1)].range[0], lstack[lstack.length - 1].range[1]];
              }
              r = this.performAction.call(yyval, yytext, yyleng, yylineno, this.yy, action[1], vstack, lstack);
              if (typeof r !== "undefined") {
                return r;
              }
              if (len) {
                stack = stack.slice(0, -1 * len * 2);
                vstack = vstack.slice(0, -1 * len);
                lstack = lstack.slice(0, -1 * len);
              }
              stack.push(this.productions_[action[1]][0]);
              vstack.push(yyval.$);
              lstack.push(yyval._$);
              newState = table[stack[stack.length - 2]][stack[stack.length - 1]];
              stack.push(newState);
              break;
            case 3:
              return true;
          }
        }
        return true;
      }
    };

    function stripFlags(open, close) {
        return {
          left: open.charAt(2) === '~',
          right: close.charAt(0) === '~' || close.charAt(1) === '~'
        };
      }
      /* Jison generated lexer */
    var lexer = (function() {
      var lexer = ({
        EOF: 1,
        parseError: function parseError(str, hash) {
          if (this.yy.parser) {
            this.yy.parser.parseError(str, hash);
          } else {
            throw new Error(str);
          }
        },
        setInput: function(input) {
          this._input = input;
          this._more = this._less = this.done = false;
          this.yylineno = this.yyleng = 0;
          this.yytext = this.matched = this.match = '';
          this.conditionStack = ['INITIAL'];
          this.yylloc = {
            first_line: 1,
            first_column: 0,
            last_line: 1,
            last_column: 0
          };
          if (this.options.ranges) this.yylloc.range = [0, 0];
          this.offset = 0;
          return this;
        },
        input: function() {
          var ch = this._input[0];
          this.yytext += ch;
          this.yyleng++;
          this.offset++;
          this.match += ch;
          this.matched += ch;
          var lines = ch.match(/(?:\r\n?|\n).*/g);
          if (lines) {
            this.yylineno++;
            this.yylloc.last_line++;
          } else {
            this.yylloc.last_column++;
          }
          if (this.options.ranges) this.yylloc.range[1] ++;
          this._input = this._input.slice(1);
          return ch;
        },
        unput: function(ch) {
          var len = ch.length;
          var lines = ch.split(/(?:\r\n?|\n)/g);
          this._input = ch + this._input;
          this.yytext = this.yytext.substr(0, this.yytext.length - len - 1);
          //this.yyleng -= len;
          this.offset -= len;
          var oldLines = this.match.split(/(?:\r\n?|\n)/g);
          this.match = this.match.substr(0, this.match.length - 1);
          this.matched = this.matched.substr(0, this.matched.length - 1);
          if (lines.length - 1) this.yylineno -= lines.length - 1;
          var r = this.yylloc.range;
          this.yylloc = {
            first_line: this.yylloc.first_line,
            last_line: this.yylineno + 1,
            first_column: this.yylloc.first_column,
            last_column: lines ? (lines.length === oldLines.length ? this.yylloc.first_column : 0) + oldLines[oldLines.length - lines.length].length - lines[0].length : this.yylloc.first_column - len
          };
          if (this.options.ranges) {
            this.yylloc.range = [r[0], r[0] + this.yyleng - len];
          }
          return this;
        },
        more: function() {
          this._more = true;
          return this;
        },
        less: function(n) {
          this.unput(this.match.slice(n));
        },
        pastInput: function() {
          var past = this.matched.substr(0, this.matched.length - this.match.length);
          return (past.length > 20 ? '...' : '') + past.substr(-20).replace(/\n/g, "");
        },
        upcomingInput: function() {
          var next = this.match;
          if (next.length < 20) {
            next += this._input.substr(0, 20 - next.length);
          }
          return (next.substr(0, 20) + (next.length > 20 ? '...' : '')).replace(/\n/g, "");
        },
        showPosition: function() {
          var pre = this.pastInput();
          var c = new Array(pre.length + 1).join("-");
          return pre + this.upcomingInput() + "\n" + c + "^";
        },
        next: function() {
          if (this.done) {
            return this.EOF;
          }
          if (!this._input) this.done = true;
          var token,
            match,
            tempMatch,
            index,
            col,
            lines;
          if (!this._more) {
            this.yytext = '';
            this.match = '';
          }
          var rules = this._currentRules();
          for (var i = 0; i < rules.length; i++) {
            tempMatch = this._input.match(this.rules[rules[i]]);
            if (tempMatch && (!match || tempMatch[0].length > match[0].length)) {
              match = tempMatch;
              index = i;
              if (!this.options.flex) break;
            }
          }
          if (match) {
            lines = match[0].match(/(?:\r\n?|\n).*/g);
            if (lines) this.yylineno += lines.length;
            this.yylloc = {
              first_line: this.yylloc.last_line,
              last_line: this.yylineno + 1,
              first_column: this.yylloc.last_column,
              last_column: lines ? lines[lines.length - 1].length - lines[lines.length - 1].match(/\r?\n?/)[0].length : this.yylloc.last_column + match[0].length
            };
            this.yytext += match[0];
            this.match += match[0];
            this.matches = match;
            this.yyleng = this.yytext.length;
            if (this.options.ranges) {
              this.yylloc.range = [this.offset, this.offset += this.yyleng];
            }
            this._more = false;
            this._input = this._input.slice(match[0].length);
            this.matched += match[0];
            token = this.performAction.call(this, this.yy, this, rules[index], this.conditionStack[this.conditionStack.length - 1]);
            if (this.done && this._input) this.done = false;
            if (token) return token;
            else return;
          }
          if (this._input === "") {
            return this.EOF;
          } else {
            return this.parseError('Lexical error on line ' + (this.yylineno + 1) + '. Unrecognized text.\n' + this.showPosition(), {
              text: "",
              token: null,
              line: this.yylineno
            });
          }
        },
        lex: function lex() {
          var r = this.next();
          if (typeof r !== 'undefined') {
            return r;
          } else {
            return this.lex();
          }
        },
        begin: function begin(condition) {
          this.conditionStack.push(condition);
        },
        popState: function popState() {
          return this.conditionStack.pop();
        },
        _currentRules: function _currentRules() {
          return this.conditions[this.conditionStack[this.conditionStack.length - 1]].rules;
        },
        topState: function() {
          return this.conditionStack[this.conditionStack.length - 2];
        },
        pushState: function begin(condition) {
          this.begin(condition);
        }
      });
      lexer.options = {};
      lexer.performAction = function anonymous(yy, yy_, $avoiding_name_collisions, YY_START) {
        function strip(start, end) {
          return yy_.yytext = yy_.yytext.substr(start, yy_.yyleng - end);
        }
        var YYSTATE = YY_START
        switch ($avoiding_name_collisions) {
          case 0:
            if (yy_.yytext.slice(-2) === "\\\\") {
              strip(0, 1);
              this.begin("mu");
            } else if (yy_.yytext.slice(-1) === "\\") {
              strip(0, 1);
              this.begin("emu");
            } else {
              this.begin("mu");
            }
            if (yy_.yytext) return 14;
            break;
          case 1:
            return 14;
            break;
          case 2:
            this.popState();
            return 14;
            break;
          case 3:
            strip(0, 4);
            this.popState();
            return 15;
            break;
          case 4:
            return 35;
            break;
          case 5:
            return 36;
            break;
          case 6:
            return 25;
            break;
          case 7:
            return 16;
            break;
          case 8:
            return 20;
            break;
          case 9:
            return 19;
            break;
          case 10:
            return 19;
            break;
          case 11:
            return 23;
            break;
          case 12:
            return 22;
            break;
          case 13:
            this.popState();
            this.begin('com');
            break;
          case 14:
            strip(3, 5);
            this.popState();
            return 15;
            break;
          case 15:
            return 22;
            break;
          case 16:
            return 41;
            break;
          case 17:
            return 40;
            break;
          case 18:
            return 40;
            break;
          case 19:
            return 44;
            break;
          case 20: // ignore whitespace
            break;
          case 21:
            this.popState();
            return 24;
            break;
          case 22:
            this.popState();
            return 18;
            break;
          case 23:
            yy_.yytext = strip(1, 2).replace(/\\"/g, '"');
            return 32;
            break;
          case 24:
            yy_.yytext = strip(1, 2).replace(/\\'/g, "'");
            return 32;
            break;
          case 25:
            return 42;
            break;
          case 26:
            return 34;
            break;
          case 27:
            return 34;
            break;
          case 28:
            return 33;
            break;
          case 29:
            return 40;
            break;
          case 30:
            yy_.yytext = strip(1, 2);
            return 40;
            break;
          case 31:
            return 'INVALID';
            break;
          case 32:
            return 5;
            break;
        }
      };
      lexer.rules = [/^(?:[^\x00]*?(?=(\{\{)))/, /^(?:[^\x00]+)/, /^(?:[^\x00]{2,}?(?=(\{\{|\\\{\{|\\\\\{\{|$)))/, /^(?:[\s\S]*?--\}\})/, /^(?:\()/, /^(?:\))/, /^(?:\{\{(~)?>)/, /^(?:\{\{(~)?#)/, /^(?:\{\{(~)?\/)/, /^(?:\{\{(~)?\^)/, /^(?:\{\{(~)?\s*else\b)/, /^(?:\{\{(~)?\{)/, /^(?:\{\{(~)?&)/, /^(?:\{\{!--)/, /^(?:\{\{![\s\S]*?\}\})/, /^(?:\{\{(~)?)/, /^(?:=)/, /^(?:\.\.)/, /^(?:\.(?=([=~}\s\/.)])))/, /^(?:[\/.])/, /^(?:\s+)/, /^(?:\}(~)?\}\})/, /^(?:(~)?\}\})/, /^(?:"(\\["]|[^"])*")/, /^(?:'(\\[']|[^'])*')/, /^(?:@)/, /^(?:true(?=([~}\s)])))/, /^(?:false(?=([~}\s)])))/, /^(?:-?[0-9]+(?=([~}\s)])))/, /^(?:([^\s!"#%-,\.\/;->@\[-\^`\{-~]+(?=([=~}\s\/.)]))))/, /^(?:\[[^\]]*\])/, /^(?:.)/, /^(?:$)/];
      lexer.conditions = {
        "mu": {
          "rules": [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32],
          "inclusive": false
        },
        "emu": {
          "rules": [2],
          "inclusive": false
        },
        "com": {
          "rules": [3],
          "inclusive": false
        },
        "INITIAL": {
          "rules": [0, 1, 32],
          "inclusive": true
        }
      };
      return lexer;
    })()
    parser.lexer = lexer;

    function Parser() {
      this.yy = {};
    }
    Parser.prototype = parser;
    parser.Parser = Parser;
    return new Parser;
  })();
  exports["default"] = handlebars;
  /* jshint ignore:end */
});
define("handlebars/1.3.0/dist/cjs/handlebars/compiler/compiler-debug", [], function(require, exports, module) {
  "use strict";
  var Exception = require("handlebars/1.3.0/dist/cjs/handlebars/exception-debug")["default"];

  function Compiler() {}
  exports.Compiler = Compiler; // the foundHelper register will disambiguate helper lookup from finding a
  // function in a context. This is necessary for mustache compatibility, which
  // requires that context functions in blocks are evaluated by blockHelperMissing,
  // and then proceed as if the resulting value was provided to blockHelperMissing.
  Compiler.prototype = {
    compiler: Compiler,
    disassemble: function() {
      var opcodes = this.opcodes,
        opcode, out = [],
        params, param;
      for (var i = 0, l = opcodes.length; i < l; i++) {
        opcode = opcodes[i];
        if (opcode.opcode === 'DECLARE') {
          out.push("DECLARE " + opcode.name + "=" + opcode.value);
        } else {
          params = [];
          for (var j = 0; j < opcode.args.length; j++) {
            param = opcode.args[j];
            if (typeof param === "string") {
              param = "\"" + param.replace("\n", "\\n") + "\"";
            }
            params.push(param);
          }
          out.push(opcode.opcode + " " + params.join(" "));
        }
      }
      return out.join("\n");
    },
    equals: function(other) {
      var len = this.opcodes.length;
      if (other.opcodes.length !== len) {
        return false;
      }
      for (var i = 0; i < len; i++) {
        var opcode = this.opcodes[i],
          otherOpcode = other.opcodes[i];
        if (opcode.opcode !== otherOpcode.opcode || opcode.args.length !== otherOpcode.args.length) {
          return false;
        }
        for (var j = 0; j < opcode.args.length; j++) {
          if (opcode.args[j] !== otherOpcode.args[j]) {
            return false;
          }
        }
      }
      len = this.children.length;
      if (other.children.length !== len) {
        return false;
      }
      for (i = 0; i < len; i++) {
        if (!this.children[i].equals(other.children[i])) {
          return false;
        }
      }
      return true;
    },
    guid: 0,
    compile: function(program, options) {
      this.opcodes = [];
      this.children = [];
      this.depths = {
        list: []
      };
      this.options = options;
      // These changes will propagate to the other compiler components
      var knownHelpers = this.options.knownHelpers;
      this.options.knownHelpers = {
        'helperMissing': true,
        'blockHelperMissing': true,
        'each': true,
        'if': true,
        'unless': true,
        'with': true,
        'log': true
      };
      if (knownHelpers) {
        for (var name in knownHelpers) {
          this.options.knownHelpers[name] = knownHelpers[name];
        }
      }
      return this.accept(program);
    },
    accept: function(node) {
      var strip = node.strip || {},
        ret;
      if (strip.left) {
        this.opcode('strip');
      }
      ret = this[node.type](node);
      if (strip.right) {
        this.opcode('strip');
      }
      return ret;
    },
    program: function(program) {
      var statements = program.statements;
      for (var i = 0, l = statements.length; i < l; i++) {
        this.accept(statements[i]);
      }
      this.isSimple = l === 1;
      this.depths.list = this.depths.list.sort(function(a, b) {
        return a - b;
      });
      return this;
    },
    compileProgram: function(program) {
      var result = new this.compiler().compile(program, this.options);
      var guid = this.guid++,
        depth;
      this.usePartial = this.usePartial || result.usePartial;
      this.children[guid] = result;
      for (var i = 0, l = result.depths.list.length; i < l; i++) {
        depth = result.depths.list[i];
        if (depth < 2) {
          continue;
        } else {
          this.addDepth(depth - 1);
        }
      }
      return guid;
    },
    block: function(block) {
      var mustache = block.mustache,
        program = block.program,
        inverse = block.inverse;
      if (program) {
        program = this.compileProgram(program);
      }
      if (inverse) {
        inverse = this.compileProgram(inverse);
      }
      var sexpr = mustache.sexpr;
      var type = this.classifySexpr(sexpr);
      if (type === "helper") {
        this.helperSexpr(sexpr, program, inverse);
      } else if (type === "simple") {
        this.simpleSexpr(sexpr);
        // now that the simple mustache is resolved, we need to
        // evaluate it by executing `blockHelperMissing`
        this.opcode('pushProgram', program);
        this.opcode('pushProgram', inverse);
        this.opcode('emptyHash');
        this.opcode('blockValue');
      } else {
        this.ambiguousSexpr(sexpr, program, inverse);
        // now that the simple mustache is resolved, we need to
        // evaluate it by executing `blockHelperMissing`
        this.opcode('pushProgram', program);
        this.opcode('pushProgram', inverse);
        this.opcode('emptyHash');
        this.opcode('ambiguousBlockValue');
      }
      this.opcode('append');
    },
    hash: function(hash) {
      var pairs = hash.pairs,
        pair, val;
      this.opcode('pushHash');
      for (var i = 0, l = pairs.length; i < l; i++) {
        pair = pairs[i];
        val = pair[1];
        if (this.options.stringParams) {
          if (val.depth) {
            this.addDepth(val.depth);
          }
          this.opcode('getContext', val.depth || 0);
          this.opcode('pushStringParam', val.stringModeValue, val.type);
          if (val.type === 'sexpr') {
            // Subexpressions get evaluated and passed in
            // in string params mode.
            this.sexpr(val);
          }
        } else {
          this.accept(val);
        }
        this.opcode('assignToHash', pair[0]);
      }
      this.opcode('popHash');
    },
    partial: function(partial) {
      var partialName = partial.partialName;
      this.usePartial = true;
      if (partial.context) {
        this.ID(partial.context);
      } else {
        this.opcode('push', 'depth0');
      }
      this.opcode('invokePartial', partialName.name);
      this.opcode('append');
    },
    content: function(content) {
      this.opcode('appendContent', content.string);
    },
    mustache: function(mustache) {
      this.sexpr(mustache.sexpr);
      if (mustache.escaped && !this.options.noEscape) {
        this.opcode('appendEscaped');
      } else {
        this.opcode('append');
      }
    },
    ambiguousSexpr: function(sexpr, program, inverse) {
      var id = sexpr.id,
        name = id.parts[0],
        isBlock = program != null || inverse != null;
      this.opcode('getContext', id.depth);
      this.opcode('pushProgram', program);
      this.opcode('pushProgram', inverse);
      this.opcode('invokeAmbiguous', name, isBlock);
    },
    simpleSexpr: function(sexpr) {
      var id = sexpr.id;
      if (id.type === 'DATA') {
        this.DATA(id);
      } else if (id.parts.length) {
        this.ID(id);
      } else {
        // Simplified ID for `this`
        this.addDepth(id.depth);
        this.opcode('getContext', id.depth);
        this.opcode('pushContext');
      }
      this.opcode('resolvePossibleLambda');
    },
    helperSexpr: function(sexpr, program, inverse) {
      var params = this.setupFullMustacheParams(sexpr, program, inverse),
        name = sexpr.id.parts[0];
      if (this.options.knownHelpers[name]) {
        this.opcode('invokeKnownHelper', params.length, name);
      } else if (this.options.knownHelpersOnly) {
        throw new Exception("You specified knownHelpersOnly, but used the unknown helper " + name, sexpr);
      } else {
        this.opcode('invokeHelper', params.length, name, sexpr.isRoot);
      }
    },
    sexpr: function(sexpr) {
      var type = this.classifySexpr(sexpr);
      if (type === "simple") {
        this.simpleSexpr(sexpr);
      } else if (type === "helper") {
        this.helperSexpr(sexpr);
      } else {
        this.ambiguousSexpr(sexpr);
      }
    },
    ID: function(id) {
      this.addDepth(id.depth);
      this.opcode('getContext', id.depth);
      var name = id.parts[0];
      if (!name) {
        this.opcode('pushContext');
      } else {
        this.opcode('lookupOnContext', id.parts[0]);
      }
      for (var i = 1, l = id.parts.length; i < l; i++) {
        this.opcode('lookup', id.parts[i]);
      }
    },
    DATA: function(data) {
      this.options.data = true;
      if (data.id.isScoped || data.id.depth) {
        throw new Exception('Scoped data references are not supported: ' + data.original, data);
      }
      this.opcode('lookupData');
      var parts = data.id.parts;
      for (var i = 0, l = parts.length; i < l; i++) {
        this.opcode('lookup', parts[i]);
      }
    },
    STRING: function(string) {
      this.opcode('pushString', string.string);
    },
    INTEGER: function(integer) {
      this.opcode('pushLiteral', integer.integer);
    },
    BOOLEAN: function(bool) {
      this.opcode('pushLiteral', bool.bool);
    },
    comment: function() {},
    // HELPERS
    opcode: function(name) {
      this.opcodes.push({
        opcode: name,
        args: [].slice.call(arguments, 1)
      });
    },
    declare: function(name, value) {
      this.opcodes.push({
        opcode: 'DECLARE',
        name: name,
        value: value
      });
    },
    addDepth: function(depth) {
      if (depth === 0) {
        return;
      }
      if (!this.depths[depth]) {
        this.depths[depth] = true;
        this.depths.list.push(depth);
      }
    },
    classifySexpr: function(sexpr) {
      var isHelper = sexpr.isHelper;
      var isEligible = sexpr.eligibleHelper;
      var options = this.options;
      // if ambiguous, we can possibly resolve the ambiguity now
      if (isEligible && !isHelper) {
        var name = sexpr.id.parts[0];
        if (options.knownHelpers[name]) {
          isHelper = true;
        } else if (options.knownHelpersOnly) {
          isEligible = false;
        }
      }
      if (isHelper) {
        return "helper";
      } else if (isEligible) {
        return "ambiguous";
      } else {
        return "simple";
      }
    },
    pushParams: function(params) {
      var i = params.length,
        param;
      while (i--) {
        param = params[i];
        if (this.options.stringParams) {
          if (param.depth) {
            this.addDepth(param.depth);
          }
          this.opcode('getContext', param.depth || 0);
          this.opcode('pushStringParam', param.stringModeValue, param.type);
          if (param.type === 'sexpr') {
            // Subexpressions get evaluated and passed in
            // in string params mode.
            this.sexpr(param);
          }
        } else {
          this[param.type](param);
        }
      }
    },
    setupFullMustacheParams: function(sexpr, program, inverse) {
      var params = sexpr.params;
      this.pushParams(params);
      this.opcode('pushProgram', program);
      this.opcode('pushProgram', inverse);
      if (sexpr.hash) {
        this.hash(sexpr.hash);
      } else {
        this.opcode('emptyHash');
      }
      return params;
    }
  };

  function precompile(input, options, env) {
    if (input == null || (typeof input !== 'string' && input.constructor !== env.AST.ProgramNode)) {
      throw new Exception("You must pass a string or Handlebars AST to Handlebars.precompile. You passed " + input);
    }
    options = options || {};
    if (!('data' in options)) {
      options.data = true;
    }
    var ast = env.parse(input);
    var environment = new env.Compiler().compile(ast, options);
    return new env.JavaScriptCompiler().compile(environment, options);
  }
  exports.precompile = precompile;

  function compile(input, options, env) {
    if (input == null || (typeof input !== 'string' && input.constructor !== env.AST.ProgramNode)) {
      throw new Exception("You must pass a string or Handlebars AST to Handlebars.compile. You passed " + input);
    }
    options = options || {};
    if (!('data' in options)) {
      options.data = true;
    }
    var compiled;

    function compileInput() {
        var ast = env.parse(input);
        var environment = new env.Compiler().compile(ast, options);
        var templateSpec = new env.JavaScriptCompiler().compile(environment, options, undefined, true);
        return env.template(templateSpec);
      }
      // Template is only compiled on first use and cached after that point.
    return function(context, options) {
      if (!compiled) {
        compiled = compileInput();
      }
      return compiled.call(this, context, options);
    };
  }
  exports.compile = compile;
});
define("handlebars/1.3.0/dist/cjs/handlebars/compiler/javascript-compiler-debug", [], function(require, exports, module) {
  "use strict";
  var COMPILER_REVISION = require("handlebars/1.3.0/dist/cjs/handlebars/base-debug").COMPILER_REVISION;
  var REVISION_CHANGES = require("handlebars/1.3.0/dist/cjs/handlebars/base-debug").REVISION_CHANGES;
  var log = require("handlebars/1.3.0/dist/cjs/handlebars/base-debug").log;
  var Exception = require("handlebars/1.3.0/dist/cjs/handlebars/exception-debug")["default"];

  function Literal(value) {
    this.value = value;
  }

  function JavaScriptCompiler() {}
  JavaScriptCompiler.prototype = {
    // PUBLIC API: You can override these methods in a subclass to provide
    // alternative compiled forms for name lookup and buffering semantics
    nameLookup: function(parent, name /* , type*/ ) {
      var wrap,
        ret;
      if (parent.indexOf('depth') === 0) {
        wrap = true;
      }
      if (/^[0-9]+$/.test(name)) {
        ret = parent + "[" + name + "]";
      } else if (JavaScriptCompiler.isValidJavaScriptVariableName(name)) {
        ret = parent + "." + name;
      } else {
        ret = parent + "['" + name + "']";
      }
      if (wrap) {
        return '(' + parent + ' && ' + ret + ')';
      } else {
        return ret;
      }
    },
    compilerInfo: function() {
      var revision = COMPILER_REVISION,
        versions = REVISION_CHANGES[revision];
      return "this.compilerInfo = [" + revision + ",'" + versions + "'];\n";
    },
    appendToBuffer: function(string) {
      if (this.environment.isSimple) {
        return "return " + string + ";";
      } else {
        return {
          appendToBuffer: true,
          content: string,
          toString: function() {
            return "buffer += " + string + ";";
          }
        };
      }
    },
    initializeBuffer: function() {
      return this.quotedString("");
    },
    namespace: "Handlebars",
    // END PUBLIC API
    compile: function(environment, options, context, asObject) {
      this.environment = environment;
      this.options = options || {};
      log('debug', this.environment.disassemble() + "\n\n");
      this.name = this.environment.name;
      this.isChild = !!context;
      this.context = context || {
        programs: [],
        environments: [],
        aliases: {}
      };
      this.preamble();
      this.stackSlot = 0;
      this.stackVars = [];
      this.registers = {
        list: []
      };
      this.hashes = [];
      this.compileStack = [];
      this.inlineStack = [];
      this.compileChildren(environment, options);
      var opcodes = environment.opcodes,
        opcode;
      this.i = 0;
      for (var l = opcodes.length; this.i < l; this.i++) {
        opcode = opcodes[this.i];
        if (opcode.opcode === 'DECLARE') {
          this[opcode.name] = opcode.value;
        } else {
          this[opcode.opcode].apply(this, opcode.args);
        }
        // Reset the stripNext flag if it was not set by this operation.
        if (opcode.opcode !== this.stripNext) {
          this.stripNext = false;
        }
      }
      // Flush any trailing content that might be pending.
      this.pushSource('');
      if (this.stackSlot || this.inlineStack.length || this.compileStack.length) {
        throw new Exception('Compile completed with content left on stack');
      }
      return this.createFunctionContext(asObject);
    },
    preamble: function() {
      var out = [];
      if (!this.isChild) {
        var namespace = this.namespace;
        var copies = "helpers = this.merge(helpers, " + namespace + ".helpers);";
        if (this.environment.usePartial) {
          copies = copies + " partials = this.merge(partials, " + namespace + ".partials);";
        }
        if (this.options.data) {
          copies = copies + " data = data || {};";
        }
        out.push(copies);
      } else {
        out.push('');
      }
      if (!this.environment.isSimple) {
        out.push(", buffer = " + this.initializeBuffer());
      } else {
        out.push("");
      }
      // track the last context pushed into place to allow skipping the
      // getContext opcode when it would be a noop
      this.lastContext = 0;
      this.source = out;
    },
    createFunctionContext: function(asObject) {
      var locals = this.stackVars.concat(this.registers.list);
      if (locals.length > 0) {
        this.source[1] = this.source[1] + ", " + locals.join(", ");
      }
      // Generate minimizer alias mappings
      if (!this.isChild) {
        for (var alias in this.context.aliases) {
          if (this.context.aliases.hasOwnProperty(alias)) {
            this.source[1] = this.source[1] + ', ' + alias + '=' + this.context.aliases[alias];
          }
        }
      }
      if (this.source[1]) {
        this.source[1] = "var " + this.source[1].substring(2) + ";";
      }
      // Merge children
      if (!this.isChild) {
        this.source[1] += '\n' + this.context.programs.join('\n') + '\n';
      }
      if (!this.environment.isSimple) {
        this.pushSource("return buffer;");
      }
      var params = this.isChild ? ["depth0", "data"] : ["Handlebars", "depth0", "helpers", "partials", "data"];
      for (var i = 0, l = this.environment.depths.list.length; i < l; i++) {
        params.push("depth" + this.environment.depths.list[i]);
      }
      // Perform a second pass over the output to merge content when possible
      var source = this.mergeSource();
      if (!this.isChild) {
        source = this.compilerInfo() + source;
      }
      if (asObject) {
        params.push(source);
        return Function.apply(this, params);
      } else {
        var functionSource = 'function ' + (this.name || '') + '(' + params.join(',') + ') {\n  ' + source + '}';
        log('debug', functionSource + "\n\n");
        return functionSource;
      }
    },
    mergeSource: function() {
      // WARN: We are not handling the case where buffer is still populated as the source should
      // not have buffer append operations as their final action.
      var source = '',
        buffer;
      for (var i = 0, len = this.source.length; i < len; i++) {
        var line = this.source[i];
        if (line.appendToBuffer) {
          if (buffer) {
            buffer = buffer + '\n    + ' + line.content;
          } else {
            buffer = line.content;
          }
        } else {
          if (buffer) {
            source += 'buffer += ' + buffer + ';\n  ';
            buffer = undefined;
          }
          source += line + '\n  ';
        }
      }
      return source;
    },
    // [blockValue]
    //
    // On stack, before: hash, inverse, program, value
    // On stack, after: return value of blockHelperMissing
    //
    // The purpose of this opcode is to take a block of the form
    // `{{#foo}}...{{/foo}}`, resolve the value of `foo`, and
    // replace it on the stack with the result of properly
    // invoking blockHelperMissing.
    blockValue: function() {
      this.context.aliases.blockHelperMissing = 'helpers.blockHelperMissing';
      var params = ["depth0"];
      this.setupParams(0, params);
      this.replaceStack(function(current) {
        params.splice(1, 0, current);
        return "blockHelperMissing.call(" + params.join(", ") + ")";
      });
    },
    // [ambiguousBlockValue]
    //
    // On stack, before: hash, inverse, program, value
    // Compiler value, before: lastHelper=value of last found helper, if any
    // On stack, after, if no lastHelper: same as [blockValue]
    // On stack, after, if lastHelper: value
    ambiguousBlockValue: function() {
      this.context.aliases.blockHelperMissing = 'helpers.blockHelperMissing';
      var params = ["depth0"];
      this.setupParams(0, params);
      var current = this.topStack();
      params.splice(1, 0, current);
      this.pushSource("if (!" + this.lastHelper + ") { " + current + " = blockHelperMissing.call(" + params.join(", ") + "); }");
    },
    // [appendContent]
    //
    // On stack, before: ...
    // On stack, after: ...
    //
    // Appends the string value of `content` to the current buffer
    appendContent: function(content) {
      if (this.pendingContent) {
        content = this.pendingContent + content;
      }
      if (this.stripNext) {
        content = content.replace(/^\s+/, '');
      }
      this.pendingContent = content;
    },
    // [strip]
    //
    // On stack, before: ...
    // On stack, after: ...
    //
    // Removes any trailing whitespace from the prior content node and flags
    // the next operation for stripping if it is a content node.
    strip: function() {
      if (this.pendingContent) {
        this.pendingContent = this.pendingContent.replace(/\s+$/, '');
      }
      this.stripNext = 'strip';
    },
    // [append]
    //
    // On stack, before: value, ...
    // On stack, after: ...
    //
    // Coerces `value` to a String and appends it to the current buffer.
    //
    // If `value` is truthy, or 0, it is coerced into a string and appended
    // Otherwise, the empty string is appended
    append: function() {
      // Force anything that is inlined onto the stack so we don't have duplication
      // when we examine local
      this.flushInline();
      var local = this.popStack();
      this.pushSource("if(" + local + " || " + local + " === 0) { " + this.appendToBuffer(local) + " }");
      if (this.environment.isSimple) {
        this.pushSource("else { " + this.appendToBuffer("''") + " }");
      }
    },
    // [appendEscaped]
    //
    // On stack, before: value, ...
    // On stack, after: ...
    //
    // Escape `value` and append it to the buffer
    appendEscaped: function() {
      this.context.aliases.escapeExpression = 'this.escapeExpression';
      this.pushSource(this.appendToBuffer("escapeExpression(" + this.popStack() + ")"));
    },
    // [getContext]
    //
    // On stack, before: ...
    // On stack, after: ...
    // Compiler value, after: lastContext=depth
    //
    // Set the value of the `lastContext` compiler value to the depth
    getContext: function(depth) {
      if (this.lastContext !== depth) {
        this.lastContext = depth;
      }
    },
    // [lookupOnContext]
    //
    // On stack, before: ...
    // On stack, after: currentContext[name], ...
    //
    // Looks up the value of `name` on the current context and pushes
    // it onto the stack.
    lookupOnContext: function(name) {
      this.push(this.nameLookup('depth' + this.lastContext, name, 'context'));
    },
    // [pushContext]
    //
    // On stack, before: ...
    // On stack, after: currentContext, ...
    //
    // Pushes the value of the current context onto the stack.
    pushContext: function() {
      this.pushStackLiteral('depth' + this.lastContext);
    },
    // [resolvePossibleLambda]
    //
    // On stack, before: value, ...
    // On stack, after: resolved value, ...
    //
    // If the `value` is a lambda, replace it on the stack by
    // the return value of the lambda
    resolvePossibleLambda: function() {
      this.context.aliases.functionType = '"function"';
      this.replaceStack(function(current) {
        return "typeof " + current + " === functionType ? " + current + ".apply(depth0) : " + current;
      });
    },
    // [lookup]
    //
    // On stack, before: value, ...
    // On stack, after: value[name], ...
    //
    // Replace the value on the stack with the result of looking
    // up `name` on `value`
    lookup: function(name) {
      this.replaceStack(function(current) {
        return current + " == null || " + current + " === false ? " + current + " : " + this.nameLookup(current, name, 'context');
      });
    },
    // [lookupData]
    //
    // On stack, before: ...
    // On stack, after: data, ...
    //
    // Push the data lookup operator
    lookupData: function() {
      this.pushStackLiteral('data');
    },
    // [pushStringParam]
    //
    // On stack, before: ...
    // On stack, after: string, currentContext, ...
    //
    // This opcode is designed for use in string mode, which
    // provides the string value of a parameter along with its
    // depth rather than resolving it immediately.
    pushStringParam: function(string, type) {
      this.pushStackLiteral('depth' + this.lastContext);
      this.pushString(type);
      // If it's a subexpression, the string result
      // will be pushed after this opcode.
      if (type !== 'sexpr') {
        if (typeof string === 'string') {
          this.pushString(string);
        } else {
          this.pushStackLiteral(string);
        }
      }
    },
    emptyHash: function() {
      this.pushStackLiteral('{}');
      if (this.options.stringParams) {
        this.push('{}'); // hashContexts
        this.push('{}'); // hashTypes
      }
    },
    pushHash: function() {
      if (this.hash) {
        this.hashes.push(this.hash);
      }
      this.hash = {
        values: [],
        types: [],
        contexts: []
      };
    },
    popHash: function() {
      var hash = this.hash;
      this.hash = this.hashes.pop();
      if (this.options.stringParams) {
        this.push('{' + hash.contexts.join(',') + '}');
        this.push('{' + hash.types.join(',') + '}');
      }
      this.push('{\n    ' + hash.values.join(',\n    ') + '\n  }');
    },
    // [pushString]
    //
    // On stack, before: ...
    // On stack, after: quotedString(string), ...
    //
    // Push a quoted version of `string` onto the stack
    pushString: function(string) {
      this.pushStackLiteral(this.quotedString(string));
    },
    // [push]
    //
    // On stack, before: ...
    // On stack, after: expr, ...
    //
    // Push an expression onto the stack
    push: function(expr) {
      this.inlineStack.push(expr);
      return expr;
    },
    // [pushLiteral]
    //
    // On stack, before: ...
    // On stack, after: value, ...
    //
    // Pushes a value onto the stack. This operation prevents
    // the compiler from creating a temporary variable to hold
    // it.
    pushLiteral: function(value) {
      this.pushStackLiteral(value);
    },
    // [pushProgram]
    //
    // On stack, before: ...
    // On stack, after: program(guid), ...
    //
    // Push a program expression onto the stack. This takes
    // a compile-time guid and converts it into a runtime-accessible
    // expression.
    pushProgram: function(guid) {
      if (guid != null) {
        this.pushStackLiteral(this.programExpression(guid));
      } else {
        this.pushStackLiteral(null);
      }
    },
    // [invokeHelper]
    //
    // On stack, before: hash, inverse, program, params..., ...
    // On stack, after: result of helper invocation
    //
    // Pops off the helper's parameters, invokes the helper,
    // and pushes the helper's return value onto the stack.
    //
    // If the helper is not found, `helperMissing` is called.
    invokeHelper: function(paramSize, name, isRoot) {
      this.context.aliases.helperMissing = 'helpers.helperMissing';
      this.useRegister('helper');
      var helper = this.lastHelper = this.setupHelper(paramSize, name, true);
      var nonHelper = this.nameLookup('depth' + this.lastContext, name, 'context');
      var lookup = 'helper = ' + helper.name + ' || ' + nonHelper;
      if (helper.paramsInit) {
        lookup += ',' + helper.paramsInit;
      }
      this.push('(' + lookup + ',helper ' + '? helper.call(' + helper.callParams + ') ' + ': helperMissing.call(' + helper.helperMissingParams + '))');
      // Always flush subexpressions. This is both to prevent the compounding size issue that
      // occurs when the code has to be duplicated for inlining and also to prevent errors
      // due to the incorrect options object being passed due to the shared register.
      if (!isRoot) {
        this.flushInline();
      }
    },
    // [invokeKnownHelper]
    //
    // On stack, before: hash, inverse, program, params..., ...
    // On stack, after: result of helper invocation
    //
    // This operation is used when the helper is known to exist,
    // so a `helperMissing` fallback is not required.
    invokeKnownHelper: function(paramSize, name) {
      var helper = this.setupHelper(paramSize, name);
      this.push(helper.name + ".call(" + helper.callParams + ")");
    },
    // [invokeAmbiguous]
    //
    // On stack, before: hash, inverse, program, params..., ...
    // On stack, after: result of disambiguation
    //
    // This operation is used when an expression like `{{foo}}`
    // is provided, but we don't know at compile-time whether it
    // is a helper or a path.
    //
    // This operation emits more code than the other options,
    // and can be avoided by passing the `knownHelpers` and
    // `knownHelpersOnly` flags at compile-time.
    invokeAmbiguous: function(name, helperCall) {
      this.context.aliases.functionType = '"function"';
      this.useRegister('helper');
      this.emptyHash();
      var helper = this.setupHelper(0, name, helperCall);
      var helperName = this.lastHelper = this.nameLookup('helpers', name, 'helper');
      var nonHelper = this.nameLookup('depth' + this.lastContext, name, 'context');
      var nextStack = this.nextStack();
      if (helper.paramsInit) {
        this.pushSource(helper.paramsInit);
      }
      this.pushSource('if (helper = ' + helperName + ') { ' + nextStack + ' = helper.call(' + helper.callParams + '); }');
      this.pushSource('else { helper = ' + nonHelper + '; ' + nextStack + ' = typeof helper === functionType ? helper.call(' + helper.callParams + ') : helper; }');
    },
    // [invokePartial]
    //
    // On stack, before: context, ...
    // On stack after: result of partial invocation
    //
    // This operation pops off a context, invokes a partial with that context,
    // and pushes the result of the invocation back.
    invokePartial: function(name) {
      var params = [this.nameLookup('partials', name, 'partial'), "'" + name + "'", this.popStack(), "helpers", "partials"];
      if (this.options.data) {
        params.push("data");
      }
      this.context.aliases.self = "this";
      this.push("self.invokePartial(" + params.join(", ") + ")");
    },
    // [assignToHash]
    //
    // On stack, before: value, hash, ...
    // On stack, after: hash, ...
    //
    // Pops a value and hash off the stack, assigns `hash[key] = value`
    // and pushes the hash back onto the stack.
    assignToHash: function(key) {
      var value = this.popStack(),
        context,
        type;
      if (this.options.stringParams) {
        type = this.popStack();
        context = this.popStack();
      }
      var hash = this.hash;
      if (context) {
        hash.contexts.push("'" + key + "': " + context);
      }
      if (type) {
        hash.types.push("'" + key + "': " + type);
      }
      hash.values.push("'" + key + "': (" + value + ")");
    },
    // HELPERS
    compiler: JavaScriptCompiler,
    compileChildren: function(environment, options) {
      var children = environment.children,
        child, compiler;
      for (var i = 0, l = children.length; i < l; i++) {
        child = children[i];
        compiler = new this.compiler();
        var index = this.matchExistingProgram(child);
        if (index == null) {
          this.context.programs.push(''); // Placeholder to prevent name conflicts for nested children
          index = this.context.programs.length;
          child.index = index;
          child.name = 'program' + index;
          this.context.programs[index] = compiler.compile(child, options, this.context);
          this.context.environments[index] = child;
        } else {
          child.index = index;
          child.name = 'program' + index;
        }
      }
    },
    matchExistingProgram: function(child) {
      for (var i = 0, len = this.context.environments.length; i < len; i++) {
        var environment = this.context.environments[i];
        if (environment && environment.equals(child)) {
          return i;
        }
      }
    },
    programExpression: function(guid) {
      this.context.aliases.self = "this";
      if (guid == null) {
        return "self.noop";
      }
      var child = this.environment.children[guid],
        depths = child.depths.list,
        depth;
      var programParams = [child.index, child.name, "data"];
      for (var i = 0, l = depths.length; i < l; i++) {
        depth = depths[i];
        if (depth === 1) {
          programParams.push("depth0");
        } else {
          programParams.push("depth" + (depth - 1));
        }
      }
      return (depths.length === 0 ? "self.program(" : "self.programWithDepth(") + programParams.join(", ") + ")";
    },
    register: function(name, val) {
      this.useRegister(name);
      this.pushSource(name + " = " + val + ";");
    },
    useRegister: function(name) {
      if (!this.registers[name]) {
        this.registers[name] = true;
        this.registers.list.push(name);
      }
    },
    pushStackLiteral: function(item) {
      return this.push(new Literal(item));
    },
    pushSource: function(source) {
      if (this.pendingContent) {
        this.source.push(this.appendToBuffer(this.quotedString(this.pendingContent)));
        this.pendingContent = undefined;
      }
      if (source) {
        this.source.push(source);
      }
    },
    pushStack: function(item) {
      this.flushInline();
      var stack = this.incrStack();
      if (item) {
        this.pushSource(stack + " = " + item + ";");
      }
      this.compileStack.push(stack);
      return stack;
    },
    replaceStack: function(callback) {
      var prefix = '',
        inline = this.isInline(),
        stack,
        createdStack,
        usedLiteral;
      // If we are currently inline then we want to merge the inline statement into the
      // replacement statement via ','
      if (inline) {
        var top = this.popStack(true);
        if (top instanceof Literal) {
          // Literals do not need to be inlined
          stack = top.value;
          usedLiteral = true;
        } else {
          // Get or create the current stack name for use by the inline
          createdStack = !this.stackSlot;
          var name = !createdStack ? this.topStackName() : this.incrStack();
          prefix = '(' + this.push(name) + ' = ' + top + '),';
          stack = this.topStack();
        }
      } else {
        stack = this.topStack();
      }
      var item = callback.call(this, stack);
      if (inline) {
        if (!usedLiteral) {
          this.popStack();
        }
        if (createdStack) {
          this.stackSlot--;
        }
        this.push('(' + prefix + item + ')');
      } else {
        // Prevent modification of the context depth variable. Through replaceStack
        if (!/^stack/.test(stack)) {
          stack = this.nextStack();
        }
        this.pushSource(stack + " = (" + prefix + item + ");");
      }
      return stack;
    },
    nextStack: function() {
      return this.pushStack();
    },
    incrStack: function() {
      this.stackSlot++;
      if (this.stackSlot > this.stackVars.length) {
        this.stackVars.push("stack" + this.stackSlot);
      }
      return this.topStackName();
    },
    topStackName: function() {
      return "stack" + this.stackSlot;
    },
    flushInline: function() {
      var inlineStack = this.inlineStack;
      if (inlineStack.length) {
        this.inlineStack = [];
        for (var i = 0, len = inlineStack.length; i < len; i++) {
          var entry = inlineStack[i];
          if (entry instanceof Literal) {
            this.compileStack.push(entry);
          } else {
            this.pushStack(entry);
          }
        }
      }
    },
    isInline: function() {
      return this.inlineStack.length;
    },
    popStack: function(wrapped) {
      var inline = this.isInline(),
        item = (inline ? this.inlineStack : this.compileStack).pop();
      if (!wrapped && (item instanceof Literal)) {
        return item.value;
      } else {
        if (!inline) {
          if (!this.stackSlot) {
            throw new Exception('Invalid stack pop');
          }
          this.stackSlot--;
        }
        return item;
      }
    },
    topStack: function(wrapped) {
      var stack = (this.isInline() ? this.inlineStack : this.compileStack),
        item = stack[stack.length - 1];
      if (!wrapped && (item instanceof Literal)) {
        return item.value;
      } else {
        return item;
      }
    },
    quotedString: function(str) {
      return '"' + str.replace(/\\/g, '\\\\').replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\r/g, '\\r').replace(/\u2028/g, '\\u2028') // Per Ecma-262 7.3 + 7.8.4
        .replace(/\u2029/g, '\\u2029') + '"';
    },
    setupHelper: function(paramSize, name, missingParams) {
      var params = [],
        paramsInit = this.setupParams(paramSize, params, missingParams);
      var foundHelper = this.nameLookup('helpers', name, 'helper');
      return {
        params: params,
        paramsInit: paramsInit,
        name: foundHelper,
        callParams: ["depth0"].concat(params).join(", "),
        helperMissingParams: missingParams && ["depth0", this.quotedString(name)].concat(params).join(", ")
      };
    },
    setupOptions: function(paramSize, params) {
      var options = [],
        contexts = [],
        types = [],
        param, inverse, program;
      options.push("hash:" + this.popStack());
      if (this.options.stringParams) {
        options.push("hashTypes:" + this.popStack());
        options.push("hashContexts:" + this.popStack());
      }
      inverse = this.popStack();
      program = this.popStack();
      // Avoid setting fn and inverse if neither are set. This allows
      // helpers to do a check for `if (options.fn)`
      if (program || inverse) {
        if (!program) {
          this.context.aliases.self = "this";
          program = "self.noop";
        }
        if (!inverse) {
          this.context.aliases.self = "this";
          inverse = "self.noop";
        }
        options.push("inverse:" + inverse);
        options.push("fn:" + program);
      }
      for (var i = 0; i < paramSize; i++) {
        param = this.popStack();
        params.push(param);
        if (this.options.stringParams) {
          types.push(this.popStack());
          contexts.push(this.popStack());
        }
      }
      if (this.options.stringParams) {
        options.push("contexts:[" + contexts.join(",") + "]");
        options.push("types:[" + types.join(",") + "]");
      }
      if (this.options.data) {
        options.push("data:data");
      }
      return options;
    },
    // the params and contexts arguments are passed in arrays
    // to fill in
    setupParams: function(paramSize, params, useRegister) {
      var options = '{' + this.setupOptions(paramSize, params).join(',') + '}';
      if (useRegister) {
        this.useRegister('options');
        params.push('options');
        return 'options=' + options;
      } else {
        params.push(options);
        return '';
      }
    }
  };
  var reservedWords = ("break else new var" + " case finally return void" + " catch for switch while" + " continue function this with" + " default if throw" + " delete in try" + " do instanceof typeof" + " abstract enum int short" + " boolean export interface static" + " byte extends long super" + " char final native synchronized" + " class float package throws" + " const goto private transient" + " debugger implements protected volatile" + " double import public let yield").split(" ");
  var compilerWords = JavaScriptCompiler.RESERVED_WORDS = {};
  for (var i = 0, l = reservedWords.length; i < l; i++) {
    compilerWords[reservedWords[i]] = true;
  }
  JavaScriptCompiler.isValidJavaScriptVariableName = function(name) {
    if (!JavaScriptCompiler.RESERVED_WORDS[name] && /^[a-zA-Z_$][0-9a-zA-Z_$]*$/.test(name)) {
      return true;
    }
    return false;
  };
  exports["default"] = JavaScriptCompiler;
});
define("arale-autocomplete/1.4.1/src/data-source-debug", ["jquery"], function(require, exports, module) {
  var Base = require("arale-base/1.2.0/base-debug");
  var $ = require('jquery');
  var DataSource = Base.extend({
    attrs: {
      source: null,
      type: 'array'
    },
    initialize: function(config) {
      DataSource.superclass.initialize.call(this, config);
      // 每次发送请求会将 id 记录到 callbacks 中，返回后会从中删除
      // 如果 abort 会清空 callbacks，之前的请求结果都不会执行
      this.id = 0;
      this.callbacks = [];
      var source = this.get('source');
      if (isString(source)) {
        this.set('type', 'url');
      } else if ($.isArray(source)) {
        this.set('type', 'array');
      } else if ($.isPlainObject(source)) {
        this.set('type', 'object');
      } else if ($.isFunction(source)) {
        this.set('type', 'function');
      } else {
        throw new Error('Source Type Error');
      }
    },
    getData: function(query) {
      return this['_get' + capitalize(this.get('type') || '') + 'Data'](query);
    },
    abort: function() {
      this.callbacks = [];
    },
    // 完成数据请求，getData => done
    _done: function(data) {
      this.trigger('data', data);
    },
    _getUrlData: function(query) {
      var that = this,
        options;
      var obj = {
        query: query ? encodeURIComponent(query) : '',
        timestamp: new Date().getTime()
      };
      var url = this.get('source').replace(/\{\{(.*?)\}\}/g, function(all, match) {
        return obj[match];
      });
      var callbackId = 'callback_' + this.id++;
      this.callbacks.push(callbackId);
      if (/^(https?:\/\/)/.test(url)) {
        options = {
          dataType: 'jsonp'
        };
      } else {
        options = {
          dataType: 'json'
        };
      }
      $.ajax(url, options).success(function(data) {
        if ($.inArray(callbackId, that.callbacks) > -1) {
          delete that.callbacks[callbackId];
          that._done(data);
        }
      }).error(function() {
        if ($.inArray(callbackId, that.callbacks) > -1) {
          delete that.callbacks[callbackId];
          that._done({});
        }
      });
    },
    _getArrayData: function() {
      var source = this.get('source');
      this._done(source);
      return source;
    },
    _getObjectData: function() {
      var source = this.get('source');
      this._done(source);
      return source;
    },
    _getFunctionData: function(query) {
      var that = this,
        func = this.get('source');
      // 如果返回 false 可阻止执行
      var data = func.call(this, query, done);
      if (data) {
        this._done(data);
      }

      function done(data) {
        that._done(data);
      }
    }
  });
  module.exports = DataSource;

  function isString(str) {
    return Object.prototype.toString.call(str) === '[object String]';
  }

  function capitalize(str) {
    return str.replace(/^([a-z])/, function(f, m) {
      return m.toUpperCase();
    });
  }
});
define("arale-autocomplete/1.4.1/src/filter-debug", ["jquery"], function(require, exports, module) {
  var $ = require('jquery');
  var Filter = {
    'default': function(data) {
      return data;
    },
    'startsWith': function(data, query) {
      query = query || '';
      var result = [],
        l = query.length,
        reg = new RegExp('^' + escapeKeyword(query));
      if (!l) return [];
      $.each(data, function(index, item) {
        var a, matchKeys = [item.value].concat(item.alias);
        // 匹配 value 和 alias 中的
        while (a = matchKeys.shift()) {
          if (reg.test(a)) {
            // 匹配和显示相同才有必要高亮
            if (item.label === a) {
              item.highlightIndex = [
                [0, l]
              ];
            }
            result.push(item);
            break;
          }
        }
      });
      return result;
    },
    'stringMatch': function(data, query) {
      query = query || '';
      var result = [],
        l = query.length;
      if (!l) return [];
      $.each(data, function(index, item) {
        var a, matchKeys = [item.value].concat(item.alias);
        // 匹配 value 和 alias 中的
        while (a = matchKeys.shift()) {
          if (a.indexOf(query) > -1) {
            // 匹配和显示相同才有必要高亮
            if (item.label === a) {
              item.highlightIndex = stringMatch(a, query);
            }
            result.push(item);
            break;
          }
        }
      });
      return result;
    }
  };
  module.exports = Filter;
  // 转义正则关键字
  var keyword = /(\[|\[|\]|\^|\$|\||\(|\)|\{|\}|\+|\*|\?|\\)/g;

  function escapeKeyword(str) {
    return (str || '').replace(keyword, '\\$1');
  }

  function stringMatch(matchKey, query) {
    var r = [],
      a = matchKey.split('');
    var queryIndex = 0,
      q = query.split('');
    for (var i = 0, l = a.length; i < l; i++) {
      var v = a[i];
      if (v === q[queryIndex]) {
        if (queryIndex === q.length - 1) {
          r.push([i - q.length + 1, i + 1]);
          queryIndex = 0;
          continue;
        }
        queryIndex++;
      } else {
        queryIndex = 0;
      }
    }
    return r;
  }
});
define("arale-autocomplete/1.4.1/src/input-debug", ["jquery"], function(require, exports, module) {
  var $ = require('jquery');
  var Base = require("arale-base/1.2.0/base-debug");
  var lteIE9 = /\bMSIE [6789]\.0\b/.test(navigator.userAgent);
  var specialKeyCodeMap = {
    9: 'tab',
    27: 'esc',
    37: 'left',
    39: 'right',
    13: 'enter',
    38: 'up',
    40: 'down'
  };
  var Input = Base.extend({
    attrs: {
      element: {
        value: null,
        setter: function(val) {
          return $(val);
        }
      },
      query: null,
      delay: 100
    },
    initialize: function() {
      Input.superclass.initialize.apply(this, arguments);
      // bind events
      this._bindEvents();
      // init query
      this.set('query', this.getValue());
    },
    focus: function() {
      this.get('element').focus();
    },
    getValue: function() {
      return this.get('element').val();
    },
    setValue: function(val, silent) {
      this.get('element').val(val);
      !silent && this._change();
    },
    destroy: function() {
      Input.superclass.destroy.call(this);
    },
    _bindEvents: function() {
      var timer, input = this.get('element');
      input.attr('autocomplete', 'off').on('focus.autocomplete', wrapFn(this._handleFocus, this)).on('blur.autocomplete', wrapFn(this._handleBlur, this)).on('keydown.autocomplete', wrapFn(this._handleKeydown, this));
      // IE678 don't support input event
      // IE 9 does not fire an input event when the user removes characters from input filled by keyboard, cut, or drag operations.
      if (!lteIE9) {
        input.on('input.autocomplete', wrapFn(this._change, this));
      } else {
        var that = this,
          events = ['keydown.autocomplete', 'keypress.autocomplete', 'cut.autocomplete', 'paste.autocomplete'].join(' ');
        input.on(events, wrapFn(function(e) {
          if (specialKeyCodeMap[e.which]) return;
          clearTimeout(timer);
          timer = setTimeout(function() {
            that._change.call(that, e);
          }, this.get('delay'));
        }, this));
      }
    },
    _change: function() {
      var newVal = this.getValue();
      var oldVal = this.get('query');
      var isSame = compare(oldVal, newVal);
      var isSameExpectWhitespace = isSame ? (newVal.length !== oldVal.length) : false;
      if (isSameExpectWhitespace) {
        this.trigger('whitespaceChanged', oldVal);
      }
      if (!isSame) {
        this.set('query', newVal);
        this.trigger('queryChanged', newVal, oldVal);
      }
    },
    _handleFocus: function(e) {
      this.trigger('focus', e);
    },
    _handleBlur: function(e) {
      this.trigger('blur', e);
    },
    _handleKeydown: function(e) {
      var keyName = specialKeyCodeMap[e.which];
      if (keyName) {
        var eventKey = 'key' + ucFirst(keyName);
        this.trigger(e.type = eventKey, e);
      }
    }
  });
  module.exports = Input;

  function wrapFn(fn, context) {
    return function() {
      fn.apply(context, arguments);
    };
  }

  function compare(a, b) {
    a = (a || '').replace(/^\s*/g, '').replace(/\s{2,}/g, ' ');
    b = (b || '').replace(/^\s*/g, '').replace(/\s{2,}/g, ' ');
    return a === b;
  }

  function ucFirst(str) {
    return str.charAt(0).toUpperCase() + str.substring(1);
  }
});
define("arale-autocomplete/1.4.1/src/autocomplete-debug.handlebars", [], function(require, exports, module) {
  var Handlebars = require("handlebars-runtime/1.3.0/dist/cjs/handlebars.runtime-debug")["default"];
  module.exports = Handlebars.template(function(Handlebars, depth0, helpers, partials, data) {
    this.compilerInfo = [4, '>= 1.0.0'];
    helpers = this.merge(helpers, Handlebars.helpers);
    partials = this.merge(partials, Handlebars.partials);
    data = data || {};
    var buffer = "",
      stack1, helper, self = this,
      functionType = "function",
      escapeExpression = this.escapeExpression,
      helperMissing = helpers.helperMissing;

    function program1(depth0, data, depth1) {
      var buffer = "",
        stack1, helper, options;
      buffer += "\n      <li data-role=\"item\" class=\"" + escapeExpression(((stack1 = (depth1 && depth1.classPrefix)), typeof stack1 === functionType ? stack1.apply(depth0) : stack1)) + "-item\">\n        ";
      stack1 = (helper = helpers.include || (depth1 && depth1.include), options = {
        hash: {
          'parent': (depth1)
        },
        inverse: self.noop,
        fn: self.program(2, program2, data),
        data: data
      }, helper ? helper.call(depth0, options) : helperMissing.call(depth0, "include", options));
      if (stack1 || stack1 === 0) {
        buffer += stack1;
      }
      buffer += "\n      </li>\n    ";
      return buffer;
    }

    function program2(depth0, data) {
      var stack1;
      stack1 = self.invokePartial(partials.html, 'html', depth0, helpers, partials, data);
      if (stack1 || stack1 === 0) {
        return stack1;
      } else {
        return '';
      }
    }
    buffer += "<div class=\"";
    if (helper = helpers.classPrefix) {
      stack1 = helper.call(depth0, {
        hash: {},
        data: data
      });
    } else {
      helper = (depth0 && depth0.classPrefix);
      stack1 = typeof helper === functionType ? helper.call(depth0, {
        hash: {},
        data: data
      }) : helper;
    }
    buffer += escapeExpression(stack1) + "\">\n  <div class=\"";
    if (helper = helpers.classPrefix) {
      stack1 = helper.call(depth0, {
        hash: {},
        data: data
      });
    } else {
      helper = (depth0 && depth0.classPrefix);
      stack1 = typeof helper === functionType ? helper.call(depth0, {
        hash: {},
        data: data
      }) : helper;
    }
    buffer += escapeExpression(stack1) + "-content\">\n    ";
    stack1 = self.invokePartial(partials.header, 'header', depth0, helpers, partials, data);
    if (stack1 || stack1 === 0) {
      buffer += stack1;
    }
    buffer += "\n    <ul data-role=\"items\">\n    ";
    stack1 = helpers.each.call(depth0, (depth0 && depth0.items), {
      hash: {},
      inverse: self.noop,
      fn: self.programWithDepth(1, program1, data, depth0),
      data: data
    });
    if (stack1 || stack1 === 0) {
      buffer += stack1;
    }
    buffer += "\n    </ul>\n    ";
    stack1 = self.invokePartial(partials.footer, 'footer', depth0, helpers, partials, data);
    if (stack1 || stack1 === 0) {
      buffer += stack1;
    }
    buffer += "\n  </div>\n</div>\n";
    return buffer;
  });
});
define("handlebars-runtime/1.3.0/dist/cjs/handlebars.runtime-debug", [], function(require, exports, module) {
  "use strict";
  /*globals Handlebars: true */
  var base = require("handlebars-runtime/1.3.0/dist/cjs/handlebars/base-debug");
  // Each of these augment the Handlebars object. No need to setup here.
  // (This is done to easily share code between commonjs and browse envs)
  var SafeString = require("handlebars-runtime/1.3.0/dist/cjs/handlebars/safe-string-debug")["default"];
  var Exception = require("handlebars-runtime/1.3.0/dist/cjs/handlebars/exception-debug")["default"];
  var Utils = require("handlebars-runtime/1.3.0/dist/cjs/handlebars/utils-debug");
  var runtime = require("handlebars-runtime/1.3.0/dist/cjs/handlebars/runtime-debug");
  // For compatibility and usage outside of module systems, make the Handlebars object a namespace
  var create = function() {
    var hb = new base.HandlebarsEnvironment();
    Utils.extend(hb, base);
    hb.SafeString = SafeString;
    hb.Exception = Exception;
    hb.Utils = Utils;
    hb.VM = runtime;
    hb.template = function(spec) {
      return runtime.template(spec, hb);
    };
    return hb;
  };
  var Handlebars = create();
  Handlebars.create = create;
  exports["default"] = Handlebars;
});
define("handlebars-runtime/1.3.0/dist/cjs/handlebars/base-debug", [], function(require, exports, module) {
  "use strict";
  var Utils = require("handlebars-runtime/1.3.0/dist/cjs/handlebars/utils-debug");
  var Exception = require("handlebars-runtime/1.3.0/dist/cjs/handlebars/exception-debug")["default"];
  var VERSION = "1.3.0";
  exports.VERSION = VERSION;
  var COMPILER_REVISION = 4;
  exports.COMPILER_REVISION = COMPILER_REVISION;
  var REVISION_CHANGES = {
    1: '<= 1.0.rc.2', // 1.0.rc.2 is actually rev2 but doesn't report it
    2: '== 1.0.0-rc.3',
    3: '== 1.0.0-rc.4',
    4: '>= 1.0.0'
  };
  exports.REVISION_CHANGES = REVISION_CHANGES;
  var isArray = Utils.isArray,
    isFunction = Utils.isFunction,
    toString = Utils.toString,
    objectType = '[object Object]';

  function HandlebarsEnvironment(helpers, partials) {
    this.helpers = helpers || {};
    this.partials = partials || {};
    registerDefaultHelpers(this);
  }
  exports.HandlebarsEnvironment = HandlebarsEnvironment;
  HandlebarsEnvironment.prototype = {
    constructor: HandlebarsEnvironment,
    logger: logger,
    log: log,
    registerHelper: function(name, fn, inverse) {
      if (toString.call(name) === objectType) {
        if (inverse || fn) {
          throw new Exception('Arg not supported with multiple helpers');
        }
        Utils.extend(this.helpers, name);
      } else {
        if (inverse) {
          fn.not = inverse;
        }
        this.helpers[name] = fn;
      }
    },
    registerPartial: function(name, str) {
      if (toString.call(name) === objectType) {
        Utils.extend(this.partials, name);
      } else {
        this.partials[name] = str;
      }
    }
  };

  function registerDefaultHelpers(instance) {
    instance.registerHelper('helperMissing', function(arg) {
      if (arguments.length === 2) {
        return undefined;
      } else {
        throw new Exception("Missing helper: '" + arg + "'");
      }
    });
    instance.registerHelper('blockHelperMissing', function(context, options) {
      var inverse = options.inverse || function() {},
        fn = options.fn;
      if (isFunction(context)) {
        context = context.call(this);
      }
      if (context === true) {
        return fn(this);
      } else if (context === false || context == null) {
        return inverse(this);
      } else if (isArray(context)) {
        if (context.length > 0) {
          return instance.helpers.each(context, options);
        } else {
          return inverse(this);
        }
      } else {
        return fn(context);
      }
    });
    instance.registerHelper('each', function(context, options) {
      var fn = options.fn,
        inverse = options.inverse;
      var i = 0,
        ret = "",
        data;
      if (isFunction(context)) {
        context = context.call(this);
      }
      if (options.data) {
        data = createFrame(options.data);
      }
      if (context && typeof context === 'object') {
        if (isArray(context)) {
          for (var j = context.length; i < j; i++) {
            if (data) {
              data.index = i;
              data.first = (i === 0);
              data.last = (i === (context.length - 1));
            }
            ret = ret + fn(context[i], {
              data: data
            });
          }
        } else {
          for (var key in context) {
            if (context.hasOwnProperty(key)) {
              if (data) {
                data.key = key;
                data.index = i;
                data.first = (i === 0);
              }
              ret = ret + fn(context[key], {
                data: data
              });
              i++;
            }
          }
        }
      }
      if (i === 0) {
        ret = inverse(this);
      }
      return ret;
    });
    instance.registerHelper('if', function(conditional, options) {
      if (isFunction(conditional)) {
        conditional = conditional.call(this);
      }
      // Default behavior is to render the positive path if the value is truthy and not empty.
      // The `includeZero` option may be set to treat the condtional as purely not empty based on the
      // behavior of isEmpty. Effectively this determines if 0 is handled by the positive path or negative.
      if ((!options.hash.includeZero && !conditional) || Utils.isEmpty(conditional)) {
        return options.inverse(this);
      } else {
        return options.fn(this);
      }
    });
    instance.registerHelper('unless', function(conditional, options) {
      return instance.helpers['if'].call(this, conditional, {
        fn: options.inverse,
        inverse: options.fn,
        hash: options.hash
      });
    });
    instance.registerHelper('with', function(context, options) {
      if (isFunction(context)) {
        context = context.call(this);
      }
      if (!Utils.isEmpty(context)) return options.fn(context);
    });
    instance.registerHelper('log', function(context, options) {
      var level = options.data && options.data.level != null ? parseInt(options.data.level, 10) : 1;
      instance.log(level, context);
    });
  }
  var logger = {
    methodMap: {
      0: 'debug',
      1: 'info',
      2: 'warn',
      3: 'error'
    },
    // State enum
    DEBUG: 0,
    INFO: 1,
    WARN: 2,
    ERROR: 3,
    level: 3,
    // can be overridden in the host environment
    log: function(level, obj) {
      if (logger.level <= level) {
        var method = logger.methodMap[level];
        if (typeof console !== 'undefined' && console[method]) {
          console[method].call(console, obj);
        }
      }
    }
  };
  exports.logger = logger;

  function log(level, obj) {
    logger.log(level, obj);
  }
  exports.log = log;
  var createFrame = function(object) {
    var obj = {};
    Utils.extend(obj, object);
    return obj;
  };
  exports.createFrame = createFrame;
});
define("handlebars-runtime/1.3.0/dist/cjs/handlebars/utils-debug", [], function(require, exports, module) {
  "use strict";
  /*jshint -W004 */
  var SafeString = require("handlebars-runtime/1.3.0/dist/cjs/handlebars/safe-string-debug")["default"];
  var escape = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#x27;",
    "`": "&#x60;"
  };
  var badChars = /[&<>"'`]/g;
  var possible = /[&<>"'`]/;

  function escapeChar(chr) {
    return escape[chr] || "&amp;";
  }

  function extend(obj, value) {
    for (var key in value) {
      if (Object.prototype.hasOwnProperty.call(value, key)) {
        obj[key] = value[key];
      }
    }
  }
  exports.extend = extend;
  var toString = Object.prototype.toString;
  exports.toString = toString;
  // Sourced from lodash
  // https://github.com/bestiejs/lodash/blob/master/LICENSE.txt
  var isFunction = function(value) {
    return typeof value === 'function';
  };
  // fallback for older versions of Chrome and Safari
  if (isFunction(/x/)) {
    isFunction = function(value) {
      return typeof value === 'function' && toString.call(value) === '[object Function]';
    };
  }
  var isFunction;
  exports.isFunction = isFunction;
  var isArray = Array.isArray || function(value) {
    return (value && typeof value === 'object') ? toString.call(value) === '[object Array]' : false;
  };
  exports.isArray = isArray;

  function escapeExpression(string) {
    // don't escape SafeStrings, since they're already safe
    if (string instanceof SafeString) {
      return string.toString();
    } else if (!string && string !== 0) {
      return "";
    }
    // Force a string conversion as this will be done by the append regardless and
    // the regex test will do this transparently behind the scenes, causing issues if
    // an object's to string has escaped characters in it.
    string = "" + string;
    if (!possible.test(string)) {
      return string;
    }
    return string.replace(badChars, escapeChar);
  }
  exports.escapeExpression = escapeExpression;

  function isEmpty(value) {
    if (!value && value !== 0) {
      return true;
    } else if (isArray(value) && value.length === 0) {
      return true;
    } else {
      return false;
    }
  }
  exports.isEmpty = isEmpty;
});
define("handlebars-runtime/1.3.0/dist/cjs/handlebars/safe-string-debug", [], function(require, exports, module) {
  "use strict";
  // Build out our basic SafeString type
  function SafeString(string) {
    this.string = string;
  }
  SafeString.prototype.toString = function() {
    return "" + this.string;
  };
  exports["default"] = SafeString;
});
define("handlebars-runtime/1.3.0/dist/cjs/handlebars/exception-debug", [], function(require, exports, module) {
  "use strict";
  var errorProps = ['description', 'fileName', 'lineNumber', 'message', 'name', 'number', 'stack'];

  function Exception(message, node) {
    var line;
    if (node && node.firstLine) {
      line = node.firstLine;
      message += ' - ' + line + ':' + node.firstColumn;
    }
    var tmp = Error.prototype.constructor.call(this, message);
    // Unfortunately errors are not enumerable in Chrome (at least), so `for prop in tmp` doesn't work.
    for (var idx = 0; idx < errorProps.length; idx++) {
      this[errorProps[idx]] = tmp[errorProps[idx]];
    }
    if (line) {
      this.lineNumber = line;
      this.column = node.firstColumn;
    }
  }
  Exception.prototype = new Error();
  exports["default"] = Exception;
});
define("handlebars-runtime/1.3.0/dist/cjs/handlebars/runtime-debug", [], function(require, exports, module) {
  "use strict";
  var Utils = require("handlebars-runtime/1.3.0/dist/cjs/handlebars/utils-debug");
  var Exception = require("handlebars-runtime/1.3.0/dist/cjs/handlebars/exception-debug")["default"];
  var COMPILER_REVISION = require("handlebars-runtime/1.3.0/dist/cjs/handlebars/base-debug").COMPILER_REVISION;
  var REVISION_CHANGES = require("handlebars-runtime/1.3.0/dist/cjs/handlebars/base-debug").REVISION_CHANGES;

  function checkRevision(compilerInfo) {
    var compilerRevision = compilerInfo && compilerInfo[0] || 1,
      currentRevision = COMPILER_REVISION;
    if (compilerRevision !== currentRevision) {
      if (compilerRevision < currentRevision) {
        var runtimeVersions = REVISION_CHANGES[currentRevision],
          compilerVersions = REVISION_CHANGES[compilerRevision];
        throw new Exception("Template was precompiled with an older version of Handlebars than the current runtime. " + "Please update your precompiler to a newer version (" + runtimeVersions + ") or downgrade your runtime to an older version (" + compilerVersions + ").");
      } else {
        // Use the embedded version info since the runtime doesn't know about this revision yet
        throw new Exception("Template was precompiled with a newer version of Handlebars than the current runtime. " + "Please update your runtime to a newer version (" + compilerInfo[1] + ").");
      }
    }
  }
  exports.checkRevision = checkRevision; // TODO: Remove this line and break up compilePartial
  function template(templateSpec, env) {
    if (!env) {
      throw new Exception("No environment passed to template");
    }
    // Note: Using env.VM references rather than local var references throughout this section to allow
    // for external users to override these as psuedo-supported APIs.
    var invokePartialWrapper = function(partial, name, context, helpers, partials, data) {
      var result = env.VM.invokePartial.apply(this, arguments);
      if (result != null) {
        return result;
      }
      if (env.compile) {
        var options = {
          helpers: helpers,
          partials: partials,
          data: data
        };
        partials[name] = env.compile(partial, {
          data: data !== undefined
        }, env);
        return partials[name](context, options);
      } else {
        throw new Exception("The partial " + name + " could not be compiled when running in runtime-only mode");
      }
    };
    // Just add water
    var container = {
      escapeExpression: Utils.escapeExpression,
      invokePartial: invokePartialWrapper,
      programs: [],
      program: function(i, fn, data) {
        var programWrapper = this.programs[i];
        if (data) {
          programWrapper = program(i, fn, data);
        } else if (!programWrapper) {
          programWrapper = this.programs[i] = program(i, fn);
        }
        return programWrapper;
      },
      merge: function(param, common) {
        var ret = param || common;
        if (param && common && (param !== common)) {
          ret = {};
          Utils.extend(ret, common);
          Utils.extend(ret, param);
        }
        return ret;
      },
      programWithDepth: env.VM.programWithDepth,
      noop: env.VM.noop,
      compilerInfo: null
    };
    return function(context, options) {
      options = options || {};
      var namespace = options.partial ? options : env,
        helpers,
        partials;
      if (!options.partial) {
        helpers = options.helpers;
        partials = options.partials;
      }
      var result = templateSpec.call(container, namespace, context, helpers, partials, options.data);
      if (!options.partial) {
        env.VM.checkRevision(container.compilerInfo);
      }
      return result;
    };
  }
  exports.template = template;

  function programWithDepth(i, fn, data /*, $depth */ ) {
    var args = Array.prototype.slice.call(arguments, 3);
    var prog = function(context, options) {
      options = options || {};
      return fn.apply(this, [context, options.data || data].concat(args));
    };
    prog.program = i;
    prog.depth = args.length;
    return prog;
  }
  exports.programWithDepth = programWithDepth;

  function program(i, fn, data) {
    var prog = function(context, options) {
      options = options || {};
      return fn(context, options.data || data);
    };
    prog.program = i;
    prog.depth = 0;
    return prog;
  }
  exports.program = program;

  function invokePartial(partial, name, context, helpers, partials, data) {
    var options = {
      partial: true,
      helpers: helpers,
      partials: partials,
      data: data
    };
    if (partial === undefined) {
      throw new Exception("The partial " + name + " could not be found");
    } else if (partial instanceof Function) {
      return partial(context, options);
    }
  }
  exports.invokePartial = invokePartial;

  function noop() {
    return "";
  }
  exports.noop = noop;
});
