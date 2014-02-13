define(function(require, exports, module) {

    var Widget     = require('widget');
    var Handlebars = require('handlebars');
    var Notify = require('common/bootstrap-notify');

    require('jquery.nouislider');
    require('jquery.nouislider-css');
    require('jquery.sortable');

    var TestpaperEditor = Widget.extend({

        attrs:{

        },

        events: {
            'click [name=mode]': 'onClickModeField',
        },

        setup:function() {
            this.initDifficultyPercentageSlider();
            //@todo, refact it, wellming.
            this.initRangeField();

            this.initQuestionTypeSortable();

        },

        onClickModeField: function(e) {
           if ($(e.currentTarget).val() == 'difficulty') {
                this.$('.difficulty-form-group').removeClass('hidden');
                this.$('.difficulty-percentage-slider').change();
            } else {
                this.$('.difficulty-form-group').addClass('hidden');
            }
        },

        initQuestionTypeSortable: function() {
            var $list = $('#testpaper-question-options').sortable({
                itemSelector: '.testpaper-question-option-item',
                handle: '.testpaper-question-option-item-sort-handler',
                serialize: function(parent, children, isContainer) {
                    return isContainer ? children : parent.attr('id');
                }
            });
        },

        initDifficultyPercentageSlider: function() {
            var self = this;
            return self.$('.difficulty-percentage-slider').noUiSlider({
                range: [0, 100],
                start: [30, 70],
                step: 5,
                serialization: {
                    resolution: 1
                },
                slide: function() {
                    this.trigger('change');
                }
            }).change(function() {
                var values = $(this).val();

                var simplePercentage = values[0],
                    normalPercentage = values[1] - values[0],
                    difficultyPercentage = 100 - values[1];

                self.$('.simple-percentage-text').html('简单' + simplePercentage + '%');
                self.$('.normal-percentage-text').html('一般' + normalPercentage + '%');
                self.$('.difficulty-percentage-text').html('困难' + difficultyPercentage + '%');

                self.$('input[name="percentages[simple]"]').val(simplePercentage);
                self.$('input[name="percentages[normal]"]').val(normalPercentage);
                self.$('input[name="percentages[difficulty]"]').val(difficultyPercentage);

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
            $("#test-range-end option").each(function() {
                if ($(this).val() == selected) {
                    $("#test-range-end").val(selected);
                }
            });
        },

        _refreshRangesValue: function() {
            var $ranges = $('input[name=ranges]');
            if ($('input[name=range]:checked').val() != 'lesson') {
                $ranges.val('');
                return;
            }

            var startIndex = this._getRangeStartIndex();
            var endIndex = this._getRangeEndIndex();

            if (startIndex < 0 || endIndex < 0) {
                $ranges.val('');
                return;
            }

            var values = [];
            for (var i = startIndex; i <= endIndex; i++) {
                values.push($("#test-range-start option:eq(" + i + ")").val());
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




    });

    exports.run = function() {
        new TestpaperEditor({
            element: '#testpaper-form'
        });
    }

});