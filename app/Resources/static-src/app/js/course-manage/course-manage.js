import sortList from 'common/sortable';
export default class CourseManage {
  constructor() {
    this.$sortBtn = $('.js-sort-btn');
    this.status = false;
    this.init();
  }

  init() {
    this.bindEvent();
    this.sortPlan();
  }


  bindEvent() {
    this.$sortBtn.on('click', () => this.sortStatus());

  }

  sortStatus() {
    this.status = true;
    this.$sortBtn.toggleClass('hidden');
    this.$sortBtn.prev().toggleClass('hidden');
    this.$sortBtn.nextAll().toggleClass('hidden');
    $('#select-single').toggleClass('hidden');
  }

  sortPlan() {
    const self = this;
    const $planList = $('.js-plan-list');
    let adjustment;
    sortList({
      element: $planList,
      ajax: false,
      group: 'nested',
      // placeholder: '<li class="placeholder task-dragged-placeholder"></li>',
      onDragStart: function (item, container, _super) {
        console.log(item);
        let offset = item.offset(),
            pointer = container.rootGroup.pointer;
        adjustment = {
          left: pointer.left - offset.left,
          top: pointer.top - offset.top
        };
        _super(item, container);
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
    }, (data) => {
      console.log('hhhh');
    });
  }
}