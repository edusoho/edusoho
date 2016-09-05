define(function(require, exports, module) {


    require('z_tree');
	require('z_tree_css');
	require('z_tree_exhide');


    var Widget = require('widget');

    var Tree = Widget.extend({

    	attrs: {
    		zTree: null
        },

    	setup: function() {
    		var self = this;
    		var element = self.element;

	        var defaultSetting = {
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

            this.hideNodes();
	    },

	    getCheckedNodes: function() {
            var tree = this.get('zTree');
            var chkedNodes = tree.getCheckedNodes(true);
            var hiddenNodes = tree.getNodesByFilter(function (node) {
                return node.isHidden;
            }, false);
            hiddenNodes = tree.transformToArray(hiddenNodes);
            return chkedNodes.concat(hiddenNodes);
        },

        hideNodes: function () {
            var tree = this.get('zTree');
            var needHiddenNodes = tree.getNodesByParam('visible', false);
            tree.hideNodes(needHiddenNodes);
        }
    });

    module.exports = Tree;
});