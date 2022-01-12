<template>
  <div id="app" class="ibs-vue">
    <item-review
      :role="role"
      :activity="activity"
      :assessment="assessment"
      :answerReport="answerReport"
      :answerRecord="answerRecord"
      :answerScene="answerScene"
      :showCKEditorData="showCKEditorData"
      :showAttachment="showAttachment"
      :cdnHost="cdnHost"
      :previewAttachmentCallback="previewAttachmentCallback"
      :downloadAttachmentCallback="downloadAttachmentCallback"
      :media-type="mediaType"
      :finish-type="finishType"
      :submit-list="submitList"
      @previewAttachment="previewAttachment"
      @downloadAttachment="downloadAttachment"
      @getReviewData="getReviewData"
      @getReviewDataAagin="getReviewDataAagin"
      @view-historical-result="handleViewHistoricalResult"
    ></item-review>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        role: $('[name=role]').val(),
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
        mediaType: $('[name=media_type]').val(),
        finishType: $('[name=finishType]').val(),
        activity: {},
        submitList: []
      };
    },
    created() {
        const that = this;
        $.ajax({
          url: '/api/answer_record/'+$("[name='answer_record_id']").val(),
          type: 'GET',
          async:false,
          headers:{
            'Accept':'application/vnd.edusoho.v2+json'
          },
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
          }
        }).done(function (res) {
          that.activity = res.activity;
          that.answerRecord = res.answer_record;
          if ('finished' == that.answerRecord.status) {
            location.href = $('[name=success_goto_url]').val();
            return;
          }
          that.assessment = res.assessment;
          that.answerReport = res.answer_report;
          that.answerScene = res.answer_scene;
        })
        this.getAnswerRecord();
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
      },

      getAnswerRecord() {
        const that = this;
        const answerRecordId = $("[name='answer_record_id']").val();
        $.ajax({
          url: `/api/answerRecord/${answerRecordId}/submitList`,
          type: 'GET',
          headers:{
            'Accept':'application/vnd.edusoho.v2+json'
          },
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
          }
        }).done(function (res) {
          that.submitList = res;
        });
      },

      handleViewHistoricalResult(params) {
        window.open(`/homework/result/${params.answer_record_id}/show?action=check`);
      }
    }
  }
</script>
