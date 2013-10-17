define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');

    exports.run = function() {

        var $form = $('#course-refund-form');

        $form.find('[name="reason[type]"]').on('change', function() {
            var $this = $(this),
                reasonType = $this.val();
            if (reasonType == 'other') {
                $form.find('[name="reason[note]"]').val('').show();
            } else {
                var reason = $this.find('option[value=' + reasonType +  ']').text();
                $form.find('[name="reason[note]"]').hide().val(reason);
            }
        }).change();

        $form.on('submit', function() {
            $.post($form.attr('action'), $form.serialize(), function(response) {
                window.location.href = $form.data('goto');
            }, 'json');
            return false;
        });

    };

});