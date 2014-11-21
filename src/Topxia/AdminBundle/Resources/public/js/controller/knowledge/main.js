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
				otherParam:{
					'gradeId': $('#gradeId').val(),
					'materialId': $('#materialId').val(),
					'subjectId': $('#subjectId').val(),
					'term': $('#term').val()
				}
				//dataFilter: filter
			},
			view: {
				expandSpeed:"",
				//addHoverDom: addHoverDom,
				//removeHoverDom: removeHoverDom,
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
					enable: true
				}
			},
			callback: {
				//beforeRemove: beforeRemove,
				//beforeEditName: beforeEditName,
				onDrop: onDrop
			}
		};

		function addDiyDom(treeId, treeNode) {
		    var html = '<div class="actions">';
		    html += '<button class="btn btn-link btn-sm" id="addBtn_'+treeNode.tId+'"><span class="glyphicon glyphicon-plus"></span> 添加子知识点</button>';
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
		  			var params = '?pid='+treeNode.id+'&tid='+treeNode.tId+'&seq='+seq +'&subjectId='+$('#subjectId').val()+'&materialId='+$('#materialId').val()+'&gradeId='+$('#gradeId').val()+'&term='+$('#term').val();
		  			var newUrl = $('#add-knowledge').data('turl') + params;
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

/*	  		$('#'+ treeId).bind('hover', function(){
	  			$(this).addClass('show').removeClass('hidden');
	  		},function(){
	  			$(this).addClass('hidden').removeClass('show');
	  		});*/
	  		
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

		$.get($('#gradeId').data('url'), {gradeId:$('#gradeId').val()}, function(result){
				$('#subjectId').html(result);
				getNodesData();
		});

		$('#gradeId').on('change', function(){
			$.get($(this).data('url'), {gradeId:$(this).val()}, function(html){
				$('#subjectId').html(html);
				getNodesData();
			});
		});
		$('#subjectId').on('change', function(){
			getNodesData();
		});

		$('#term').on('change', function(){
			getNodesData();
		});
		
		function getNodesData()
		{
			$.get($('.select-section').data('url'), {subjectId:$('#subjectId').val(), gradeId:$('#gradeId').val()}, function(result){
				if('id' in result) {
					$('#materialId').val(result.id);
					$('#material').val(result.name);
				} else {
					$('#materialId').val('');
					$('#material').val('');
				}
				var zTree = $.fn.zTree.getZTreeObj("knowledge-tree");
				zTree.setting.async.otherParam = {
					'gradeId': $('#gradeId').val(),
					'materialId': $('#materialId').val(),
					'subjectId': $('#subjectId').val(),
					'term': $('#term').val()
				};
				zTree.reAsyncChildNodes(null, "refresh");
			});
		}

		$('#add-knowledge-fake').on('click', function(){
			if(!$('#gradeId').val()) {
				Notify.danger('没有选择年级!');
				return;
			}
			if(!$('#subjectId').val()) {
				Notify.danger('没有选择科目!');	
				return;
			} 
  			var params = '?subjectId='+$('#subjectId').val()+'&materialId='+$('#materialId').val()+'&gradeId='+$('#gradeId').val()+'&term='+$('#term').val();
  			var newUrl = $('#add-knowledge').data('turl') + params;
  			$('#add-knowledge').data('url', newUrl);
  			$('#add-knowledge').click();
		});
		$.fn.zTree.init($("#knowledge-tree"), setting);

	}
});