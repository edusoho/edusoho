import notify from 'common/notify';

(function($) {
  $(document).on('click.cd.table.filter', '[data-toggle="table-filter"]', function() {
    let $this = $(this);
    if ($this.closest('li').hasClass('active')) {
      return;
    }

    let url = $this.data('url');
    let $target = $($this.data('target'));

    if (!$target) {
      return;
    }

    $.get(url).done(function(html) {
      $target.html(html);
    }).fail(function() {
      notify('danger', Translator.trans('site.response_error'))
    })
    
  });
})(jQuery);