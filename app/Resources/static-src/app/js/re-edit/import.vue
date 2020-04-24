<template>
    <div id="app" class="test-vue">
        <item-import
            :subject="subject"
            :showCKEditorData="showCKEditorData"
            :bank_id="bank_id"
            :category="category"
            :importType="importType"
            :showAttachment="showAttachment"
            :cdnHost="cdnHost"
            :uploadSDKInitData="uploadSDKInitData"
            @getImportData="getImportData"
        ></item-import>
    </div>
</template>

<script>
  export default {
    data() {
      return {
        subject: {
          fileName: $('[name=filename]').val(),
          items: JSON.parse($('[name=items]').val()),
        },
        showCKEditorData: {
          publicPath: '/static-dist/libs/es-ckeditor/ckeditor.js',
          filebrowserImageUploadUrl: $('[name=ckeditor_image_upload_url]').val(),
          filebrowserImageDownloadUrl: $('[name=ckeditor_image_download_url]').val(),
        },
        bank_id: $('[name=bankId]').val(),
        category: JSON.parse($('[name=categoryTree]').val()),
        importType: $('[name=type]').val(),
        showAttachment: $('[name=show_attachment]').val(),
        cdnHost: $('[name=cdn_host]').val(),
        uploadSDKInitData: {
          sdkBaseUri: app.cloudSdkBaseUri,
          disableDataUpload: app.cloudDisableLogReport,
          disableSentry: app.cloudDisableLogReport,
          initUrl: $('[name=upload_init_url]').val(),
          finishUrl: $('[name=upload_finish_url]').val(),
          accept: JSON.parse($('[name=upload_accept]').val()),
          fileSingleSizeLimit: $('[name=upload_size_limit]').val(),
          locale: document.documentElement.lang
        }
      }
    },
    methods: {
      getImportData(subject) {
        $.ajax({
          url: $('[name=saveUrl]').val(),
          contentType: 'application/json;charset=utf-8',
          type: 'post',
          data: JSON.stringify(subject),
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          }
        }).done(function (resp) {
          if (resp.goto) {
            window.location.href = resp.goto;
          }
        })
      }
    }
  }
</script>
