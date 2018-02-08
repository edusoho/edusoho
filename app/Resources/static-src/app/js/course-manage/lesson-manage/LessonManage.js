

import sortList from 'common/sortable';
export default class Manage {
  constructor(element) {
    this.$element = $(element);
    this._sort();
    this._event();
  }
  
  _event() {
    this.$element.on('sort', function(){
      sortList();
    });

    this.$element.on('addItem', function(e, $elm){
      sortList();
    });
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
      self.sortList();
    });
  }

  sortList() {
    // 排序seq值
    let ids = [];
    this.$element.find('.task-manage-item').each(function(){
        ids.push($(this).attr('id'));
    });
    $.post(this.$element.data('sortUrl'), { ids: ids }, (response) => {
    });
    this.sortablelist()
  }

  sortablelist() {
    // 排序 章，课时，任务 的序号
    let sortableElements = ['.js-task-manage-lesson', '.js-task-manage-chapter', '.js-task-manage-item'];
    for(let j = 0; j < sortableElements.length; j++) {
      this._sortNumberByClassName(sortableElements[j]);
    } 
    this._sortUnitNumber();
  }

  _sortNumberByClassName(name) {
    let num = 1;
    this.$element.find(name).each(function(){
      let $item = $(this);
      $item.find('.number').text(num++);
    })   
  }

  _sortUnitNumber() {
     // 排序 节 的序号
    let unitClass = 'js-task-manage-unit';
    let num = 1;
    this.$element.find('.'+unitClass).each(function(){
      let $item = $(this);
      $item.find('.number').text(num);
      num = $item.next().hasClass(unitClass) ? num+1 : 1;
    });
  }
}