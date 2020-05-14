<template>
    <div id="app" class="ibs-vue">
        <item-import
            :subject="subject"
            :showCKEditorData="showCKEditorData"
            :bank_id="bank_id"
            :category="category"
            :importType="importType"
            :showAttachment="showAttachment"
            :cdnHost="cdnHost"
            :uploadSDKInitData="uploadSDKInitData"
            :deleteAttachmentCallback="deleteAttachmentCallback"
            :previewAttachmentCallback="previewAttachmentCallback"
            :downloadAttachmentCallback="downloadAttachmentCallback"
            @previewAttachment="previewAttachment"
            @downloadAttachment="downloadAttachment"
            @getImportData="getImportData"
            @deleteAttachment="deleteAttachment"
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
          publicPath: $('[name=ckeditor_path]').val(),
          filebrowserImageUploadUrl: $('[name=ckeditor_image_upload_url]').val(),
          filebrowserImageDownloadUrl: $('[name=ckeditor_image_download_url]').val(),
          language: document.documentElement.lang === 'zh_CN' ? 'zh-cn' : document.documentElement.lang,
          jqueryPath:  $('[name=jquery_path]').val(),
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
        },
        fileId: 0,
        redirect:true
      }
    },
    created() {
      let self = this;
      $(window).on('beforeunload',function(){
        if (self.redirect) {
          return Translator.trans('admin.block.not_saved_data_hint');
        }
      });
    },
    methods: {
      getImportData(subject) {
        this.redirect = false;
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
      },
      deleteAttachmentCallback() {
        return new Promise(resolve => {
          $.ajax({
            url: $('[name=delete-attachment-url]').val(),
            type: 'post',
            data: {id: this.fileId},
            beforeSend(request) {
              request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
            }
          }).done(function (resp) {
            resolve(resp);
          })
        });
      },
      deleteAttachment(fileId, flag) {
        if (flag) {
          this.fileId = fileId;
        }
      },
      previewAttachment(fileId) {
        this.fileId = fileId;
      },
      downloadAttachment(fileId) {
        this.fileId = fileId;
      },
      previewAttachmentCallback() {
        let self = this;
        return new Promise(resolve => {
          $.ajax({
            url: $('[name=preview-attachment-url]').val(),
            type: 'post',
            data: {id: this.fileId},
            beforeSend(request) {
              request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
            }
          }).done(function (resp) {
            console.log(app);
            console.log(resp);
            resp.data['playServer'] = app.cloudPlayServer;
            resp.data['sdkBaseUri'] = app.cloudSdkBaseUri;
            resp.data['disableDataUpload'] = app.cloudDisableLogReport;
            resp.data['disableSentry'] = app.cloudDisableLogReport;
            resolve(resp);
            self.fileId = 0;
          })
        });
      },
      downloadAttachmentCallback() {
        let self = this;
        return new Promise(resolve => {
          $.ajax({
            url: $('[name=download-attachment-url]').val(),
            type: 'post',
            data: {id: this.fileId},
            beforeSend(request) {
              request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
            }
          }).done(function (resp) {
            resolve(resp);
            self.fileId = 0;
          })
        });
      }
    }
  }
</script>
