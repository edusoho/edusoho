define(function(require, exports, module) {

    var teacherSayIndex = [
        '喜欢',
        '很喜欢',
        '非常喜欢',
        '非常非常喜欢',
        '非常非常非常喜欢'
    ];

    var teacherSays = [
        '这是喜欢',
        '这是很喜欢',
        '这是非常喜欢',
        '这是非常非常喜欢',
        '这是非常非常非常喜欢'
    ];



    exports.run = function() {

        $('#testpaper-teacherSay-select').change(function(){
            var index =0;

            for (var i = 0; i < teacherSayIndex.length; i++) {
                if (teacherSayIndex[i] == $(this).val()){
                    index = i;
                }
            };

            $('#testpaper-teacherSay-input').val(teacherSays[index]);

        });

        $('#testpaper-teacherSay-btn').on('click', function(){
            val = $('#testpaper-teacherSay-input').val();
            $('#teacherSay').val(val);
        });

    };


});

