define("arale/autocomplete/1.3.0/textarea-complete-debug", [ "$-debug", "gallery/selection/0.9.0/selection-debug", "./autocomplete-debug", "arale/overlay/1.1.2/overlay-debug", "arale/position/1.0.1/position-debug", "arale/iframe-shim/1.0.2/iframe-shim-debug", "arale/widget/1.1.1/widget-debug", "arale/base/1.1.1/base-debug", "arale/class/1.1.0/class-debug", "arale/events/1.1.0/events-debug", "arale/templatable/0.9.2/templatable-debug", "gallery/handlebars/1.0.2/handlebars-debug", "./data-source-debug", "./filter-debug", "./input-debug", "./autocomplete-debug.handlebars" ], function(require, exports, module) {
    var $ = require("$-debug");
    var selection = require("gallery/selection/0.9.0/selection-debug");
    var AutoComplete = require("./autocomplete-debug");
    var TextareaComplete = AutoComplete.extend({
        attrs: {
            cursor: false
        },
        setup: function() {
            TextareaComplete.superclass.setup.call(this);
            this.sel = selection(this.get("trigger"));
            var inputFilter = this.get("inputFilter"), that = this;
            this.set("inputFilter", function(val) {
                var v = val.substring(0, that.sel.cursor()[1]);
                return inputFilter.call(that, v);
            });
            if (this.get("cursor")) {
                this.mirror = Mirror.init(this.get("trigger"));
                this.dataSource.before("getData", function() {
                    that.mirror.setContent(that.get("inputValue"), that.queryValue, that.sel.cursor());
                });
            }
        },
        _keyUp: function(e) {
            if (this.get("visible")) {
                e.preventDefault();
                if (this.get("data").length) {
                    this._step(-1);
                }
            }
        },
        _keyDown: function(e) {
            if (this.get("visible")) {
                e.preventDefault();
                if (this.get("data").length) {
                    this._step(1);
                }
            }
        },
        _keyEnter: function(e) {
            // 如果没有选中任一一项也不会阻止
            if (this.get("visible")) {
                if (this.currentItem) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    // 阻止冒泡及绑定的其他 keydown 事件
                    this.selectItem();
                } else {
                    this.hide();
                }
            }
        },
        show: function() {
            var cursor = this.get("cursor");
            if (cursor) {
                if ($.isArray(cursor)) {
                    var offset = cursor;
                } else {
                    var offset = [ 0, 0 ];
                }
                var pos = this.mirror.getFlagRect();
                var align = this.get("align");
                align.baseXY = [ pos.left + offset[0], pos.bottom + offset[1] ];
                align.selfXY = [ 0, 0 ];
                this.set("align", align);
            }
            TextareaComplete.superclass.show.call(this);
        },
        selectItem: function() {
            this.hide();
            var item = this.currentItem, index = this.get("selectedIndex"), data = this.get("data")[index];
            if (item) {
                var matchKey = item.attr("data-value");
                var right = this.sel.cursor()[1];
                var left = right - this.queryValue.length;
                this.sel.cursor(left, right).text("").append(matchKey, "right");
                var value = this.get("trigger").val();
                this.set("inputValue", value);
                this.mirror && this.mirror.setContent(value, "", this.sel.cursor());
                this.trigger("itemSelect", data);
                this._clear();
            }
        }
    });
    // 计算光标位置
    // MIT https://github.com/ichord/At.js/blob/master/js/jquery.atwho.js
    var Mirror = {
        mirror: null,
        css: [ "overflowY", "height", "width", "paddingTop", "paddingLeft", "paddingRight", "paddingBottom", "marginTop", "marginLeft", "marginRight", "marginBottom", "fontFamily", "borderStyle", "borderWidth", "wordWrap", "fontSize", "lineHeight", "overflowX" ],
        init: function(origin) {
            origin = $(origin);
            var css = {
                position: "absolute",
                left: -9999,
                top: 0,
                zIndex: -2e4,
                "white-space": "pre-wrap"
            };
            $.each(this.css, function(i, p) {
                return css[p] = origin.css(p);
            });
            this.mirror = $("<div><span></span></div>").css(css).insertAfter(origin);
            return this;
        },
        setContent: function(content, query, cursor) {
            var left = query ? cursor[1] - query.length : cursor[1];
            var right = cursor[1];
            var v = [ "<span>", content.substring(0, left), "</span>", '<span id="flag">', query || "", "</span>", "<span>", content.substring(right), "</span>" ].join("");
            this.mirror.html(v);
            return this;
        },
        getFlagRect: function() {
            var pos, rect, flag;
            flag = this.mirror.find("span#flag");
            pos = flag.position();
            rect = {
                left: pos.left,
                right: flag.width() + pos.left,
                top: pos.top,
                bottom: flag.height() + pos.top
            };
            return rect;
        }
    };
    module.exports = TextareaComplete;
});

define("arale/autocomplete/1.3.0/autocomplete-debug", [ "$-debug", "arale/overlay/1.1.2/overlay-debug", "arale/position/1.0.1/position-debug", "arale/iframe-shim/1.0.2/iframe-shim-debug", "arale/widget/1.1.1/widget-debug", "arale/base/1.1.1/base-debug", "arale/class/1.1.0/class-debug", "arale/events/1.1.0/events-debug", "arale/templatable/0.9.2/templatable-debug", "gallery/handlebars/1.0.2/handlebars-debug", "arale/autocomplete/1.3.0/data-source-debug", "arale/autocomplete/1.3.0/filter-debug", "arale/autocomplete/1.3.0/input-debug" ], function(require, exports, module) {
    var $ = require("$-debug");
    var Overlay = require("arale/overlay/1.1.2/overlay-debug");
    var Templatable = require("arale/templatable/0.9.2/templatable-debug");
    var DataSource = require("arale/autocomplete/1.3.0/data-source-debug");
    var Filter = require("arale/autocomplete/1.3.0/filter-debug");
    var Input = require("arale/autocomplete/1.3.0/input-debug");
    var IE678 = /\bMSIE [678]\.0\b/.test(navigator.userAgent);
    var template = require("arale/autocomplete/1.3.0/autocomplete-debug.handlebars");
    var AutoComplete = Overlay.extend({
        Implements: Templatable,
        attrs: {
            // 触发元素
            trigger: null,
            classPrefix: "ui-autocomplete",
            align: {
                baseXY: [ 0, "100%" ]
            },
            submitOnEnter: true,
            // 回车是否会提交表单
            dataSource: [],
            //数据源，支持 Array, URL, Object, Function
            locator: "data",
            // 输出过滤
            filter: null,
            // 输入过滤
            inputFilter: function(v) {
                return v;
            },
            disabled: false,
            selectFirst: false,
            delay: 100,
            // 以下为模板相关
            model: {
                value: {
                    items: []
                },
                getter: function(val) {
                    val.classPrefix || (val.classPrefix = this.get("classPrefix"));
                    return val;
                }
            },
            template: template,
            footer: "",
            header: "",
            html: "{{label}}",
            // 以下仅为组件使用
            selectedIndex: null,
            data: []
        },
        events: {
            mousedown: "_handleMouseDown",
            "click [data-role=item]": "_handleSelection",
            "mouseenter [data-role=item]": "_handleMouseMove",
            "mouseleave [data-role=item]": "_handleMouseMove"
        },
        templateHelpers: {
            // 将匹配的高亮文字加上 hl 的样式
            highlightItem: highlightItem
        },
        parseElement: function() {
            var t = [ "header", "footer", "html" ];
            for (var i in t) {
                this.templatePartials || (this.templatePartials = {});
                this.templatePartials[t[i]] = this.get(t[i]);
            }
            AutoComplete.superclass.parseElement.call(this);
        },
        setup: function() {
            AutoComplete.superclass.setup.call(this);
            this._isOpen = false;
            this._initInput();
            // 初始化输入框
            this._initDataSource();
            // 初始化数据源
            this._initFilter();
            // 初始化过滤器
            this._bindHandle();
            // 绑定事件
            this._blurHide([ $(this.get("trigger")) ]);
            this._tweakAlignDefaultValue();
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
            if (this.items && index && this.items.length > index && index >= -1) {
                this.set("selectedIndex", index);
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
            var filter = this.get("filter"), locator = this.get("locator");
            // 获取目标数据
            data = locateResult(locator, data);
            // 进行过滤
            data = filter.call(this, normalize(data), this.input.get("query"));
            this.set("data", data);
        },
        // 通过数据渲染模板
        _onRenderData: function(data) {
            if (!data.length) return;
            // 渲染下拉
            this.set("model", {
                items: data,
                query: this.input.get("query")
            });
            this.renderPartial();
            // 初始化下拉的状态
            this.items = this.$("[data-role=items]").children();
            if (this.get("selectFirst")) {
                this.set("selectedIndex", 0);
            }
            // 选中后会修改 input 的值并触发下一次渲染，但第二次渲染的结果不应该显示出来。
            this._isOpen && this.show();
        },
        // 键盘控制上下移动
        _onRenderSelectedIndex: function(index) {
            var hoverClass = this.get("classPrefix") + "-item-hover";
            this.items && this.items.removeClass(hoverClass);
            // -1 什么都不选
            if (index === -1) return;
            this.items.eq(index).addClass(hoverClass);
            this.trigger("indexChanged", index, this.lastIndex);
            this.lastIndex = index;
        },
        // 初始化
        // ------------
        _initDataSource: function() {
            this.dataSource = new DataSource({
                source: this.get("dataSource")
            });
        },
        _initInput: function() {
            this.input = new Input({
                element: this.get("trigger"),
                delay: this.get("delay")
            });
        },
        _initFilter: function() {
            var filter = this.get("filter");
            filter = initFilter(filter, this.dataSource);
            this.set("filter", filter);
        },
        // 事件绑定
        // ------------
        _bindHandle: function() {
            this.dataSource.on("data", this._filterData, this);
            this.input.on("blur", this.hide, this).on("focus", this._handleFocus, this).on("keyEnter", this._handleSelection, this).on("keyEsc", this.hide, this).on("keyUp keyDown", this.show, this).on("keyUp keyDown", this._handleStep, this).on("queryChanged", this._clear, this).on("queryChanged", this._hide, this).on("queryChanged", this._handleQueryChange, this).on("queryChanged", this.show, this);
            this.after("hide", function() {
                this.set("selectedIndex", -1);
            });
            // 选中后隐藏浮层
            this.on("itemSelected", function() {
                this._hide();
            });
        },
        // 选中的处理器
        // 1. 鼠标点击触发
        // 2. 回车触发
        // 3. selectItem 触发
        _handleSelection: function(e) {
            var isMouse = e ? e.type === "click" : false;
            var index = isMouse ? this.items.index(e.currentTarget) : this.get("selectedIndex");
            var item = this.items.eq(index);
            var data = this.get("data")[index];
            if (index >= 0 && item) {
                this.input.setValue(data.label);
                this.set("selectedIndex", index, {
                    silent: true
                });
                // 是否阻止回车提交表单
                if (e && !isMouse && !this.get("submitOnEnter")) e.preventDefault();
                this.trigger("itemSelected", data, item);
            }
        },
        _handleFocus: function() {
            this._isOpen = true;
        },
        _handleMouseMove: function(e) {
            var hoverClass = this.get("classPrefix") + "-item-hover";
            this.items.removeClass(hoverClass);
            if (e.type === "mouseenter") {
                var index = this.items.index(e.currentTarget);
                this.set("selectedIndex", index, {
                    silent: true
                });
                this.items.eq(index).addClass(hoverClass);
            }
        },
        _handleMouseDown: function(e) {
            if (IE678) {
                var trigger = this.input.get("element")[0];
                trigger.onbeforedeactivate = function() {
                    window.event.returnValue = false;
                    trigger.onbeforedeactivate = null;
                };
            }
            e.preventDefault();
        },
        _handleStep: function(e) {
            e.preventDefault();
            this.get("visible") && this._step(e.type === "keyUp" ? -1 : 1);
        },
        _handleQueryChange: function(val, prev) {
            if (this.get("disabled")) return;
            this.dataSource.abort();
            this.dataSource.getData(val);
        },
        // 选项上下移动
        _step: function(direction) {
            var currentIndex = this.get("selectedIndex");
            if (direction === -1) {
                // 反向
                if (currentIndex > -1) {
                    this.set("selectedIndex", currentIndex - 1);
                } else {
                    this.set("selectedIndex", this.items.length - 1);
                }
            } else if (direction === 1) {
                // 正向
                if (currentIndex < this.items.length - 1) {
                    this.set("selectedIndex", currentIndex + 1);
                } else {
                    this.set("selectedIndex", -1);
                }
            }
        },
        _clear: function() {
            this.$("[data-role=items]").empty();
            this.set("selectedIndex", -1);
            delete this.items;
            delete this.lastIndex;
        },
        _hide: function() {
            this._isOpen = false;
            AutoComplete.superclass.hide.call(this);
        },
        _isEmpty: function() {
            var data = this.get("data");
            return !(data && data.length > 0);
        },
        // 调整 align 属性的默认值
        _tweakAlignDefaultValue: function() {
            var align = this.get("align");
            align.baseElement = this.get("trigger");
            this.set("align", align);
        }
    });
    module.exports = AutoComplete;
    function isString(str) {
        return Object.prototype.toString.call(str) === "[object String]";
    }
    function isObject(obj) {
        return Object.prototype.toString.call(obj) === "[object Object]";
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
        if (!locator) {
            return data;
        }
        if ($.isFunction(locator)) {
            return locator.call(this, data);
        } else if (isString(locator)) {
            var s = locator.split("."), p = data;
            while (s.length) {
                var v = s.shift();
                if (!p[v]) {
                    break;
                }
                p = p[v];
            }
            return p;
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
    //   1. null: 使用默认的 startWith
    //   2. string: 从 Filter 中找
    //   3. function: 自定义
    function initFilter(filter, dataSource) {
        // 字符串
        if (isString(filter)) {
            // 从组件内置的 FILTER 获取
            if (Filter[filter]) {
                filter = Filter[filter];
            } else {
                filter = Filter["startsWith"];
            }
        } else if (!$.isFunction(filter)) {
            // 异步请求的时候不需要过滤器
            if (dataSource.get("type") === "url") {
                filter = Filter["default"];
            } else {
                filter = Filter["startsWith"];
            }
        }
        return filter;
    }
    function highlightItem(label, classPrefix) {
        var index = this.highlightIndex, cursor = 0, v = label || this.label || "", h = "";
        if ($.isArray(index)) {
            for (var i = 0, l = index.length; i < l; i++) {
                var j = index[i], start, length;
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
                    var className = classPrefix ? 'class="' + classPrefix + '-item-hl"' : "";
                    h += "<span " + className + ">" + v.substr(start, length) + "</span>";
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

define("arale/autocomplete/1.3.0/data-source-debug", [ "arale/base/1.1.1/base-debug", "arale/class/1.1.0/class-debug", "arale/events/1.1.0/events-debug", "$-debug" ], function(require, exports, module) {
    var Base = require("arale/base/1.1.1/base-debug");
    var $ = require("$-debug");
    var DataSource = Base.extend({
        attrs: {
            source: null,
            type: "array"
        },
        initialize: function(config) {
            DataSource.superclass.initialize.call(this, config);
            // 每次发送请求会将 id 记录到 callbacks 中，返回后会从中删除
            // 如果 abort 会清空 callbacks，之前的请求结果都不会执行
            this.id = 0;
            this.callbacks = [];
            var source = this.get("source");
            if (isString(source)) {
                this.set("type", "url");
            } else if ($.isArray(source)) {
                this.set("type", "array");
            } else if ($.isPlainObject(source)) {
                this.set("type", "object");
            } else if ($.isFunction(source)) {
                this.set("type", "function");
            } else {
                throw new Error("Source Type Error");
            }
        },
        getData: function(query) {
            return this["_get" + capitalize(this.get("type") || "") + "Data"](query);
        },
        abort: function() {
            this.callbacks = [];
        },
        // 完成数据请求，getData => done
        _done: function(data) {
            this.trigger("data", data);
        },
        _getUrlData: function(query) {
            var that = this, options;
            var obj = {
                query: query ? encodeURIComponent(query) : "",
                timestamp: new Date().getTime()
            };
            var url = this.get("source").replace(/{{(.*?)}}/g, function(all, match) {
                return obj[match];
            });
            var callbackId = "callback_" + this.id++;
            this.callbacks.push(callbackId);
            if (/^(https?:\/\/)/.test(url)) {
                options = {
                    dataType: "jsonp"
                };
            } else {
                options = {
                    dataType: "json"
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
            var source = this.get("source");
            this._done(source);
            return source;
        },
        _getObjectData: function() {
            var source = this.get("source");
            this._done(source);
            return source;
        },
        _getFunctionData: function(query) {
            var that = this, func = this.get("source");
            // 如果返回 false 可阻止执行
            function done(data) {
                that._done(data);
            }
            var data = func.call(this, query, done);
            if (data) {
                this._done(data);
            }
        }
    });
    module.exports = DataSource;
    function isString(str) {
        return Object.prototype.toString.call(str) === "[object String]";
    }
    function capitalize(str) {
        return str.replace(/^([a-z])/, function(f, m) {
            return m.toUpperCase();
        });
    }
});

define("arale/autocomplete/1.3.0/filter-debug", [ "$-debug" ], function(require, exports, module) {
    var $ = require("$-debug");
    var Filter = {
        "default": function(data) {
            return data;
        },
        startsWith: function(data, query) {
            query = query || "";
            var result = [], l = query.length, reg = new RegExp("^" + escapeKeyword(query));
            if (!l) return [];
            $.each(data, function(index, item) {
                var a, matchKeys = [ item.value ].concat(item.alias);
                // 匹配 value 和 alias 中的
                while (a = matchKeys.shift()) {
                    if (reg.test(a)) {
                        // 匹配和显示相同才有必要高亮
                        if (item.label === a) {
                            item.highlightIndex = [ [ 0, l ] ];
                        }
                        result.push(item);
                        break;
                    }
                }
            });
            return result;
        },
        stringMatch: function(data, query) {
            query = query || "";
            var result = [], l = query.length;
            if (!l) return [];
            $.each(data, function(index, item) {
                var a, matchKeys = [ item.value ].concat(item.alias);
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
    var keyword = /(\[|\[|\]|\^|\$|\||\(|\)|\{|\}|\+|\*|\?)/g;
    function escapeKeyword(str) {
        return (str || "").replace(keyword, "\\$1");
    }
    function stringMatch(matchKey, query) {
        var r = [], a = matchKey.split("");
        var queryIndex = 0, q = query.split("");
        for (var i = 0, l = a.length; i < l; i++) {
            var v = a[i];
            if (v === q[queryIndex]) {
                if (queryIndex === q.length - 1) {
                    r.push([ i - q.length + 1, i + 1 ]);
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

define("arale/autocomplete/1.3.0/input-debug", [ "$-debug", "arale/base/1.1.1/base-debug", "arale/class/1.1.0/class-debug", "arale/events/1.1.0/events-debug" ], function(require, exports, module) {
    var $ = require("$-debug");
    var Base = require("arale/base/1.1.1/base-debug");
    var lteIE9 = /\bMSIE [6789]\.0\b/.test(navigator.userAgent);
    var specialKeyCodeMap = {
        9: "tab",
        27: "esc",
        37: "left",
        39: "right",
        13: "enter",
        38: "up",
        40: "down"
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
            this.set("query", this.getValue());
        },
        focus: function() {
            this.get("element").focus();
        },
        getValue: function() {
            return this.get("element").val();
        },
        setValue: function(val, silent) {
            this.get("element").val(val);
            !silent && this._change();
        },
        destroy: function() {
            Input.superclass.destroy.call(this);
        },
        _onRenderValue: function(value) {
            this._change();
        },
        _bindEvents: function() {
            var timer, input = this.get("element");
            input.attr("autocomplete", "off").on("focus.autocomplete", wrapFn(this._handleFocus, this)).on("blur.autocomplete", wrapFn(this._handleBlur, this)).on("keydown.autocomplete", wrapFn(this._handleKeydown, this)).on("keyUp.autocomplete", wrapFn(this._handleKeyup, this));
            // IE678 don't support input event
            // IE 9 does not fire an input event when the user removes characters from input filled by keyboard, cut, or drag operations.
            if (!lteIE9) {
                input.on("input.autocomplete", wrapFn(this._change, this));
            } else {
                var that = this, events = [ "keydown.autocomplete", "keypress.autocomplete", "cut.autocomplete", "paste.autocomplete" ].join(" ");
                input.on(events, wrapFn(function(e) {
                    if (specialKeyCodeMap[e.which]) return;
                    clearTimeout(timer);
                    timer = setTimeout(function() {
                        that._change.call(that, e);
                    }, this.get("delay"));
                }, this));
            }
        },
        _change: function() {
            var newVal = this.getValue();
            var oldVal = this.get("query");
            var isSame = compare(oldVal, newVal);
            var isSameExpectWhitespace = isSame ? newVal.length !== oldVal.length : false;
            if (isSameExpectWhitespace) {
                this.trigger("whitespaceChanged", oldVal);
            }
            if (!isSame) {
                this.set("query", newVal);
                this.trigger("queryChanged", newVal, oldVal);
            }
        },
        _handleFocus: function(e) {
            this.trigger("focus", e);
        },
        _handleBlur: function(e) {
            this.trigger("blur", e);
        },
        _handleKeydown: function(e) {
            var keyName = specialKeyCodeMap[e.which];
            if (keyName) {
                var eventKey = "key" + ucFirst(keyName);
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
        a = (a || "").replace(/^\s*/g, "").replace(/\s{2,}/g, " ");
        b = (b || "").replace(/^\s*/g, "").replace(/\s{2,}/g, " ");
        return a === b;
    }
    function ucFirst(str) {
        return str.charAt(0).toUpperCase() + str.substring(1);
    }
});

define("arale/autocomplete/1.3.0/autocomplete-debug.handlebars", [ "gallery/handlebars/1.0.2/runtime-debug" ], function(require, exports, module) {
    var Handlebars = require("gallery/handlebars/1.0.2/runtime-debug");
    var template = Handlebars.template;
    module.exports = template(function(Handlebars, depth0, helpers, partials, data) {
        this.compilerInfo = [ 3, ">= 1.0.0-rc.4" ];
        helpers = helpers || {};
        for (var key in Handlebars.helpers) {
            helpers[key] = helpers[key] || Handlebars.helpers[key];
        }
        partials = partials || Handlebars.partials;
        data = data || {};
        var buffer = "", stack1, functionType = "function", escapeExpression = this.escapeExpression, self = this;
        function program1(depth0, data, depth1) {
            var buffer = "", stack1, stack2;
            buffer += '\n    <li data-role="item" class="' + escapeExpression((stack1 = depth1.classPrefix, 
            typeof stack1 === functionType ? stack1.apply(depth0) : stack1)) + '-item">';
            stack2 = self.invokePartial(partials.html, "html", depth0, helpers, partials, data);
            if (stack2 || stack2 === 0) {
                buffer += stack2;
            }
            buffer += "</li>\n  ";
            return buffer;
        }
        buffer += '<div class="';
        if (stack1 = helpers.classPrefix) {
            stack1 = stack1.call(depth0, {
                hash: {},
                data: data
            });
        } else {
            stack1 = depth0.classPrefix;
            stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1;
        }
        buffer += escapeExpression(stack1) + '">\n  ';
        stack1 = self.invokePartial(partials.header, "header", depth0, helpers, partials, data);
        if (stack1 || stack1 === 0) {
            buffer += stack1;
        }
        buffer += '\n  <ul class="';
        if (stack1 = helpers.classPrefix) {
            stack1 = stack1.call(depth0, {
                hash: {},
                data: data
            });
        } else {
            stack1 = depth0.classPrefix;
            stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1;
        }
        buffer += escapeExpression(stack1) + '-ctn" data-role="items">\n  ';
        stack1 = helpers.each.call(depth0, depth0.items, {
            hash: {},
            inverse: self.noop,
            fn: self.programWithDepth(1, program1, data, depth0),
            data: data
        });
        if (stack1 || stack1 === 0) {
            buffer += stack1;
        }
        buffer += "\n  </ul>\n  ";
        stack1 = self.invokePartial(partials.footer, "footer", depth0, helpers, partials, data);
        if (stack1 || stack1 === 0) {
            buffer += stack1;
        }
        buffer += "\n</div>\n";
        return buffer;
    });
});
