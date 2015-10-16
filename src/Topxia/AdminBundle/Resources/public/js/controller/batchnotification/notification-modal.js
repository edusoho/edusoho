define(function(require, exports, module) {

    require('ckeditor');

    var Validator = require('bootstrap.validator');
    var Uploader = require('upload');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    exports.run = function() {

        var $form = $("#notification-form");
        $modal = $form.parents('.modal');

        var validator = _initValidator($form, $modal);
        console.log(validator);
        var $editor = _initEditorFields($form, validator);
    };
    function _initEditorFields($form, validator) {

        // group: 'default'
        CKEDITOR.replace('richeditor-body-field', {
            toolbar: 'Full',
            filebrowserImageUploadUrl: $('#richeditor-body-field').data('imageUploadUrl'),
            filebrowserFlashUploadUrl: $('#richeditor-body-field').data('flashUploadUrl'),
            height: 300
        });
    }
    function _initValidator($form, $modal) {
        var validator = new Validator({
            element: '#notification-form',
            failSilently: true,
            triggerType: 'change',
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                CKupdate();
                $('#notification-operate-save').button('loading').addClass('disabled');
                Notify.success('保存成功！');
            }
        });

        validator.addItem({
            element: '[name=title]',
            rule: 'maxlength{max:50}',
            required: true
        });

        validator.addItem({
            element: '[name=content]',
            required: true
        });
        return validator;
    }
    function CKupdate() {
    for ( instance in CKEDITOR.instances ) {
        CKEDITOR.instances[instance].updateElement(); 
    }
}
    
});