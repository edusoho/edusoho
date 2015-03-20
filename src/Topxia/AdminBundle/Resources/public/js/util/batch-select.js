define(function(require, exports, module) {

    module.exports = function($element) {
        $element.on('click', '[data-role=batch-select]', function(){
           if( $(this).is(":checked") == true){
                $element.find('[data-role=batch-select], [data-role=batch-item]').prop('checked', true);
            } else {
                $element.find('[data-role=batch-select], [data-role=batch-item]').prop('checked', false);
            }
        });

    };

});