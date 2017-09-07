import notify from 'common/notify';

$('.js-unbind-btn').on('click', function() {
  let $this = $(this);
  let url = $this.data('url');
  $.get(url).done(function(data) {
    notify('success', Translator.trans(data.message))
  })
})