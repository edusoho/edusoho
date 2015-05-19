define(function(require, exports, module) {

    exports.run = function() {

        if ($('[name=access-intercept-check]').length > 0) {
            $('.thread-list-default').on('click', '.thread-item-title', function(e) {
                var $that = $(this);
                e.preventDefault();
                $.get($('[name=access-intercept-check]').val(), function(response) {
                    if (response) {
                        window.location.href = $that.attr('href');
                        return;
                    }

                    $('.access-intercept-modal').modal('show');
                }, 'json');
            });
        }

    };

});