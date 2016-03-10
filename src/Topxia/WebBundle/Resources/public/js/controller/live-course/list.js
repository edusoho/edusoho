define(function(require, exports, module) {

	exports.run = function() {
        $('#categoryId').change(function(){
            $(this).closest('form').submit();
        });

        $('#vipCategoryId').change(function(){
            $(this).closest('form').submit();
        });
	};

});