define(function(require, exports, module) {

    var Importer = require('edusoho.importer');

    exports.run = function() {

        $id = $('#user-importer-app');
        var importer = new Importer({
            element: "#user-importer-app",
            templateUrl: $id.data('templateUrl'),
            registerMode: $id.data('registerMode'),
            type: 'User'
        });
        /*$("input[type=file]").change(function(){$(this).parents(".uploader").find(".filename").val($(this).val());});
        $("input[type=file]").each(function(){
            if($(this).val()==""){$(this).parents(".uploader").find(".filename").val("");}
        });

        if($("input[name='data']").val()&&$("input[name='data']").val().length<3){
        	$('#start-import-btn').addClass('disabled');
        }

        $('#start-import-btn').on("click",function(){
            $('#start-import-btn').button('submiting').addClass('disabled');
        });*/

    };

});