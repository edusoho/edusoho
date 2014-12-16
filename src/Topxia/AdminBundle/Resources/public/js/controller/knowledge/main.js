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
				otherParam:{"categoryId":$tree.data('cid')}
				//dataFilter: filter
			},
			view: {
				expandSpeed:"",
				// addHoverDom: addHoverDom,
				// removeHoverDom: removeHoverDom,
				selectedMulti: false,
				showLine: false,
				showIcon: false,
				addDiyDom: addDiyDom
			},
			edit: {
				enable: true,
				showRemoveBtn: false,
				showRenameBtn: false
			},
			data: {
				simpleData: {
					enable: true,
					idkey: "id",
					pidKey: "pid"
				}
			},
			callback: {
				onAsyncSuccess: onAsyncSuccess,
				onDrop: onDrop
			}
		};

		function onAsyncSuccess(event, treeId, treeNode, msg) {
			if(typeof(treeNode) == "undefined" ) {
				var zTree = $.fn.zTree.getZTreeObj(treeId);
				var nodes = zTree.getNodes();
				for(var i=0; i< nodes.length;i++) {
					zTree.expandNode(nodes[i],true, false, false);
				}
			}
		}
		
		function addDiyDom(treeId, treeNode) {
		    var html = '<div class="actions ">';
		    html += '<button class="btn btn-link btn-sm" id="addBtn_'+treeNode.tId+'"><span class="glyphicon glyphicon-plus"></span> 添加子节点</button>';
		    html += '<button class="btn btn-link btn-sm" id="editBtn_'+treeNode.tId+'"><span class="glyphicon glyphicon-edit"></span> 编辑</button>';
		    html += '<button class="btn btn-link btn-sm" id="removeBtn_'+treeNode.tId+'"><span class="glyphicon glyphicon-remove-circle"> 删除</span></button>';
		    html += '</div>';
		  	$('#' + treeNode.tId + '_a').after(html);
		  	var addBtn = $("#addBtn_"+treeNode.tId),
		  		editBtn = $("#editBtn_"+treeNode.tId),
		  		removeBtn = $("#removeBtn_"+treeNode.tId);
		  	if(addBtn) {
		  		addBtn.bind("click", function(){
		  			var zTree = $.fn.zTree.getZTreeObj("knowledge-tree");
		  			var seq = treeNode.children ? treeNode.children.length+1:1;
		  			var newUrl = $('#add-knowledge').data('turl')+'?pid='+treeNode.id+'&tid='+treeNode.tId+'&seq='+seq;
		  			$('#add-knowledge').data('url', newUrl);
		  			$('#add-knowledge').click();
		  		});
		  	}

		  	if(editBtn) {
		  		editBtn.bind("click", function(){
		  			var pid = treeNode.parentId,
		  				cid = treeNode.categoryId,
		  				id = treeNode.id,
		  				newUrl = $('#edit-knowledge').data('turl')+'?id='+id+'&pid='+pid+'&cid='+cid+'&tid='+treeNode.tId;

		  			$('#edit-knowledge').data('url', newUrl);
		  			$('#edit-knowledge').click();
		  		});
		  	}
		  	if(removeBtn) {
		  		removeBtn.bind("click", function(){
					var zTree = $.fn.zTree.getZTreeObj("knowledge-tree");
					zTree.selectNode(treeNode);
					if(confirm("确认删除 节点 -- " + treeNode.name + " 吗？")) {
						$.ajax({ 
							type: 'POST', 
							url: $tree.data('durl'), 
							data: {id:treeNode.id}, 
							success: function(result){
		                    	Notify.success('删除知识点成功！');
		                    	zTree.removeNode(treeNode);
							}, 
							error:function() {
		                    	Notify.danger("删除知识点失败！");
		                	},
							async:false 
						});
					} else {
						return false;
					}
		  		});
	  		}
	  		
		};

	

		function onDrop(event, treeId, treeNodes, targetNode, moveType, isCopy) {
			var pid = treeNodes[0].pId,
				id = treeNodes[0].id,
				zTree = $.fn.zTree.getZTreeObj(treeId),
				parentNode = treeNodes[0].getParentNode(),
				childNodes = parentNode ? parentNode.children :  zTree.getNodesByParam('pId', null);
			var seq = [];
			for (var i = 0; i <= childNodes.length - 1; i++) {
				seq[i] =  childNodes[i].id;
			};

			$.ajax({ 
					type: 'POST', 
					url: $tree.data('surl'), 
					data: {id:id, pid:pid, seq:seq}, 
					success: function(result){
						treeNodes[0].parentId = pid;
					}, 
					error:function() {
                	},
					async:true 
				});
			
		}

		function addHoverDom(treeId, treeNode) {
			var sObj = $("#" + treeNode.tId + "_span");
			if (treeNode.editNameFlag || $("#addBtn_"+treeNode.tId).length>0) return;
			var addStr = "<span class='button add' id='addBtn_" + treeNode.tId
				+ "' title='增加知识点' onfocus='this.blur();'></span>";
			sObj.after(addStr);
			var btn = $("#addBtn_"+treeNode.tId);
			if (btn) btn.bind("click", function(){
				var zTree = $.fn.zTree.getZTreeObj("knowledge-tree");
				var seq = treeNode.children ? treeNode.children.length+1:1;
				var newUrl = $('#add-knowledge').data('turl')+'?pid='+treeNode.id+'&tid='+treeNode.tId+'&seq='+seq;
				$('#add-knowledge').data('url', newUrl);
				$('#add-knowledge').click();
				return false;
			});
		};
		function removeHoverDom(treeId, treeNode) {
			// $("#addBtn_"+treeNode.tId).unbind().remove();
			$('.actions').html('');
		};

		$.fn.zTree.init($("#knowledge-tree"), setting);


	}
});