class Table {
  constructor(props) {
    Object.assign(this, {
      filterEl: '[data-toggle="table-filter"]',
      sortEl: '[data-toggle="table-sort"]',
      parent: document
    }, props);
    
    this.init();
  }

  init() {
    this.event();
  }

  event() {
    $(this.parent).on('click.cd.table.filter', this.filterEl, (event) => this.filterHandle(event));
    $(this.parent).on('click.cd.table.sort', this.sortEl, (event) => this.sortHandle(event));
  }

  filterHandle(event) {
    let $this = $(event.currentTarget);

    if ($this.closest('li').hasClass('active')) {
      return;
    }
  
    let $target = $($this.data('target'));
    let url = $target.data('url');
  
    let filterStr = $this.data('filter');
    $target.data('filter', filterStr);
  
    if (filterStr) {
      url = `${url}?${filterStr}`;
    }
  
    let sortStr = $target.data('sort');
    if (sortStr) {
      url = `${url}&${sortStr}`;
    }
  
    this.cb($target, url);
  }

  sortHandle(event) {
    let $this = $(event.currentTarget);

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
    url = `${url}?${sortStr}`;

    let filterStr = $target.data('filter');
    if (filterStr) {
      url = `${url}&${filterStr}`;
    }
  
    this.cb($target, url);
  }

  cb() {

  }
}

function table(props) {
  return new Table(props);
}

// HOW TO USE
// cd.table({
//   cb($target, url) {
//   }
// })

export default table;