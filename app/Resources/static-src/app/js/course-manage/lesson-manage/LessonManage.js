

import sortList from 'common/sortable';

export default class Manage {
  constructor(element) {
    this.$element = $(element);
    this._sort();
  }
  
  _sort() {
    let self = this;
    sortList({
      element: self.$element,
      ajax: false,
      group: 'nested',
      isValidTarget: function ($item, container) {
        // 任务只能挂在课时下
        if ($item.hasClass('js-task-manage-item') && !container.target.hasClass('js-lesson-box')) {
            return false;
        }
        // 章节只能挂在总节点下
        if ($item.hasClass('js-task-manage-unit') || $item.hasClass('js-task-manage-chapter')) {
          if(!container.target.hasClass('sortable-list')) {
            return false;
          }   
        }
        // 课时不能不能在课时下
        if ($item.hasClass('js-task-manage-lesson') && container.target.hasClass('js-lesson-box')) {
            return false;
        }

        return true;
      }
    }, (data) => {
      self._sortList();
    });
  }

  _sortList() {
    let ids = [];
    this.$element.find('.task-manage-item').each(function(){
        ids.push($(this).attr('id'));
    });
    $.post(this.$element.data('sortUrl'), { ids: ids }, (response) => {
        console.log(response);
    });
  }
}