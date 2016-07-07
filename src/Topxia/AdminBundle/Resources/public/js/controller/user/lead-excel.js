define(function(require, exports, module) {

    exports.run = function() {

        $("input[type=file]").change(function(){$(this).parents(".uploader").find(".filename").val($(this).val());});
        $("input[type=file]").each(function(){
            if($(this).val()==""){$(this).parents(".uploader").find(".filename").val("");}
        });

        if($("input[name='data']").val()&&$("input[name='data']").val().length<3){
        	$('#start-import-btn').addClass('disabled');
        }

        $('#start-import-btn').on("click",function(){
            $('#start-import-btn').button('submiting').addClass('disabled');
        });
    };

});