define(function(require, exports, module) {

    var noUiSlider = require('jquery.nouislider');
    require('jquery.nouislider-css');
    require('jquery.sortable');

    var Notify = require('common/bootstrap-notify');

    var CreateBase = {

        initDifficultyPercentageSlider: function($form) {
            return $form.find('.difficulty-percentage-slider').noUiSlider({
                range: [0,100],
                start: [30,70],
                step: 5,
                serialization: {
                    resolution: 1
                },
                slide: function() {
                    this.trigger('change');
                }
            }).change(function(){
                var values = $(this).val();

                var simplePercentage = values[0],
                    normalPercentage = values[1] - values[0],
                    difficultyPercentage = 100 - values[1];

                $form.find('.simple-percentage-text').html('简单' + simplePercentage + '%');
                $form.find('.normal-percentage-text').html('一般' + normalPercentage + '%');
                $form.find('.difficulty-percentage-text').html('困难' + difficultyPercentage + '%');

                $form.find('input[name="percentages[simple]"]').val(simplePercentage);
                $form.find('input[name="percentages[normal]"]').val(normalPercentage);
                $form.find('input[name="percentages[difficulty]"]').val(difficultyPercentage);

            });
        },

        initModeField: function(){
            var $form = $('#testpaper-form');
            var $slider = this.initDifficultyPercentageSlider($form);
             $form.find('[name=mode]').on('click', function(){
                if ($(this).val() == 'difficulty') {
                    $form.find('.difficulty-form-group').removeClass('hidden');
                    $slider.change();
                } else {
                    $form.find('.difficulty-form-group').addClass('hidden');
                }
            });
        },

        initRangeField: function() {
            var self = this;
            $('input[name=range]').on('click', function() {
                if ($(this).val() == 'lesson') {
                    $("#test-range-selects").show();
                } else {
                    $("#test-range-selects").hide();
                }

                self._refreshRangesValue();
            });

            $("#test-range-start").change(function() {
                var startIndex = self._getRangeStartIndex();

                self._resetRangeEndOptions(startIndex);

                self._refreshRangesValue();
            });

            $("#test-range-end").change(function() {
                self._refreshRangesValue();
            });

        },

        _resetRangeEndOptions: function(startIndex) {
            if (startIndex > 0) {
                startIndex--;
                var $options = $("#test-range-start option:gt(" + startIndex + ")");
            } else {
                var $options = $("#test-range-start option");
            }

            var selected = $("#test-range-end option:selected").val();

            $("#test-range-end option").remove();
            $("#test-range-end").html($options.clone());
            $("#test-range-end option").each(function(){
                if ($(this).val() == selected) {
                    $("#test-range-end").val(selected);
                }
            });
        },

        _refreshRangesValue: function () {
            var $ranges = $('input[name=ranges]');
            if ($('input[name=range]:checked').val() != 'lesson') {
                $ranges.val('');
                return ;
            }

            var startIndex = this._getRangeStartIndex();
            var endIndex = this._getRangeEndIndex();

            if (startIndex < 0 || endIndex < 0) {
                $ranges.val('');
                return ;
            }

            var values = [];
            for (var i=startIndex; i<=endIndex; i++) {
                values.push( $("#test-range-start option:eq(" + i + ")").val());
            }

            $ranges.val(values.join(','));
        },

        _getRangeStartIndex: function() {
            var $startOption = $("#test-range-start option:selected");
            return parseInt($("#test-range-start option").index($startOption));
        },

        _getRangeEndIndex: function() {
            var selected = $("#test-range-end option:selected").val();
            if (selected == '') {
                return -1;
            }

            var index = -1;
            $("#test-range-start option").each(function(i, item) {
                if ($(this).val() == selected) {
                    index = i;
                }
            });

            return index;
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
            return true;
            var flag = 0;

            var isDifficulty = $('input[name="isDifficulty"]:checked').val();

            var perventage = CreateBase.noUiSlider.val();

            var itemCounts = new Object;
            var itemScores = new Object;

            var allCountZero = true;
            $('.item-number[name^=count]').each(function(index){
                var count = parseInt($(this).val());
                if (count > 0) {
                    allCountZero = false;
                }
                itemCounts[$(this).data('key')] = count;
            });

            if (allCountZero) {
                flag = 1;
                Notify.danger('题目数量不能全为0');
            }

            $('.item-number[name^=score]').each(function(index){
                itemScores[$(this).data('key')] = $(this).val() ;
            });

            var ranges = $('input[name="ranges"]').val();

            $.ajaxSetup({
                async : false
            });    

            $.post($('.noUiSlider').data('url'), {isDifficulty: isDifficulty, itemCounts: itemCounts,itemScores: itemScores, perventage:perventage, ranges:ranges}, function(data) {
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