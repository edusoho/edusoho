define(function(require, exports, module) {

    var AutoComplete = require('autocomplete');
    var DynamicCollection = require('../widget/dynamic-collection2');
    var Notify = require('common/bootstrap-notify');
    require('jquery.sortable');

    exports.run = function() {

        var dynamicCollection = new DynamicCollection({
            element: '#teachers-control-group',
            onlyAddItemWithModel: true
        });

        dynamicCollection.on('beforeAddItem', function(value) {
        	if (value.length > 0) {
        		Notify.danger('该教师不存在！');
        	}

        });

	    var autocomplete = new AutoComplete({
	        trigger: '#teacher-input',
	        dataSource: $("#teacher-input").data('url'),
	        filter: {
	            name: 'stringMatch',
	            options: {
	                key: 'nickname'
	            }
	        },
	    }).render();

	    autocomplete.on('itemSelect', function(data){
	    	var error = '';
	    	dynamicCollection.element.find('input[name="ids[]"]').each(function(i, item) {
	    		if (parseInt(data.id) == parseInt($(item).val())) {
	    			error = '该教师已添加，不能重复添加！';
	    		}
	    	});

	    	if (error) {
	    		Notify.danger(error);
	    		dynamicCollection.clearInput();
	    	} else {
		    	dynamicCollection.addItemWithModel(data);
	    	}
		});

		$(".teacher-list-group").sortable();


    };

});