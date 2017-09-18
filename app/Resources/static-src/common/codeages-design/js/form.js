(function($) {
  $(document).on('click.cd.pic.review', '[data-toggle="pic-review"]', function() {
    let picUrl = $(this).data('url');
    window.open(picUrl);
  });

})(jQuery);
