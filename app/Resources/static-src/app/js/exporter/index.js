import notify from 'common/notify';

class Export {
  constructor($exprtBtns) {
    this.$exportBtns = $exprtBtns;
    this.$modal = $('#modal');
    this.fileNames = [];
    this.names = [];
    this.totalCount = 0;
    this.currentCount = 0;
    this.exportDataEvent();
  }

  exportDataEvent()
  {
    let self  = this;
    self.$exportBtns.on('click', function () {
      self.$exportBtn = $(this);
      self.names = self.$exportBtn.data('fileNames');
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

      self.exportData(0, '', urls, '');
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
      data: {
        names: self.names
      },
      success : function(response){
        if (!response.success) {
          self.notifyError(Translator.trans(response.message,response.parameters));
          can = false;
        } else {
          response.counts.forEach(function(val) {
            self.totalCount += val;
          }, 0);
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

  download(urls, fileNames) {
    if (urls.url && fileNames) {
      let url = urls.url + '&';
      $.each(fileNames, function (index, value) {
        url += `fileNames[]=${value}&`;
      });
      this.fileNames = [];
      this.totalCount = 0;
      this.currentCount = 0;
      window.location.href = url;
      return true;
    }

    return false;
  }

  notifyError(message){
    this.$modal.modal('hide');
    notify('warning', message);
  }

  exportData(start, fileName, urls, currentName) {
    let self = this;
    let data = {
      'start': start,
      'fileName': fileName,
      'names': self.names,
      'name': currentName,
    };

    $.get(urls.preUrl, data, function (response) {
      if (!response.success) {
        notify('danger', Translator.trans(response.message));
        return;
      }

      if (response.name !== '') {
        if (response.status === 'finish') {
          self.fileNames.push(response.csvName);
          self.currentCount += response.count;
        }
        let process = (response.start + self.currentCount) * 100 / self.totalCount + '%';
        self.$modal.find('#progress-bar').width(process);
        self.exportData(response.start, response.fileName, urls, response.name);
      } else {
        self.fileNames.push(response.csvName);
        self.$exportBtn.button('reset');
        self.download(urls, self.fileNames) ?  self.finish() : self.notifyError('unexpected error, try again');
      }
    }).error(function(e){
      notify('danger', e.responseJSON.error.message);
    });
  }
}

new Export($('.js-export-btn'));