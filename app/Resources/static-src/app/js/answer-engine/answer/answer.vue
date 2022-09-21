<template>
  <div id="app" class="ibs-vue">
    <div id="cd-modal"></div>
    <item-engine
      :assessment="assessment"
      :answerRecord="answerRecord"
      :answerScene="answerScene"
      :showCKEditorData="showCKEditorData"
      :showSaveProgressBtn="showSaveProgressBtn"
      :assessmentResponse="assessmentResponse"
      :showAttachment="showAttachment"
      :cdnHost="cdnHost"
      :uploadSDKInitData="uploadSDKInitData"
      :deleteAttachmentCallback="deleteAttachmentCallback"
      :previewAttachmentCallback="previewAttachmentCallback"
      :downloadAttachmentCallback="downloadAttachmentCallback"
      :getCurrentTime="getCurrentTime"
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
  import {checkBrowserCompatibility} from '../../face-inspection/util';
  import { Modal } from 'ant-design-vue';
  
  const commonConfig = { keyboard: false, centered: true, footer: false, class: 'error-modal' }

  export default {
    data() {
      let inspectionOpen = $('[name=token]').length > 0 && $('[name=token]').val() !== '';
      let comp = checkBrowserCompatibility();
      return {
        showCKEditorData: {
          publicPath: $('[name=ckeditor_path]').val(),
          filebrowserImageUploadUrl: $('[name=ckeditor_image_upload_url]').val(),
          filebrowserImageDownloadUrl: $('[name=ckeditor_image_download_url]').val(),
          language: document.documentElement.lang === 'zh_CN' ? 'zh-cn' : document.documentElement.lang,
          jqueryPath: $('[name=jquery_path]').val()
        },
        showAttachment: $('[name=show_attachment]').val(),
        showSaveProgressBtn: $('[name=show_save_progress_btn]').val() === undefined ? 0 : parseInt($('[name=show_save_progress_btn]').val()),
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
        isNotMobile: !isMobileDevice(),
        errorMessage: comp.ok ? '' : comp.message,
        getCurrentTime: () => {
          let time = Date.parse(new Date());
          $.ajax({
            type: "GET",
            beforeSend: request => {
              request.setRequestHeader("Accept", "application/vnd.edusoho.v2+json");
            },
            url: "/api/system/timestamp",
            async: false,
            success: resp => {
              if (!isNaN(resp)) {
                time = resp * 1000;
              }
            },
            error: error => {
              console.log(error);
            }
          });
          return time;
        },
        ajaxTimeOut: null,
        isReachTime: false,
      };
    },
    created() {
      this.emitter = new ActivityEmitter();
      this.emitter.emit('doing', {data: ''});
        

      $.ajax({
        url: '/api/continue_answer',
        type: 'POST',
        async: false,
        headers: {
          'Accept':'application/vnd.edusoho.v2+json'
        },
        data: {answer_record_id: $("[name='answer_record_id']").val()},
        beforeSend(request) {
          request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
        },
      }).done((res) => {
        this.assessment = res.assessment;
        this.answerRecord = res.answer_record;
        this.answerScene = res.answer_scene;
        this.assessmentResponse = res.assessment_response;
      })
    },
    methods: {
      getAnswerData(assessmentResponse) {
        const that = this;
        $.ajax({
          url: '/api/submit_answer',
          contentType: 'application/json;charset=utf-8',
          type: 'POST',
          headers:{
            'Accept':'application/vnd.edusoho.v2+json'
          },
          data: JSON.stringify(assessmentResponse),
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          },
        }).fail((result) => {
          if (!result.responseJSON) {
            this.networkError(assessmentResponse);

            return
          }

          const { code: errorCode } = result.responseJSON.error;

          if (errorCode == '50095204') {
            // 试卷已提交 -- 退出答题
            Modal.error({
              ...commonConfig,
              title: '你已提交过答题，当前页面无法重复提交',
              okText: '退出答题',
              onOk: () => this.returnToCourseDetail()
            })
            return
          }
        }).done(function (resp) {
          that.emitter.emit('finish', {data: ''});
          location.replace($('[name=submit_goto_url]').val());
        })
      },
      reachTimeSubmitAnswerData(assessmentResponse) {
        const that = this;
        this.isReachTime = true;
        $.ajax({
          url: '/api/submit_answer',
          contentType: 'application/json;charset=utf-8',
          type: 'POST',
          headers:{
            'Accept':'application/vnd.edusoho.v2+json'
          },
          data: JSON.stringify(assessmentResponse),
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          },
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
        this.postAnswerData(assessmentResponse)
      },
      saveAnswerData(assessmentResponse){
        this.postAnswerData(assessmentResponse).done(() => {
          this.returnToCourseDetail()
        })
      },
      postAnswerData(assessmentResponse) {
        if (this.isReachTime) return
        
        if (!this.ajaxTimeOut) {
          this.ajaxTimeOut = setTimeout(() => {
            this.networkError(assessmentResponse);
            this.ajaxTimeOut = null
          }, 10 * 1000)
        }

        assessmentResponse.admission_ticket = this.answerRecord.admission_ticket;
        return $.ajax({
          url: '/api/save_answer',
          contentType: 'application/json;charset=utf-8',
          type: 'POST',
          headers: {
            'Accept':'application/vnd.edusoho.v2+json'
          },
          data: JSON.stringify(assessmentResponse),
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          },
        }).then(result => {
          this.ajaxTimeOut && clearTimeout(this.ajaxTimeOut)

          if (!result.assessment_id) {
            this.networkError(assessmentResponse);
          }
        }).fail((result) => {
          if (!result.responseJSON) {
            this.networkError(assessmentResponse);
            this.ajaxTimeOut && clearTimeout(this.ajaxTimeOut)

            return
          }

          const { code: errorCode, message, traceId } = result.responseJSON.error;

          if (errorCode == '50095204') {
            // 试卷已提交 -- 退出答题
            Modal.error({
              ...commonConfig,
              title: '你已提交过答题，当前页面无法重复提交',
              okText: '退出答题',
              onOk: () => this.returnToCourseDetail()
            })
            return
          }

          if (errorCode == '50095209') {
            // 不能同时多端答题
            Modal.error({
              ...commonConfig,
              title: '有新答题页面，请在新页面中继续答题',
              okText: '确定',
              onOk: () => this.returnToCourseDetail()
            })
            return
          }

          if (traceId) {
            Modal.error({
              ...commonConfig,
              title: '答题保存失败，请保存截图后，联系技术支持处理',
              content: `【${message}】【${traceId}】`,
              cancelText: '取消',
              okText: '退出答题',
              onOk: () => this.returnToCourseDetail()
            })
            return
          }

        })
      },
      networkError(assessmentResponse) {
        Modal.error({
          ...commonConfig,
          title: '网络连接不可用，自动保存失败',
          okText: '重新保存',
          onOk: () => {
            Modal.destroyAll();
            this.postAnswerData(assessmentResponse)
          }
        })
      },
      returnToCourseDetail() {
        parent.location.href = $('[name=save_goto_url]').val();
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
            faceUrl: $node.val(),
            errorMessage: this.errorMessage,
          });
        } else {
          this.$refs['inspection'].captureModal({
            token: $('[name=token]').val(),
            errorMessage: this.errorMessage,
          });
        }
      },
      saveCheatRecord(cheating) {
        let data = new FormData();
        data.append('status', 'cheating');
        data.append('level', '1');
        data.append('duration','15000');
        data.append('behavior', cheating.behavior);
        data.append('picture', dataURLToBlob(cheating.image));

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
