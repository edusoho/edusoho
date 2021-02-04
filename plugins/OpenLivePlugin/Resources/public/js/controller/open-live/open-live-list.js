define(function(require, exports, module) {
  require("jquery.bootstrap-datetimepicker");
  var Notify = require('common/bootstrap-notify');

  var dateTimePickerCreate = function (startElementName, endElementName) {
    let startDateInput = $("#"+startElementName);
    let endDateInput = $("#"+endElementName);
    startDateInput.datetimepicker({
      autoclose: true,
    }).on('changeDate', function() {
      endDateInput.datetimepicker('setStartDate', startDateInput.val().substring(0, 16));
    });
    startDateInput.datetimepicker('setEndDate', endDateInput.val().substring(0, 16));

    endDateInput.datetimepicker({
      autoclose: true,
    }).on('changeDate', function() {
      startDateInput.datetimepicker('setEndDate', endDateInput.val().substring(0, 16));
    });
    endDateInput.datetimepicker('setStartDate', startDateInput.val().substring(0, 16));
  };

  exports.run = function() {
    dateTimePickerCreate('planStartDateST', 'planStartDateED');
    dateTimePickerCreate('planEndDateST', 'planEndDateED');
    dateTimePickerCreate('realStartDateST', 'realStartDateED');
    dateTimePickerCreate('realEndDateST', 'realEndDateED');

    var $table = $('#open-live-table');
    $table.on('click', '.js-open-live-edit', function () {
      window.open($(this).data('url'));
    });
    $table.on('click', '.js-open-live-publish', function () {
      if (!confirm(Translator.trans('您确认要取布该公开课吗？'))) return;
      $.post($(this).attr('data-url'), function (res) {
        if (true === res.data.success) {
          let tr = $('#open-live-table-tr-'+res.liveRoomId);
          tr.find('td').eq('8').html("<span class='color-success'>已发布</span>");
          let tdBtn = tr.find('.js-open-live-publish');
          tdBtn.attr('class', 'js-open-live-unpublish');
          tdBtn.attr('data-url', '/admin/v2/open_live/'+res.liveRoomId+'/unpublish');
          tdBtn.html("<span class='glyphicon glyphicon-ban-circle'></span> 取消发布");
          Notify.success(Translator.trans('公开课发布成功'));
          tr.find('.js-open-live-delete').hide();
          return;
        }
        Notify.danger(Translator.trans('服务错误，公开课发布失败'));
      });
    });
    $table.on('click', '.js-open-live-unpublish', function () {
      if (!confirm(Translator.trans('您确认要取消发布该公开课吗？'))) return;
      $.post($(this).attr('data-url'), function (res) {
        if (true === res.data.success) {
          let tr = $('#open-live-table-tr-'+res.liveRoomId);
          tr.find('td').eq('8').html("<span>未发布</span>");
          let tdBtn = tr.find('.js-open-live-unpublish');
          tdBtn.attr('class', 'js-open-live-publish');
          tdBtn.attr('data-url', '/admin/v2/open_live/'+res.liveRoomId+'/publish');
          tdBtn.html("<span class='glyphicon glyphicon-ok-circle'></span> 发布");
          if (res.canDelete) {
            tr.find('.js-open-live-delete').show();
          } else {
            tr.find('.js-open-live-delete').hide();
          }
          Notify.success(Translator.trans('公开课取消发布成功'));
          return;
        }
        Notify.danger(Translator.trans('服务错误，公开课取消发布失败'));
      });
    });
    $table.on('click', '.js-open-live-close', function () {
      if (!confirm(Translator.trans('您确认要结束该公开课吗？'))) return;
      $.post($(this).data('url'), function (res) {
        if (true === res.data.success) {
          let tr = $('#open-live-table-tr-'+res.liveRoomId);
          tr.find('td').eq('7').html("<span class='color-danger'>已结束</span>");
          tr.find('.js-open-live-close').remove();
          let tdBtn = tr.find('.js-open-live-edit');
          tdBtn.remove();
          Notify.success(Translator.trans('公开课结束成功'));
          return;
        }
        Notify.danger(Translator.trans('服务错误，公开课结束失败'));
      });
    });
    $table.on('click', '.js-open-live-delete', function () {
      if (!confirm(Translator.trans('您确认要删除该公开课吗？'))) return;
      $.post($(this).data('url'), function (res) {
        if (true === res.data.success) {
          let tr = $('#open-live-table-tr-'+res.liveRoomId).remove();
          Notify.success(Translator.trans('公开课删除成功'));
          return;
        }
        if (false === res.data.success) {
          Notify.danger(Translator.trans(res.data.errorMsg));
          return;
        }
        Notify.danger(Translator.trans('服务错误，公开课结束删除失败'));
      });
    });
  }

});