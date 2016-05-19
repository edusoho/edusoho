define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');

    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    require('es-ckeditor');
   var SelectZtree = require('edusoho.selectztree');

    exports.run = function() {
        var selectTree = new SelectZtree({
            ztreeDom: '#orgZtree',
            clickDom: "#orgName",
            valueDom: "#orgCode"
        });
        /*        var editor_classroom = CKEDITOR.replace('description', {
                    toolbar: 'Detail',
                    filebrowserImageUploadUrl: $('#description').data('imageUploadUrl'),
                    filebrowserFlashUploadUrl: $('#description').data('flashUploadUrl')
                });*/

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