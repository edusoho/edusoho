define(function(require, exports, module) {

    module.exports = function($element, onSuccess) {
        $element.on('click', '[data-role=batch-select]', function(e) {

            if ($(e.currentTarget).is(":checked") == true){
                $('[data-role=batch-select]:visible, [data-role=batch-item]:visible').prop('checked', true);
            } else {
                $('[data-role=batch-select]:visible, [data-role=batch-item]:visible').prop('checked', false);
            }
            
        });


    };

});