export default class Test {

  constructor(event, element) {
    this.init(event, element);
    this.delete();
  }

  init(event, element) {
    element.bind('contextmenu', function(event) {
      const $target = $(event.currentTarget);
      $target.popover({
        container: 'body',
        html: true,
        content: '<div class="delete-item js-delete-item"><i class="es-icon es-icon-delete"></i><span class="schedule-popover-content__time cd-dark-major cd-ml8">删除</span></div>',
        template: `<div class="popover schedule-popover delete-popover" role="tooltip">
                  <div class="schedule-popover-content delete-popover-content popover-content">
                  </div>
                </div>`,
        trigger: 'click'
      });
      $target.popover('toggle');
      return false;
    });
  }

  delete() {
    $('.js-delete-item').click(()=> {
      localStorage.removeItem('start');
      localStorage.removeItem('end');
    });
  }

}