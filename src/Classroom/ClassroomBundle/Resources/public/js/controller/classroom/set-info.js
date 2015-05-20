define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');

    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    require('ckeditor');


    exports.run = function() {

        var editor_classroom = CKEDITOR.replace('description', {
            toolbar: 'Detail',
            filebrowserImageUploadUrl: $('#description').data('imageUploadUrl'),
            filebrowserFlashUploadUrl: $('#description').data('flashUploadUrl')
        });

        var validator = new Validator({
            element: '#classroom-set-form',
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#classroom-save').button('submiting').addClass('disabled');
            }
        });

        validator.addItem({
            element: '[name="title"]',
            required: true
        });

        validator.on('formValidate', function(elemetn, event) {
            editor_classroom.updateElement();
        });
        
        $("#publishSure").on("click",function(){

            $('#publishSure').button('submiting').addClass('disabled');

            $.post($("#publishSure").data("url"), function(html) {

                    $("#modal").modal('hide');
                    window.location.reload();

                }).error(function(){
            });
        });
    };

});