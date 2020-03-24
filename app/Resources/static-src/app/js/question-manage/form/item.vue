<template>
  <div id="app">
    <item-manage
      v-if="mode === 'create'"
      :bank_id="bank_id"
      :mode="mode"
      :category="category"
      :type="type"
      :showCKEditorData="showCKEditorData"
      @getData="getData"
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
          publicPath: "http://t5.edusoho.cn/static-dist/libs/es-ckeditor/ckeditor.js",
        },
      };
    },
    methods: {
      getData(data) {
        data['submission'] = '';
        data['type'] = $('[name=type]').val();
        console.log(data.questions);
        $.ajax({
          url: $('[name=submitUrl]').val(),
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
      }
    }
  }
</script>
