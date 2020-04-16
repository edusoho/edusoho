<template>
  <div id="app" class="test-vue">
    <item-manage
      v-if="mode === 'create'"
      :bank_id="bank_id"
      :mode="mode"
      :category="category"
      :type="type"
      :showCKEditorData="showCKEditorData"
      @getData="getData"
      @goBack="goBack"
    ></item-manage>
    <item-manage
      v-if="mode === 'edit'"
      :bank_id="bank_id"
      :mode="mode"
      :category="category"
      :subject="subject"
      :type="type"
      :showCKEditorData="showCKEditorData"
      @getData="getData"
      @goBack="goBack"
    ></item-manage>
  </div>
</template>

<script>
  export default {
    data() {
      let mode = $('[name=mode]').val();
      let item = {};
      if (mode === 'edit') {
        item = JSON.parse($('[name=item]').val());
        item.questions = Object.values(item.questions);
      }

      return {
        bank_id: $('[name=bank_id]').val(),
        mode: mode,
        category: JSON.parse($('[name=category]').val()),
        subject: item,
        type: $('[name=type]').val(),
        showCKEditorData: {
          publicPath: $('[name=ckeditor_path]').val(),
          filebrowserImageUploadUrl: $('[name=ckeditor_image_upload_url]').val(),
          filebrowserImageDownloadUrl: $('[name=ckeditor_image_download_url]').val(),
        },
      };
    },
    methods: {
      getData(data) {
        let submission = data.isAgain ? 'continue' : '';
        data = data.data;
        data['submission'] = submission;
        data['type'] = $('[name=type]').val();
        let mode = $('[name=mode]').val();
        $.ajax({
          url: mode === 'create' ? $('[name=create_url]').val() : $('[name=update_url]').val(),
          contentType: 'application/json;charset=utf-8',
          type: 'post',
          data: JSON.stringify(data),
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          }
        }).done(function (resp) {
          if (resp.goto) {
            window.location.href = resp.goto;
          }
        })
      },
      goBack() {
        window.location.href = $('[name=back_url]').val();
      }
    }
  }
</script>
