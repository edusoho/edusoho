define(function(require, exports, module) {

    exports.run = function() {

        $('#testpaper-teacherSay-select').change(function() {
            var $option = $(this).find('option:checked');
            if ($option.val() == '') {
                $('#testpaper-teacherSay-input').val('');
            } else {
                $('#testpaper-teacherSay-input').val($option.text());
            }
        });

        $('#testpaper-teacherSay-btn').on('click', function(){
            val = $('#testpaper-teacherSay-input').val();
            $('#teacherSay').val(val);
        });

    };


});

