define(function(require, exports, module) {

    var noUiSlider = require('jquery.nouislider');
    require('jquery.nouislider-css');
    require('jquery.sortable');

    var Notify = require('common/bootstrap-notify');

    var CreateBase = {

        noUiSlider:null,

        initNoUiSlider: function(){
            CreateBase.noUiSlider = $('.noUiSlider').noUiSlider({
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
        },

        isDifficulty: function(){
            $('[name=isDifficulty]').on('click', function(){
                var val = $('input[name="isDifficulty"]:checked').val();
                if(val == 0){
                    $('.difficulty-html').addClass('hidden');
                }else{
                    $('.difficulty-html').removeClass('hidden');
                }
            });
        },

        sortable: function(){
            var $list = $('.test-sort-body').sortable({
            itemSelector: '.type-item',
            handle: '.test-sort-handle',
            serialize: function(parent, children, isContainer) {
                    return isContainer ? children : parent.attr('id');
                }
            });
        },

        getCheckResult : function(){
            var flag = 0;

            var isDifficulty = $('input[name="isDifficulty"]:checked').val();

            var perventage = CreateBase.noUiSlider.val();

            var itemCounts = new Object;
            var itemScores = new Object;

            $('.item-number[name^=itemCounts]').each(function(index){
                itemCounts[$(this).data('key')] = $(this).val();
            });

            $('.item-number[name^=itemScores]').each(function(index){
                itemScores[$(this).data('key')] = $(this).val() ;
            });

            $.ajaxSetup({
                async : false
            });    

            $.post($('.noUiSlider').data('url'), {isDifficulty: isDifficulty, itemCounts: itemCounts,itemScores: itemScores, perventage:perventage}, function(data) {
                if (data) {
                    Notify.danger(data);
                    flag = 1 ;
                }
            });

            return flag == 0 ? true : false ;
        },

        checkIsNum : function(){
            var flag = 0;
            $('.item-number:input').each(function(){
                if(isNaN($(this).val())){
                    $(this).focus();
                    Notify.danger('请填写数字');
                    flag = 1;
                    return false;
                }
            });
            
            return flag == 0 ? true : false ;
        },

        initValidator: function(validator){
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
        }
    }

    module.exports = CreateBase;

});