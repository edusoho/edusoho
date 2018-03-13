$('.js-show-resolve').on('click', function() {
  let $this = $(this);
  $('.js-topic-content').toggleClass('hidden');
  $('.js-topic-resolve').toggleClass('hidden').is(':visible') ? $this.text('返回题目') : $this.text('查看解析');
});