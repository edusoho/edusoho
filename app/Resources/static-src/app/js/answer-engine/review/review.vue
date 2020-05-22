<template>
  <div id="app" class="ibs-vue">
    <item-review
      :assessment="assessment"
      :answerReport="answerReport"
      :answerRecord="answerRecord"
      :answerScene="answerScene"
      :showCKEditorData="showCKEditorData"
      :showAttachment="showAttachment"
      :cdnHost="cdnHost"
      :previewAttachmentCallback="previewAttachmentCallback"
      :downloadAttachmentCallback="downloadAttachmentCallback"
      @previewAttachment="previewAttachment"
      @downloadAttachment="downloadAttachment"
      @getReviewData="getReviewData"
      @getReviewDataAagin="getReviewDataAagin"
    ></item-review>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        showCKEditorData: {
          publicPath: $('[name=ckeditor_path]').val(),
          filebrowserImageUploadUrl: $('[name=ckeditor_image_upload_url]').val(),
          filebrowserImageDownloadUrl: $('[name=ckeditor_image_download_url]').val(),
          language: document.documentElement.lang === 'zh_CN' ? 'zh-cn' : document.documentElement.lang,
          jqueryPath: $('[name=jquery_path]').val()
        },
        showAttachment: $('[name=show_attachment]').val(),
        cdnHost: $('[name=cdn_host]').val(),
        fileId: 0,
      };
    },
    created() {
        this.answerRecord = JSON.parse($('[name=answer_record]').val());
        if ('finished' == this.answerRecord.status) {
          location.href = $('[name=success_goto_url]').val();
          return;
        }
        this.assessment = JSON.parse($('[name=assessment]').val());
        this.answerReport = JSON.parse($('[name=answer_report]').val());
        this.answerScene = JSON.parse($('[name=answer_scene]').val());
    },
    methods: {
      getReviewData(reviewReport) {
        $.ajax({
          url: $("[name='answer_engine_review_url']").val(),
          contentType: 'application/json;charset=utf-8',
          type: 'post',
          data: JSON.stringify(reviewReport),
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          }
        }).done(function (resp) {
          location.href = $('[name=success_goto_url]').val();
        })
      },
      getReviewDataAagin(reviewReport){
        $.ajax({
          url: $("[name='answer_engine_review_url']").val(),
          contentType: 'application/json;charset=utf-8',
          type: 'post',
          data: JSON.stringify(reviewReport),
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          }
        }).done(function (resp) {
          location.href = $('[name=success_continue_goto_url]').val();
        })
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
      },
      deleteAttachmentCallback() {
        let self = this;
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
            self.fileId = 0;
          })
        });
      }
    }
  }
</script>
