(function($) {
  $(document).on('click.cg.radio', '[data-toggle="radio"]', function() {
    let $this = $(this);
    $this.siblings().removeClass('checked');
    $this.addClass('checked');
  });

  $(document).on('click.cg.pic.review', '[data-toggle="pic-review"]', function() {
    let picUrl = $(this).data('url');
    window.open(picUrl);
  });

})(jQuery);
