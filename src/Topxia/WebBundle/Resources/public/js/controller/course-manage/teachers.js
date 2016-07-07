define(function(require, exports, module) {

    var AutoComplete = require('edusoho.autocomplete');
    var DynamicCollection = require('../widget/dynamic-collection4');
    var Notify = require('common/bootstrap-notify');
    require('jquery.sortable');

    exports.run = function() {

        var dynamicCollection = new DynamicCollection({
            element: '#teachers-form-group',
            onlyAddItemWithModel: true,
            beforeDeleteItem: function(e){
            	var teacherCounts=$("#teacher-list-group").children("li").length;
	            if(teacherCounts <= 1){
	                Notify.danger("课程至少需要一个教师！");
	                return false;
	            }
	            return true;
            }
        });

        var autocomplete = new AutoComplete({
            trigger: '#teacher-input',
            dataSource: $("#teacher-input").data('url'),
            filter: {
                name: 'stringIgnoreCaseMatch',
                options: {
                    key: 'nickname'
                }
            },
            selectFirst: true
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

        dynamicCollection.on('beforeAddItem', function(value) {
            autocomplete.set('inputValue', null);
            autocomplete.setInputValue(value);
        });

        $(".teacher-list-group").sortable({
            'distance':20
        });

        $('#teacher-add').on('click', function(){
            var inputValue = $('#teacher-input').val();
            if(inputValue.length<1 || !autocomplete.items){
            	    return ;
            }
            for(var i=0;i<autocomplete.items.length;i++){
            	 if(inputValue == autocomplete.items[i].textContent){
            	 	autocomplete.set("selectedIndex", i);
			autocomplete.selectItem(i);
			return ;
            	 }
            }
        });

    };

});