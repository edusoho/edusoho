import notify from 'common/notify';

class SubtitleDialog {

  upload_id = 'subtitle-uploader';
  inited = false;

  constructor(element) {
    this.element = $(element);

    if (this.element.length > 0) {
      this.init();
      this.inited = true;
    }

    let $container = this.element.closest('#video-subtitle-form-group');
    if ($container.find('#ext_mediaId_for_subtitle').val() > 0) {
      this.render({id: $container.find('#ext_mediaId_for_subtitle').val()});
    }
  }

  init() {

    let self = this;
    //删除字幕
    this.element.on('click', '.js-subtitle-delete', function () {
      let $elem = $(this);
      $.post($elem.data('subtitleDeleteUrl'), function (data) {
        if (data) {
          notify('success', '删除字幕成功');
          $elem.parent().remove();
          $('#' + self.upload_id).show();
        }
      });
    });
  }


  render(media) {
    if (!this.inited) {
      return;
    }

    if (media && 'id' in media && media.id > 0) {
      this.media = media;
      this.element.html('加载字幕...');
      let self = this;
      $.get(this.element.data('dialogUrl'), {mediaId: this.media.id}, function (html) {
        self.element.html(html);
        self.initUploader();
      });
    }
  }

  initUploader() {
    let self = this;
    let $elem = $('#' + this.upload_id);
    let mediaId = $('.js-subtitle-dialog').data('mediaId');
    let globalId = $elem.data('mediaGlobalId');

    if (this.uploader) {
      this._destroyUploader();
    }
    let uploader = new UploaderSDK({
      initUrl: $elem.data('initUrl'),
      finishUrl: $elem.data('finishUrl'),
      id: this.upload_id,
      ui: 'simple',
      multi: true,
      accept: {
        extensions: ['srt'],
        mimeTypes: ['text/srt']
      },
      type: 'sub',
      process: {
        videoNo: globalId,
      }
    });

    uploader.on('error', function (err) {
      if (err.error === 'Q_TYPE_DENIED') {
        notify('danger', '请上传srt格式的文件！');
      }
    });

    uploader.on('file.finish', function (file) {
      $.post($elem.data('subtitleCreateUrl'), {
        "name": file.name,
        "subtitleId": file.id,
        "mediaId": mediaId
      }).success(function (data) {
        let convertStatus = {
          waiting: '等待转码',
          doing: '正在转码',
          success: '转码成功',
          error: '转码失败',
          none: '等待转码'
        };
        $('.js-media-subtitle-list').append('<li class="pvs">' +
          '<span class="subtitle-name prl">' + data.name + '</span>' +
          '<span class="subtitle-transcode-status ' + data.convertStatus + '">' + convertStatus[data.convertStatus] + '</span>' +
          '<a href="javascript:;" class="btn-link pll color-primary js-subtitle-delete" data-subtitle-delete-url="/media/' + mediaId + '/subtitle/' + data.id + '/delete">删除</a>' +
          '</li>');
        if ($('.js-media-subtitle-list li').length > 3) {
          $('#' + self.upload_id).hide();
        }
        notify('success', '字幕上传成功！');
      }).error(function (data) {
        notify('danger', data.responseJSON.error.message);
      });
    });

    this.uploader = uploader;
  }

  show() {
    let parent = this.element.parent('.form-group');
    if (parent.length > 0) {
      parent.removeClass('hide');
    }
  }

  hide() {
    let parent = this.element.parent('.form-group');
    if (parent.length > 0) {
      parent.addClass('hide');
    }
  }

  _destroyUploader() {
    if (!this.uploader) {
      return;
    }
    this.uploader.__events = null;
    try {
      this.uploader.destroy();
    } catch (e) {
      //忽略destroy异常
    }
    this.uploader = null;
  }

  destroy() {
    if (!this.inited) {
      return;
    }
    this._destroyUploader();
  }
}

export default SubtitleDialog;