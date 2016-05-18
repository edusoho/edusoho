define(function(require, exports, module) {
    require('z_tree');
    require('jquery-plugin/zTree/3.5.21/css/edusohoStyle/zTreeStyle.css');
    require('org_z_tree_css');
    var Widget = require('widget');

    var SelectTree = Widget.extend({
        attr: {
            displayBlock: 'ztreeContent',
        },

        events: {

        },

        setup: function() {
            this.treeRoot = this.get('ztreeDom');
            this.clickDom = this.get('clickDom');
            this.valueDom = this.get('valueDom');
            this.menuContent = this.get('displayBlock')|| 'ztreeContent';
            this.lastValue = this.oldValue = this.nodeList = null;
            this.initSelectTree();
            this.zTree = this.getSelectTree();
            this.initTreeEvent();
           console.log(this.menuContent)

        },

        getSelectTree: function() {
            var treeRoot = this.get('ztreeDom');
            return $.fn.zTree.getZTreeObj($(treeRoot).attr('id'));
        },

        initTreeEvent: function() {
            var self = this;

            $(this.clickDom).bind("focus", this.focusKey.bind(self)).bind("blur", this.blurKey.bind(self)).bind('click input propertychange', function() {
                self.zTree.expandAll(false);
                self.showMenu();
                self.lastValue = $(self.clickDom).val();
                if ($(self.clickDom).val() == "") return;
                self.updateNodes(false);
                self.nodeList = self.zTree.getNodesByParamFuzzy('name', $(self.clickDom).val());
                self.checkAllParents(self.nodeList[0]);
                console.log(1)
                self.updateNodes(true)
            });
        },

        zTreeOnClick: function(event, treeId, treeNode) {
            this.zTree.expandNode(treeNode, true, true, false); //展开当前选择的第一个节点（包括其全部子节点）
            $(this.clickDom).val(treeNode.name);
            $(this.valueDom).val(treeNode.orgCode);
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
            oldValue = $(this.get('clickDom')).val();
            $(this.get('clickDom')).val('');
        },

        blurKey: function(e) {
            if ($(this.get('clickDom')).val() === "") {
                $(this.get('clickDom')).val(oldValue);
            }
        },

        showMenu: function() {
            var self = this;
            var cityOffset = $(this.get('clickDom')).offset();
            $("#" + this.menuContent).slideDown("fast");
            $("body").bind("mousedown", self.onBodyDown.bind(self));
        },

        hideMenu: function() {
            $("#" + this.menuContent).fadeOut("fast");
            $("body").unbind("mousedown", this.onBodyDown);
        },

        onBodyDown: function(event) {
            var self = this;
            if (!(event.target.id == "menuBtn" || event.target.id == self.menuContent || $(event.target).parents("#" + self.menuContent).length > 0)) {
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
            $.ajax($(treeRoot).data('source'), {
                type: 'GET',
                async: false,
                dataType: "json"
            }).then(function(treeData) {
                $.fn.zTree.init($(treeRoot), setting, treeData);
            });
        }
    });

    module.exports = SelectTree;


});