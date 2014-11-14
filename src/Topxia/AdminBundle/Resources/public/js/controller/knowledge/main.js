define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	var Ztree = require('ztree');
	require('ztree-css');

	exports.run = function() {
		var $tree = $('#knowledge-tree');
		var setting = {
			async: {
				enable: true,
				url:$tree.data('url'),
				autoParam:["id"],
				otherParam:{"categoryId":$tree.data('cid')},
				dataFilter: filter
			},
			view: {expandSpeed:"",
				addHoverDom: addHoverDom,
				removeHoverDom: removeHoverDom,
				selectedMulti: false
			},
			edit: {
				enable: true
			},
			data: {
				simpleData: {
					enable: true
				}
			},
			callback: {
				beforeRemove: beforeRemove,
				beforeEditName: beforeEditName
			}
		};

		function filter(treeId, parentNode, childNodes) {
			if (!childNodes) return null;
			for (var i=0, l=childNodes.length; i<l; i++) {
				childNodes[i].name = childNodes[i].name.replace(/\.n/g, '.');
			}
			return childNodes;
		}
		function beforeRemove(treeId, treeNode) {
			var zTree = $.fn.zTree.getZTreeObj("knowledge-tree");
			zTree.selectNode(treeNode);
			if(confirm("确认删除 节点 -- " + treeNode.name + " 吗？")) {
				var flag = false;
				$.ajax({ 
					type: 'POST', 
					url: $tree.data('durl'), 
					data: {id:treeNode.id}, 
					success: function(result){
                    	Notify.success('删除知识点成功！');
                    	flag = true;
					}, 
					error:function() {
                    	Notify.danger("删除知识点失败！");
                    	flag = false;
                	},
					async:false 
				});
				return flag;
			} else {
				return false;
			}
		}		
		function beforeEditName(treeId, treeNode, newName) {
			var pid = treeNode.parentId,
				cid = treeNode.categoryId,
				id = treeNode.id,
				newUrl = $('#edit-knowledge').data('turl')+'?id='+id+'&pid='+pid+'&cid='+cid+'&tid='+treeNode.tId;

			$('#edit-knowledge').data('url', newUrl);
			$('#edit-knowledge').click();
			return false;
		}

		function addHoverDom(treeId, treeNode) {
			var sObj = $("#" + treeNode.tId + "_span");
			if (treeNode.editNameFlag || $("#addBtn_"+treeNode.tId).length>0) return;
			var addStr = "<span class='button add' id='addBtn_" + treeNode.tId
				+ "' title='add node' onfocus='this.blur();'></span>";
			sObj.after(addStr);
			var btn = $("#addBtn_"+treeNode.tId);
			if (btn) btn.bind("click", function(){
				var zTree = $.fn.zTree.getZTreeObj("knowledge-tree");
				var newUrl = $('#add-knowledge').data('turl')+'?pid='+treeNode.id+'&tid='+treeNode.tId;
				$('#add-knowledge').data('url', newUrl);
				$('#add-knowledge').click();
				return false;
			});
		};
		function removeHoverDom(treeId, treeNode) {
			$("#addBtn_"+treeNode.tId).unbind().remove();
		};

		$.fn.zTree.init($("#knowledge-tree"), setting);

	}
});