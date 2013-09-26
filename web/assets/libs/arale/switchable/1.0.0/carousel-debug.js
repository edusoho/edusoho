define("arale/switchable/1.0.0/carousel-debug", [ "./switchable-debug", "$-debug", "arale/widget/1.1.1/widget-debug", "arale/base/1.1.1/base-debug", "arale/class/1.1.0/class-debug", "arale/events/1.1.0/events-debug", "./plugins/effects-debug", "arale/easing/1.0.0/easing-debug", "./plugins/autoplay-debug", "./plugins/circular-debug" ], function(require, exports, module) {
    var Switchable = require("./switchable-debug");
    var $ = require("$-debug");
    // 旋转木马组件
    module.exports = Switchable.extend({
        attrs: {
            circular: true,
            prevBtn: {
                getter: function(val) {
                    return $(val).eq(0);
                }
            },
            nextBtn: {
                getter: function(val) {
                    return $(val).eq(0);
                }
            },
            disabledBtnClass: {
                getter: function(val) {
                    return val ? val : this.get("classPrefix") + "-disabled-btn";
                }
            }
        },
        _initTriggers: function(role) {
            Switchable.prototype._initTriggers.call(this, role);
            // attr 里没找到时，才根据 data-role 来解析
            var prevBtn = this.get("prevBtn");
            var nextBtn = this.get("nextBtn");
            if (!prevBtn[0] && role.prev) {
                prevBtn = role.prev;
                this.set("prevBtn", prevBtn);
            }
            if (!nextBtn[0] && role.next) {
                nextBtn = role.next;
                this.set("nextBtn", nextBtn);
            }
            prevBtn.addClass(this.CONST.PREV_BTN_CLASS);
            nextBtn.addClass(this.CONST.NEXT_BTN_CLASS);
        },
        _getDatasetRole: function() {
            var role = Switchable.prototype._getDatasetRole.call(this);
            var element = this.element;
            var roles = [ "next", "prev" ];
            $.each(roles, function(index, key) {
                var elems = $("[data-role=" + key + "]", element);
                if (elems.length) {
                    role[key] = elems;
                }
            });
            return role;
        },
        _bindTriggers: function() {
            Switchable.prototype._bindTriggers.call(this);
            var that = this;
            var circular = this.get("circular");
            this.get("prevBtn").click(function(ev) {
                ev.preventDefault();
                if (circular || that.get("activeIndex") > 0) {
                    that.prev();
                }
            });
            this.get("nextBtn").click(function(ev) {
                ev.preventDefault();
                var len = that.get("length") - 1;
                if (circular || that.get("activeIndex") < len) {
                    that.next();
                }
            });
            // 注册 switch 事件，处理 prevBtn/nextBtn 的 disable 状态
            // circular = true 时，无需处理
            if (!circular) {
                this.on("switch", function(toIndex) {
                    that._updateButtonStatus(toIndex);
                });
            }
        },
        _updateButtonStatus: function(toIndex) {
            var prevBtn = this.get("prevBtn");
            var nextBtn = this.get("nextBtn");
            var disabledBtnClass = this.get("disabledBtnClass");
            prevBtn.removeClass(disabledBtnClass);
            nextBtn.removeClass(disabledBtnClass);
            if (toIndex === 0) {
                prevBtn.addClass(disabledBtnClass);
            } else if (toIndex === this.get("length") - 1) {
                nextBtn.addClass(disabledBtnClass);
            }
        }
    });
});

define("arale/switchable/1.0.0/switchable-debug", [ "$-debug", "arale/widget/1.1.1/widget-debug", "arale/base/1.1.1/base-debug", "arale/class/1.1.0/class-debug", "arale/events/1.1.0/events-debug", "arale/switchable/1.0.0/plugins/effects-debug", "arale/easing/1.0.0/easing-debug", "arale/switchable/1.0.0/plugins/autoplay-debug", "arale/switchable/1.0.0/plugins/circular-debug" ], function(require, exports, module) {
    // Switchable
    // -----------
    // 可切换组件，核心特征是：有一组可切换的面板（Panel），可通过触点（Trigger）来触发。
    // 感谢：
    //  - https://github.com/kissyteam/kissy/tree/6707ecc4cdfddd59e21698c8eb4a50b65dbe7632/src/switchable
    var $ = require("$-debug");
    var Widget = require("arale/widget/1.1.1/widget-debug");
    var Effects = require("arale/switchable/1.0.0/plugins/effects-debug");
    var Autoplay = require("arale/switchable/1.0.0/plugins/autoplay-debug");
    var Circular = require("arale/switchable/1.0.0/plugins/circular-debug");
    var Switchable = Widget.extend({
        attrs: {
            // 用户传入的 triggers 和 panels
            // 可以是 Selector、jQuery 对象、或 DOM 元素集
            triggers: {
                value: [],
                getter: function(val) {
                    return $(val);
                }
            },
            panels: {
                value: [],
                getter: function(val) {
                    return $(val);
                }
            },
            classPrefix: "ui-switchable",
            // 是否包含 triggers，用于没有传入 triggers 时，是否自动生成的判断标准
            hasTriggers: true,
            // 触发类型
            triggerType: "hover",
            // or 'click'
            // 触发延迟
            delay: 100,
            // 初始切换到哪个面板
            activeIndex: {
                value: 0,
                setter: function(val) {
                    return parseInt(val) || 0;
                }
            },
            // 一屏内有多少个 panels
            step: 1,
            // 有多少屏
            length: {
                readOnly: true,
                getter: function() {
                    return Math.ceil(this.get("panels").length / this.get("step"));
                }
            },
            // 可见视图区域的大小。一般不需要设定此值，仅当获取值不正确时，用于手工指定大小
            viewSize: [],
            activeTriggerClass: {
                getter: function(val) {
                    return val ? val : this.get("classPrefix") + "-active";
                }
            }
        },
        setup: function() {
            this._initConstClass();
            this._initElement();
            var role = this._getDatasetRole();
            this._initPanels(role);
            // 配置中的 triggers > dataset > 自动生成
            this._initTriggers(role);
            this._bindTriggers();
            this._initPlugins();
            // 渲染默认初始状态
            this.render();
        },
        _initConstClass: function() {
            this.CONST = constClass(this.get("classPrefix"));
        },
        _initElement: function() {
            this.element.addClass(this.CONST.UI_SWITCHABLE);
        },
        // 从 HTML 标记中获取各个 role, 替代原来的 markupType
        _getDatasetRole: function() {
            var element = this.element;
            var role = {};
            var roles = [ "trigger", "panel", "nav", "content" ];
            $.each(roles, function(index, key) {
                var elems = $("[data-role=" + key + "]", element);
                if (elems.length) {
                    role[key] = elems;
                }
            });
            return role;
        },
        _initPanels: function(role) {
            var panels = this.get("panels");
            // 先获取 panels 和 content
            if (panels.length > 0) {} else if (role.panel) {
                this.set("panels", panels = role.panel);
            } else if (role.content) {
                this.set("panels", panels = role.content.find("> *"));
                this.content = role.content;
            }
            if (panels.length === 0) {
                throw new Error("panels.length is ZERO");
            }
            if (!this.content) {
                this.content = panels.parent();
            }
            this.content.addClass(this.CONST.CONTENT_CLASS);
            this.get("panels").addClass(this.CONST.PANEL_CLASS);
        },
        _initTriggers: function(role) {
            var triggers = this.get("triggers");
            // 再获取 triggers 和 nav
            if (triggers.length > 0) {} else if (role.trigger) {
                this.set("triggers", triggers = role.trigger);
            } else if (role.nav) {
                triggers = role.nav.find("> *");
                // 空的 nav 标记
                if (triggers.length === 0) {
                    triggers = generateTriggersMarkup(this.get("length"), this.get("activeIndex"), this.get("activeTriggerClass"), true).appendTo(role.nav);
                }
                this.set("triggers", triggers);
                this.nav = role.nav;
            } else if (this.get("hasTriggers")) {
                this.nav = generateTriggersMarkup(this.get("length"), this.get("activeIndex"), this.get("activeTriggerClass")).appendTo(this.element);
                this.set("triggers", triggers = this.nav.children());
            }
            if (!this.nav && triggers.length) {
                this.nav = triggers.parent();
            }
            this.nav && this.nav.addClass(this.CONST.NAV_CLASS);
            triggers.addClass(this.CONST.TRIGGER_CLASS).each(function(i, trigger) {
                $(trigger).data("value", i);
            });
        },
        _bindTriggers: function() {
            var that = this, triggers = this.get("triggers");
            if (this.get("triggerType") === "click") {
                triggers.click(focus);
            } else {
                triggers.hover(focus, leave);
            }
            function focus(ev) {
                that._onFocusTrigger(ev.type, $(this).data("value"));
            }
            function leave() {
                clearTimeout(that._switchTimer);
            }
        },
        _onFocusTrigger: function(type, index) {
            var that = this;
            // click or tab 键激活时
            if (type === "click") {
                this.switchTo(index);
            } else {
                this._switchTimer = setTimeout(function() {
                    that.switchTo(index);
                }, this.get("delay"));
            }
        },
        _initPlugins: function() {
            this._plugins = [];
            this._plug(Effects);
            this._plug(Autoplay);
            this._plug(Circular);
        },
        // 切换到指定 index
        switchTo: function(toIndex) {
            this.set("activeIndex", toIndex);
        },
        // change 事件触发的前提是当前值和先前值不一致, 所以无需验证 toIndex !== fromIndex
        _onRenderActiveIndex: function(toIndex, fromIndex) {
            this._switchTo(toIndex, fromIndex);
        },
        _switchTo: function(toIndex, fromIndex) {
            this.trigger("switch", toIndex, fromIndex);
            this._switchTrigger(toIndex, fromIndex);
            this._switchPanel(this._getPanelInfo(toIndex, fromIndex));
            this.trigger("switched", toIndex, fromIndex);
        },
        _switchTrigger: function(toIndex, fromIndex) {
            var triggers = this.get("triggers");
            if (triggers.length < 1) return;
            triggers.eq(fromIndex).removeClass(this.get("activeTriggerClass"));
            triggers.eq(toIndex).addClass(this.get("activeTriggerClass"));
        },
        _switchPanel: function(panelInfo) {
            // 默认是最简单的切换效果：直接隐藏/显示
            panelInfo.fromPanels.hide();
            panelInfo.toPanels.show();
        },
        _getPanelInfo: function(toIndex, fromIndex) {
            var panels = this.get("panels").get();
            var step = this.get("step");
            var fromPanels, toPanels;
            // 初始情况下 fromIndex 为 undefined
            if (fromIndex > -1) {
                fromPanels = panels.slice(fromIndex * step, (fromIndex + 1) * step);
            }
            toPanels = panels.slice(toIndex * step, (toIndex + 1) * step);
            return {
                toIndex: toIndex,
                fromIndex: fromIndex,
                toPanels: $(toPanels),
                fromPanels: $(fromPanels)
            };
        },
        // 切换到上一视图
        prev: function() {
            var fromIndex = this.get("activeIndex");
            // 考虑循环切换的情况
            var index = (fromIndex - 1 + this.get("length")) % this.get("length");
            this.switchTo(index);
        },
        // 切换到下一视图
        next: function() {
            var fromIndex = this.get("activeIndex");
            var index = (fromIndex + 1) % this.get("length");
            this.switchTo(index);
        },
        _plug: function(plugin) {
            var pluginAttrs = plugin.attrs;
            if (pluginAttrs) {
                for (var key in pluginAttrs) {
                    if (pluginAttrs.hasOwnProperty(key) && // 不覆盖用户传入的配置
                    !(key in this.attrs)) {
                        this.set(key, pluginAttrs[key]);
                    }
                }
            }
            if (!plugin.isNeeded.call(this)) return;
            if (plugin.install) {
                plugin.install.call(this);
            }
            this._plugins.push(plugin);
        },
        destroy: function() {
            // todo: events
            var that = this;
            $.each(this._plugins, function(i, plugin) {
                if (plugin.destroy) {
                    plugin.destroy.call(that);
                }
            });
            Switchable.superclass.destroy.call(this);
        }
    });
    module.exports = Switchable;
    // Helpers
    // -------
    function generateTriggersMarkup(length, activeIndex, activeTriggerClass, justChildren) {
        var nav = $("<ul>");
        for (var i = 0; i < length; i++) {
            var className = i === activeIndex ? activeTriggerClass : "";
            $("<li>", {
                "class": className,
                html: i + 1
            }).appendTo(nav);
        }
        return justChildren ? nav.children() : nav;
    }
    // 内部默认的 className
    function constClass(classPrefix) {
        return {
            UI_SWITCHABLE: classPrefix || "",
            NAV_CLASS: classPrefix ? classPrefix + "-nav" : "",
            CONTENT_CLASS: classPrefix ? classPrefix + "-content" : "",
            TRIGGER_CLASS: classPrefix ? classPrefix + "-trigger" : "",
            PANEL_CLASS: classPrefix ? classPrefix + "-panel" : "",
            PREV_BTN_CLASS: classPrefix ? classPrefix + "-prev-btn" : "",
            NEXT_BTN_CLASS: classPrefix ? classPrefix + "-next-btn" : ""
        };
    }
});

define("arale/switchable/1.0.0/plugins/effects-debug", [ "$-debug", "arale/easing/1.0.0/easing-debug" ], function(require, exports, module) {
    var $ = require("$-debug");
    require("arale/easing/1.0.0/easing-debug");
    var SCROLLX = "scrollx";
    var SCROLLY = "scrolly";
    var FADE = "fade";
    // 切换效果插件
    module.exports = {
        attrs: {
            // 切换效果，可取 scrollx | scrolly | fade 或直接传入 effect function
            effect: "none",
            easing: "linear",
            duration: 500
        },
        isNeeded: function() {
            return this.get("effect") !== "none";
        },
        install: function() {
            var panels = this.get("panels");
            // 注：
            // 1. 所有 panel 的尺寸应该相同
            //    最好指定第一个 panel 的 width 和 height
            //    因为 Safari 下，图片未加载时，读取的 offsetHeight 等值会不对
            // 2. 初始化 panels 样式
            //    这些特效需要将 panels 都显示出来
            // 3. 在 CSS 里，需要给 container 设定高宽和 overflow: hidden
            panels.show();
            var effect = this.get("effect");
            var step = this.get("step");
            // 初始化滚动效果
            if (effect.indexOf("scroll") === 0) {
                var content = this.content;
                var firstPanel = panels.eq(0);
                // 设置定位信息，为滚动效果做铺垫
                content.css("position", "relative");
                // 注：content 的父级不一定是 container
                if (content.parent().css("position") === "static") {
                    content.parent().css("position", "relative");
                }
                // 水平排列
                if (effect === SCROLLX) {
                    panels.css("float", "left");
                    // 设置最大宽度，以保证有空间让 panels 水平排布
                    content.width("9999px");
                }
                // 只有 scrollX, scrollY 需要设置 viewSize
                // 其他情况下不需要
                var viewSize = this.get("viewSize");
                if (!viewSize[0]) {
                    viewSize[0] = firstPanel.outerWidth() * step;
                    viewSize[1] = firstPanel.outerHeight() * step;
                    this.set("viewSize", viewSize);
                }
                if (!viewSize[0]) {
                    throw new Error("Please specify viewSize manually");
                }
            } else if (effect === FADE) {
                var activeIndex = this.get("activeIndex");
                var min = activeIndex * step;
                var max = min + step - 1;
                panels.each(function(i, panel) {
                    var isActivePanel = i >= min && i <= max;
                    $(panel).css({
                        opacity: isActivePanel ? 1 : 0,
                        position: "absolute",
                        zIndex: isActivePanel ? 9 : 1
                    });
                });
            }
            // 覆盖 switchPanel 方法
            this._switchPanel = function(panelInfo) {
                var effect = this.get("effect");
                var fn = $.isFunction(effect) ? effect : Effects[effect];
                fn.call(this, panelInfo);
            };
        }
    };
    // 切换效果方法集
    var Effects = {
        // 淡隐淡现效果
        fade: function(panelInfo) {
            // 简单起见，目前不支持 step > 1 的情景。若需要此效果时，可修改结构来达成。
            if (this.get("step") > 1) {
                throw new Error('Effect "fade" only supports step === 1');
            }
            var fromPanel = panelInfo.fromPanels.eq(0);
            var toPanel = panelInfo.toPanels.eq(0);
            if (this.anim) {
                // 立刻停止，以开始新的
                this.anim.stop(false, true);
            }
            // 首先显示下一张
            toPanel.css("opacity", 1);
            toPanel.show();
            if (panelInfo.fromIndex > -1) {
                var that = this;
                var duration = this.get("duration");
                var easing = this.get("easing");
                // 动画切换
                this.anim = fromPanel.animate({
                    opacity: 0
                }, duration, easing, function() {
                    that.anim = null;
                    // free
                    // 切换 z-index
                    toPanel.css("zIndex", 9);
                    fromPanel.css("zIndex", 1);
                    fromPanel.css("display", "none");
                });
            } else {
                toPanel.css("zIndex", 9);
            }
        },
        // 水平/垂直滚动效果
        scroll: function(panelInfo) {
            var isX = this.get("effect") === SCROLLX;
            var diff = this.get("viewSize")[isX ? 0 : 1] * panelInfo.toIndex;
            var props = {};
            props[isX ? "left" : "top"] = -diff + "px";
            if (this.anim) {
                this.anim.stop();
            }
            if (panelInfo.fromIndex > -1) {
                var that = this;
                var duration = this.get("duration");
                var easing = this.get("easing");
                this.anim = this.content.animate(props, duration, easing, function() {
                    that.anim = null;
                });
            } else {
                this.content.css(props);
            }
        }
    };
    Effects[SCROLLY] = Effects.scroll;
    Effects[SCROLLX] = Effects.scroll;
    module.exports.Effects = Effects;
});

define("arale/switchable/1.0.0/plugins/autoplay-debug", [ "$-debug" ], function(require, exports, module) {
    var $ = require("$-debug");
    var win = $(window);
    // 自动播放插件
    module.exports = {
        attrs: {
            autoplay: false,
            // 自动播放的间隔时间
            interval: 5e3
        },
        isNeeded: function() {
            return this.get("autoplay");
        },
        install: function() {
            var element = this.element;
            var EVENT_NS = "." + this.cid;
            var timer;
            var interval = this.get("interval");
            var that = this;
            // start autoplay
            start();
            function start() {
                // 停止之前的
                stop();
                // 设置状态
                that.paused = false;
                // 开始现在的
                timer = setInterval(function() {
                    if (that.paused) return;
                    that.next();
                }, interval);
            }
            function stop() {
                if (timer) {
                    clearInterval(timer);
                    timer = null;
                }
                that.paused = true;
            }
            // public api
            this.stop = stop;
            this.start = start;
            // 滚出可视区域后，停止自动播放
            this._scrollDetect = throttle(function() {
                that[isInViewport(element) ? "start" : "stop"]();
            });
            win.on("scroll" + EVENT_NS, this._scrollDetect);
            // 鼠标悬停时，停止自动播放
            this.element.hover(stop, start);
        },
        destroy: function() {
            var EVENT_NS = "." + this.cid;
            this.stop && this.stop();
            if (this._scrollDetect) {
                this._scrollDetect.stop();
                win.off("scroll" + EVENT_NS);
            }
        }
    };
    // Helpers
    // -------
    function throttle(fn, ms) {
        ms = ms || 200;
        var throttleTimer;
        function f() {
            f.stop();
            throttleTimer = setTimeout(fn, ms);
        }
        f.stop = function() {
            if (throttleTimer) {
                clearTimeout(throttleTimer);
                throttleTimer = 0;
            }
        };
        return f;
    }
    function isInViewport(element) {
        var scrollTop = win.scrollTop();
        var scrollBottom = scrollTop + win.height();
        var elementTop = element.offset().top;
        var elementBottom = elementTop + element.height();
        // 只判断垂直位置是否在可视区域，不判断水平。只有要部分区域在可视区域，就返回 true
        return elementTop < scrollBottom && elementBottom > scrollTop;
    }
});

define("arale/switchable/1.0.0/plugins/circular-debug", [ "$-debug", "arale/switchable/1.0.0/plugins/effects-debug", "arale/easing/1.0.0/easing-debug" ], function(require, exports, module) {
    var $ = require("$-debug");
    var SCROLLX = "scrollx";
    var SCROLLY = "scrolly";
    var Effects = require("arale/switchable/1.0.0/plugins/effects-debug").Effects;
    // 无缝循环滚动插件
    module.exports = {
        // 仅在开启滚动效果时需要
        isNeeded: function() {
            var effect = this.get("effect");
            var circular = this.get("circular");
            return circular && (effect === SCROLLX || effect === SCROLLY);
        },
        install: function() {
            this._scrollType = this.get("effect");
            this.set("effect", "scrollCircular");
        }
    };
    Effects.scrollCircular = function(panelInfo) {
        var toIndex = panelInfo.toIndex;
        var fromIndex = panelInfo.fromIndex;
        var isX = this._scrollType === SCROLLX;
        var prop = isX ? "left" : "top";
        var viewDiff = this.get("viewSize")[isX ? 0 : 1];
        var diff = -viewDiff * toIndex;
        var props = {};
        props[prop] = diff + "px";
        // 开始动画
        if (fromIndex > -1) {
            // 开始动画前，先停止掉上一动画
            if (this.anim) {
                this.anim.stop(false, true);
            }
            var len = this.get("length");
            // scroll 的 0 -> len-1 应该是正常的从 0->1->2->.. len-1 的切换
            var isBackwardCritical = false;
            //(fromIndex === 0 && toIndex === len - 1);
            // len-1 -> 0
            var isForwardCritical = fromIndex === len - 1 && toIndex === 0;
            var isBackward = isBackwardCritical || !isForwardCritical && toIndex < fromIndex;
            var isCritical = isBackwardCritical || isForwardCritical;
            // 在临界点时，先调整 panels 位置
            if (isCritical) {
                diff = adjustPosition.call(this, isBackward, prop, viewDiff);
                props[prop] = diff + "px";
            }
            var duration = this.get("duration");
            var easing = this.get("easing");
            var that = this;
            this.anim = this.content.animate(props, duration, easing, function() {
                that.anim = null;
                // free
                // 复原位置
                if (isCritical) {
                    resetPosition.call(that, isBackward, prop, viewDiff);
                }
            });
        } else {
            this.content.css(props);
        }
    };
    // 调整位置
    function adjustPosition(isBackward, prop, viewDiff) {
        var step = this.get("step");
        var len = this.get("length");
        var start = isBackward ? len - 1 : 0;
        var from = start * step;
        var to = (start + 1) * step;
        var diff = isBackward ? viewDiff : -viewDiff * len;
        var panelDiff = isBackward ? -viewDiff * len : viewDiff * len;
        // 调整 panels 到下一个视图中
        var toPanels = $(this.get("panels").get().slice(from, to));
        toPanels.css("position", "relative");
        toPanels.css(prop, panelDiff + "px");
        // 返回偏移量
        return diff;
    }
    // 复原位置
    function resetPosition(isBackward, prop, viewDiff) {
        var step = this.get("step");
        var len = this.get("length");
        var start = isBackward ? len - 1 : 0;
        var from = start * step;
        var to = (start + 1) * step;
        // 滚动完成后，复位到正常状态
        var toPanels = $(this.get("panels").get().slice(from, to));
        toPanels.css("position", "");
        toPanels.css(prop, "");
        // 瞬移到正常位置
        this.content.css(prop, isBackward ? -viewDiff * (len - 1) : "");
    }
});
