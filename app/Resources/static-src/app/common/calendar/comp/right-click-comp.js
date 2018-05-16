import Comp from './comp';

/**
 * 左键按下，拖动选择
 * 如 new SelectComp()
 */
export default class rightClickComp extends Comp {

  registerAction(options) {
    let self = this;
    options['eventRender'] = function(event, element, view) {
      // 选中后触发组件
      element.bind('contextmenu', function(event) {
        const $target = $(event.currentTarget);
        $('body').append(`<div class="delete-popover" style="top: ${event.pageY}px; left: ${event.pageX}px"><div class="schedule-popover-content delete-popover-content popover-content"><div class="delete-item js-delete-item"><i class="es-icon es-icon-delete"></i><span class="schedule-popover-content__time cd-dark-major cd-ml8">删除</span></div></div></div>`);
        return false;
      });
    };

    return options;
  }

}