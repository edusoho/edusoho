define(function(require, exports, module) {


    require('z_tree');
	require('z_tree_css');
	require('z_tree_exhide');

	/*前台隐藏的方案 现在暂时也用后台去做控制
	var _ = require('underscore');
	var z_data = $.fn.zTree._z.data;
    Override zTree getTreeCheckedNodes method in jquery.ztree.exhide extension
	z_data.getTreeCheckedNodes = function(setting, nodes, checked, results) {
		if (!nodes) return [];
		var childKey = setting.data.key.children,
			checkedKey = setting.data.key.checked,
			onlyOne = (checked && setting.check.chkStyle == $.fn.zTree.consts.radio.STYLE && setting.check.radioType == $.fn.zTree.consts.radio.TYPE_ALL);
		results = !results ? [] : results;
		for (var i = 0, l = nodes.length; i < l; i++) {

			//扩展了visible属性  visible为false UI不显示， 但是是选中状态
			if ((nodes[i].nocheck !== true || (nodes[i].visible !== undefined && nodes[i].visible === false))&& nodes[i].chkDisabled !== true && nodes[i][checkedKey] == checked) {
				results.push(nodes[i]);
				if (onlyOne) {
					break;
				}
			}
			z_data.getTreeCheckedNodes(setting, nodes[i][childKey], checked, results);
			if (onlyOne && results.length > 0) {
				break;
			}
		}
		return results;
	};*/

    var Widget = require('widget');

    var Tree = Widget.extend({

    	attrs: {
    		zTree: null
        },

    	setup: function() {
    		var self = this;
    		var element = self.element;

	        var defaultSetting = {
	        	/*callback: {
					onCheck: _.bind(this.onCheckHandler, this)
				},*/
	            check: {
	                enable: true,
	                chkboxType: { "Y": "ps", "N": "s" }
	            },
	            data: {
	                simpleData: {
	                    enable: true,
						idKey: 'code',
						pIdKey: 'parent'
	                }
	            }
	        };

	        var setting = $.extend(self.get(), defaultSetting);
	        var zNodes = element.find('textarea').text();

	        this.set('zTree', $.fn.zTree.init($(element), setting, JSON.parse(zNodes)));
			//this.hieNodes();
	    },

	    getCheckedNodes: function() {
            var tree = this.get('zTree');
            return tree.getCheckedNodes(true);
        },
		/*前台隐藏的方案 现在暂时也用后台去做控制
        hideNodes: function () {
            var tree = this.get('zTree');
            var needHiddenNodes = tree.getNodesByParam('visible', false);
            tree.hideNodes(needHiddenNodes);
        },

		onCheckHandler: function (event, treeId, treeNode) {
			if(treeNode.checked){
				return;
			}

			var visibleNodes = this.get('zTree').getNodesByParam('visible', false, treeNode);

			_.each(visibleNodes, this.cancelCheckNode, this);
		},

		cancelCheckNode: function (node) {
			node.checked = false;
			var tree = this.get('zTree');
			//修改 checked 勾选状态不会触发 beforeCheck / onCheck 事件回调函数 防止时间复杂度呈指数增长
			tree.updateNode(node);
		}*/
    });

    module.exports = Tree;
});