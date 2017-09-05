import notify from 'common/notify';

(function($) {
  function tabelAjax($target, url) {
    $.get(url).done(function(html) {
      $target.html(html);
    }).fail(function() {
      notify('danger', Translator.trans('site.response_error'))
    })
  }

  $(document).on('click.cd.table.filter', '[data-toggle="table-filter"]', function() {
    let $this = $(this);
    if ($this.closest('li').hasClass('active')) {
      return;
    }
    
    let $target = $($this.data('target'));
    let url = $target.data('url');

    let filterStr = $this.data('filter');
    $target.data('filter', filterStr);

    let sortStr = $target.data('sort');

    if (sortStr) {
      url = `${url}?${sortStr}`;

      if (filterStr) {
        url = `${url}&${filterStr}`;
      }
    } else {
      if (filterStr) {
        url = `${url}?${filterStr}`;
      }
    }

    tabelAjax($target, url);
  });

  $(document).on('click.cd.table.sort', '[data-toggle="table-sort"]', function() {
    let $this = $(this);

    let $target = $($this.data('target')); 
    let url = $target.data('url');

    let sortKey = $this.data('sort-key');
    let sortValue = 'desc';

    let $sortIcon = $this.find('.active');
    if ($sortIcon.length) {
      sortValue = $sortIcon.siblings().data('sort-value');
    }
    
    let sortStr = `${sortKey}=${sortValue}`;
    $target.data('sort', sortStr);

    let filterStr = $target.data('filter');

    if (filterStr) {
      url = `${url}?${sortStr}&${filterStr}`;
    } else {
      url = `${url}?${sortStr}`;
    }

    tabelAjax($target, url);
  });
})(jQuery);