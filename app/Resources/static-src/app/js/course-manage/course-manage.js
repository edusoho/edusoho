import sortList from 'common/sortable';
import notify from 'common/notify';

export default class CourseManage {
  constructor() {
    this.$sortBtn = $('.js-sort-btn');
    this.sortList = this._getSort();
    this.init();
  }

  init() {
    this.bindEvent();
    this.sortPlanEvent();
  }

  bindEvent() {
    this.$sortBtn.on('click', () => this.sortEvent());
    $('.js-cancel-sort-btn').on('click', () => this.cancelSort());
    $('.js-save-sort-btn').on('click', () => this.saveSort());

    cd.select({
      el: '#select-single',
      type: 'single'
    }).on('change', (value, text) => {
      if (value) {
        $('.js-plan-item').not('.js-status-' + value).hide();
        $('.js-status-' + value).show();
      } else {
        $('.js-plan-item').show();
      }
    });
  }

  sortEvent() {
    this._toggleSortStatus();
  }

  sortPlanEvent() {
    const self = this;
    const $planList = $('.js-plan-list');
    let adjustment;
    sortList({
      element: $planList,
      ajax: false,
      group: 'nested',
      placeholder: '<li class="placeholder task-dragged-placeholder cd-mb24"></li>',
      onDragStart: function(item, container, _super) {
        let offset = item.offset(),
            pointer = container.rootGroup.pointer;
        adjustment = {
          left: pointer.left - offset.left,
          top: pointer.top - offset.top
        };
        _super(item, container);
        self.hiddenOperations(item);
      },
      onDrag: function(item, position) {
        const height = item.height();
        $('.task-dragged-placeholder').css({
          'height': height,
          'background-color': '#eee'
        });
        item.css({
          left: position.left - adjustment.left,
          top: position.top - adjustment.top
        });
      },
      onDrop: function(item, container, _super) {
        _super(item, container);
        self.hiddenOperations(item);
      },
    });
  }

  hiddenOperations($item) {
    $item.find('.js-plan-icon, .js-plan-dragged-icon').toggleClass('hidden');
  }

  cancelSort() {
    this._restore();
    this._toggleSortStatus();
    notify('success', Translator.trans('course.manage.sort_cancel'));
  }

  saveSort() {
    let sort = this._getSort();

    $.post($('.js-plan-list').data('sortUrl'), { 'ids': sort }, (response) => {
      notify('success', Translator.trans('site.save_success_hint'));
      this.sortList = sort;
      this._toggleSortStatus();
    }).error(function(e) {
      notify('danger', e.responseText);
    });
  }

  _restore() {
    let $list = $('.js-plan-list'),
        targets = '',
        len = this.sortList.length;
    for (let j = 0; j < len; j++) {
      targets += $list.find('#course-plan-' + this.sortList[j]).prop("outerHTML");
    }
    $list.html(targets);
  }

  _toggleSortStatus() {
    $('.js-sort-group, #select-single').toggleClass('hide');
    $('.js-plan-item').toggleClass('drag');
  }

  _getSort() {
    let sort = [];
    $('.js-plan-item').each(function() {
      sort.push($(this).data('courseId'));
    });

    return sort;
  }
}