(function() {
  $(document).on('click.modal.data-api', '[data-toggle="modal"]', function() {
    let $this = $(this),
      href = $this.attr('href'),
      url =  $this.data('url');

    if (url) {
      let $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, '')));

      let loading = cd.loading({
        isFixed: true
      });
      $target.html(loading);

      $target.load(url);
    }
  });
  // 同时存在多个modal时，关闭时还有其他modal存在，防止无法上下拖动
  $(document).on('hidden.bs.modal', '#attachment-modal', function() {
    if ($('#modal').attr('aria-hidden')) $(document.body).addClass('modal-open');
    if ($('#material-preview-player').length > 0) $('#material-preview-player').html('');
  });

  $('.modal').on('click', '[data-toggle=form-submit]', function(e) {
    e.preventDefault();
    $($(this).data('target')).submit();
  });

  $('.modal').on('click.modal-pagination', '.pagination a', function(e) {
    e.preventDefault();
    let $modal = $(e.delegateTarget);
    const url = $(this).attr('href');
    const limitHref = 'javascript:;';
    if (url === limitHref || typeof url === 'undefined') {
      return;
    }
    $.get(url, function(html) {
      $modal.html(html);
    });
  });
}());
