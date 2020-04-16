<template>
  <div id="app" class="test-vue">
    <item-engine
      mode="do"
      :assessment="assessment"
      :answerRecord="answerRecord"
      :answerScene="answerScene"
      :showCKEditorData="showCKEditorData"
      @getAnswerData="getAnswerData"
      @saveAnswerData="saveAnswerData"
      @checkResult="checkResult"
      @showCKEditorData="showCKEditorData"
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
    },
    methods: {
      getAnswerData(assessmentResponse) {
        const that = this;
        assessmentResponse.answer_record_id = this.answerRecord.id
        assessmentResponse.used_time = 0
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
          location.replace = $('[name=submit_goto_url]').val();
        })
      },
      checkResult(data) {

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
          location.href = $('[name=save_goto_url]').val();
        })
      }
    }
  }
</script>
