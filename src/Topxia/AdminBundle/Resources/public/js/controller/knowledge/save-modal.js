define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	require('common/validator-rules').inject(Validator);
    require('webuploader');
    
	exports.run = function() {
        var $form = $('#knowledge-form');
		var $modal = $form.parents('.modal');
        var $list = $('.knowledge-list');

		var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                $('#knowledge-create-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(result){
                    $modal.modal('hide');
                    var zTree = $.fn.zTree.getZTreeObj("knowledge-tree");
                    var node = result.tid ? zTree.getNodeByTId(result.tid) : null;
                    if(result.type) {
                        node.name = result.knowledge.name;
                        node.id = result.knowledge.id;
                        zTree.updateNode(node);
                    } else {
                        zTree.addNodes(node,  {id:(result.knowledge.id), pId:result.knowledge.parentId, name:result.knowledge.name});
                    }
                    Notify.success('保存知识点成功！');
				}).fail(function() {
                    Notify.danger("保存知识点失败，请重试！");
                });

            }
        });

        validator.addItem({
            element: '#knowledge-name-field',
            required: true,
            rule: 'maxlength{max:100}'
        });

	};

});