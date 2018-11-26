import notify from 'common/notify';

class SubtitleDialog {
  constructor(element) {
    this.element = $(element);
    this.upload_id = 'subtitle-uploader';
    this.inited = false;
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
          notify('success', Translator.trans('activity.video_manage.delete_success_hint'));
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
      this.element.html(Translator.trans('activity.video_manage.subtitle_load_hint'));
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
      sdkBaseUri: app.cloudSdkBaseUri,
      disableDataUpload: app.cloudDisableLogReport,
      disableSentry: app.cloudDisableLogReport,
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
        common: {
          videoNo: globalId,
        }
      },
      locale: document.documentElement.lang
    });

    uploader.on('error', function (err) {
      if (err.error === 'Q_TYPE_DENIED') {
        notify('danger', Translator.trans('activity.video_manage.subtitle_upload_error_hint'));
      }
    });

    uploader.on('file.finish', function (file) {
      $.post($elem.data('subtitleCreateUrl'), {
        'name': file.name,
        'subtitleId': file.id,
        'mediaId': mediaId
      }).success(function (data) {
        let convertStatus = {
          waiting: Translator.trans('activity.video_manage.convert_status_waiting'),
          doing: Translator.trans('activity.video_manage.convert_status_doing'),
          success: Translator.trans('activity.video_manage.convert_status_success'),
          error: Translator.trans('activity.video_manage.convert_status_error'),
          none: Translator.trans('activity.video_manage.convert_status_none')
        };
        $('.js-media-subtitle-list').append('<li class="pvs mtm">' +
          '<span class="subtitle-name prl pull-left">' + data.name + '</span>' +
          '<span class="subtitle-transcode-status ' + data.convertStatus + '">' + convertStatus[data.convertStatus] + '</span>' +
          '<a href="javascript:;" class="btn-link pll color-primary js-subtitle-delete" data-subtitle-delete-url="/media/' + mediaId + '/subtitle/' + data.id + '/delete">'+Translator.trans('activity.video_manage.subtitle_delete_hint')+'</a>' +
          '</li>');
        if ($('.js-media-subtitle-list li').length > 3) {
          $('#' + self.upload_id).hide();
        }
        notify('success', Translator.trans('activity.video_manage.subtitle_upload_success_hint'));
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
