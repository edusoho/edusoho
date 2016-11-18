define(function(require,exports,module){

    require('new-uploader');
    require('subtitle-browser');
    var Select = require('./text-select-module');
    var messenger = require('./messeger.js');
    var Notify = require('common/bootstrap-notify');

    var $textTrackDisplay = $('.text-track-overview');
    var courseId = $('#lesson-dashboard').data('course-id');
    (function (){
        var height = $('.manage-edit-body').height();
        var tabHeight = $('.nav-tabs-edit').height();
        var textTrackTitleHeight = $('.text-track-title').height();
        var selectorHeight = $('#track-select').height();
        $textTrackDisplay.height(height - tabHeight - textTrackTitleHeight - selectorHeight - 100).show();
    })();

    //选择框组件实例
    var select = Object.create(Select);
    select.init('track-select');
    select.on('valuechange',function(data){
        if(!data){
            $textTrackDisplay.html('当前无字幕');
            return;
        }
        $.get(data.url, showSubtitleContent);
    });
    select.on('deleteoption',function(data){
        $.post('/subtitle/'+data.id+'/delete?courseId='+courseId,function(data){
            if(data){
                Notify.success(Translator.trans('删除字幕成功'));
            }
        });
    });
    
    //初始获取字幕列表
    var videoNo = $(window.frames['viewerIframe'].document).find('#lesson-video-content').data('file-global-id');
    var $elem = $('#uploader');
    var mediaId = $elem.data('mediaId');
    var subtitleCreateUrl = $elem.data('subtitleCreateUrl');
    var subtitleListUrl = $elem.data('subtitleListUrl');
    var loadSubtitleList = function() {
        $.post(subtitleListUrl).done(function(data){
            if(data.subtitles){
                select.resetOptions(data.subtitles);
            }
        })
    }
    loadSubtitleList();

    //上传实例
    var uploader = new UploaderSDK({
        initUrl:$elem.data('initUrl'),
        finishUrl:$elem.data('finishUrl'),
        id:'uploader',
        ui:'simple',
        multi:true,
        accept:{
            extensions:['srt'],
            mimeTypes:['text/srt']
        },
        videoNo:videoNo
    });
    uploader.on('error',function(err){
        if(err.error === 'Q_TYPE_DENIED'){
            Notify.danger(Translator.trans('请上传srt格式的文件！'));
        }
    });
    uploader.on('file.finish', function (file) {
        $.post(subtitleCreateUrl, {
            "name": file.name,
            "subtitleId": file.id,
            "mediaId": mediaId
        }).success(function (data) {
            Notify.success(Translator.trans('字幕上传成功！'));
            loadSubtitleList();
        }).error(function (data){
            Notify.danger(Translator.trans(data.responseJSON.error.message));
        });
    });

    //字幕解析显示实例
    var captions = new Subtitle();
    function showSubtitleContent(data)
    {
        try{
            captions.parse(data);
        }
        catch(e){
            Notify.danger(Translator.trans('当前字幕解析出错，请删除重新上传！'));
            $textTrackDisplay.html('当前字幕解析出错，请删除重新上传！');
            return;
        }

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
                if(!last){
                    return;
                }
                if(last.index>1 && subtitleArray[last.index - 2].end > parseFloat(data.currentTime)*1000){
                    $subtitleDom.eq(last.index - 2).addClass('active');
                }
            },0);
        })
    }
});
