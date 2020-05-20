<template>
  <div id="app" class="ibs-vue">
    <div id="cd-modal"></div>
    <item-engine
      :assessment="assessment"
      :answerRecord="answerRecord"
      :answerScene="answerScene"
      :showCKEditorData="showCKEditorData"
      :assessmentResponse="assessmentResponse"
      :showAttachment="showAttachment"
      :cdnHost="cdnHost"
      :uploadSDKInitData="uploadSDKInitData"
      :deleteAttachmentCallback="deleteAttachmentCallback"
      :previewAttachmentCallback="previewAttachmentCallback"
      :downloadAttachmentCallback="downloadAttachmentCallback"
      @getAnswerData="getAnswerData"
      @saveAnswerData="saveAnswerData"
      @timeSaveAnswerData="timeSaveAnswerData"
      @reachTimeSubmitAnswerData="reachTimeSubmitAnswerData"
      @deleteAttachment="deleteAttachment"
      @previewAttachment="previewAttachment"
      @downloadAttachment="downloadAttachment"
    >
      <template slot="inspection" v-if="inspectionOpen && isNotMobile">
        <inspection-control mode="watching" ref="inspection" @ready="readyHandler" @cheatHappened="saveCheatRecord" @faceCaptured="captureHandler"></inspection-control>
      </template>
    </item-engine>
  </div>
</template>

<script>
  import { isMobileDevice } from 'common/utils';
  import ActivityEmitter from '../../activity/activity-emitter';
  import dataURLToBlob from "dataurl-to-blob";
  export default {
    data() {
      let inspectionOpen = $('[name=token]').length > 0 && $('[name=token]').val() !== '';
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
        inspectionOpen: inspectionOpen,
        isNotMobile: !isMobileDevice()
      };
    },
    created() {
      this.emitter = new ActivityEmitter();
      this.emitter.emit('doing', {data: ''});
      this.assessment = JSON.parse($('[name=assessment]').val());
      this.answerRecord = JSON.parse($('[name=answer_record]').val());
      this.answerScene = JSON.parse($('[name=answer_scene]').val());
      this.assessmentResponse = JSON.parse($('[name=assessment_response]').val());
    },
    methods: {
      getAnswerData(assessmentResponse) {
        const that = this;
        $.ajax({
          url: $("[name='answer_engine_submit_url']").val(),
          contentType: 'application/json;charset=utf-8',
          type: 'post',
          data: JSON.stringify(assessmentResponse),
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          }
        }).done(function (resp) {
          that.emitter.emit('finish', {data: ''});
          location.replace($('[name=submit_goto_url]').val());
        })
      },
      reachTimeSubmitAnswerData(assessmentResponse) {
        const that = this;
        $.ajax({
          url: $("[name='answer_engine_submit_url']").val(),
          contentType: 'application/json;charset=utf-8',
          type: 'post',
          data: JSON.stringify(assessmentResponse),
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          }
        }).done(function (resp) {
          that.emitter.emit('finish', {data: ''});
          cd.confirm({
            title: '答题结束',
            content: '答题已结束，您的试卷已提交，请点击下面的按钮查看结果！',
            okText: '查看结果',
            cancelText: '返回',
            className: '',
          }).on('ok', () => {
            location.replace($('[name=submit_goto_url]').val());
          }).on('cancel', () => {
            location.replace($('[name=submit_goto_url]').val());
          })
        })
      },
      timeSaveAnswerData(assessmentResponse) {
        $.ajax({
          url: $("[name='answer_engine_save_url']").val(),
          contentType: 'application/json;charset=utf-8',
          type: 'post',
          data: JSON.stringify(assessmentResponse),
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          }
        }).done(function (resp) {
        })
      },
      saveAnswerData(assessmentResponse){
        $.ajax({
          url: $("[name='answer_engine_save_url']").val(),
          contentType: 'application/json;charset=utf-8',
          type: 'post',
          data: JSON.stringify(assessmentResponse),
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          }
        }).done(function (resp) {
          parent.location.href = $('[name=save_goto_url]').val();
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
      },
      readyHandler() {
        let $node = $('[name=img-url]');
        if ($node.length > 0 && $node.val() !== '') {
          this.$refs['inspection'].captureModal({
            token: $('[name=token]').val(),
            faceUrl: $node.val()
          });
        } else {
          this.$refs['inspection'].captureModal({
            token: $('[name=token]').val(),
          });
        }
      },
      saveCheatRecord(cheating) {
        let data = new FormData();
        data.append('status', cheating.status);
        data.append('level', cheating.level);
        data.append('duration', cheating.duration);
        data.append('behavior', cheating.behavior);
        data.append('picture', dataURLToBlob(cheating.picture));

        $.ajax({
          url: $('[name=inspection-save-url]').val(),
          type: 'POST',
          contentType: false,
          processData: false,
          data: data,
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          },
          success: function (result) {
            console.log(result)
          }
        });
      },
      captureHandler(data) {
        let img = new Image(480);
        img.src = data.capture;
        let params = new FormData();
        params.append('picture', dataURLToBlob(data.capture));

        $.ajax({
          url: $('[name=upload-url]').val(),
          type: 'POST',
          contentType: false,
          processData: false,
          data: params,
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          },
          success: function (response) {
          }
        });
      }
    }
  }
</script>
