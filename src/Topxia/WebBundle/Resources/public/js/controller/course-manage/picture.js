define(function(require, exports, module) {
    require("jquery.jcrop");
    require('jquery.form');
    var Notify = require('common/bootstrap-notify');
    exports.run = function() {
      	require('./header').run();

        function updateCoords(c) {
            $('#x').val(c.x);
            $('#y').val(c.y);
            $('#w').val(c.w);
            $('#h').val(c.h);
        };

        function resetCrop() {
           $("#pic2crop").Jcrop({
                setSelect: [ 100, 100, 300, 200 ],
                onSelect: updateCoords
            });
        };

        $("#upload-picture-btn").on('click', function(e){
            e.preventDefault();
            $form = $("#course-picture-form"); 

            $form.ajaxSubmit({
                clearForm: true,
                success: function(data){
                    $("#crop-with-new-window").remove();
                    $form.remove();
                    $(".panel-body").append(data.html);
                    resetCrop();
                }
            });
            
        });

       
    };
  
});