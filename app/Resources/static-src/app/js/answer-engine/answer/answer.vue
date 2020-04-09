<template>
  <div id="app" class="test-vue">
    <item-engine
      :sections="sections"
      :testpaper="testpaper"
      @saveAnswerData="saveAnswerData"
      @getAnswerData="getAnswerData"
      :showCKEditorData="showCKEditorData"
    ></item-engine>
  </div>
</template>

<script>
  import ActivityEmitter from '../../activity/activity-emitter';
  export default {
    data() {
      return {
        showCKEditorData: {
          publicPath: '/static-dist/libs/es-ckeditor/ckeditor.js',
          filebrowserImageUploadUrl: $('[name=image_upload_url]').val(),
          filebrowserImageDownloadUrl: $('[name=image_download_url]').val(),
        },
      };
    },
    created() {
       this.emitter = new ActivityEmitter();
       this.emitter.emit('doing', {data: ''});
       const assessment = JSON.parse($('[name=assessment]').val());
       this.sections = assessment.sections;
       this.testpaper = assessment;
    },
    methods: {
      getAnswerData(sectionResponses) {
        const that = this;
        const answerRecord = JSON.parse($('[name=answer_record]').val());
        const assessmentResponse = {
          'assessment_id': this.testpaper.id,
          'answer_record_id': answerRecord.id,
          'used_time': 300,
          'section_responses': sectionResponses,
        };
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
          location.href = $('[name=submit_goto_url]').val();
        })
      },
      saveAnswerData(sectionResponses){
        // const answerRecord = JSON.parse($('[name=answer_record]').val());
        // const assessmentResponse = {
        //   'assessment_id': this.testpaper.id,
        //   'answer_record_id': answerRecord.id,
        //   'used_time': 300,
        //   'section_responses': sectionResponses,
        // };
        // $.ajax({
        //   url: $("[name='answer_engine_save_url']").val(),
        //   contentType: 'application/json;charset=utf-8',
        //   type: 'post',
        //   data: JSON.stringify(assessmentResponse),
        //   beforeSend(request) {
        //     request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
        //   }
        // }).done(function (resp) {
        //   location.href = $('[name=save_goto_url]').val();
        // })
      }
    }
  }
</script>
