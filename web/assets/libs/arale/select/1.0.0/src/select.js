define(function(require, exports, module) {

    var Overlay = require('overlay');
    var $ = require('$');
    var Templatable = require('templatable');

    var template = require('./select.tpl');

    var Select = Overlay.extend({

        Implements: Templatable,

        attrs: {
            trigger: {
                value: null, // required
                getter: function(val) {
                    return $(val).eq(0);
                }
            },
            classPrefix: 'ui-select',
            template: template,
            // 定位配置
            align: {
                baseXY: [0, '100%-1px']
            },

            // 原生 select 的属性
            name: '',
            value: '',
            length: 0,
            selectedIndex: -1,
            multiple: false, // TODO
            disabled: false,

            // 以下不要覆盖
            selectSource: null // 原生表单项的引用，select/input
        },

        events: {
            'click [data-role=item]': function(e) {
                var target = $(e.currentTarget);
                this.select(target);
            },
            'mouseenter [data-role=item]': function(e) {
                $(e.currentTarget).addClass(this.get('classPrefix') + '-hover');
            },
            'mouseleave [data-role=item]': function(e) {
                $(e.currentTarget).removeClass(this.get('classPrefix') + '-hover');
            }
        },

        // 覆盖父类
        // --------

        initAttrs: function(config, dataAttrsConfig) {
            Select.superclass.initAttrs.call(this, config, dataAttrsConfig);

            var trigger = this.get('trigger');
            if (trigger[0].tagName.toLowerCase() == 'select') {
                // 初始化 name
                // 如果 select 的 name 存在则覆盖 name 属性
                var selectName = trigger.attr('name');
                if (selectName) {
                    this.set('name', selectName);
                }

                // 替换之前把 select 保存起来
                this.set('selectSource', trigger);
                // 替换 trigger
                var triggerTemplate = '<a href="#" class="' +
                    this.get('classPrefix') + '-trigger"></a>';
                var newTrigger = $(triggerTemplate);
                this.set('trigger', newTrigger);
                trigger.after(newTrigger).hide();

                // trigger 如果为 select 则根据 select 的结构生成
                this.model = convertSelect(trigger[0], this.get('classPrefix'));

            } else {
                // 如果 name 存在则创建隐藏域
                var selectName = this.get('name');
                if (selectName) {
                    var input = $('input[name=' + selectName + ']').eq(0);
                    if (!input[0]) {
                        input = $(
                            '<input type="hidden" id="select-' + selectName +
                            '" name="' + selectName +
                            '" />'
                        ).insertBefore(trigger);
                    }
                    this.set('selectSource', input);
                }

                // trigger 如果为其他 DOM，则由用户提供 model
                this.model = completeModel(this.model, this.get('classPrefix'));
            }
        },

        setup: function() {
            var that = this;
            var trigger = this.get('trigger')
                .on('click', {self: this}, this._trigger_click)
                .on('mouseenter', function(e) {
                    trigger.addClass(that.get('classPrefix') + '-trigger-hover');
                })
                .on('mouseleave', function(e) {
                    trigger.removeClass(that.get('classPrefix') + '-trigger-hover');
                });

            this.options = this.$('[data-role=content]').children();
            // 初始化 select 的参数
            // 必须在插入文档流后操作
            this.select('[data-selected=true]');
            this.set('length', this.options.length);

            this._tweakAlignDefaultValue();

            // 调用 overlay，点击 body 隐藏
            this._blurHide(trigger);

            Select.superclass.setup.call(this);
        },

        render: function() {
            Select.superclass.render.call(this);
            this._setTriggerWidth();
            return this;
        },

        show: function() {
            Select.superclass.show.call(this);
            this._setPosition();
            return this;
        },

        // trigger 的宽度和浮层保持一致
        _setTriggerWidth: function() {
            var trigger = this.get('trigger');
            var width = this.element.outerWidth();
            var pl = parseInt(trigger.css('padding-left'), 10);
            var pr = parseInt(trigger.css('padding-right'), 10);
            var bl = parseInt(trigger.css('border-left-width'), 10);
            var br = parseInt(trigger.css('border-right-width'), 10);
            trigger.css('width', width - pl - pr - bl - br);
        },

        // borrow from dropdown
        // 调整 align 属性的默认值, 在 trigger 下方
        _tweakAlignDefaultValue: function() {
            var align = this.get('align');
            // 默认基准定位元素为 trigger
            if (align.baseElement._id === 'VIEWPORT') {
                align.baseElement = this.get('trigger');
            }
            this.set('align', align);
        },

        _trigger_click: function(e) {
            var self = e.data.self;
            e.preventDefault();
            if (!self.get('disabled')) {
                self.show();
            }
        },

        destroy: function() {
            this.element.remove();
            Select.superclass.destroy.call(this);
        },

        // 方法接口
        // --------

        select: function(option) {
            var selectIndex = getOptionIndex(option, this.options);
            var oldSelectIndex = this.get('selectedIndex');
            this.set('selectedIndex', selectIndex);

            // 如果不是原来选中的则触发 change 事件
            if (oldSelectIndex !== selectIndex) {
                var selector = this.options.eq(selectIndex);
                this.trigger('change', selector);
            }

            this.hide();
            return this;
        },

        syncModel: function(model) {
            this.model = completeModel(model, this.get('classPrefix'));
            this.renderPartial('[data-role=content]');
            // 渲染后重置 select 的属性
            this.options = this.$('[data-role=content]').children();
            this.set('length', this.options.length);
            this.set('selectedIndex', -1);
            this.set('value', '');
            this.select('[data-selected=true]');

            // 重新设置 trigger 宽度
            this._setTriggerWidth();
            return this;
        },

        getOption: function(option) {
            var index = getOptionIndex(option, this.options);
            return this.options.eq(index);
        },

        addOption: function(option) {
            var model = this.model.select;
            model.push(option);
            this.syncModel(model);
            return this;
        },

        removeOption: function(option) {
            var removedIndex = getOptionIndex(option, this.options),
                oldIndex = this.get('selectedIndex'),
                removedOption = this.options.eq(removedIndex);

            // 删除 option，更新属性
            removedOption.remove();
            this.options = this.$('[data-role=content]').children();
            this.set('length', this.options.length);

            // 如果被删除的是当前选中的，则选中第一个
            if (removedIndex === oldIndex) {
                this.set('selectedIndex', 0);

            // 如果被删除的在选中的前面，则选中的索引向前移动一格
            } else if (removedIndex < oldIndex) {
                this.set('selectedIndex', oldIndex - 1);
            }
            return this;
        },

        // set 后的回调
        // ------------

        _onRenderSelectedIndex: function(index) {
            if (index == -1) return;

            var selector = this.options.eq(index),
                currentItem = this.currentItem,
                value = selector.attr('data-value');

            // 如果两个 DOM 相同则不再处理
            if (currentItem && selector[0] == currentItem[0]) {
                return;
            }

            // 设置原来的表单项
            var source = this.get('selectSource');
            source && (source[0].value = value);

            // 处理之前选中的元素
            if (currentItem) {
                currentItem.attr('data-selected', 'false')
                    .removeClass(this.get('classPrefix') + '-selected');
            }

            // 处理当前选中的元素
            selector.attr('data-selected', 'true')
                .addClass(this.get('classPrefix') + '-selected');
            this.set('value', value);

            // 填入选中内容，位置先找 "data-role"="trigger-content"，再找 trigger
            var trigger = this.get('trigger');
            var triggerContent = trigger.find('[data-role=trigger-content]');
            if (triggerContent.length) {
                triggerContent.html(selector.html());
            } else {
                trigger.html(selector.html());
            }
            this.currentItem = selector;
        },

        _onRenderDisabled: function(val) {
            var className = this.get('classPrefix') + '-disabled';
            var trigger = this.get('trigger');
            trigger[(val ? 'addClass' : 'removeClass')](className);
        }
    });

    module.exports = Select;

    // Helper
    // ------

    // 将 select 对象转换为 model
    //
    // <select>
    //   <option value='value1'>text1</option>
    //   <option value='value2' selected>text2</option>
    // </select>
    //
    // ------->
    //
    // [
    //   {value: 'value1', text: 'text1',
    //      defaultSelected: false, selected: false}
    //   {value: 'value2', text: 'text2',
    //      defaultSelected: true, selected: true}
    // ]
    function convertSelect(select, classPrefix) {
        var i, model = [], options = select.options,
            l = options.length, hasDefaultSelect = false;
        for (i = 0; i < l; i++) {
            var j, o = {}, option = options[i];
            var fields = ['text', 'value', 'defaultSelected', 'selected'];
            for (j in fields) {
                var field = fields[j];
                o[field] = option[field];
            }
            o.defaultSelected = option.defaultSelected ? 'true' : 'false';
            if (option.selected) {
                o.selected = 'true';
                hasDefaultSelect = true;
            } else {
                o.selected = 'false';
            }
            model.push(o);
        }
        // 当所有都没有设置 selected，默认设置第一个
        if (!hasDefaultSelect) {
            newModel[0].selected = 'true';
        }
        return {select: model, classPrefix: classPrefix};
    }

    // 补全 model 对象
    function completeModel(model, classPrefix) {
        var i, j, l, ll, newModel = [], selectIndexArray = [];
        for (i = 0, l = model.length; i < l; i++) {
            var o = model[i];
            if (o.selected) {
                o.selected = o.defaultSelected = 'true';
                selectIndexArray.push(i);
            } else {
                o.selected = o.defaultSelected = 'false';
            }
            newModel.push(o);
        }
        if (selectIndexArray.length > 0) {
            // 如果有多个 selected 则选中最后一个
            selectIndexArray.pop();
            for (j = 0, ll = selectIndexArray.length; j < ll; j++) {
                newModel[j].selected = 'false';
            }
        } else { //当所有都没有设置 selected 则默认设置第一个
            newModel[0].selected = 'true';
        }
        return {select: newModel, classPrefix: classPrefix};
    }

    function getOptionIndex(option, options) {
        var index;
        if ($.isNumeric(option)) { // 如果是索引
            index = option;
        } else if (typeof option === 'string') { // 如果是选择器
            index = options.index(options.parent().find(option));
        } else { // 如果是 DOM
            index = options.index(option);
        }
        return index;
    }
});
