define(function(require, exports, module) {
    var VideoJS = require('video-js'),
        swfobject = require('swfobject'),
        Notify = require('common/bootstrap-notify');

    var SlidePlayer = require('../widget/slider-player');
    var DocumentPlayer = require('../widget/document-player');
    var Messenger = require('../player/messenger');
    require('../../util/qrcode').run();

    exports.run = function() {

        var firstLessonUrl = $('#firstLesson').data('url');
        showPlayer(firstLessonUrl);
        
        $("#alert-btn").on('click', function() {
            var $btn = $(this);

            if (typeof($btn.attr("data-toggle"))=="undefined"){
                $.post($btn.data('url'), function(response) {
                    if (response['result']) {
                        $('.member-num').html(response['number']);
                        $btn.hide();
                        $("#alerted-btn").show();
                    } else {
                        Notify.danger(response['message']);
                    }
                    
                });
            }
            
        });

        $('#live-tab li a').click(function(){
            var $this = $(this);
            var url = $this.data('url');
            $('#live-tab li a').removeClass('active');
            $this.addClass('active');
            
            showPlayer(url);
        })

        function showPlayer(url)
        {
            var $ifrimeContent = $('#lesson-preview-iframe');
            var $videoContent = $('#lesson-preview-player');
            var $swfContent = $('#lesson-preview-swf-player');

            swfobject.removeSWF('lesson-preview-swf-player');
            $ifrimeContent.empty();
            $videoContent.html("");
            $('.media-unconvert').hide();

            $('.lesson-content').hide();

            $.get(url,function(lesson){

                if (lesson.mediaError) {
                    Notify.danger(lesson.mediaError);
                    return;
                }

                if (lesson.mediaSource == 'iframe') {
                    var html = '<iframe class="embed-responsive-item" src="' + lesson.mediaUri + '" style="position:absolute; left:0; top:0; height:100%; width:100%; border:0px;" scrolling="no"></iframe>';

                    $ifrimeContent.html(html);
                    $ifrimeContent.show();

                } else if (lesson.type == 'video' || lesson.type == 'audio') {
                    if (lesson.mediaSource == 'self') {
                        var lessonVideoDiv = $videoContent;

                        if ((lesson.mediaConvertStatus == 'waiting') || (lesson.mediaConvertStatus == 'doing')) {
                            $('.media-unconvert').show();
                            return;
                        }

                        var playerUrl = '../../open/course/' + lesson.courseId + '/lesson/' + lesson.id + '/player';
                        
                        var html = '<iframe class="embed-responsive-item" src="' + playerUrl + '" name="viewerIframe" id="viewerIframe" width="100%" allowfullscreen webkitallowfullscreen height="100%"" style="border:0px;position:absolute; left:0; top:0;"></iframe>';

                        $videoContent.show();
                        $videoContent.html(html);

                    } else {
                        $swfContent.html('<div id="lesson-swf-player"></div>');
                        swfobject.embedSWF(lesson.mediaUri,
                            'lesson-swf-player', '100%', '100%', "9.0.0", null, null, {
                                wmode: 'opaque',
                                allowFullScreen: 'true'
                            });
                        $swfContent.show();
                    }
                }
            })
        }
    };

});