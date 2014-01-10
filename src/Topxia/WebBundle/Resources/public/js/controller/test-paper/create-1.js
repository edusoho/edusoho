define(function(require, exports, module) {

    var AutoComplete = require('autocomplete');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var noUiSlider = require('jquery.nouislider');
    require('jquery.nouislider-css');
    require('jquery.sortable');
    var Uploader = require('upload');
    var EditorFactory = require('common/kindeditor-factory');

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $noUiSlider = $('.noUiSlider').noUiSlider({
            range: [0,100],
            start: [30,70],
            step: 5,
            serialization: {
                resolution: 1
            },
            slide: function(){
                var values = $(".noUiSlider").val();
                $('#value-0').text($('#value-0').data('text')+values['0']+'%');
                $('#value-1').text($('#value-1').data('text')+(values['1']-values['0'])+'%');
                $('#value-2').text($('#value-2').data('text')+(100-values['1'])+'%');
                $("#perventage-1").val(values['0']);
                $("#perventage-2").val(values['1']);
            }
        });

        var $list = $('.test-sort-body').sortable({
            itemSelector: '.type-item',
            handle: '.test-sort-handle',
            serialize: function(parent, children, isContainer) {
                return isContainer ? children : parent.attr('id');
            }
        });

        var validator = new Validator({
            element: '#test-create-form',
            autoSubmit: false,
        });

        validator.addItem({
            element: '#test-name-field',
            required: true,
        });

        validator.addItem({
            element: '#test-description-field',
            required: true,
            rule: 'maxlength{max:500}',
        });

        validator.addItem({
            element: '#test-limitedTime-field',
            required: true,
            rule: 'integer'
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return ;
            }
            var flag = 0;

            $('.item-number:input').each(function(){
                if(isNaN($(this).val())){
                    $(this).focus();
                    Notify.danger('请填写数字');
                    flag = 1;
                    return false;
                }
            });

            if(validator.get('autoSubmit') == false){

                if ($('[name=isDifficulty]').is(':checked')){
                    var isDifficulty = 1;
                }else{
                    var isDifficulty = 0;
                }

                var perventage = $noUiSlider.val();
                var itemCounts = new Object;
                var itemScores = new Object;

                $('.item-number[name^=itemCounts]').each(function(index){
                    itemCounts[$(this).data('key')] = $(this).val();
                });

                $('.item-number[name^=itemScores]').each(function(index){
                    itemScores[$(this).data('key')] = $(this).val() ;
                });

                $.post($('.noUiSlider').data('url'), {isDifficulty: isDifficulty, itemCounts: itemCounts,itemScores: itemScores, perventage:perventage}, function(data) {
                    if (data) {

                        Notify.danger(data);
                        return false;
                    } else {

                        if(flag == 0){
                            validator.set('autoSubmit',true);
                            $('button[type=submit]').trigger('click');
                        }
                    }
                });
            }
        });


        var editor = EditorFactory.create('#test-description-field', 'simple_noimage');
        validator.on('formValidate', function(elemetn, event) {
            editor.sync();
        });


    };

});