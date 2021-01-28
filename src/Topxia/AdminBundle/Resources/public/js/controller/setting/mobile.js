define(function (require, exports, module) {

  var Notify = require('common/bootstrap-notify');
  var WebUploader = require('edusoho.webuploader');
  var Validator = require('bootstrap.validator');
  require('es-ckeditor');

  exports.run = function () {

    if ('0' == $('input[name=\'appDiscoveryVersion\']').val()) {
      $('#upgrade-modal').modal('show');
    }

    var $form = $('#mobile-form');

    var validator = new Validator({
      element: $form
    });

    if ($('input[name=\'bundleId\']').length) {
      validator.addItem({
        element: '[name="bundleId"]',
        required: true,
        display: Translator.trans('admin.setting.mobile.bundle_id'),
      });
    }

    if ($('#mobile-splash1-upload').length) {
      var uploader = new WebUploader({
        element: '#mobile-splash1-upload'
      });

      uploader.on('uploadSuccess', function (file, response) {
        var url = $('#mobile-splash1-upload').data('gotoUrl');
        $.post(url, response, function (data) {
          response = $.parseJSON(data);
          $('#mobile-splash1-container').html('<img src="' + response.url + '">');
          $form.find('[name=splash1]').val(response.path);
          $('#mobile-splash1-remove').show();
          Notify.success(Translator.trans('admin.setting.mobile.upload_startup_diagram_figure_1_success_hint'));
        });
      });

      $('#mobile-splash1-remove').on('click', function () {
        if (!confirm(Translator.trans('admin.setting.mobile.startup_diagram_delete_hint'))) return false;
        var $btn = $(this);
        $.post($btn.data('url'), function () {
          $('#mobile-splash1-container').html('');
          $form.find('[name=splash1]').val('');
          $btn.hide();
          Notify.success(Translator.trans('admin.setting.mobile.delete_startup_diagram_figure_1_success_hint'));
        }).error(function () {
          Notify.danger(Translator.trans('admin.setting.mobile.delete_startup_diagram_figure_1_fail_hint'));
        });
      });
    }

    if ($('#mobile-splash2-upload').length) {
      var uploader = new WebUploader({
        element: '#mobile-splash2-upload'
      });
      uploader.on('uploadSuccess', function (file, response) {
        var url = $('#mobile-splash2-upload').data('gotoUrl');
        $.post(url, response, function (data) {
          response = $.parseJSON(data);
          $('#mobile-splash2-container').html('<img src="' + response.url + '">');
          $form.find('[name=splash2]').val(response.path);
          $('#mobile-splash2-remove').show();
          Notify.success(Translator.trans('admin.setting.mobile.upload_startup_diagram_figure_2_success_hint'));
        });
      });

      $('#mobile-splash2-remove').on('click', function () {
        if (!confirm(Translator.trans('admin.setting.mobile.startup_diagram_delete_hint'))) return false;
        var $btn = $(this);
        $.post($btn.data('url'), function () {
          $('#mobile-splash2-container').html('');
          $form.find('[name=splash2]').val('');
          $btn.hide();
          Notify.success(Translator.trans('admin.setting.mobile.delete_startup_diagram_figure_2_success_hint'));
        }).error(function () {
          Notify.danger(Translator.trans('admin.setting.mobile.delete_startup_diagram_figure_2_fail_hint'));
        });
      });
    }

    if ($('#mobile-splash3-upload').length) {
      var uploader = new WebUploader({
        element: '#mobile-splash3-upload'
      });
      uploader.on('uploadSuccess', function (file, response) {
        var url = $('#mobile-splash3-upload').data('gotoUrl');
        $.post(url, response, function (data) {
          response = $.parseJSON(data);
          $('#mobile-splash3-container').html('<img src="' + response.url + '">');
          $form.find('[name=splash3]').val(response.path);
          $('#mobile-splash3-remove').show();
          Notify.success(Translator.trans('admin.setting.mobile.upload_startup_diagram_figure_3_success_hint'));
        });
      });

      $('#mobile-splash3-remove').on('click', function () {
        if (!confirm(Translator.trans('admin.setting.mobile.startup_diagram_delete_hint'))) return false;
        var $btn = $(this);
        $.post($btn.data('url'), function () {
          $('#mobile-splash3-container').html('');
          $form.find('[name=splash3]').val('');
          $btn.hide();
          Notify.success(Translator.trans('admin.setting.mobile.delete_startup_diagram_figure_3_success_hint'));
        }).error(function () {
          Notify.danger(Translator.trans('admin.setting.mobile.delete_startup_diagram_figure_3_fail_hint'));
        });
      });
    }

    if ($('#mobile-splash4-upload').length) {
      var uploader = new WebUploader({
        element: '#mobile-splash4-upload'
      });
      uploader.on('uploadSuccess', function (file, response) {
        var url = $('#mobile-splash4-upload').data('gotoUrl');
        $.post(url, response, function (data) {
          response = $.parseJSON(data);
          $('#mobile-splash4-container').html('<img src="' + response.url + '">');
          $form.find('[name=splash4]').val(response.path);
          $('#mobile-splash4-remove').show();
          Notify.success(Translator.trans('admin.setting.mobile.upload_startup_diagram_figure_4_success_hint'));
        });
      });

      $('#mobile-splash4-remove').on('click', function () {
        if (!confirm(Translator.trans('admin.setting.mobile.startup_diagram_delete_hint'))) return false;
        var $btn = $(this);
        $.post($btn.data('url'), function () {
          $('#mobile-splash4-container').html('');
          $form.find('[name=splash4]').val('');
          $btn.hide();
          Notify.success(Translator.trans('admin.setting.mobile.delete_startup_diagram_figure_4_success_hint'));
        }).error(function () {
          Notify.danger(Translator.trans('admin.setting.mobile.delete_startup_diagram_figure_4_fail_hint'));
        });
      });
    }

    if ($('#mobile-splash5-upload').length) {
      var uploader = new WebUploader({
        element: '#mobile-splash5-upload'
      });
      uploader.on('uploadSuccess', function (file, response) {
        var url = $('#mobile-splash5-upload').data('gotoUrl');
        $.post(url, response, function (data) {
          response = $.parseJSON(data);
          $('#mobile-splash5-container').html('<img src="' + response.url + '">');
          $form.find('[name=splash5]').val(response.path);
          $('#mobile-splash5-remove').show();
          Notify.success(Translator.trans('admin.setting.mobile.upload_startup_diagram_figure_5_success_hint'));
        });
      });

      $('#mobile-splash5-remove').on('click', function () {
        if (!confirm(Translator.trans('admin.setting.mobile.startup_diagram_delete_hint'))) return false;
        var $btn = $(this);
        $.post($btn.data('url'), function () {
          $('#mobile-splash5-container').html('');
          $form.find('[name=splash5]').val('');
          $btn.hide();
          Notify.success(Translator.trans('admin.setting.mobile.delete_startup_diagram_figure_5_success_hint'));
        }).error(function () {
          Notify.danger(Translator.trans('admin.setting.mobile.delete_startup_diagram_figure_5_fail_hint'));
        });
      });
    }

    if ($('#mobile-logo-upload').length) {
      var uploader = new WebUploader({
        element: '#mobile-logo-upload'
      });
      uploader.on('uploadSuccess', function (file, response) {
        var url = $('#mobile-logo-upload').data('gotoUrl');
        $.post(url, response, function (data) {
          response = $.parseJSON(data);
          $('#mobile-logo-container').html('<img src="' + response.url + '">');
          $form.find('[name=logo]').val(response.path);
          $('#mobile-logo-remove').show();
          Notify.success(Translator.trans('admin.setting.mobile.upload_logo_success_hint'));
        });
      });

      $('#mobile-logo-remove').on('click', function () {
        if (!confirm(Translator.trans('admin.setting.mobile.startup_diagram_delete_hint'))) return false;
        var $btn = $(this);
        $.post($btn.data('url'), function () {
          $('#mobile-logo-container').html('');
          $form.find('[name=logo]').val('');
          $btn.hide();
          Notify.success(Translator.trans('admin.setting.mobile.delete_logo_success_hint'));
        }).error(function () {
          Notify.danger(Translator.trans('admin.setting.mobile.delete_logo_fail_hint'));
        });
      });

      group: 'default';
      CKEDITOR.replace('mobile_about', {
        toolbar: 'Simple',
        filebrowserImageUploadUrl: $('#mobile_about').data('imageUploadUrl')
      });
    }
    //

    if ($('#site-applogo-upload').length) {
      var uploader = new WebUploader({
        element: '#site-applogo-upload'
      });
      uploader.on('uploadSuccess', function (file, response) {
        var url = $('#site-applogo-upload').data('gotoUrl');
        $.post(url, response, function (data) {
          response = $.parseJSON(data);
          $('#site-applogo-container').html('<img src="' + response.url + '">');
          $form.find('[name=applogo]').val(response.path);
          $('#mobile-applogo-remove').show();
          Notify.success(Translator.trans('admin.setting.mobile.upload_app_icon_success_hint'));
        });
      });

      $('#site-applogo-remove').on('click', function () {
        if (!confirm(Translator.trans('admin.setting.mobile.startup_diagram_delete_hint'))) return false;
        var $btn = $(this);
        $.post($btn.data('url'), function () {
          $('#site-applogo-container').html('');
          $form.find('[name=applogo]').val('');
          $btn.hide();
          Notify.success(Translator.trans('admin.setting.mobile.delete_app_icon_success_hint'));
        }).error(function () {
          Notify.danger(Translator.trans('admin.setting.mobile.delete_app_icon_fail_hint'));
        });
      });
    }
    //

    if ($('#site-appcover-upload').length) {
      var uploader = new WebUploader({
        element: '#site-appcover-upload'
      });

      uploader.on('uploadSuccess', function (file, response) {
        var url = $('#site-appcover-upload').data('gotoUrl');
        $.post(url, response, function (data) {
          response = $.parseJSON(data);
          $('#site-appcover-container').html('<img src="' + response.url + '">');
          $form.find('[name=appcover]').val(response.path);
          $('#mobile-appcover-remove').show();
          Notify.success(Translator.trans('admin.setting.mobile.upload_app_cover_success_hint'));
        });
      });

      $('#site-appcover-remove').on('click', function () {
        if (!confirm(Translator.trans('admin.setting.mobile.startup_diagram_delete_hint'))) return false;
        var $btn = $(this);
        $.post($btn.data('url'), function () {
          $('#site-appcover-container').html('');
          $form.find('[name=appcover]').val('');
          $btn.hide();
          Notify.success(Translator.trans('admin.setting.mobile.delete_app_cover_success_hint'));
        }).error(function () {
          Notify.danger(Translator.trans('admin.setting.mobile.delete_app_cover_fail_hint'));
        });
      });

    }

    if ($('input[name=\'liveScheduleEnabled\']').length) {
      $('input[name=\'liveScheduleEnabled\']').on('change', function () {
        console.log($('input[name=\'liveScheduleEnabled\']:checked').val());
        if ($('input[name=\'liveScheduleEnabled\']:checked').val() == 1) {
          $('.img-has-no-live').addClass('hidden');
          $('.img-has-live').removeClass('hidden');
        } else {
          $('.img-has-no-live').removeClass('hidden');
          $('.img-has-live').addClass('hidden');
        }
      });
    }

  };

});