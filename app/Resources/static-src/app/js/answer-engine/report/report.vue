<template>
  <div id="app" class="test-vue">
    <item-report
      :answerReport="answerReport"
      :assessment="assessment"
      :answerRecord="answerRecord"
      :answerScene="answerScene"
      :questionFavorites="questionFavorites"
      :showCKEditorData="showCKEditorData"
      @doAgainEvent="doAgainEvent"
      @cancelFavoriteEvent="cancelFavoriteEvent"
      @favoriteEvent="favoriteEvent"
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
        this.questionFavorites = JSON.parse($('[name=question_favorites]').val());
        console.log(this.questionFavorites)
    },
    methods: {
      doAgainEvent(data) {
        location.href = $('[name=restart_url]').val();
      },
      cancelFavoriteEvent(favorite) {
        $.ajax({
          url: '/api/me/question_favorite/1',
          headers:{
            'Accept':'application/vnd.edusoho.v2+json'
          },
          contentType: 'application/json;charset=utf-8',
          type: 'DELETE',
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          },
          data: JSON.stringify(favorite),
        }).done(function (res) {
          
        })
      },
      favoriteEvent(favorite) {
        $.ajax({
          url: '/api/me/question_favorite',
          headers:{
            'Accept':'application/vnd.edusoho.v2+json'
          },
          contentType: 'application/json;charset=utf-8',
          type: 'POST',
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          },
          data: JSON.stringify(favorite),
        }).done(function (res) {
          
        })
      }
    }
  }
</script>
