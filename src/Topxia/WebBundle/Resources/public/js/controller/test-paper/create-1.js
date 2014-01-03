define(function(require, exports, module) {

    var AutoComplete = require('autocomplete');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('jquery.sortable');

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

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
                    Notify.warning('请填写数字');
                    flag = 1;
                    return false;
                }
            });

            if(validator.get('autoSubmit') == false){

                if ($('[name=isDiffculty]').is(':checked')){
                    var isDiffculty = 1;
                }else{
                    var isDiffculty = 0;
                }

                var perventage = $('#test-percentage-field').val();
                var itemCounts = new Array();
                var itemScores = new Array();

                $('.item-number[name^=itemCounts]').each(function(index){
                    var item = new Array($(this).data('key'),$(this).val());
                    itemCounts.push(item);
                });

                $('.item-number[name^=itemScores]').each(function(index){
                    var item = new Array($(this).data('key'),$(this).val());
                    itemScores.push(item);
                });

                $.post($('#test-percentage-field').data('url'), {isDiffculty: isDiffculty, itemCounts: itemCounts,itemScores: itemScores, perventage:perventage}, function(data) {
                    if (data) {

                        Notify.warning(data);
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


    };

});