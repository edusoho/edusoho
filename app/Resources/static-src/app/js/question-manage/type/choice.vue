<template>
  <div id="app">
    <item-manage :showCKEditorData="showCKEditorData" :type="type" @getData="getData"></item-manage>
  </div>
</template>

<script>
  import 'common/ajax-event';
  export default {
    data() {
      return {
        type: $('[name=type]').val(),
        showCKEditorData: {
          publicPath: "http://t5.edusoho.cn/static-dist/libs/es-ckeditor/ckeditor.js",
        },
        // category: $('[name=category]').val()
      };
    },
    methods: {
      getData(data) {
        console.log(data);
        data['submission'] = '';
        data['type'] = $('[name=type]').val();
        $.ajax({
          url: $('[name=submitUrl]').val(),
          type: 'post',
          data: data,
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
