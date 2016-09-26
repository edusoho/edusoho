define(function(require, exports, module) {

    require("jquery.select2");
    require("jquery.select2-css");

    exports.run = function(options) {
        $('[data-role="tree-select"], [name="categoryId"]').select2({
            treeview: true,
            dropdownAutoWidth: true,
            treeviewInitState: 'collapsed'
            // treeviewInitState: 'expanded'
        });
        
        /*workaround to fix org drop down auto width too short issue*/
    	var updateWidth = function(){
    		var dropDown = $('.select2-drop-auto-width.select2-drop-active').last();
    		var width = 0;
	        $(dropDown).find('ul li').each(function() {
	           var thisWidth = 5 + $(this).attr('data-indent-count') * 15 + $(this).find('.select2-result-text').text().length*30;
	             width = width > thisWidth ? width : thisWidth;
	        });
	        $(dropDown).css("width",width); 
        };
        
        $('[data-role="tree-select"]').on('select2-open', function(){
            updateWidth();
            $( window ).scroll(function() {
        		updateWidth();
            })
        });
        
        $('[data-role="tree-select"]').on('select2-close', function(){
        	$(window).unbind('scroll');
        });
    };

});
