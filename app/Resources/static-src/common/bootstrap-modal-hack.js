(function() {
  $(document).on('click.modal.data-api', '[data-toggle="modal"]', function(e) {
    var imgUrl = app.config.loading_img_path;
    var $this = $(this),
      href = $this.attr('href'),
      url = $(this).data('url');
    if (url) {
      var $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, '')));
      var $loadingImg = "<img src='" + imgUrl + "' class='modal-loading' style='z-index:1041;width:60px;height:60px;position:absolute;top:50%;left:50%;margin-left:-30px;margin-top:-30px;'/>";
      $target.html($loadingImg);
      $target.load(url);
    }
  });
  // 同时存在多个modal时，关闭时还有其他modal存在，防止无法上下拖动
  $(document).on("hidden.bs.modal", "#attachment-modal", function() {
    if ($("#modal").attr('aria-hidden')) $(document.body).addClass("modal-open");
    if ($('#material-preview-player').length > 0) $('#material-preview-player').html("");
  });

  $('.modal').on('click', '[data-toggle=form-submit]', function(e) {
    e.preventDefault();
    $($(this).data('target')).submit();
  });

  $(".modal").on('click.modal-pagination', '.pagination a', function(e) {
    e.preventDefault();
    var $modal = $(e.delegateTarget);
    $.get($(this).attr('href'), function(html) {
      $modal.html(html);
    });
  });
}());
