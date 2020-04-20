<template>
  <div id="app" class="test-vue">
    <item-report
      :answerReport="answerReport"
      :assessment="assessment"
      :answerRecord="answerRecord"
      :answerScene="answerScene"
      :showCKEditorData="showCKEditorData"
      @doAgainEvent="doAgainEvent"
    ></item-report>
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
        },
      };
    },
    created() {
        this.assessment = JSON.parse($('[name=assessment]').val());
        this.answerReport = JSON.parse($('[name=answer_report]').val());
        this.answerRecord = JSON.parse($('[name=answer_record]').val());
        this.answerScene = JSON.parse($('[name=answer_scene]').val());
    },
    methods: {
      doAgainEvent(data) {
        location.href = $('[name=restart_url]').val();
      }
    }
  }
</script>
