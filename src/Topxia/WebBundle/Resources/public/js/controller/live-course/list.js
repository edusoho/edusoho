define(function(require, exports, module) {

	exports.run = function() {
        $('#categoryId').change(function(){
            $(this).closest('form').submit();
        });

        $('#vipCategoryId').change(function(){
             $("#categoryId").find("option[text='课程分类']").attr("selected",true);
            $(this).closest('form').submit();
        });
	};

});