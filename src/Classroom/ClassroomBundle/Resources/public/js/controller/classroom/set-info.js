define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var SelectTree = require('edusoho.selecttree');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);
    require('es-ckeditor');
    exports.run = function() {
        if ($("#orgSelectTree").val()) {
            var selectTree = new SelectTree({
                element: "#orgSelectTree",
                name: 'orgCode'
            });
        }

        var editor_classroom_about = CKEDITOR.replace('about', {
            allowedContent: true,
            toolbar: 'Detail',
            filebrowserImageUploadUrl: $('#about').data('imageUploadUrl'),
            filebrowserFlashUploadUrl: $('#about').data('flashUploadUrl')
        });

        var validator = new Validator({
            element: '#classroom-set-form',
            onFormValidated: function(error) {
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


    };

});