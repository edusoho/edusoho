define(function(require, exports, module) {
    require("jquery.jcrop-css");
    require("jquery.jcrop");
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var EditorFactory = require('common/kindeditor-factory');

    exports.run = function() {

        if($('#group_about').length>0){
            var editor = EditorFactory.create('#group_about', 'simpleHaveEmoticons', {extraFileUploadParams:{group:'user'}});
            var validator = new Validator({
            element: '#user-group-form',
            failSilently: true,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#group-save-btn').button('submiting').addClass('disabled');
            }
        });
        
        validator.addItem({
            element: '[name="group[grouptitle]"]',
            required: true,
            rule: 'minlength{min:2} maxlength{max:12}',
            errormessageUrl: '长度为2-12位'
           
            
        });

       }
        var $form = $("#avatar-crop-form"),
            $picture = $("#avatar-crop");

        var scaledWidth = $picture.attr('width'),
            scaledHeight = $picture.attr('height'),
            naturalWidth = $picture.data('naturalWidth'),
            naturalHeight = $picture.data('naturalHeight'),
            cropedWidth = 220,
            cropedHeight = 220,
            ratio = cropedWidth / cropedHeight,
            selectWidth = 200 * (naturalWidth/scaledWidth),
            selectHeight = 200 * (naturalHeight/scaledHeight);

        $picture.Jcrop({
            trueSize: [naturalWidth, naturalHeight],
            setSelect: [0, 0, selectWidth, selectHeight],
            aspectRatio: ratio,
            onSelect: function(c) {
                $form.find('[name=x]').val(c.x);
                $form.find('[name=y]').val(c.y);
                $form.find('[name=width]').val(c.w);
                $form.find('[name=height]').val(c.h);
            }
        });

    };
  
});