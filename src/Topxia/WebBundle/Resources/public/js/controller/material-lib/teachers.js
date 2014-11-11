define(function(require, exports, module) {
    var AutoComplete = require('autocomplete');
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');

    require('common/validator-rules').inject(Validator);

    exports.run = function() {

//    	require('./header').run();
    	
	    var $modal = $('#share-materials-form').parents('.modal');
	    
	    var autocomplete = new AutoComplete({
	        trigger: '#teacher-input',
	        dataSource: $("#teacher-input").data('url'),
	        filter: {
	            name: 'stringMatch',
	            options: {
	                key: 'nickname'
	            			}
	        		},
            selectFirst: true
	    }).render();

	   autocomplete.on('itemSelect', function(data){
//		   var error = '';
//	    	dynamicCollection.element.find('input[name="ids[]"]').each(function(i, item) {
//	    		if (parseInt(data.id) == parseInt($(item).val())) {
//	    			error = '该教师已添加，不能重复添加！';
//	    		}
//	    	});*/

//	    	if (error) {
//	    		Notify.danger(error);
	    		//dynamicCollection.clearInput();
//	    	} else {
//	    		alert("Teacher added.");
		    	addTeacher(data.id, data.nickname);
//	    	}
		});

       /* dynamicCollection.on('beforeAddItem', function(value) {
            autocomplete.set('inputValue', null);
            autocomplete.setInputValue(value);
        });*/

		/*$(".teacher-list-group").sortable({
			'distance':20
		});*/

	   var validator = new Validator({
	            element: '#share-materials-form',
	            autoSubmit: false,
	            onFormValidated: function(error, results, $form) {
	                if (error) {
	                    return false;
	                }

	                var $btn = $("#share-materials-form-submit");
	                $btn.button('submiting').addClass('disabled');
	                
	                $.post($form.attr('action'), {targetUserIds: $("#target-teachers-input").select2("val")}, function(html) {
	                		Notify.success('素材分享成功!');
	                    $modal.modal('hide');
	                }).error(function(){
	                    Notify.danger('素材分享失败!');
	                    $btn.button('reset').removeClass('disabled');
	                });

	            }
	        });

    };

});