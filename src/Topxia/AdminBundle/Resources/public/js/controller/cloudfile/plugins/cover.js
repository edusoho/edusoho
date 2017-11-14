define(function(require, exports, module) {
  var Widget = require('widget');
  var Notify = require('common/bootstrap-notify');
  var Messenger = require('../../player/messenger');

  var Cover = Widget.extend({
    attrs: {
      callback: ''
    },
    events: {
      'click .js-img-set': 'onClickChangePic',
      'click .js-reset-btn': 'onClickReset',
      'click .js-set-default': 'onClickDefault',
      'click .js-set-select': 'onClickSelect',
      'click .js-screenshot-btn': 'onClickScreenshot',
      'submit #cover-form': 'onSubmitCoverForm',
    },
    setup: function() {
      this._initPlayer();
    },
    onClickReset: function(event) {
      this.$('#thumbNo').val('');
      this.$('.js-cover-img').attr('src', this.$('#orignalThumb').val());
    },
    onClickDefault: function(event) {
      this._changePane($(event.currentTarget));
    },
    onClickSelect: function(event) {
      this._changePane($(event.currentTarget));
    },
    onClickScreenshot: function(event) {
      var $target = $(event.currentTarget);
      var self = this;
      var second = this.player.getCurrentTime();
      second = Math.floor(second);
      $target.button('loading');
      $.ajax({
        type: 'get',
        url: $target.data('url'),
        data: {
          'second': second
        }
      }).done(function(resp) {
        if (resp.status == 'success') {
          self._successGeneratePic($target, resp);
        } else if (resp.status == 'waiting') {
          //轮询
          self.intervalId = setInterval(function() {
            $.get($target.data('url'), {
              'second': second
            }, function(resp) {
              if (resp.status == 'success') {
                self._successGeneratePic($target, resp);
                clearInterval(self.intervalId);
              }
            });
          }, 3000);
        } else {
          $target.button('reset');
          Notify.danger(Translator.trans('生成截图失败！'));
        }

      }).fail(function() {
        $target.button('reset');
        Notify.danger(Translator.trans('生成截图失败！'));
      });

    },
    _successGeneratePic: function($btn, resp) {
      $btn.button('reset');
      Notify.success(Translator.trans('生成截图成功!'));
      var $coverTab = $btn.closest('#cover-tab');
      $coverTab.find('.js-cover-img').attr('src', resp.url);
      $coverTab.find('#thumbNo').val(resp.no);
    },
    _initPlayer: function() {
      var self = this;
      if (this.$('#viewerIframe').length > 0) {
        this.$('#viewerIframe');
        var messenger = new Messenger({
          name: 'parent',
          project: 'PlayerProject',
          children: [document.getElementById('viewerIframe')],
          type: 'parent'
        });

        messenger.on("ready", function() {
          self.player = window.frames["viewerIframe"].contentWindow.BalloonPlayer;
        });
      }

    },
    _changePane: function($target) {
      $target.closest('.nav').find('li.active').removeClass('active');
      $target.addClass('active');

      var $tabcontent = $('.tab-content-img');
      $tabcontent.find('.tab-pane-img.active').removeClass('active');
      $tabcontent.find($target.data('target')).addClass('active');

    },
    onSubmitCoverForm: function(event) {
      var $target = $(event.currentTarget);
      $target.find('#save-btn').button('loading');
      if ($target.find('#thumbNo').val()) {
        $.ajax({
          type: 'POST',
          url: $target.attr('action'),
          data: $target.serialize()
        }).done(function() {
          Notify.success(Translator.trans('保存成功！'));
        }).fail(function() {
          Notify.danger(Translator.trans('保存失败！'));
        }).always(function() {
          $target.find('#save-btn').button('reset');
        });
      } else {
        Notify.success(Translator.trans('保存成功！'));
        $target.find('#save-btn').button('reset');
      }

      event.preventDefault();
    },
    onClickChangePic: function(event) {
      var $target = $(event.currentTarget);
      var $coverTab = $target.closest('#cover-tab');
      $coverTab.find('.js-cover-img').attr('src', $target.attr('src'));
      $coverTab.find('#thumbNo').val($target.data('no'));
    },
    destroy: function() {
      clearInterval(this.intervalId);
    }
  });

  module.exports = Cover;

});