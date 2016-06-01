define(function(require, exports, module) {
    require('z_tree');
    require('jquery-plugin/zTree/3.5.21/css/edusohoStyle/zTreeStyle.css');
    require('org_z_tree_css');
    var Widget = require('widget');

    var SelectTree = Widget.extend({
        attrs: {
            displayBlockEl: 'ztreeContent',
            treeRootEl: 'orgZtree'
        },

        events: {

        },

        setup: function() {
            this.inputID = $(this.element).data('inputName');
            this.menuContent = this.get('displayBlockEl');
            this.treeRoot = this.get('treeRootEl');
            if (this.get('modal')) {
                this.inputID = 'modal-' + this.inputID;
                this.menuContent = 'modal-' + this.menuContent;
                this.treeRoot = 'modal-' + this.treeRoot;
            }

            this.lastValue = this.oldValue = this.nodeList = null;
            this.initDom();
            this.initSelectTree();
            this.zTree = this.getSelectTree();
            this.initTreeEvent();
        },

        getSelectTree: function() {
            return $.fn.zTree.getZTreeObj(this.treeRoot);
        },

        initDom: function() {
            var sourceUrl = $(this.element).data('url');
            var width = $(this.element).outerWidth(true);
            var inputName = $(this.element).data('inputName');
            var inputValue = $(this.element).data('inputValue');
            //ztree 初始化的容器
            var selectDom = "<div id='" + this.menuContent + "' class='ztreeContent' style='display:none; position: absolute; z-index: 10000;'>" +
                "<ul id='" + this.treeRoot + "' class='ztree' style='margin-top:0; width:" + width + "px;z-index: 10000' data-source=" + sourceUrl + "></ul>" +
                "</div>";

            var selectOrgCodeDom = ' <input  type ="hidden" name="' + inputName + '" id ="' + this.inputID + '" value="' +inputValue+ '" >';

            $(this.element).parents('.controls').append(selectOrgCodeDom);
            $(this.element).parents('.controls').append(selectDom);

        },

        initTreeEvent: function() {
            var self = this;

            $(this.element).bind("focus", this.focusKey.bind(self)).bind("blur", this.blurKey.bind(self)).bind('click input propertychange', function() {
                self.zTree.expandAll(false);
                self.showMenu();
                self.lastValue = $(self.element).val();
                if ($(self.element).val() == "") return;
                self.updateNodes(false);
                self.nodeList = self.zTree.getNodesByParamFuzzy('name', $(self.element).val());
                self.checkAllParents(self.nodeList[0]);
                self.updateNodes(true)
            });
        },

        zTreeOnClick: function(event, treeId, treeNode) {
            this.zTree.expandNode(treeNode, true, true, false); //展开当前选择的第一个节点（包括其全部子节点）
            $(this.element).val(treeNode.name);
            $('#' + this.inputID).val(treeNode.orgCode);

            this.lastValue = treeNode.name;
            this.hideMenu.bind(this)();
        },

        updateNodes: function(highlight) {
            if (!this.nodeList) {
                return;
            }
            for (var i = 0, l = this.nodeList.length; i < l; i++) {
                this.nodeList[i].highlight = highlight;
                this.zTree.updateNode(this.nodeList[i]);
            }
        },

        checkAllParents: function(treeNode) {
            if (treeNode == null || treeNode.parentId == null) {
                this.zTree.expandNode(treeNode, true, true, false, true);
            } else {
                this.checkAllParents(treeNode.getParentNode());
            }
        },

        getFontCss: function(treeId, treeNode) {
            return (!!treeNode.highlight) ? {
                color: "#428bca",
                "font-weight": "bold"
            } : {
                color: "#333",
                "font-weight": "normal"
            };
        },

        focusKey: function(e) {
            oldValue = $(this.element).val();
            $(this.element).val('');
        },

        blurKey: function(e) {
            if ($(this.element).val() === "") {
                $(this.element).val(oldValue);
            }
        },

        showMenu: function() {
            var self = this;
            var cityOffset = $(this.element).offset();
            $("#" + this.menuContent).slideDown("fast");
            $("body").bind("mousedown", self.onBodyDown.bind(self));
        },

        hideMenu: function() {
            $("#" + this.menuContent).fadeOut("fast");
            $("body").unbind("mousedown", this.onBodyDown);
        },

        onBodyDown: function(event) {
            var self = this;
            if (!(event.target.id == self.menuContent || $(event.target).parents("#" + self.menuContent).length > 0)) {

                self.hideMenu.bind(self)();
            }
        },

        initSelectTree: function() {
            var setting = {
                view: {
                    dblClickExpand: false,
                    showLine: false,
                    showIcon: false,
                    fontCss: this.getFontCss,
                    selectedMulti: false
                },
                callback: {
                    onClick: this.zTreeOnClick.bind(this)
                },
                data: {
                    simpleData: {
                        enable: true,
                        pIdKey: "parentId"
                    },
                    key: {
                        title: "name"
                    },
                }
            };
            var treeRoot = this.treeRoot;
            $.ajax($('#' + treeRoot).data('source'), {
                type: 'GET',
                async: false,
                dataType: "json"
            }).then(function(treeData) {
                $.fn.zTree.init($('#' + treeRoot), setting, treeData);
            });
        }
    });

    module.exports = SelectTree;


});