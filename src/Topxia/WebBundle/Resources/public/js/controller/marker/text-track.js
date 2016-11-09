define(function(require,exports,module){

    var messenger = require('./messeger.js');
    var TrackSelect = require('./text-select-module.js');
    var BatchUploader = require('../uploader/batch-uploader.js');
    require('new-uploader');
    require('subtitle');

    var $textTrackDisplay = $('.text-track-overview');
    initHeight();

    var handler = $('#uploader')
    var uploader = new UploaderSDK({
        initUrl:handler.data('initUrl'),
        finishUrl:handler.data('finishUrl'),
        id:'uploader',
        ui:'simple',
    })
    uploader.on('file.finish', function (file) {
      console.log('事件触发：', file)
    });

    var captions = new Subtitle();
    var select = Object.create(TrackSelect);
    select.init('track-select');
    select.on('valuechange',function(value){
        $.get(value.src,handleData);
    })
    select.on('optionempty',function(){
        $textTrackDisplay.html();
    })

    // Get the file 、parse it 、display it;
    function handleData(data)
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

    function initHeight(){
        var height = $('.manage-edit-body').height();
        var tabHeight = $('.nav-tabs-edit').height();
        var textTrackTitleHeight = $('.text-track-title').height();
        var selctHeight = $('.js-texttrack-select').height();
        $textTrackDisplay.height(height - tabHeight - textTrackTitleHeight - selctHeight - 60).show();
    }
})