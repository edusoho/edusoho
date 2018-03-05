import sortList from 'common/sortable';
export default class CourseManage {
  constructor() {
    this.$sortBtn = $('.js-sort-btn');
    this.status = false;
    this.init();
  }

  init() {
    this.bindEvent();
  }


  bindEvent() {
    this.$sortBtn.on('click', () => this.sortStatus());
    $('.js-cancel-sort-btn').on('click', () => this.cancelSort());
  }

  sortStatus() {
    this.status = true;
    this.$sortBtn.toggleClass('hidden');
    this.$sortBtn.prev().toggleClass('hidden');
    this.$sortBtn.nextAll().toggleClass('hidden');
    $('#select-single').toggleClass('hidden');
    if (this.status) {
      this.sortPlan();
    }
  }

  sortPlan() {
    const self = this;
    const $planList = $('.js-plan-list');
    let adjustment;
    sortList({
      element: $planList,
      ajax: false,
      group: 'nested',
      placeholder: '<li class="placeholder task-dragged-placeholder cd-mb24"></li>',
      onDragStart: function (item, container, _super) {
        console.log(item);
        let offset = item.offset(),
            pointer = container.rootGroup.pointer;
        adjustment = {
          left: pointer.left - offset.left,
          top: pointer.top - offset.top
        };
        _super(item, container);
        self.hiddenOperations(item);
      },
      onDrag: function (item, position) {
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
      }
    }, (data) => {

    });
  }

  hiddenOperations($item) {
    $item.find('.js-plan-icon').toggleClass('hidden');
    $item.find('.js-plan-dragged-icon').toggleClass('hidden');
  }

  cancelSort() {
    window.location.reload();
  }
}