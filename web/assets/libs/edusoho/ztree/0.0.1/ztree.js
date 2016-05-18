define(function (require, exports, module) {
    "use strict";

    require('z_tree');
    require('jquery-plugin/zTree/3.5.21/css/edusohoStyle/zTreeStyle.css');
    require('org_z_tree_css');

    /**
     *
     * @param ztreeDom
     * @param clickDom 输入框
     * @param valueDom 值的DOM
     * @param displayBlock 展示ztree节点的DOM 默认为ztreeContent 必须为ID
     */
    module.exports = function (ztreeDom, clickDom, valueDom, displayBlock) {
        var $clickObj = $(clickDom);
        var $valueObj = $(valueDom);
        var menuContent = displayBlock || 'ztreeContent';
        zTreeInit();
        var zTree = $.fn.zTree.getZTreeObj($(ztreeDom).attr('id'));
        var nodeList = [];
        var oldValue = "";
        var lastValue = "";

        function zTreeOnClick(event, treeId, treeNode) {
            zTree.expandNode(treeNode, true, true, false); //展开当前选择的第一个节点（包括其全部子节点）
            var node = zTree.getSelectedNodes()[0];
            $clickObj.val(node.name);
            $valueObj.val(node.orgCode);
            lastValue = node.name;
        };

        $clickObj.bind("focus", focusKey).bind("blur", blurKey).bind('click input propertychange', function() {
            zTree.expandAll(false);
            showMenu();
            lastValue = $clickObj.val();
            if ($clickObj.val() == "") return;
            updateNodes(false);
            nodeList = zTree.getNodesByParamFuzzy('name', $clickObj.val());
            checkAllParents(nodeList[0]);
            updateNodes(true)
        });

        function updateNodes(highlight) {
            for (var i = 0, l = nodeList.length; i < l; i++) {
                nodeList[i].highlight = highlight;
                zTree.updateNode(nodeList[i]);
            }
        }

        function checkAllParents(treeNode) {
            if (treeNode == null || treeNode.parentId == null) {
                zTree.expandNode(treeNode, true, true, false,true);
            } else {
                checkAllParents(treeNode.getParentNode());
            }
        }

        function getFontCss(treeId, treeNode) {
            return (!!treeNode.highlight) ? {
                color: "#428bca",
                // "font-weight": "bold"
            } : {
                color: "#333",
                "font-weight": "normal"
            };
        }

        function focusKey(e) {
            oldValue = $clickObj.val();
            $clickObj.val('');
        }

        function blurKey(e) {
            if ($clickObj.val() === "") {
                $clickObj.val(oldValue);
            }
        }

        function showMenu() {
            var cityOffset = $clickObj.offset();
            $("#"+menuContent).slideDown("fast");
            $("body").bind("mousedown", onBodyDown);
        }

        function hideMenu() {
            $("#"+menuContent).fadeOut("fast");
            $("body").unbind("mousedown", onBodyDown);
        }

        function onBodyDown(event) {
            if (!(event.target.id == "menuBtn" || event.target.id == menuContent || $(event.target).parents("#"+menuContent).length > 0)) {
                hideMenu();
            }
        }

        function zTreeInit() {
            var setting = {
                view: {
                    dblClickExpand: false,
                    showLine: false,
                    showIcon: false,
                    fontCss: getFontCss,
                    selectedMulti: false
                },
                callback: {
                    onClick: zTreeOnClick
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

            $.ajax($(ztreeDom).data('source'), {
                type: 'GET',
                async: false,
                dataType: "json"
            }).then(function (treeData) {
                $.fn.zTree.init($(ztreeDom), setting, treeData);
            });
        }
    };
});