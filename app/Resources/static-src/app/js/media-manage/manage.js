import './video';
import './coord';
import Select from './subtitle-select';
import messenger from './messenger';
import notify from 'common/notify';
import Subtitle from 'subtitle';

let $textTrackDisplay = $('.text-track-overview');
let $uploader = $('#uploader');

class Manage {
  constructor() {
    this.select = null;
    this.init();
  }

  init() {
    this.initTextDisplay();
    this.initSelect();
    this.initUploader();
  }

  initTextDisplay() {
    let height = $('.manage-edit-body').height();
    let tabHeight = $('.nav-tabs-edit').height();
    let textTrackTitleHeight = $('.text-track-title').height();
    let selectorHeight = $('#track-select').height();
    $textTrackDisplay.height(height - tabHeight - textTrackTitleHeight - selectorHeight - 140).show();
  }

  initSelect() {
    let select = Object.create(Select);
    let $subtitleListElem = $('#track-select');
    let subtitleList = $subtitleListElem.data('subtitleList');
    let mediaId = $uploader.data('mediaId');
    let _this = this;
    
    select.init({
      id: '#track-select',
      optionsLimit: 4
    });
    select.on('valuechange', function(data) {
      if (!data) {
        $textTrackDisplay.html('当前无字幕');
        return;
      }
      $.get(data.url, _this.showSubtitleContent);
    });
    select.on('deleteoption', function(data) {
      let url = `/media/${mediaId}/subtitle/${data.id}/delete`;
      $.post(url, (data) => {
        if (data) {
          notify('success', Translator.trans('删除字幕成功'));
          $uploader.show();
        }
      });
    });
    select.on('optionlimit', function() {
      $uploader.hide();
    });
    select.resetOptions(subtitleList);

    this.select = select;
  }

  initUploader() {
    let select = this.select;

    let videoNo = $uploader.data('mediaGlobalId');;
    let mediaId = $uploader.data('mediaId');
    let subtitleCreateUrl = $uploader.data('subtitleCreateUrl');
    
    let uploader = new UploaderSDK({
      initUrl: $uploader.data('initUrl'),
      finishUrl: $uploader.data('finishUrl'),
      id: 'uploader',
      ui: 'simple',
      multi: true,
      accept: {
        extensions: ['srt'],
        mimeTypes: ['text/srt']
      },
      type: 'sub',
      process: {
        videoNo: videoNo
      }
    });

    uploader.on('error', function(err) {
      if (err.error === 'Q_TYPE_DENIED') {
        notify('danger', Translator.trans('请上传srt格式的文件！'));
      }
    });

    uploader.on('file.finish', function(file) {
      $.post(subtitleCreateUrl, {
        "name": file.name,
        "subtitleId": file.id,
        "mediaId": mediaId
      }).success(function (data) {
        if (!data) {
          return;
        }
        select.addOption(data);
        notify('success', Translator.trans('字幕上传成功！'));
        
        setTimeout(function() {
          let url = `/media/${mediaId}/subtitles`;
          $.get(url).done(function(data) {
            if (data.subtitles) {
              select.resetOptions(data.subtitles);
            }
          })
        }, 5000);
      }).error(function(data) {
        notify('danger', Translator.trans(data.responseJSON.error.message));
      });
    });
  }

  showSubtitleContent(data) {
    let captions = new Subtitle();
    
    try {
      captions.parse(data);
    } catch(e) {
      notify('danger', Translator.trans('当前字幕解析出错，请删除重新上传！'))
      $textTrackDisplay.html('当前字幕解析出错，请删除重新上传！');
      return;
    }

    let subtitleArray = captions.getSubtitles({
      duration: true,
      timeFormat: 'ms'
    });

    let html = '';
    subtitleArray.map((cue) => {
      html += `<p>${cue.text}</p>`;
    });
    
    $textTrackDisplay.html(html);

    let $subtitleDom = $textTrackDisplay.find('p');
    
    messenger.on('timechange', function(data) {
      setTimeout(function() {
        let last = subtitleArray.find(function(cue, index) {
          if (cue.start / 1000 > data.currentTime) {
            return cue;
          }
        });

        $subtitleDom.removeClass('active');
        if (!last) {
          return;
        }
        if (last.index > 1 && subtitleArray[last.index - 2].end > parseFloat(data.currentTime) * 1000) {
          $subtitleDom.eq(last.index - 2).addClass('active');
        }
      }, 0);
    })
  }
  
}

export default Manage;