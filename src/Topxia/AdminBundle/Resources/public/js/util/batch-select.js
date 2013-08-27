define(function(require, exports, module) {

    module.exports = function($container) {
        $container.on('click', '[data-role=batch-select]', function(){
           if( $(this).is(":checked") == true){
                $container.find('[data-role=batch-select], [data-role=batch-item]').prop('checked', true);
            } else {
                $container.find('[data-role=batch-select], [data-role=batch-item]').prop('checked', false);
            }
        });

    };

});