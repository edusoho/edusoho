define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  var Widget = require('widget');
  var ProgressBar = require('../course/ProgressBar');

  var CourseSetClone = Widget.extend({
    attrs: {
      checkCrontabStatusUrl: '',
      cloneByWebUrl: '',
      cloneByCrontabUrl: '',
    },


    setup: function () {
    },

    doClone: function (courseSetId,title) {
      var self = this;
      self._doWebClone(courseSetId,title);
      // this._isCrontabEnabled().then(function (crontabStatus) {
      //   if (crontabStatus.enabled) {
      //     self._doCrontabClone(courseSetId,title);
      //   } else {
      //     self._doWebClone(courseSetId,title);
      //   }
      // });
    console.log('run');
    },

    _makeProgressBar: function () {
      return '<div class="modal-dialog ">'
            +   '<div class="modal-content">'
            +     '<div class="modal-header">'
            +       '<h4 class="modal-title">正在复制课程'
            +       '</h4>'
            +     '</div>'
            +     '<div class="modal-body">'
            +       '<div id="clone-progress" class="package-update-progress">'
            +         '<div class="progress progress-striped active">'
            +           '<div class="progress-bar progress-bar-success" style="width: 0%"></div>'
            +         '</div>'
            +         '<div class="color-success progress-text"></div>'
            +       '</div>'
            +     '</div>'
            +   '</div>'
            + '</div>';
    },

    _doWebClone: function (courseSetId,title) {
      $('#modal').html(this._makeProgressBar()).modal();
      var progressbar = new ProgressBar({
        element: '#clone-progress'
      });

      var webClonePromise = new Promise(function (resolve, reject) {
        $.ajax({
          type: "POST",
          data: {
            'title': title
          },
          beforeSend: function (request) {
            request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
            request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
          },
          url: '/admin/course_set/'+courseSetId+'/clone_by_web',
          success: function (resp) {
            resolve(1);
          },
          error: function (jqXHR) {
            reject(jqXHR);
          }
        });
      });

      var progress = 0;
      var intervalId = setInterval(function () {
        progress++;
        if (progress <= 99) {
          progressbar.setProgress(progress, '进度：'+progress+'%');
        }

      }, 200);

      webClonePromise.then(function () {
        progressbar.setProgress(100, '复制完成!');
        clearInterval(intervalId);
        window.location.reload();
      }).catch(function (jqXHR) {
        console.log(jqXHR);
        if (jqXHR.status === 504) {
          Notify.danger('复制课程超时了，请使用任务调度的方式复制课程', 10);
        } else {
          Notify.danger('复制课程发生了错误', 10);
        }

        clearInterval(intervalId);
      });
      
      
    },

    _doCrontabClone: function (courseSetId,title) {
      $.ajax({
        type: "POST",
        data: {
            'title': title
        },
        beforeSend: function (request) {
          request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
          request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
        },
        url: '/admin/course_set/'+courseSetId+'/clone_by_crontab',
        success: function (resp) {
          if (resp.success) {
            Notify.info(resp.msg);
          } else {
            Notify.warning(resp.msg);
          }

        }
      });
    },

    _isCrontabEnabled: function () {
      return Promise.resolve(
        $.ajax({
          type: "GET",
          beforeSend: function (request) {
            request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
            request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
          },
          url: '/api/crontab/status'
        })
      );
    }

  });

  module.exports = CourseSetClone;
});