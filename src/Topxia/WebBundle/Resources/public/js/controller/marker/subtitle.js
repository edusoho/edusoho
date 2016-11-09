define(function(require,exports,module){

    require('new-uploader');
    require('subtitle-browser');

    var messenger = require('./messeger.js');
    var BatchUploader = require('../uploader/batch-uploader.js');

    var $textTrackDisplay = $('.text-track-overview');

    var fixTextTrackDisplayHeight = function (){
        var height = $('.manage-edit-body').height();
        var tabHeight = $('.nav-tabs-edit').height();
        var textTrackTitleHeight = $('.text-track-title').height();
        var selectorHeight = $('#track-select').height();
        $textTrackDisplay.height(height - tabHeight - textTrackTitleHeight - selectorHeight - 60).show();
    }

    var videoNo = $(window.frames['viewerIframe'].document).find('#lesson-video-content').data('file-global-id');
    var $elem = $('#uploader');
    var $selector = $('#track-select');

    var mediaId = $elem.data('mediaId');
    var subtitleCreateUrl = $elem.data('subtitleCreateUrl');
    var subtitleListUrl = $elem.data('subtitleListUrl');

    var loadSubtitleList = function() {
        $selector.load(subtitleListUrl, function() {
            fixTextTrackDisplayHeight();
        });
    }

    loadSubtitleList();

    var uploader = new UploaderSDK({
        initUrl:$elem.data('initUrl'),
        finishUrl:$elem.data('finishUrl'),
        id:'uploader',
        ui:'simple',
        videoNo:videoNo
    });

    uploader.on('file.finish', function (file) {
        $.post(subtitleCreateUrl, {
            "name": file.name,
            "subtitleId": file.id,
            "mediaId": mediaId
        }, function () {
            loadSubtitleList();
        });
    });

    $("#track-select").delegate(".subtitle-label", "click", function(){
        $.get($(this).data('url'), showSubtitleContent);
    });

    $("#track-select").delegate(".subtitle-delete", "click", function(){
        $.post($(this).data('url'), function() {
            loadSubtitleList();
        });
    });

    var captions = new Subtitle();
    function showSubtitleContent(data)
    {
        captions.parse(data);
        var subtitleArray = captions.getSubtitles({
            duration:true,
            timeFormat:'ms'
        });
        var html = '';
        subtitleArray.map(function(cue){
            html += '<p>' + cue.text + '</p>';
        });
        $textTrackDisplay.html(html);
        var $subtitleDom = $textTrackDisplay.find('p');
        messenger.on('timechange',function(data){
            setTimeout(function(){
                var last = subtitleArray.find(function(cue,index){
                    if(cue.start/1000 > data.currentTime){
                        return cue;
                    }
                });
                $subtitleDom.removeClass('active');
                if(last.index>1 && subtitleArray[last.index - 2].end > parseFloat(data.currentTime)*1000){
                    $subtitleDom.eq(last.index - 2).addClass('active');
                }
            },0);
        })
    }
});
