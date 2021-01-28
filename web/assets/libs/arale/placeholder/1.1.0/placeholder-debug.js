define("arale/placeholder/1.1.0/placeholder-debug", [ "$-debug" ], function(require, exports, module) {
    // placeholder
    // --------
    // 针对不支持placeholder的浏览器做的模拟支持
    // Thanks to
    // - https://github.com/mathiasbynens/jquery-placeholder
    var $ = require("$-debug"), placeholder;
    // 以下代码引用，稍微修改了点
    var ret = function($) {
        var isInputSupported = "placeholder" in document.createElement("input"), isTextareaSupported = "placeholder" in document.createElement("textarea"), // 这里的修改是为了防止修改$.fn
        // prototype = $.fn,
        prototype = {}, valHooks = $.valHooks, hooks, placeholder;
        if (isInputSupported && isTextareaSupported) {
            placeholder = prototype.placeholder = function() {
                return this;
            };
            placeholder.input = placeholder.textarea = true;
        } else {
            placeholder = prototype.placeholder = function() {
                var $this = this;
                $this.filter((isInputSupported ? "textarea" : ":input") + "[placeholder]").unbind({
                    "focus.placeholder": clearPlaceholder,
                    "blur.placeholder": setPlaceholder
                }).bind({
                    "focus.placeholder": clearPlaceholder,
                    "blur.placeholder": setPlaceholder
                }).data("placeholder-enabled", true).trigger("blur.placeholder");
                return $this;
            };
            placeholder.input = isInputSupported;
            placeholder.textarea = isTextareaSupported;
            hooks = {
                get: function(element) {
                    var $element = $(element);
                    return $element.data("placeholder-enabled") && $element.hasClass("placeholder") ? "" : element.value;
                },
                set: function(element, value) {
                    var $element = $(element);
                    if (!$element.data("placeholder-enabled")) {
                        return element.value = value;
                    }
                    if (value == "") {
                        element.value = value;
                        // Issue #56: Setting the placeholder causes problems if the element continues to have focus.
                        if (element != document.activeElement) {
                            // We can't use `triggerHandler` here because of dummy text/password inputs :(
                            setPlaceholder.call(element);
                        }
                    } else if ($element.hasClass("placeholder")) {
                        clearPlaceholder.call(element, true, value) || (element.value = value);
                    } else {
                        element.value = value;
                    }
                    // `set` can not return `undefined`; see http://jsapi.info/jquery/1.7.1/val#L2363
                    return $element;
                }
            };
            // 这里的修改是为了防止别的hooks被覆盖
            if (!isInputSupported) {
                var _old = valHooks.input;
                if (_old) {
                    valHooks.input = {
                        get: function() {
                            if (_old.get) {
                                _old.get.apply(this, arguments);
                            }
                            return hooks.get.apply(this, arguments);
                        },
                        set: function() {
                            if (_old.set) {
                                _old.set.apply(this, arguments);
                            }
                            return hooks.set.apply(this, arguments);
                        }
                    };
                } else {
                    valHooks.input = hooks;
                }
            }
            if (!isTextareaSupported) {
                var _old = valHooks.textarea;
                if (_old) {
                    valHooks.textarea = {
                        get: function() {
                            if (_old.get) {
                                _old.get.apply(this, arguments);
                            }
                            return hooks.get.apply(this, arguments);
                        },
                        set: function() {
                            if (_old.set) {
                                _old.set.apply(this, arguments);
                            }
                            return hooks.set.apply(this, arguments);
                        }
                    };
                } else {
                    valHooks.textarea = hooks;
                }
            }
            $(function() {
                // Look for forms
                $(document).delegate("form", "submit.placeholder", function() {
                    // Clear the placeholder values so they don't get submitted
                    var $inputs = $(".placeholder", this).each(clearPlaceholder);
                    setTimeout(function() {
                        $inputs.each(setPlaceholder);
                    }, 10);
                });
            });
            // Clear placeholder values upon page reload
            $(window).bind("beforeunload.placeholder", function() {
                $(".placeholder").each(function() {
                    this.value = "";
                });
            });
        }
        function args(elem) {
            // Return an object of element attributes
            var newAttrs = {}, rinlinejQuery = /^jQuery\d+$/;
            $.each(elem.attributes, function(i, attr) {
                if (attr.specified && !rinlinejQuery.test(attr.name)) {
                    newAttrs[attr.name] = attr.value;
                }
            });
            return newAttrs;
        }
        function clearPlaceholder(event, value) {
            var input = this, $input = $(input);
            // 修改演示四出现的问题
            if ((input.value == $input.attr("placeholder") || input.value == "") && $input.hasClass("placeholder")) {
                if ($input.data("placeholder-password")) {
                    $input = $input.hide().next().show().attr("id", $input.removeAttr("id").data("placeholder-id"));
                    // If `clearPlaceholder` was called from `$.valHooks.input.set`
                    if (event === true) {
                        return $input[0].value = value;
                    }
                    $input.focus();
                } else {
                    input.value = "";
                    $input.removeClass("placeholder");
                    input == document.activeElement && input.select();
                }
            }
        }
        function setPlaceholder() {
            var $replacement, input = this, $input = $(input), $origInput = $input, id = this.id;
            if ($(input).val() == "") {
                if (input.type == "password") {
                    if (!$input.data("placeholder-textinput")) {
                        try {
                            $replacement = $input.clone().attr({
                                type: "text"
                            });
                        } catch (e) {
                            $replacement = $("<input>").attr($.extend(args(this), {
                                type: "text"
                            }));
                        }
                        $replacement.removeAttr("name").data({
                            "placeholder-password": true,
                            "placeholder-id": id
                        }).bind("focus.placeholder", clearPlaceholder);
                        $input.data({
                            "placeholder-textinput": $replacement,
                            "placeholder-id": id
                        }).before($replacement);
                    }
                    $input = $input.removeAttr("id").hide().prev().attr("id", id).show();
                }
                $input.addClass("placeholder");
                $input[0].value = $input.attr("placeholder");
            } else {
                $input.removeClass("placeholder");
            }
        }
        return placeholder;
    }($);
    // 做简单的api封装
    placeholder = !ret.input || !ret.textarea ? function(element) {
        if (!element) {
            element = $("input, textarea");
        }
        if (element) {
            ret.call($(element));
        }
    } : function() {};
    // 默认运行，这样就不需要手动调用
    placeholder();
    // 提供清除 input.value 的方法
    placeholder.clear = function(element) {
        element = $(element);
        if (element[0].tagName === "FORM") {
            // 寻找表单下所有的 input 元素
            clearInput(element.find("input.placeholder, textarea.placeholder"));
        } else {
            // 清除指定的 input 元素
            clearInput(element);
        }
        function clearInput(input) {
            input.each(function(i, item) {
                item = $(item);
                if (item[0].value === item.attr("placeholder") && item.hasClass("placeholder")) {
                    item[0].value = "";
                }
            });
        }
    };
    module.exports = placeholder;
});
