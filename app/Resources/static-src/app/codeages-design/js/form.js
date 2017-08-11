(function($) {
  $(document).on('click.code.radio', '[data-toggle="radio"]', function() {
    let $this = $(this);
    $this.siblings().removeClass('checked');
    $this.addClass('checked');
  });

  $(document).on('click.code.pic.review', '[data-toggle="pic-review"]', function() {
    let picUrl = $(this).data('url');
    window.open(picUrl);
  });

})(jQuery);
