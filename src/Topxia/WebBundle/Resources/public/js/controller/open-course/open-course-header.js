define(function(require, exports, module) {
    var VideoJS = require('video-js'),
        swfobject = require('swfobject'),
        Notify = require('common/bootstrap-notify');

    var SlidePlayer = require('../widget/slider-player');
    var DocumentPlayer = require('../widget/document-player');
    var Messenger = require('../player/messenger');
    require('../../util/qrcode').run();

    exports.run = function() {

        if ($("#lesson-preview-player").length > 0) {
            var lessonVideoDiv = $('#lesson-preview-player');
            
            var courseId = lessonVideoDiv.data("courseId");
            var lessonId = lessonVideoDiv.data("lessonId");
            var playerUrl = lessonVideoDiv.data("playerUrl");

            var html = '<iframe src=\''+playerUrl+'\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';

            lessonVideoDiv.html(html);

            if (lessonVideoDiv.data('timelimit')) {
                $(".modal-footer").prepend($('.js-buy-text').html());
                var messenger = new Messenger({
                    name: 'parent',
                    project: 'PlayerProject',
                    children: [viewerIframe],
                    type: 'parent'
                });

                messenger.on("ended", function(){
                    lessonVideoDiv.html($('.js-time-limit-dev').html());
                });
            }
        }

        if($("#lesson-preview-flash").length>0){
            var player = $("#lesson-preview-flash");
            if (!swfobject.hasFlashPlayerVersion('11')) {
                var html = '<div class="alert alert-warning alert-dismissible fade in" role="alert">';
                html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                html += '<span aria-hidden="true">×</span>';
                html += '</button>';
                html += '您的浏览器未装Flash播放器或版本太低，请先安装Flash播放器。';
                html += '</div>';
                player.html(html);
            } else {
                $.get(player.data('url'), function(response) {
                    var html = '<div id="lesson-swf-player" ></div>';
                    $("#lesson-preview-flash").html(html);
                    swfobject.embedSWF(response.mediaUri, 
                        'lesson-swf-player', '100%', '100%', "9.0.0", null, null, 
                        {wmode:'opaque',allowFullScreen:'true'});
                });
            }
            player.css("height", '360px');
        }     

        if ($("#lesson-preview-swf-player").length > 0) {
            swfobject.embedSWF($("#lesson-preview-swf-player").data('url'), 'lesson-preview-swf-player', '100%', '360', "9.0.0", null, null, {wmode: 'transparent'});

            $('#modal').one('hidden.bs.modal', function () {
                swfobject.removeSWF('lesson-preview-swf-player');
            });
        }

        if ($("#lesson-preview-iframe").length > 0) {

            var html = '<iframe src="' + $("#lesson-preview-iframe").data('url') + '" style="height:500px; width:100%; border:0px;" scrolling="no"></iframe>';
            $("#lesson-preview-iframe").html(html).show();

            $('#modal').one('hidden.bs.modal', function () {
                $("#lesson-preview-iframe").remove();
            });
        }

        $modal = $('#modal');
        $modal.on('hidden.bs.modal', function(){
            if ($("#lesson-preview-player").length > 0) {
                $("#lesson-preview-player").html("");
            }
        });
        $modal.on('click','.js-buy-btn', function(){
            $.get($(this).data('url'), function(html) {
                $modal.html(html);
            });
        });

        $("#alert-btn").on('click', function() {
            var $btn = $(this);

            if (typeof($btn.attr("data-toggle"))=="undefined"){
                /*$("#modal").html("");
                $("#modal").load($btn.data('url'));
            } else {*/
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
    };

});