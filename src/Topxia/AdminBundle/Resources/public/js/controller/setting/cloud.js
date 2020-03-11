define(function(require, exports, module) {
    require('echarts');

    var Notify = require('common/bootstrap-notify');
    var Uploader = require('upload');
    var BatchUploader = require('../uploader/batch-uploader');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        $("[data-toggle='popover']").popover();

        var mobileSupportVal = $('input[name="support_mobile"]:checked').val();

        var $form = $("#cloud-setting-form");
        var uploader = new Uploader({
            trigger: '#cloud-video-watermark-upload',
            name: 'watermark',
            action: $('#cloud-video-watermark-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/png,image/jpg,image/jpeg,imge/bmp,image/gif',
            error: function(file) {
                Notify.danger(Translator.trans('admin.setting.cloud.upload_cloud_video_watermark_error_hint'));
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#cloud-video-watermark-container").html('<img src="' + response.url + '">');
                $form.find('[name=video_watermark_image]').val(response.path);
                $("#cloud-video-watermark-remove").show();
                Notify.success(Translator.trans('admin.setting.cloud.upload_cloud_video_watermark_success_hint'));
            }
        });

        $("#cloud-video-watermark-remove").on('click', function(){
            if (!confirm(Translator.trans('admin.setting.cloud.delete_cloud_video_watermark_hint'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#cloud-video-watermark-container").html('');
                $form.find('[name=video_watermark_image]').val('');
                $btn.hide();
                Notify.success(Translator.trans('admin.setting.cloud.delete_cloud_video_watermark_success_hint'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.setting.cloud.delete_cloud_video_watermark_fail_hint'));
            });
        });

        var uploader = new Uploader({
            trigger: '#cloud-video-embed-watermark-upload',
            name: 'watermark',
            action: $('#cloud-video-embed-watermark-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/png,image/jpg,image/jpeg,imge/bmp,image/gif',
            error: function(file) {
                Notify.danger(Translator.trans('admin.setting.cloud.upload_cloud_video_embed_watermark_fail_hint'));
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#cloud-video-embed-watermark-container").html('<img src="' + response.url + '">');
                $form.find('[name=video_embed_watermark_image]').val(response.path);
                $("#cloud-video-embed-watermark-remove").show();
                Notify.success(Translator.trans('admin.setting.cloud.upload_cloud_video_embed_watermark_success_hint'));
            }
        });
        $("#cloud-video-embed-watermark-remove").on('click', function(){
            if (!confirm(Translator.trans('admin.setting.cloud.delete_cloud_video_embed_watermark_hint'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#cloud-video-embed-watermark-container").html('');
                $form.find('[name=video_embed_watermark_image]').val('');
                $btn.hide();
                Notify.success(Translator.trans('admin.setting.cloud.delete_cloud_video_embed_watermark_success_hint'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.setting.cloud.delete_cloud_video_embed_watermark_fail_hint'));
            });
        });


        $('.video-watermark-property-tips')
          .popover({
            html: true,
            placement: 'top',
            trigger: 'hover',
            animation:true,
            container:'body'
        });

       if ($('.upload-mode').length >0 ) {
            var $el = $('#balloon-uploader');


            if(!$el.is(":hidden")){
                initUploader()
                $el.data('init', true);
            }

            $(".edit-btn",".head-leader-edit").on('click', function(){
                $(".file-chooser-main").show();
                $(".head-leader-edit").hide();

                if(!$el.data('init')){
                    initUploader();
                }
            });
        }

        function initUploader()
        {
            var $el = $('#balloon-uploader');
            var uploader = new BatchUploader({
                element: $el,
                initUrl: $el.data('initUrl'),
                finishUrl: $el.data('finishUrl'),
                uploadAuthUrl: $el.data('uploadAuthUrl'),
                multi: false
            });

            uploader.on('file.uploaded', function(file){
                if (file) {
                    $(".head-leader-edit").find('[data-role=placeholder]').html(file.name);
                    $(".file-chooser-main").hide();
                    $(".head-leader-edit").show();
                }
            });
        }

        var validator = new Validator({
          element: $form,
          autoSubmit: false,
          onFormValidated: function(error, results, $form) {
            if (error) {
              return false;
            }

            var updateSupportMobile = $('input[name="support_mobile"]:checked').val();

            if (mobileSupportVal == updateSupportMobile) {
                $('#cloud-video-form-btn').button('submiting').addClass('disabled');
                submitForm();
            } else {
              if (updateSupportMobile == 1) {
                $('#change-normal-modal').modal('show');
              } else {
                $('#delete-video-modal').modal('show');
              } 
              return false;
            }
          }
        });

        validator.addItem({
            element: '[name="video_fingerprint_time"]',
            required: true,
            rule: 'decimal min{min:0.1} max{max:100}',
            display:Translator.trans('admin.setting.cloud.validate_old.video_fingerprint_time.display')
        });

      validator.addItem({
        element: '[name="video_fingerprint_opacity"]',
        required: true,
        rule: 'decimal min{min:0} max{max:1}',
        display:Translator.trans('admin.setting.cloud.validate_old.video_fingerprint_opacity.display')
      });

        $('input[name="video_fingerprint"]').change(function(){
            if($(this).val() == 1) {
                validator.addItem({
                    element: '[name="video_fingerprint_time"]',
                    required: true,
                    rule: 'decimal min{min:0.1} max{max:100}',
                    display:Translator.trans('admin.setting.cloud.validate_old.video_fingerprint_time.display')
                });
            } else {
                validator.removeItem('[name="video_fingerprint_time"]');
            }
        })

        $('input[name="support_mobile"]').change(function(){
          var val = $(this).val();
          if (val == 1) {
            $('.js-normal-mode-tip').removeClass('hidden');
            $('.js-encryption-mode-tip').addClass('hidden');
            $('.js-delete-video-btn').hide();
          } else {
            $('.js-normal-mode-tip').addClass('hidden');
            $('.js-encryption-mode-tip').removeClass('hidden');
            $('.js-delete-video-btn').show();
          }
        })

        $('#delete-video-modal').on('click', 'button', function(){
          var isDelete = $(this).data('delete');
          
          $form.find('input[name="isDeleteMP4"]').val(isDelete);
          submitForm();
        })

        $('#change-normal-modal').on('click', '.js-confirm-submit', function(response){
          submitForm();
        })

        function submitForm()
        {
          $.post($form.attr('action'), $form.serialize(), function(response){
            window.location.reload();
          })
        }

    }

})