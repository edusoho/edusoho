<template>
  <div id="app" class="test-vue">
    <assessment-preview
      :assessment="assessment"
      :answerRecord="answerRecord"
      :answerScene="answerScene"
      :showCKEditorData="showCKEditorData"
    ></assessment-preview>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        scene: 1,
        answerRecord:{},
        answerScene:{},
        showCKEditorData: {
          publicPath: '/static-dist/libs/es-ckeditor/ckeditor.js',
          filebrowserImageUploadUrl: $('[name=image_upload_url]').val(),
          filebrowserImageDownloadUrl: $('[name=image_download_url]').val(),
        },
      };
    },
    created() {
       const assessment = JSON.parse($('[name=assessment]').val());
       this.assessment = assessment;
    },
    methods: {
      getAnswerData(data) {

      }
    }
  }
</script>
