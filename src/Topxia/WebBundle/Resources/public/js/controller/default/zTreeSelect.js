/**
 * Created by wt on 16/3/3.
 */
define(function (require, exports, module) {
    require('ztree-core');
    require('jquery-plugin/zTree/3.5.21/css/zTreeStyle/zTreeStyle.css');
    require('jquery-plugin/zTree/3.5.21/css/demo.css');


    var zTreeSelect = (function () {
        function zTreeSelect(options) {
            var _self = this;
            // _self.options = options;
            _self.selectInput = options.selectInput;             //输入框input display
            _self.selectInputValue = options.selectInputValue;   //输入框input value
            _self.zTreeRoot = options.zTreeRoot;   //zTree的DOM 容器
            _self.init();
            $("#" + _self.selectInput).on('click', function () {
                _self.showMenu();
            });
            return {};
        }

        zTreeSelect.prototype = {
            setting: function () {
                return {
                    view: {
                        dblClickExpand: false,
                        selectedMulti: false,
                        showLine:false,
                        showIcon:false
                    },
                    data: {
                        simpleData: {
                            enable: true,
                            pIdKey: "parentId",
                        },
                        key: {
                            title: "name"
                        },
                    },
                    callback: {
                        // beforeClick: this.beforeClick.bind(this),
                        onClick: this.onClick.bind(this)
                    }
                }
            },
            beforeClick: function (treeId, treeNode) {
                var check = (treeNode && !treeNode.isParent);
                // if (!check) alert("只能选择城市...");
                return check;
            },
            onClick: function (e, treeId, treeNode) {
                var nodes = this.zTree.getSelectedNodes();
                console.log('onClick')
                $('#' + this.selectInput).attr("value", this.getselectAttr(nodes, 'name'));
                $("#" + this.selectInputValue).attr("value", this.getselectAttr(nodes, 'id'));
                console.log( $("#" + this.selectInputValue))
            },
            showMenu: function () {
                var selectInput = $("#" + this.selectInput);
                var selectInputOffset = $("#" + this.selectInput).offset();
                $("#" + this.selectInput + "-menu").slideDown("fast");

                $("body").bind("mousedown", this.onBodyDown.bind(this));
            },
            hideMenu: function () {
                $("#" + this.selectInput + "-menu").fadeOut("fast");
                $("body").unbind("mousedown", this.onBodyDown.bind(this));
            },
            onBodyDown: function (event) {
                var _self = this;
                if (!(event.target.id == _self.selectInput || event.target.id == (this.selectInput + "-menu") || $(event.target).parents("#" + this.selectInput + "-menu").length > 0)) {
                    _self.hideMenu();
                }
            },
            getselectAttr: function (nodes, attr) {
                v = "";
                nodes.sort(function compare(a, b) {
                    return a.id - b.id;
                });
                for (var i = 0, l = nodes.length; i < l; i++) {
                    if (attr == 'name') {
                        v += nodes[i].name + ",";
                    } else {
                        v += nodes[i].id + ",";
                    }
                }
                if (v.length > 0) v = v.substring(0, v.length - 1);
                console.log(v)
                return v;
            },
            init: function () {
                var _self = this;
                $.post($("#" + _self.selectInput).data('url'), function (zNodes) {
                    _self.zTree = $.fn.zTree.init($("#" + _self.zTreeRoot), _self.setting.bind(_self)(), zNodes);
                })
            }
        }
        return zTreeSelect;
    })();
    module.exports = zTreeSelect;
});
