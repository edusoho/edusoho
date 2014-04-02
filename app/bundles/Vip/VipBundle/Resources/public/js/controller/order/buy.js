define(function(require, exports, module) {

    var moment = require('moment');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $form = $("#member-buy-form");
        var $modal = $("#member-buy-confirm-modal");
        $modal.modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                var type = $form.find('[name=type]').val();
                if (type == 'renew') {
                    var levelName = $form.find('.level-name').text();
                } else {
                    var levelName = $form.find('[name=level]:checked').parent().text();
                }

                $modal.find('.confirm-level').html(levelName);
                $modal.find('.confirm-amount').html($form.find('.amount').text());

                $modal.modal('show');
            }
        });

        validator.addItem({
            element: '[name="duration"]',
            display: '开通时长',
            required: true,
            rule: 'integer min{min:1} max{max:999}'
        })

        $form.find('[name=level]').on('change', function() {
            refresh();
        });

        $form.find('[name=unit]').on('change', function() {
            refresh();
        });

        $form.find('[name=duration]').on('change', function() {
            refresh();
        });

        $("#member-order-confirm-btn").on('click', function() {
            $form[0].submit();
        });

        refresh();
    };

    function refresh() {
        var $form = $("#member-buy-form");

        var prices = $.parseJSON($form.find('[data-role=prices]').text());

        var type = $form.find('[name=type]').val();

        if (type == 'renew') {
            var level = $form.find('[name=level]').val();
        } else {
            var level = $form.find('[name=level]:checked').val();
        }

        var unit = $form.find('[name=unit]:checked').val();
        var duration = $form.find('[name=duration]').val();

        var currentPrice = prices[level][unit];
        var amount = currentPrice * duration;

        $form.find('.unit-label').hide();
        $form.find('.unit-label-' + unit).show();

        var startDate = $form.find('[name=startDate]').val();
        if (startDate) {
            var deadline = moment(startDate);
        } else {
            var deadline = moment();
        }
        deadline = deadline.add(unit+'s', duration).format('YYYY-MM-DD');


        if (isNaN(amount)) {
            $form.find('.amount').html('--');
            $form.find('.deadline').html('').parent().addClass('hide');
        } else {
            $form.find('.amount').html(Number(amount).toFixed(2) + ' 元');
            $form.find('.deadline').html(deadline).parent().removeClass('hide');
        }
    }

});