define(function(require, exports, module) {

    exports.run = function() {

        var $form = $("#member-upgrade-form");

        $form.find('[name=level]').on('change', function() {
            $form.find('.amount').html('正在计算...');
            var levelId = $form.find('[name=level]:checked').val();
            $.get($form.data('calUpgradeAmountUrl'), {levelId:levelId}, function(amount) {
                $form.find('.amount').html(amount + ' 元');
            });
        });

        $form.find('[name=level]:checked').trigger('change');

        var $modal = $("#member-upgrade-confirm-modal");
        $modal.modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

        $form.on('submit', function(event) {
            event.preventDefault();

            var levelName = $form.find('[name=level]:checked').data('name');

            $modal.find('.confirm-level').html(levelName);
            $modal.find('.confirm-amount').html($form.find('.amount').text());

            $modal.modal('show');

            return false;

        });

        $("#member-order-confirm-btn").on('click', function() {
            $form[0].submit();
        });

    }

});