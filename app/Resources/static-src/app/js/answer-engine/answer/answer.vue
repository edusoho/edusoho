<template>
  <div id="app" class="test-vue">
    <div id="cd-modal"></div>
    <item-engine
      :assessment="assessment"
      :answerRecord="answerRecord"
      :answerScene="answerScene"
      :showCKEditorData="showCKEditorData"
      :assessmentResponse="assessmentResponse"
      @getAnswerData="getAnswerData"
      @saveAnswerData="saveAnswerData"
      @timeSaveAnswerData="timeSaveAnswerData"
      @reachTimeSubmitAnswerData="reachTimeSubmitAnswerData"
    ></item-engine>
  </div>
</template>

<script>
  import ActivityEmitter from '../../activity/activity-emitter';
  export default {
    data() {
      return {
        showCKEditorData: {
          publicPath: $('[name=ckeditor_path]').val(),
          filebrowserImageUploadUrl: $('[name=ckeditor_image_upload_url]').val(),
          filebrowserImageDownloadUrl: $('[name=ckeditor_image_download_url]').val(),
        },
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
          // location.href = $('[name=save_goto_url]').val();
        })
      }
    }
  }
</script>
