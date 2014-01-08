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

        $('#test-reset-form').submit( function () {

            var flag = 0;
            $('.item-number:input').each(function(){
                if(isNaN($(this).val())){
                    $(this).focus();
                    Notify.warning('请填写数字');
                    flag = 1;
                    return false;
                }
            });

            if(flag == 1){
                return false;
            }


            var itemCounts = new Array();
            var itemScores = new Array();

            var perventage = $('#test-percentage-field').val();

            if ($('[name=isDiffculty]').is(':checked')){
                var isDiffculty = 1;
            }else{
                var isDiffculty = 0;
            }

            $('.item-number[name^=itemCounts]').each(function(index){
                var item = new Array($(this).data('key'),$(this).val());
                itemCounts.push(item);
            });

            $('.item-number[name^=itemScores]').each(function(index){
                var item = new Array($(this).data('key'),$(this).val());
                itemScores.push(item);
            });

            $.ajaxSetup({
                async : false
            });

            $.post($('#test-percentage-field').data('url'), {isDiffculty: isDiffculty, itemCounts: itemCounts,itemScores: itemScores, perventage:perventage}, function(arr) {
                if (arr) {
                    Notify.warning(arr);
                    flag = 1;
                }
            });

            if(flag == 1){
                return false;
            }

            return true;

        });

    };

});