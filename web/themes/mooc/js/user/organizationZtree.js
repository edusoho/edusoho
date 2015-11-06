define(function(require, exports, module) {
    require('jquery-plugin/zTree/3.5.19/css/demo.css');
    require('jquery-plugin/zTree/3.5.19/css/zTreeStyle/zTreeStyle.css');
    require('jquery-plugin/zTree/3.5.19/js/ztree.core');
    exports.run = function() {
        var $clickObj = $("#orgName");
        var $valueObj = $("#organizationId");
        var menuContent = 'menuContent';
        zTreeInit();
        var zTree = $.fn.zTree.getZTreeObj("organizationZtree");
        var nodeList = [];
        var oldValue = "";
        var lastValue = "";

        function zTreeOnClick(event, treeId, treeNode) {
            zTree.expandNode(treeNode, true, true, false); //展开当前选择的第一个节点（包括其全部子节点）
            // zTree.expandNode(treeNode); //展开当前选择的第一个节点（包括其全部子节点）
            node = zTree.getSelectedNodes()[0],
                $clickObj.val(node.name);
            $valueObj.val(node.id);
            lastValue = node.name;
        };
        //
        $clickObj.bind("focus", focusKey).bind("blur", blurKey).bind('click input propertychange', function() {
            zTree.expandAll(false);
            showMenu();
            lastValue = $clickObj.val();
            if ($clickObj.val() == "") return;
            updateNodes(false);
            nodeList = zTree.getNodesByParamFuzzy('name', $clickObj.val());
            nodes = zTree.getNodes();
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
                        pIdKey: "parentId",
                    },
                    key: {
                        title: "name"
                    },
                }
            };
            $.fn.zTree.init($("#organizationZtree"), setting, app.arguments.ztreeDates);
        }
    }
});