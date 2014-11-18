define(function(require, exports, module){
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    exports.run = function(){
        var $form = $("#courseware-form");
        $modal = $form.parents('.modal');
        var validator = _initValidator($form, $modal);

        function _initValidator($form, $modal) {
                var validator = new Validator({
                element: '#courseware-form',
	            failSilently: true,
	            triggerType: 'change',
	            onFormValidated: function(error, results, $form) {
	                if (error) {
	                    return false;
	                }
	                $('#courseware-create-btn').button('loading').addClass('disabled');
	                // Notify.success('操作成功！');

                    $.post($form.attr('action'), $form.serialize(), function(result){
	                    // $modal.modal('hide');
	                });
	            }
	        });

	        validator.addItem({
	            element: '[name=url]',
	            required: true
	        });

	        validator.addItem({
	            element: '[name=mainKnowledgeId]',
	            required: true
	        });

	        validator.addItem({
	            element: '[name=releatedKnowledgeIds]',
	            required: true
	        });   

	        validator.addItem({
	            element: '[name=tagIds]',
	            required: true
	        });

	        validator.addItem({
	            element: '[name=source]',
	            required: true
	        });

	        return validator;
	    }
    }
});