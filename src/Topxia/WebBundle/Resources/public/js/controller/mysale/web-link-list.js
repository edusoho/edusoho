define(function(require, exports, module) {

    require('jquery');

    require('zclip');

    exports.run = function() {

        $(document).ready(function() {

            var copyButton = 'button';


            $('.copy-button').each(function() {

                var thisId = this.id.replace(copyButton, '');

                $(this).zclip({

                    path: '/assets/libs/jquery-plugin/zclip/1.1.1/ZeroClipboard.swf',
                    copy: function() {

                        var hpid='p#content' + thisId;
                       
                        return $(hpid).text();
                    },
                    afterCopy:function(){
                        $('div#copy'+ thisId).html('链接地址复制成功!');
                        $('div#copy'+ thisId).css('color', 'green');
                    }
                });

            });

        });

    };
});