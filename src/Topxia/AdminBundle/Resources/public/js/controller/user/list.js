define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    var validator = require('bootstrap.validator');
    exports.run = function() {
        var $datePicker = $('#datePicker');
        var $table = $('#user-table');

        $table.on('click', '.lock-user, .unlock-user', function() {
            var $trigger = $(this);

            if (!confirm(Translator.trans('admin.user.lock_operational_hint',{title:$trigger.attr('title')}))) {
                return;
            }

            $.post($(this).data('url'), function(html) {
                Notify.success(Translator.trans('admin.user.lock_operational_success_hint',{title:$trigger.attr('title')}));
                var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            }).error(function(e, textStatus, errorThrown) {
                var $json = jQuery.parseJSON(e.responseText);
                if($json.error.message){
                    Notify.danger(Translator.trans($json.error.message));
                }else{
                    Notify.danger(Translator.trans('admin.user.lock_operational_fail_hint',{title:$trigger.attr('title')}));
                }
            });
        });

      $table.on('click', '.delete-user', function() {
        var $trigger = $(this);
        // var performDelete = function() {
        //   $.post($trigger.data('url'), function(result) {
        //     Notify.success(Translator.trans('admin.user.lock_operational_success_hint',{title:$trigger.attr('title')}));
        //     window.location.reload();
        //   }).error(function(e, textStatus, errorThrown) {
        //     var $json = jQuery.parseJSON(e.responseText);
        //     if($json.error.message){
        //       Notify.danger(Translator.trans($json.error.message));
        //     }else{
        //       Notify.danger(Translator.trans('admin.user.lock_operational_fail_hint',{title:$trigger.attr('title')}));
        //     }
        //   });
        // };

        // 第一步：先进行常规确认
        if (!confirm(Translator.trans('admin.user.lock_operational_hint_new',{title:$trigger.attr('title')}))) {
          return;
        }

        // 第二步：确认后检查是否需要二次验证
        // if ($('#needValidate').val() == 1) {
          var $modal = $('#modal');
          $modal.load('/secondary/verification?exportFileName=deleteUser&targetFormId=1&url='+$trigger.data('url')+'&title='+$trigger.attr('title'), function() {
            $modal.modal('show');

            // 监听验证成功事件（需要与后端验证逻辑配合）
            $modal.off('verification.success').on('verification.success', function() {
              $modal.modal('hide');
              // performDelete();
            });
          });
        // } else {
        //   // 无需二次验证直接执行
        //   performDelete();
        // }
      });

        // $table.on('click', '.delete-user', function() {
        //     var $trigger = $(this);
        //   if ($('#needValidate').val() == 1) {
        //     console.log('&&&&&&&&&&&&&&');
        //     // const verificationResponse = fetch(`/secondary/verification?exportFileName=courseStudent&targetFormId=1`;
        //     // const html = await verificationResponse.text();
        //     const $modal = $('#modal');
        //     const html = 'abcddd';
        //     // $modal.html(html).modal('show');
        //     // $modal.html(html).modal('show');
        //     $modal.load('/secondary/verification?exportFileName=courseStudent&targetFormId=1');
        //     $modal.modal('show');
        //     return;
        //   }
        //     if (!confirm(Translator.trans('admin.user.lock_operational_hint_new',{title:$trigger.attr('title')}))) {
        //         return;
        //     }
        //
        //     $.post($(this).data('url'), function(result) {
        //         Notify.success(Translator.trans('admin.user.lock_operational_success_hint',{title:$trigger.attr('title')}));
        //         window.location.reload();
        //     }).error(function(e, textStatus, errorThrown) {
        //         var $json = jQuery.parseJSON(e.responseText);
        //         if($json.error.message){
        //             Notify.danger(Translator.trans($json.error.message));
        //         }else{
        //             Notify.danger(Translator.trans('admin.user.lock_operational_fail_hint',{title:$trigger.attr('title')}));
        //         }
        //     });
        // });

        $table.on('click', '.send-passwordreset-email', function() {
            Notify.info(Translator.trans('admin.user.sending_passwordreset_email_hint'), 60);
            $.post($(this).data('url'), function(response) {
                Notify.success(Translator.trans('admin.user.password_reset_email_send_success_hint'));
            }).error(function() {
                Notify.danger(Translator.trans('admin.user.password_reset_email_send_fail_hint'));
            });
        });

        $table.on('click', '.send-emailverify-email', function() {
            Notify.info(Translator.trans('admin.user.sending_email_verify_email_hint'), 60);
            $.post($(this).data('url'), function(response) {
                Notify.success(Translator.trans('admin.user.email_verify_email_send_success_hint'));
            }).error(function() {
                Notify.danger(Translator.trans('admin.user.email_verify_email_send_fail_hint'));
            });
        });

        var $userSearchForm = $('#user-search-form');

        $('#user-export').on('click', function() {
            var self = $(this);
            var data = $userSearchForm.serialize();
            self.attr('data-url', self.attr('data-url') + "?" + data);
        });

        $("#startDate").datetimepicker({
            autoclose: true,
        }).on('changeDate', function() {
            $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));
        });

        $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));

        $("#endDate").datetimepicker({
            autoclose: true,
        }).on('changeDate', function() {

            $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));
        });

        $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));

    };

});