define(function(require, exports, module) {
	var ClassChooser = require('../class/class-chooser');
    exports.run = function() {
    	var $form=$('#user-import-form');
        $("input[type=file]").change(function(){$(this).parents(".uploader").find(".filename").val($(this).val());});
        $("input[type=file]").each(function(){
            if($(this).val()==""){$(this).parents(".uploader").find(".filename").val("");}
        });
        $('#start-import-btn').on("click",function(){
            $('#start-import-btn').button('submiting').addClass('disabled');
        });

        //调用
        if($form.find('#className').length > 0) {
            var classChooser = new ClassChooser({
                element:'#className',
                modalTarget:$('#modal'),
                url:$form.find('#className').data().url
            });
            
            classChooser.on('choosed',function(id,name){
                $form.find('#classId').val(id);
                $form.find('#className').val(name);
            });
        }
     
    };

});