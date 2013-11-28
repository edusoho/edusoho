define(function(require, exports, module) {

    exports.run = function() {
        $(".mode-radios input").on('click', function(){
            var mode = $(this).val();

            $('.with-discuz').hide();
            $('.with-phpwind').hide();
            $('.with-' + mode).show();

        });

        $(".mode-radios input:checked").trigger('click');

    };

});