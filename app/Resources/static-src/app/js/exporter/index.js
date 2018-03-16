import notify from 'common/notify';

class Export {
  constructor($exprtBtns) {
    this.$exportBtns = $exprtBtns;
    this.$modal = $('#modal');
    this.exportDataEvent();
  }

  exportDataEvent()
  {
    let self  = this;
    self.$exportBtns.on('click', function () {
      self.$exportBtn = $(this);
      let $form = $(self.$exportBtn.data('targetForm'));
      let formData = $form.length > 0 ? $form.serialize() : '';
      let preUrl = self.$exportBtn.data('preUrl') + '?' + formData;
      let tryUrl = self.$exportBtn.data('tryUrl') + '?' + formData;
      let can = self.tryExport(tryUrl);
      if (!can) {
        return false;
      }

      self.$exportBtn.button('loading');
      let urls = {'preUrl':preUrl, 'url':self.$exportBtn.data('url')};
      self.showProgress();

      self.exportData(0, '', urls);
    });
  }

  tryExport(tryUrl)
  {
    let can = true;
    let self = this;
    $.ajax({
      type : 'get',
      url : tryUrl,
      async : false,
      success : function(response){
        if (!response.success) {
          self.notifyError(Translator.trans(response.message,response.parameters));
          can = false;
        }
      }
    });

    return can;
  }

  finish() {
    let self = this;
    self.$modal.find('#progress-bar').width('100%').parent().removeClass('active');
    let $title = self.$modal.find('.modal-title');
    setTimeout(function(){
      notify('success', $title.data('success'));
      self.$modal.modal('hide');
    },500);

  }




  showProgress() {
    let progressHtml = $('#export-modal').html();
    this.$modal.html(progressHtml);
    this.$modal.modal({backdrop: 'static', keyboard: false});
  }

  download(urls, fileName) {
    if (urls.url && fileName) {
      window.location.href = urls.url + '?fileName=' + fileName;
      return true;
    }

    return false;
  }

  notifyError(message){
    this.$modal.modal('hide');
    notify('warning', message);
  }

  exportData(start, fileName, urls) {
    let self = this;
    let data = {
      'start': start,
      'fileName': fileName,
    };

    $.get(urls.preUrl, data, function (response) {
      if (!response.success) {
        console.log(response);

        notify('danger', Translator.trans(response.message));
        return;
      }

      if (response.status === 'continue') {
        let process = response.start * 100 / response.count + '%';
        self.$modal.find('#progress-bar').width(process);
        self.exportData(response.start, response.fileName, urls);
      } else {
        self.$exportBtn.button('reset');
        self.download(urls, response.fileName) ?  self.finish() : self.notifyError('unexpected error, try again');
      }
    }).error(function(e){
      notify('danger', e.responseJSON.error.message);
    });
  }
}

new Export($('.js-export-btn'));