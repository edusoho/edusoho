define(function(require, exports, module) {

    exports.run = function() {
        $("#item-picker-form").submit(function(e) {
            var $form = $(this),
                $modal = $form.parents('.modal');
            e.preventDefault();

            $.get($form.attr('action'), $form.serialize(), function(html) {
                $modal.html(html);
            });
        });
    }

});