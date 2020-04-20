<template>
    <div id="app" class="test-vue">
        <item-import
            :subject="subject"
            :showCKEditorData="showCKEditorData"
            :bank_id="bank_id"
            :category="category"
            :importType="importType"
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
          // filebrowserImageUploadUrl: $('[name=image_upload_url]').val(),
          // filebrowserImageDownloadUrl: $('[name=image_download_url]').val(),
        },
        bank_id: $('[name=bankId]').val(),
        category: JSON.parse($('[name=categoryTree]').val()),
        importType: $('[name=type]').val(),
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
