import sortList from 'common/sortable';
import notify from 'common/notify';

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
    this._publish();
  }

  _collapse() {
    let collapseTexts = [
      '<i class="es-icon es-icon-chevronright cd-mr16"></i>',
      '<i class="es-icon es-icon-keyboardarrowdown cd-mr16"></i>'
    ]
    this.$element.on('click', '.js-toggle-show', (event) => {
      let $this = $(event.currentTarget);
      $this.toggleClass('toogle-hide');
      let $chapter = $this.closest('.task-manage-item');
      let until = $chapter.hasClass('js-task-manage-chapter') ? '.js-task-manage-chapter' : '.js-task-manage-chapter,.js-task-manage-unit';
      let $hideElements = $chapter.nextUntil(until);

      if ($this.hasClass('js-toggle-unit')) {
        $hideElements.toggleClass('unit-hide');
      } else {
        $hideElements = $hideElements.not('.unit-hide');
      }

      $hideElements.stop().animate({ height: 'toggle', opacity: 'toggle' }, "normal");
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
        let $unit = this.$element.find('#chapter-'+this.position);
        let $lesson = $unit.nextUntil('.js-task-manage-unit,.js-task-manage-chapter').last();
        if (0 == $lesson.length) {
          $unit.after(elm);
        } else {
          $lesson.after(elm);
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

      cd.confirm({
        title: Translator.trans('site.delete'),
        content: text,
        confirmText: Translator.trans('site.confirm'),
        cancelText: Translator.trans('site.close'),
        confirm() {
          $parent.remove();
          self.sortList();
          self.handleEmptyShow();
          
          $.post($this.data('url'), function (data) {
          });
        }
      })

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
    let adjustment;
    sortList({
      element: self.$element,
      ajax: false,
      group: 'nested',
      placeholder: '<li class="placeholder task-dragged-placeholder"></li>',
      isValidTarget: function ($item, container) {
        // 任务课时内拖动
        if ($item.hasClass('js-task-manage-item') && ($item.data('type') === 'normal') &&
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
      },
      onDragStart: function (item, container, _super) {
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

  _publish() {
    this.$element.on('click', '.unpublish-item', (event) => {
      let $this = $(event.target);
      $.post($this.data('url'), function (data) {   
        let $parentLi = $this.closest('.task-manage-item');

        $parentLi.find('.publish-item, .js-delete, .publish-status').removeClass('hidden');
        $parentLi.find('.unpublish-item').addClass('hidden');
        notify('success', Translator.trans('course.manage.task_unpublish_success_hint'));
      }).fail(function(data){
        notify('danger', Translator.trans('course.manage.task_unpublish_fail_hint') + ':' + data.responseJSON.error.message);
      });
    })

    this.$element.on('click', '.publish-item', (event) => {
      $.post($(event.target).data('url'), function (data) {
        let $parentLi = $(event.target).closest('.task-manage-item');
        notify('success', Translator.trans('course.manage.task_publish_success_hint'));
        $parentLi.find('.publish-item, .js-delete, .publish-status').addClass('hidden')
        $parentLi.find('.unpublish-item').removeClass('hidden')
      }).fail(function(data){
        notify('danger', Translator.trans('course.manage.task_publish_fail_hint') + ':' + data.responseJSON.error.message);
      });
    })
  }
}