define(function(require, exports, module) {

    require('z_tree');
    require('z_tree_css');
    var Widget = require('widget');

    var Tree = Widget.extend({

    	attrs: {
        },

    	setup: function() {
    		var self = this;
    		var element = self.element;

	        var defaultSetting = {
	            check: {
	                enable: true
	            },
	            data: {
	                simpleData: {
	                    enable: true
	                }
	            }
	        };

	        var setting = $.extend(self.get(), defaultSetting);

	        var zNodes = element.data('nodes')

	        function getCheckedNodes() {
	            var zTree = $.fn.zTree.getZTreeObj("treeDemo");
	            var nodes = zTree.getCheckedNodes(true);
	            return nodes;
	        }

	        $.fn.zTree.init($(element), setting, zNodes);
	    }
    });

    module.exports = Tree;
});