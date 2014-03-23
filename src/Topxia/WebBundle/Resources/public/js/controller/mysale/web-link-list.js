define(function(require, exports, module) {

    require('jquery');

    require('zclip');

    exports.run = function() {

        $(document).ready(function() {

            var copyButton = 'copy';

            var textInput = 'becopied';

            $('.copy-button').each(function() { //给每个a标签加上copy样式

                var thisId = this.id.replace(copyButton, '');

                $(this).zclip({

                    path: 'js/ZeroClipboard.swf',

                    copy: $('p#becopied' + thisId).text()

                })

            })



            $('#copy-button').zclip({
                path: '/assets/libs/jquery-plugin/zclip/1.1.1/ZeroClipboard.swf',
                copy: function() {
                    return $('#content').val();
                },
                afterCopy:function(){
                    $('#copy').html('链接地址复制成功!');
                    $('#copy').css('color','green');

                }
            });

        });

    };
});