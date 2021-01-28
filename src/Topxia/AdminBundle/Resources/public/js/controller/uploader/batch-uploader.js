define(function(require, exports, module) {
  require('webuploader2');
  var store = require('store');
  var filesize = require('filesize');
  var Widget = require('widget');
  var _ = require('underscore');
  var Notify = require('common/bootstrap-notify');

  var BatchUploader = Widget.extend({

    uploader: null,

    attrs: {
      initUrl: null,
      finishUrl: null,
      uploadUrl: null,
      uploadProxyUrl: null,
      accept: null,
      process: 'none',
      uploadToken: null,
      multi: true,
      fileSingleSizeLimit: null,
      hookRegisted: false
    },

    _onUploadStop: function(event) {
      var self = this;

      //过滤出未完成的文件并做处理
      this.element.find('li').filter(function() {
        var fileId = $(this).attr('id');
        var file = self.uploader.getFile(fileId);
        return file !== undefined && 'complete' !== file.getStatus();
      }).each(function() {
        var file = self.uploader.getFile($(this).attr('id'));
        self.uploader.cancelFile(file.id);
        $(this).find('.file-status').html(Translator.trans('uploader.status.pausing'));
        $(this).find('.js-file-resume').removeClass('hidden');
        $(this).find('.js-file-pause').addClass('hidden');
      });
    },

    _onUploadResume: function(event) {
      var self = this;
      //过滤出未完成的文件并做处理
      this.element.find('li').filter(function() {
        var fileId = $(this).attr('id');
        var file = self.uploader.getFile(fileId);
        return file !== undefined && 'complete' !== file.getStatus();
      }).each(function() {
        var file = self.uploader.getFile($(this).attr('id'));
        $(this).find('.file-status').html(Translator.trans('uploader.status.wait'));
        $(this).find('.js-file-resume').addClass('hidden');
        file.getStatus() === 'cancelled' && file.setStatus('queued');
      });
      this.uploader.upload();
    },

    _onFileUploadResume: function(event) {
      var fileId = $(event.target).parents('li.file-item').attr('id');
      var file = this.uploader.getFile(fileId);
      $(event.target).addClass('hidden');
      $(event.target).siblings('.js-file-pause').removeClass('hidden');
      file !== undefined && file.getStatus() === 'cancelled' && file.setStatus('interrupt');
      this.uploader.upload(fileId);
      //this._displaySpeed();
    },

    _onFileUploadStop: function(event) {
      var $li = $(event.target).parents('li.file-item');
      var fileId = $li.attr('id');
      var file = this.uploader.getFile(fileId);
      $(event.target).addClass('hidden');
      if (file !== undefined && file.getStatus() !== 'complete' && file.getStatus() !== 'error') {
        $(event.target).siblings('.js-file-resume').removeClass('hidden');
        $li.find('.file-status').html(Translator.trans('uploader.status.pausing'));
        this.uploader.cancelFile(fileId);
      }
      //this._displaySpeed();
    },

    _onFileUploadRemove: function(event) {
      // 本来应该uploader监听fileDequeued事件来删除DOM节点, 但是uploader stop api的问题导致目前暂停其实用的是cancelFile, 该API会触发该事件;
      var $li = $(event.target).parents('li.file-item');
      var fileId = $li.attr('id');
      var file = this.uploader.getFile(fileId);
      this.trigger('file.remove', file);
      if (file !== undefined) {
        this.uploader.removeFile(fileId, true);
      }

      $li.remove();

      delete this.uploader.totalSpeedQueue[fileId];
      delete this.uploader.leftTotalSizeQueue[fileId];

      if (jQuery.isEmptyObject(this.uploader.leftTotalSizeQueue)) {
        $('.ballon-uploader-display-footer').addClass('hidden');
      }
    },

    _makeAccept: function() {
      var mimeTypes = require('edusoho.mimetypes');
      var accept = {};
      accept.title = 'file';
      accept.extensions = this.get('accept')['extensions'].join(',');
      accept.mimeTypes = Array.prototype.concat.apply([], _.map(this.get('accept')['extensions'], mimeTypes)); // 二维数组降维到一维数组
      return accept;
    },

    setup: function() {
      this._initUI();
      var accept = this._makeAccept();

      var defaults = {
        runtimeOrder: 'html5,flash',

        dnd: this.element.find('.balloon-uploader-body'),
        accept: accept,
        fileSingleSizeLimit: this.get('fileSingleSizeLimit'),

        // 不压缩image
        resize: false,

        // swf文件路径
        swf: '/assets/libs/webuploader/0.1.5/Uploader.swf',

        // 选择文件的按钮。可选。
        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
        pick: this.element.find('.file-pick-btn'),
        threads: 1,
        chunkRetry: 5,
        isBatchUploader: true,
        formData: {}
      };

      if (!this.get('multi')) {
        defaults['fileNumLimit'] = 1;
      }
      this._initUploaderHook();
      if (!WebUploader.Uploader.support()) {
        alert(Translator.trans('uploader.browser.not_support'));
        throw new Error('WebUploader does not support the browser you are using.');
      }
      var uploader = this.uploader = WebUploader.create(defaults);

      this._registerUploaderEvent(uploader);

      var self = this;
      this.element.find('.start-upload-btn').on('click', function() {
        uploader.upload();
        $(self.element).find('.pause-btn').prop('disabled', false);
      });

      //解决IE8下JS解释器不支持Function类型的bind方法;
      $(this.element).on('click', '.js-upload-pause', _.bind(this._onUploadStop, this));
      $(this.element).on('click', '.js-upload-resume', _.bind(this._onUploadResume, this));
      $(this.element).on('click', '.js-file-resume', _.bind(this._onFileUploadResume, this));
      $(this.element).on('click', '.js-file-pause', _.bind(this._onFileUploadStop, this));
      $(this.element).on('click', '.js-file-cancel', _.bind(this._onFileUploadRemove, this));

    },

    destroy: function() {
      if (this.uploader) {
        this.uploader.stop();
        this.uploader.destroy();
      }
      this.element.undelegate();
      BatchUploader.superclass.destroy.call(this);
    },

    _initUI: function() {
      var html = '';
      html += '<div class="balloon-uploader-heading">' + Translator.trans('uploader.modal.heading') + '</div>';
      html += '<div class="balloon-uploader-body">';
      html += '  <div class="balloon-nofile">' + Translator.trans('uploader.modal.body.tips') + '</div>';
      html += '  <div class="balloon-filelist">';
      html += '    <div class="balloon-filelist-heading">';
      html += '    <div class="file-name">' + Translator.trans('uploader.filelist.head.filename') + '</div>';
      html += '    <div class="file-size">' + Translator.trans('uploader.filelist.head.size') + '</div>';
      html += '    <div class="file-status">' + Translator.trans('uploader.filelist.head.status') + '</div>';
      html += '    <div class="file-manage">' + Translator.trans('uploader.filelist.head.operation') + '</div>';
      html += '  </div>';
      html += '  <ul></ul>';
      html += '</div>';
      html += '<div class="balloon-uploader-footer">';
      html += '  <div class="pull-left mtm">';
      html += '    <span class="upload-finish"></span>';
      html += '      <span class="ballon-uploader-display-footer hidden">';
      html += '      <span><strong class="js-speed">0</strong> MB/s</span>';
      html += '      <span class="js-left-time"></span>';
      html += '    </span>';
      html += '  </div>';
      html += '  <div class="pause-btn js-upload-pause btn btn-default hidden">' + Translator.trans('uploader.btn.batch_paused') + '</div>';
      html += '  <div class="pause-btn js-upload-resume btn btn-default hidden">' + Translator.trans('uploader.btn.batch_continue') + '</div>';
      html += '  <div class="file-pick-btn"><i class="glyphicon glyphicon-plus"></i>' + Translator.trans('uploader.btn.add') + '</div>';

      if (this.get('multi')) {
        html += '<div class="start-upload-btn"><i class="glyphicon glyphicon-upload"></i>' + Translator.trans('uploader.btn.begin') + '</div>';
      }

      html += '</div>';

      this.element.addClass('balloon-uploader');
      this.element.html(html);
    },


    _registerUploaderEvent: function(uploader) {
      var self = this;
      var $uploader = this.element;
      // 当有文件添加进来的时候
      uploader.on('fileQueued', function(file) {
        $uploader.find('.balloon-nofile').remove();
        var $list = $uploader.find('.balloon-filelist ul');

        $list.append(
          '<li id="' + file.id + '" class="file-item">' +
          '  <div class="file-name">' + file.name + '</div>' +
          '  <div class="file-size">' + filesize(file.size) + '</div>' +
          '  <div class="file-status">' + Translator.trans('uploader.status.wait') + '</div>' +
          '  <div class="file-manage">' +
          '    <i class="js-file-resume btn btn-xs glyphicon glyphicon-play hidden"></i>' +
          '    <i class="glyphicon glyphicon-pause js-file-pause btn btn-xs hidden"></i>' +
          '    <i class="glyphicon glyphicon-remove js-file-cancel btn btn-xs"></i>' +
          '  </div>' +
          '  <div class="file-progress"><div class="file-progress-bar" style="width: 0%;"></div></div>' +
          '</li>'
        );

        self.trigger('file.queued', file);

        if (!self.get('multi')) {
          uploader.upload();
        }

      });
      uploader.on('error', function(handler) {
        switch (handler) {
          case 'F_EXCEED_SIZE':
            Notify.danger(Translator.trans('uploader.size_limit_hint'));
            break;
          case 'Q_EXCEED_NUM_LIMIT':
            Notify.danger(Translator.trans('uploader.num_limit_hint'));
            break;
          case 'Q_TYPE_DENIED':
            Notify.danger(Translator.trans('uploader.type_denied_limit_hint'));
            break;
        }
      });
      uploader.on('beforeFileQueued', function(file) {
        file.uploaderWidget = self;

        /*if ($('.ballon-uploader-display-footer').hasClass('hidden')) {
         $('.upload-finish').text('');
         $('.js-left-time').text('');
         $('.js-speed').text(0);
         $('.ballon-uploader-display-footer').removeClass('hidden');
         $('.upload-finish').addClass('hidden');
         }*/
        this.uploadQueue = this.uploadQueue || {}; //存储队列中文件开始上传的信息
        this.totalSpeedQueue = this.totalSpeedQueue || {}; //当前上传的总数的 |单位 MB/s
        this.leftTotalSizeQueue = this.leftTotalSizeQueue || {}; //上传剩余的总文件大小 |单位 MB
        this.updateDisplayIndex = 0; //自带的进度条更新的太快了,速度也刷新的有点快, 所以计时器增加到5,然后刷新一下,同时重置为0
      });

      uploader.on('uploadStart', function(file) {
        this.uploadQueue[file.id] = {
          id: file.id,
          size: file.size,
          starttime: _.now()
        };
        self.trigger('file.uploadStart');
      });
      // 文件上传过程中创建进度条实时显示。
      uploader.on('uploadProgress', function(file, percentage) {

        var queuefile = this.uploadQueue[file.id]; //获取文件开始上传时的信息

        var speed = (((queuefile.size * percentage) / 1024 / 1024) / ((_.now() - queuefile.starttime) / 1000)).toFixed(2); //MB/s

        this.totalSpeedQueue[file.id] = speed; //纪录每个文件的的上传速度
        this.leftTotalSizeQueue[file.id] = (file.size * (1 - percentage) / 1024 / 1024).toFixed(2); //更新每个文件的剩余大小

        this.updateDisplayIndex++;
        if (this.updateDisplayIndex == 1 || this.updateDisplayIndex >= 60 || file.size <= 262144) { //256KB
          this.updateDisplayIndex = 0;
          //file.uploaderWidget._displaySpeed()
        }

        var $li = $('#' + file.id);
        var percentageStr = (percentage * 100).toFixed(2) + '%';
        var lastPercentage = $li.data('currentPercent');
        var strategy = self.get('strategy');
        if (percentageStr != '100.00%' && strategy.needDisplayPercent(lastPercentage, percentage)) {
          $li.data('currentPercent', percentage);
          $li.find('.file-status').html(percentageStr);
          $li.find('.file-progress-bar').css('width', percentageStr);
        }
      });

      uploader.on('uploadSuccess', function(file) {
        var $li = $('#' + file.id);
        $li.find('.file-status').html(Translator.trans('uploader.status.finished'));
        $li.find('.file-progress-bar').css('width', '0%');
        $li.find('.js-file-resume').addClass('hidden');
        $li.find('.js-file-pause').addClass('hidden');
        $li.find('.js-file-cancel').addClass('hidden');
        var key = 'file_' + file.hash;
        store.remove(key);
      });

      uploader.on('beforeFileQueued', function(file) {
        file.uploaderWidget = self;
      });

      uploader.on('uploadComplete', function(file) {});

      uploader.on('uploadAccept', function(object, ret) {
        var key = 'file_' + object.file.hash;
        var value = store.get(key);
        value[object.chunk] = ret;
        store.set(key, value);

        var strategy = self.get('strategy');
        strategy.uploadAccept(object, ret);
      });

      uploader.on('uploadBeforeSend', function(object, data, headers, tr) {
        var strategy = self.get('strategy');
        strategy.uploadBeforeSend(object, data, headers, tr);
      });

      uploader.on('upload.finish', function(file) {
        delete this.totalSpeedQueue[file.id];
        delete this.leftTotalSizeQueue[file.id];
        var uploadStates = uploader.getStats();

        /*if ($.isEmptyObject(this.leftTotalSizeQueue) && uploadStates.cancelNum == 0) {
         $('.upload-finish').removeClass('hidden').text('上传已完成');
         $('.ballon-uploader-display-footer').addClass('hidden');
         }*/
      });
    },

    _getDirectives: function(file) {
      var extOutputs = {
        'mp4': 'video',
        'avi': 'video',
        'flv': 'video',
        'wmv': 'video',
        'mov': 'video',
        'rmvb': 'video',
        'vob': 'video',
        'mpg': 'video',
        'f4v': 'video',
        'mkv': 'video',
        'm4v': 'video',
        'doc': 'document',
        'docx': 'document',
        'pdf': 'document',
        'xls': 'document',
        'xlsx': 'document',
        'ppt': 'ppt',
        'pptx': 'ppt',
        'mp3': 'audio'
      };

      var paramsDefault = {
        'video': {
          videoQuality: 'normal',
          audioQuality: 'normal'
        },
        'document': {
          type: 'html'
        },
        'ppt': {},
        'audio': {
          videoQuality: 'normal',
          audioQuality: 'normal'
        }
      }

      var params = {};

      var extOutput = extOutputs[file.ext.toLocaleLowerCase()];
      if (extOutput == 'video') {
        if (this.get('process') == 'none' || this.get('process') == 'auto') {
          params = paramsDefault[extOutput];
        } else if (this.get('process') instanceof Object) {
          params = this.get('process');
        }
      }

      if (extOutput == 'document' || extOutput == 'ppt') {
        params = paramsDefault[extOutput];
      }

      params.output = extOutput;

      return params;
    },

    _getUploader: function() {
      return this.uploader;
    },

    _initUploaderHook: function() {
      if (WebUploader.Uploader.hookRegisted) {
        return;
      } else {
        WebUploader.Uploader.hookRegisted = true;
      }


      WebUploader.Uploader.register({
        'before-send-file': 'preupload',
        'before-send': 'checkchunk',
        'after-send-file': 'finishupload',
      }, {
        preupload: function(file) {
          //不是批量上传组件的uploader直接退出
          if (!('isBatchUploader' in this.owner.options)) {
            return;
          }

          var deferred = WebUploader.Deferred();
          file.uploaderWidget.trigger('preupload', file);
          file.uploaderWidget._makeFileHash(file).done(function(hash) {

            file.hash = hash;
            var params = {
              name: file.name,
              fileSize: file.size,
              hash: hash,
              directives: file.uploaderWidget._getDirectives(file)
            }

            $.support.cors = true;

            var key = 'file_' + file.hash;
            var value = store.get(key);

            if (value && value.id) {
              params.id = value.id;
            }

            $.post(file.uploaderWidget.get('initUrl'), params, function(response) {
              var key = 'file_' + file.hash;
              file.hashId = response.hashId;
              if (response.resumed != 'ok') {
                var value = {};
                value.id = response.outerId;
                value.response = response;
                store.set(key, value);
              }

              var value = store.get(key);
              if (value.response) {
                file.initResponse = value.response;
              }
              var uploadMode = file.uploaderWidget.getStrategyModel(response.uploadMode);
              file.uploaderWidget.set('uploadMode', uploadMode);

              var $li = $('#' + file.id);
              if (uploadMode !== undefined && uploadMode !== 'local') {
                $li.find('.js-file-pause').removeClass('hidden');
                file.uploaderWidget._showHiddenButton();
              }

              require.async('./' + uploadMode + '-strategy', function(Strategy) {
                var strategy = new Strategy(file, response);
                file.uploaderWidget.set('strategy', strategy);

                deferred.resolve();
              });
            }, 'json');
          });

          return deferred.promise();
        },

        checkchunk: function(block) {
          //不是批量上传组件的uploader直接退出
          if (!('isBatchUploader' in this.owner.options)) {
            return;
          }

          var deferred = WebUploader.Deferred();
          var key = 'file_' + block.file.hash;
          var resumedChunk = store.get(key);
          if (resumedChunk === undefined || resumedChunk[block.chunk] === undefined) {
            block.file.startUploading = true;
            deferred.resolve();
          } else {
            deferred.reject();
            var strategy = block.file.uploaderWidget.get('strategy');
            strategy.uploadAccept(block, resumedChunk[block.chunk]);
          }

          return deferred.promise();
        },

        finishupload: function(file, ret, hds) {
          //不是批量上传组件的uploader直接退出
          if (!('isBatchUploader' in this.owner.options)) {
            return;
          }

          var deferred = WebUploader.Deferred();
          var key = 'file_' + file.hash;
          var uploader = this.owner;
          store.remove(key);

          var strategy = file.uploaderWidget.get('strategy');
          var data = strategy.finishUpload(deferred, file);
          data.name = file.name;
          data.size = file.size;
          data.id = file.fileId;

          $.post(file.uploaderWidget.get('finishUrl'), data).done(function(response) {
            deferred.resolve();

            file.uploaderWidget.trigger('file.uploaded', file, data, response);

            file.setStatus('complete');

            var $li = $('#' + file.id);
            $li.find('.file-status').html(Translator.trans('uploader.progress.tips'));
            $li.find('.file-progress-bar').css('width', '0%');

            if (file.uploaderWidget.get('multi')) {
              file.uploaderWidget._getUploader().trigger('upload.finish', file, data);
            }

          }).fail(function() {
            var $li = $('#' + file.id);
            var html = Translator.trans('uploader.status.error') + "<a class='glyphicon glyphicon-question-sign text-muted' data-toggle='popover'>";
            $li.find('.file-status').html(html);
            $li.find('.file-progress-bar').css({
              'width': '100%',
              'background': '#f0c7bd'
            });
            $li.find('.js-file-pause').addClass('hidden');
            $li.find('[data-toggle="popover"]').popover({
              content: Translator.trans('uploader.error.bad_file.hint'),
              template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content" style="z-index: 5000"></div></div>',
              trigger: 'hover',
              placement: 'bottom',
              container: 'body'
            });
            uploader.removeFile(file.id, true);
          });

          return deferred.promise();
        }

      });

    },

    _makeFileHash: function(file) {
      var start1 = 0;
      var end1 = (file.size < 4096) ? file.size : 4096;
      var promise1 = this.uploader.md5File(file, start1, end1);

      var start2 = parseInt(file.size / 3);
      var end2 = ((start2 + 4096) > file.size) ? file.size : start2 + 4096;
      var promise2 = this.uploader.md5File(file, start2, end2);

      var start3 = parseInt(file.size / 3 * 2);
      var end3 = ((start3 + 4096) > file.size) ? file.size : start3 + 4096;
      var promise3 = this.uploader.md5File(file, start3, end3);

      var start4 = ((file.size - 4096) < 0) ? 0 : file.size - 4096;
      var end4 = file.size;
      var promise4 = this.uploader.md5File(file, start4, end4);

      var deferred = WebUploader.Deferred();
      WebUploader.when(promise1, promise2, promise3, promise4).done(function(hash1, hash2, hash3, hash4) {
        var hash = hash1.slice(0, 8);
        hash += hash2.slice(8, 16);
        hash += hash3.slice(16, 24);
        hash += hash4.slice(24, 32);
        deferred.resolve('cmd5|' + hash);

      });
      return deferred.promise();
    },

    _secondToDate: function(sd) {
      var time = isNaN(parseFloat(sd)) ? 0 : parseFloat(sd);
      if (null != time && "" != time) {
        if (time > 60 && time < 60 * 60) {
          time = parseInt(time / 60.0) + Translator.trans('site.date.minute') + parseInt((parseFloat(time / 60.0) -
            parseInt(time / 60.0)) * 60) + Translator.trans('site.date.second');
        } else if (time >= 60 * 60 && time < 60 * 60 * 24) {
          time = parseInt(time / 3600.0) + Translator.trans('site.date.hour') + parseInt((parseFloat(time / 3600.0) -
              parseInt(time / 3600.0)) * 60) + Translator.trans('site.date.minute') +
            parseInt((parseFloat((parseFloat(time / 3600.0) - parseInt(time / 3600.0)) * 60) -
              parseInt((parseFloat(time / 3600.0) - parseInt(time / 3600.0)) * 60)) * 60) + Translator.trans('site.date.second');
        } else {
          time = parseInt(time) + Translator.trans('site.date.second');
        }
      }
      return time;
    },

    _displaySpeed: function() {
      var totalspeed = 0;
      var leftsize = 0;
      var resumeFileCount = 0;

      for (var index in this.uploader.totalSpeedQueue) {
        var file = this.uploader.getFile(index);
        if (file.getStatus() == 'progress' || file.getStatus() == 'queued') {
          totalspeed += parseFloat(this.uploader.totalSpeedQueue[index]);
        } else {
          resumeFileCount++;
        }
      }

      for (var index in this.uploader.leftTotalSizeQueue) {
        leftsize += parseFloat(this.uploader.leftTotalSizeQueue[index]);
      }
      var uploadStats = this.uploader.getStats();
      $('.js-speed').text(totalspeed.toFixed(2));

      if (resumeFileCount == (this.uploader.getFiles().length - uploadStats.successNum)) {
        $('.js-left-time').text('');
      } else {
        var time = totalspeed == 0 ? 0 : this._secondToDate((leftsize / totalspeed));

        $('.js-left-time').text((time == 0) ? Translator.trans('uploader.status.will_done') : Translator.trans('uploader.status.remnant_time', { '%time%': time }));
      }
    },

    getStrategyModel: function(mode) {
      if (this.get('uploadMode') !== undefined) {
        return this.get('uploadMode');
      }

      if (mode == 'baidu' && (this.isIE(8) || this.isIE(9))) {
        return mode + "-direct";
      }
      return mode;
    },

    isIE: function(ver) {
      var b = document.createElement('b');
      b.innerHTML = '<!--[if IE ' + ver + ']><i></i><![endif]-->';
      return b.getElementsByTagName('i').length === 1
    },

    _showHiddenButton: function() {
      if (this.get('multi')) {
        this.element.find('.pause-btn').removeClass('hidden');
      }
    }
  });

  module.exports = BatchUploader;

});