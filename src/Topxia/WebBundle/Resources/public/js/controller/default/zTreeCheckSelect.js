/**
 * Created by Simon on 2/25/16.
 */
define(function(require, exports, module) {
    require('ztree-core');
    require('ztree-excheck');
    require('jquery-plugin/zTree/3.5.21/css/zTreeStyle/zTreeStyle.css');
    require('jquery-plugin/zTree/3.5.21/css/demo.css');

    var ztreeCheckSelect = (function() {
        function ztreeCheckSelect(options) {
            var _self = this;
            _self.options = options;
            _self.selectInput = options.selectInput; //输入框input display
            _self.selectInputValue = options.selectInputValue; //输入框input value
            _self.zTreeRoot = options.zTreeRoot; //zTree的DOM 容器
            _self.checkEnable = options.checkEnable;
            _self.validator = options.validator; //form表单的校验组件
            _self.func = options.func;

            _self.init();
            $("#" + _self.selectInput).on('click', function() {
                _self.showMenu();
            });
            return {};
        }

        ztreeCheckSelect.prototype = {
            setting: function() {
                return {
                    check: {
                        enable: this.checkEnable,
                        chkboxType: {
                            "Y": "ps",
                            "N": "ps"
                        }
                    },
                    view: {
                        dblClickExpand: false,
                        showLine: false,
                        showIcon: false
                    },
                    callback: {
                        beforeClick: this.beforeClick.bind(this),
                        onCheck: this.onCheck.bind(this)
                    }
                }
            },
            init: function() {
                var _self = this;
                $.post($('#' + this.selectInput).data('url'), function(ztreeDatas) {
                    _self.zTree = $.fn.zTree.init($('#' + _self.zTreeRoot), _self.setting.bind(_self)(), ztreeDatas);
                })
            },
            beforeClick: function(treeId, treeNode) {
                this.zTree.checkNode(treeNode, !treeNode.checked, null, true);
                return false;
            },
            onCheck: function(e, treeId, treeNode) {
                var nodes = this.zTree.getCheckedNodes(true);
                $('#' + this.selectInput).attr("value", this.getselectAttr(nodes, 'name'));
                $("#" + this.selectInputValue).attr("value", this.getselectAttr(nodes, 'id'));
            },
            showMenu: function() {
                var $selectInput = $('#' + this.selectInput);
                var selectInputOffset = $selectInput.offset();
                $("#" + this.selectInput + "-menu").slideDown("fast");

                $("body").bind("mousedown", this.onBodyDown.bind(this));
            },
            hideMenu: function() {
                var _self = this;
                $("#" + _self.selectInput + "-menu").fadeOut("fast");
                $("body").unbind("mousedown", this.onBodyDown.bind(_self));
                if (_self.validator) {
                    _self.validator.query('[id="' + _self.selectInput + '"]').execute(function(error, results, element) {
                        if (!error) {
                            $('#'+_self.selectInput).next().remove();
                        }
                    })
                }

                this.func();

            },
            onBodyDown: function(event) {
                var _self = this;
                if (!(event.target.id == _self.selectInput || event.target.id == (_self.selectInput + "-menu") || $(event.target).parents("#" + _self.selectInput + "-menu").length > 0)) {
                    _self.hideMenu();
                }
            },
            getselectAttr: function(nodes, attr) {
                v = "";
                for (var i = 0, l = nodes.length; i < l; i++) {
                    if (attr == 'name') {
                        v += nodes[i].name + ",";
                    } else {
                        v += nodes[i].id + ",";
                    }
                }
                if (v.length > 0) v = v.substring(0, v.length - 1);
                return v;
            }
        };
        return ztreeCheckSelect;
    })();
    module.exports = ztreeCheckSelect;
});