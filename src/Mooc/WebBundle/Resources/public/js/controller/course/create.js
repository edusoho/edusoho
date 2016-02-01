define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var WebUploader = require('edusoho.webuploader');
    require('common/validator-rules').inject(Validator);
    require('ckeditor');

    exports.run = function() {

        if($("#course-create-form").length>0) {

            var validator = new Validator({
                element: '#course-create-form',
                triggerType: 'change',
                onFormValidated: function(error){
                    if (error) {
                        return false;
                    }
                    $('#course-create-btn').button('submiting').addClass('disabled');
                }
            });

            validator.addItem({
                element: '[name="title"]',
                required: true
            });
        
        }


        // var uploader = new WebUploader({
        //     element: '#upload-picture-btn'
        // });

        // uploader.on('uploadSuccess', function(file, response ) {
        //     var url = $("#upload-picture-btn").data("gotoUrl");
        //     Notify.success('上传成功！', 1);
        //     document.location.href = url;
        // });

        // $('.use-partner-avatar').on('click', function(){
        //     var $this = $(this);
        //     var goto = $this.data('goto');

        //     $.post($this.data('url'), {imgUrl:$this.data('imgUrl')},function(){
        //         window.location.href = goto;
        //     });
        // });



        var editor = CKEDITOR.replace('profile_about', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#profile_about').data('imageUploadUrl')
        });

        var validator = new Validator({
            element: '#user-profile-form',
            failSilently: true,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#profile-save-btn').button('submiting').addClass('disabled');
            }
        });




    };

});