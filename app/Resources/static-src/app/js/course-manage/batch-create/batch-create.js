import notify from 'common/notify';

class BatchCreate {
  constructor(options) {
    this.element = $(options.element);
    this.uploader = null;
    this.files = [];
    this.$sortable = $('#sortable-list');
    this.init();
  }

  init() {
    this.initUploader();
    this.initEvent();
  }

  initUploader() {
    let $uploader = this.element;
    this.uploader = new UploaderSDK({
      id: $uploader.attr('id'),
      sdkBaseUri: app.cloudSdkBaseUri,
      disableDataUpload: app.cloudDisableLogReport,
      disableSentry: app.cloudDisableLogReport,
      initUrl: $uploader.data('initUrl'),
      finishUrl: $uploader.data('finishUrl'),
      accept: $uploader.data('accept'),
      process: this.getUploadProcess(),
      ui: 'batch',
      fileNumLimit: $uploader.data('numLimit') !== undefined ? $uploader.data('numLimit') : null,
      locale: document.documentElement.lang
    });

    this.uploader.on('file.finish', (file) => {
      this.files.push(file);
    });

    this.uploader.on('error', (error) => {
      let status = {'F_DUPLICATE':Translator.trans('uploader.file.exist')};
      if (!error.message){
        error.message = status[error.error];
      }
      notify('danger', error.message);
    });
  }

  initEvent() {
    $('.js-upload-params').on('change', (event) => {
      this.uploader.setProcess(this.getUploadProcess());
    });

    $('.js-batch-create-lesson-btn').on('click', (event) => {

      if (!this.files.length) {
        notify('danger', Translator.trans('uploader.select_one_file'));
        return;
      }

      let $btn = $(event.currentTarget);
      $btn.button('loading');
      if (!this.validLessonNum($btn)) {
        console.log(this.validLessonNum($btn));
        return ;
      }

      console.log('files', this.files);

      this.files.map((file, index) => {
        let isLast = false;
        if (index + 1 == this.files.length) {
          isLast = true;
        }
        console.log('file', file);
        this.createLesson($btn, file, isLast);
      });
    });

    $('[data-toggle="popover"]').popover({
      html: true,
    });
  }

  getUploadProcess() {
    let video = $('.js-upload-params').get().reduce((prams, dom) => {
      prams[$(dom).attr('name')] = $(dom).find('option:selected').val();
      return prams;
    }, {});


    let uploadProcess = {
      video,
      document: {
        type: 'html',
      },
    };

    if ($('[name=support_mobile]').length > 0) {
      uploadProcess.common = {
        supportMobile: $('[name=support_mobile]').val(),
      };
    }

    return uploadProcess;
  }

  validLessonNum($btn) {
    let valid = true;
    $.ajax({
      type: 'post',
      url: $btn.data('validUrl'),
      async: false,
      data: {
        number: this.files.length
      },
      success: function(response) {
        if (response && response.error) {
          notify('danger', response.error);
          $btn.button('reset');
          valid = false;
        }
        valid = true;
      }
    });
    return valid;
  }

  createLesson($btn, file, isLast) {
    let self = this;
    $.ajax({
      type: 'post',
      url: $btn.data('url'),
      async: false,
      data: {
        fileId: file.id
      },
      success: function(response) {
        if (response && response.error) {
          notify('danger', response.error);
        } else {
          self.$sortable.trigger('addItem', response);
        }
      },
      error: function(response) {
        console.log('error', response);
        notify('danger', Translator.trans('uploader.status.error'));
      },
      complete: function (response) {
        console.log('complete', response);
        if (isLast) {
          $('#modal').modal('hide');
        }
      }
    });
  }

}

export default BatchCreate;