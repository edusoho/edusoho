import notify from 'common/notify';
import EsMessenger from 'app/common/messenger';

export default class Cover {
  constructor(options) {
    this.callback = options.callback;
    this.element = options.element;
    this.init();
  }
  init() {
    this.initEvent();
    this._initPlayer();
  }
  initEvent() {
    $('.js-img-set').on('click', (event) => {
      this.onClickChangePic(event);
    });
    $('.js-reset-btn').on('click', (event) => {
      this.onClickReset(event);
    });
    $('.js-set-default').on('click', (event) => {
      this.onClickDefault(event);
    });
    $('.js-set-select').on('click', (event) => {
      this.onClickSelect(event);
    });
    $('.js-screenshot-btn').on('click', (event) => {
      this.onClickScreenshot(event);
    });
    $('#cover-form').on('submit', (event) => {
      this.onSubmitCoverForm(event);
    });
  }
  onClickChangePic(event) {
    var $target = $(event.currentTarget);
    var $coverTab = $target.closest('#cover-tab');
    $coverTab.find('.js-cover-img').attr('src', $target.attr('src'));
    $coverTab.find('#thumbNo').val($target.data('no'));
  }
  onClickReset() {
    $('#thumbNo').val('');
    $('.js-cover-img').attr('src', $('#orignalThumb').val());
  }
  onClickDefault(event) {
    this._changePane($(event.currentTarget));
  }
  onClickSelect(event) {
    this._changePane($(event.currentTarget));
  }
  onClickScreenshot() {
    var $target = $(event.currentTarget);
    var self = this;
    $target.button('loading');
    var screenshotSecond = self.second;
    $.ajax({
      type: 'get',
      url: $target.data('url'),
      data: {
        'second': screenshotSecond
      }
    }).done(function(resp) {
      if (resp.status == 'success') {
        self._successGeneratePic($target, resp);
      } else if (resp.status == 'waiting') {
        //轮询
        self.intervalId = setInterval(function() {
          $.get($target.data('url'), {
            'second': screenshotSecond
          }, function(resp) {
            if (resp.status == 'success') {
              self._successGeneratePic($target, resp);
              clearInterval(self.intervalId);
            }
          });
        }, 3000);
      } else {
        $target.button('reset');
        notify('danger', Translator.trans('meterial_lib.generate_screenshots_error_hint'));
      }

    }).fail(function() {
      $target.button('reset');
      notify('danger', Translator.trans('meterial_lib.generate_screenshots_error_hint'));
    });
  }
  onSubmitCoverForm(event) {
    var $target = $(event.currentTarget);
    $target.find('#save-btn').button('loading');
    if ($target.find('#thumbNo').val()) {
      $.ajax({
        type: 'POST',
        url: $target.attr('action'),
        data: $target.serialize()
      }).done(function() {
        notify('success', Translator.trans('site.save_success_hint'));
      }).fail(function() {
        notify('danger', Translator.trans('site.save_error_hint'));
      }).always(function() {
        $target.find('#save-btn').button('reset');
      });
    } else {
      notify('success', Translator.trans('site.save_success_hint'));
      $target.find('#save-btn').button('reset');
    }
    event.preventDefault();
  }
  destroy() {
    clearInterval(this.intervalId);
  }
  _initPlayer() {
    var self = this;
    if ($('#viewerIframe').length > 0) {
      $('#viewerIframe');
      var messenger = new EsMessenger({
        name: 'parent',
        project: 'PlayerProject',
        children: [document.getElementById('viewerIframe')],
        type: 'parent'
      });

      // messenger.on("ready", function() {
      //   self.player = window.frames["viewerIframe"].contentWindow.BalloonPlayer;
      // });

      messenger.on('video.timeupdate', function(data) {
        self.second = Math.floor(data.currentTime);
      });
    }
  }
  _successGeneratePic($btn, resp) {
    $btn.button('reset');
    notify('success', Translator.trans('meterial_lib.generate_screenshots_success_hint'));
    var $coverTab = $btn.closest('#cover-tab');
    $coverTab.find('.js-cover-img').attr('src', resp.url);
    $coverTab.find('#thumbNo').val(resp.no);
  }
  _changePane($target) {
    $target.closest('.nav').find('li.active').removeClass('active');
    $target.addClass('active');

    var $tabcontent = $('.tab-content-img');
    $tabcontent.find('.tab-pane-img.active').removeClass('active');
    $tabcontent.find($target.data('target')).addClass('active');
  }
}
