import sortList from 'common/sortable';
import { toggleIcon } from 'app/common/widget/chapter-animate';

export default class Manage {
  constructor(element) {
    this.$element = $(element);
    this._sort();
    this._event();
  }
  
  _event() {
    let self = this;

    this.$element.on('addItem', function(e, elm){
      self.addItem(elm);
      self.sortList();
    });

    this.$element.on('click','[data-toggle]', function(e){
      let $this = $(this);
      self.position = $this.data('position');
      self.type = $this.data('type');
    });
    this._deleteChapter();
    this._collapse();
  }

  _collapse() {
    let collapseTexts = [
      '<i class="es-icon es-icon-keyboardarrowup mr5"></i>'+Translator.trans('site.data.expand'),
      '<i class="es-icon es-icon-keyboardarrowdown mr5"></i>'+Translator.trans('site.data.collapse')
    ]
    this.$element.on('click', '.js-chapter-toggle-show', (event) => {
      let $this = $(event.currentTarget);
      $this.toggleClass('toogle-hide');
      let $chapter = $this.closest('.task-manage-item');
      let until = $chapter.hasClass('js-task-manage-chapter') ? '.js-task-manage-chapter' : '.js-task-manage-chapter,.js-task-manage-unit';
      $chapter.nextUntil(until).animate({ height: 'toggle', opacity: 'toggle' }, "normal");
      $this.hasClass('toogle-hide') ? $this.html(collapseTexts[0]) :  $this.html(collapseTexts[1]);
    });
  }

  addItem(elm) {
    //添加章节课时
    switch(this.type)
    {
      case 'chapter':
        let $position = this.$element.find('#chapter-'+this.position);
        $position = $position.nextUntil('.js-task-manage-chapter').last();
        if (0 == $position.length) {
          this.$element.append(elm);
        } else {
          $position.after(elm);
        }
        break;
      case 'task':
        this.$element.find('#chapter-'+this.position+' .js-lesson-box').append(elm);
        break;
      case 'lesson':
        $position = this.$element.find('#chapter-'+this.position);
        $position = $position.nextUntil('.task-manage-unit,.js-task-manage-chapter').last();
        if (0 == $position.length) {
          $position.append(elm);
        } else {
          $position.after(elm);
        }
        break;
      default:
        this.$element.append(elm);
    }
    this.handleEmptyShow();
    this._clearPosition();
  }

  _clearPosition() {
    this.position = '';
    this.type = '';
  }

  _deleteChapter() {
    //删除章节课时
    let self = this;
    this.$element.on('click', '.js-delete', function(evt){
      let $this = $(this);
      let $parent = $this.closest('.task-manage-item');
      let text = self._getDeleteText($this);
      if (!confirm(text)) {
        return;
      }
      
      $parent.remove();
      self.sortList();
      self.handleEmptyShow();
      $.post($this.data('url'), function (data) {
      });
    });
  }

  _getDeleteText($element) {
    // 获得删除章节课时时，提示文案
    if ('task' == $element.data('type')) {
      return Translator.trans('course.manage.task_delete_hint');
    }
    return Translator.trans('course.manage.chapter_delete_hint',{name: $element.data('name')});
  }

  _sort() {
    // 拖动，及拖动规则
    let self = this;
    sortList({
      element: self.$element,
      ajax: false,
      group: 'nested',
      isValidTarget: function ($item, container) {
        // 任务课时内拖动
        if ($item.hasClass('js-task-manage-item') && 
          container.target.closest('.task-manage-lesson').attr('id') != $item.closest('.task-manage-lesson').attr('id')) {
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

  handleEmptyShow() {
    if (0 === $('#sortable-list').find('li').length) {
      $('.js-task-empty').removeClass('hidden');
    } else {
      $('.js-task-empty').addClass('hidden');
    }
  }

  sortList() {
    // 后台排序seq值
    let ids = [];
    this.$element.find('.task-manage-item').each(function(){
        ids.push($(this).attr('id'));
    });
    $.post(this.$element.data('sortUrl'), { ids: ids }, (response) => {
    });
    this.sortablelist()
  }

  sortablelist() {
    // 前台排序 章，课时，任务 的序号
    let sortableElements = ['.js-task-manage-lesson', '.js-task-manage-chapter', '.js-task-manage-item'];
    for(let j = 0; j < sortableElements.length; j++) {
      this._sortNumberByClassName(sortableElements[j]);
    } 
    this._sortUnitNumber();
  }

  _sortNumberByClassName(name) {
    //前台排序 num 通用方法
    let num = 1;
    this.$element.find(name).each(function(){
      let $item = $(this);
      $item.find('.number').text(num++);
    })   
  }

  _sortUnitNumber() {
     // 排序 节 的序号
    let num = 1;
    this.$element.find('.js-task-manage-unit').each(function(){
      let $item = $(this);
      $item.find('.number').text(num);
      num = $item.next().hasClass('task-manage-chapter') ? 1 : num+1;
    });
  }
}