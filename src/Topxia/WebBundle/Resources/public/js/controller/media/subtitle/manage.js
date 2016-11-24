define(function(require,exports,module){

    require('new-uploader');
    require('subtitle-browser');
    var Select = require('./subtitle-select.js');
    var messenger = require('../player.js');
    var Notify = require('common/bootstrap-notify');

    var $textTrackDisplay = $('.text-track-overview');
    var courseId = $('#lesson-dashboard').data('course-id');
    (function (){
        var height = $('.manage-edit-body').height();
        var tabHeight = $('.nav-tabs-edit').height();
        var textTrackTitleHeight = $('.text-track-title').height();
        var selectorHeight = $('#track-select').height();
        $textTrackDisplay.height(height - tabHeight - textTrackTitleHeight - selectorHeight - 140).show();
    })();



    //选择框组件实例
    var select = Object.create(Select);
    var $subtitleListElem = $('#track-select');
    var subtitleList = $subtitleListElem.data('subtitleList');
    select.init({
        id:'#track-select',
        optionsLimit:4
    });
    select.on('valuechange',function(data){
        if(!data){
            $textTrackDisplay.html('当前无字幕');
            return;
        }
        $.get(data.url, showSubtitleContent);
    });
    select.on('deleteoption',function(data){
        $.post('/media/'+mediaId+'/subtitle/'+data.id+'/delete',function(data){
            if(data){
                Notify.success(Translator.trans('删除字幕成功'));
                $subtitleUploaderElem.show();
            }
        });
    });
    select.on('optionlimit',function(){
        $subtitleUploaderElem.hide();
    })
    select.resetOptions(subtitleList);
    
    //上传实例
    var $subtitleUploaderElem = $('#uploader');
    var videoNo = $subtitleUploaderElem.data('mediaGlobalId');;
    var mediaId = $subtitleUploaderElem.data('mediaId');
    var subtitleCreateUrl = $subtitleUploaderElem.data('subtitleCreateUrl');
    var uploader = new UploaderSDK({
        initUrl:$subtitleUploaderElem.data('initUrl'),
        finishUrl:$subtitleUploaderElem.data('finishUrl'),
        id:'uploader',
        ui:'simple',
        multi:true,
        accept:{
            extensions:['srt'],
            mimeTypes:['text/srt']
        },
        type:'sub',
        process:{
            videoNo:videoNo
        }
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
            if(!data){
                return;
            }
            select.addOption(data);
            Notify.success(Translator.trans('字幕上传成功！'));
            setTimeout(function(){
                $.get('/media/'+ mediaId +'/subtitles').done(function(data){
                    if(data.subtitles){
                        select.resetOptions(data.subtitles);
                    }
                })
            },5000);
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
