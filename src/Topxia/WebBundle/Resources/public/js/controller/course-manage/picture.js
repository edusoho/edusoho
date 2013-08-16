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
            // Notify.info("上传之后请如发现图片并未改变，请重新页面！")
        };

        function resetCrop() {
           $("#pic2crop").Jcrop({
                onSelect: updateCoords
            });
        };

        $("#upload-picture-btn").on('click', function(e){
            e.preventDefault();
            $form = $("#course-picture-form"); 

            $form.ajaxSubmit({
                clearForm: true,
                success: function(data){
                    Notify.success("课程图片上传成功！");
                    $("#crop-with-new-window").remove();
                    $form.remove();
                    $(".panel-body").append(data.html);
                    resetCrop();
                }
            });
            
        });

       
    };
  
});