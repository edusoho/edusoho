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
        type: 'choice',
        showCKEditorData: {
          publicPath: "http://t5.edusoho.cn/static-dist/libs/es-ckeditor/ckeditor.js",
        },
      };
    },
    methods: {
      getData(data) {
        data['submission'] = '';
        data['type'] = 'choice';
        // data['_csrf_token'] = $('[name=_csrf_token]').val();
        console.log(data);
        $.ajax({
          url: $('[name=submitUrl]').val(),
          type: 'post',
          data: data,
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          }
        }).done(function (resp) {
          //
        })
      }
    }
  }
</script>
