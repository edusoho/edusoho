define(function(require, exports, module) {

    require('jquery');

    require('zclip');

    exports.run = function() {

        $(document).ready(function() {

            $('#copy-button').zclip({
                path: '/assets/libs/jquery-plugin/zclip/1.1.1/ZeroClipboard.swf',
                copy: function() {
                    return $('#content').val();
                },
                afterCopy:function(){
                    $('#copy').html('推广码复制成功!');
                    $('#copy').css('color','green');

                }
            });

        });

    };
});