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

    this.$element.on('addItem', function(e, elm) {
      self.addItem(elm);
      self.sortList();
    });

    $('body').on('click', '[data-position]', function(e) {
      let $this = $(this);

      self.position = $this.data('position');
      self.type = $this.data('type');
    });
    this._deleteChapter();
    this._collapse();
    this._publish();
    this._createTask();
    this._optional();
    this._initLessonTaskAction();
  }

  _collapse() {
    let collapseTexts = [
      '<i class="es-icon es-icon-chevronright cd-mr16"></i>',
      '<i class="es-icon es-icon-keyboardarrowdown cd-mr16"></i>'
    ];
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

      $hideElements.stop().animate({ height: 'toggle', opacity: 'toggle' }, 'fast');
      $this.hasClass('toogle-hide') ? $this.html(collapseTexts[0]) : $this.html(collapseTexts[1]);
    });
  }

  addItem(elm) {
    let $elm = $(elm);
    let $exsit = $('#' + $elm.attr('id'));

    //编辑时，替换元素
    if ($exsit.length > 0) {
      $exsit.replaceWith($elm);
      this.afterAddItem($elm);
      return;
    }
    //添加章节课时
    switch (this.type) {
    case 'chapter':
    {
      let $position = this.$element.find('#chapter-' + this.position);
      let $last = $position.nextUntil('.js-task-manage-chapter').last();
      if (0 == $last.length) {
        $position.after($elm);
      } else {
        $last.after($elm);
      }
      break;
    }
    case 'task':
    {
      this.$element.find('#chapter-' + this.position + ' .js-lesson-box').append($elm);
      let container = $elm.parents('.js-lesson-container');
      this._triggerAsTaskNumUpdated(container);
      break;
    }
    case 'lesson':
    {
      let $unit = this.$element.find('#chapter-' + this.position);
      let $lesson = $unit.nextUntil('.js-task-manage-unit,.js-task-manage-chapter').last();
      if (0 == $lesson.length) {
        $unit.after($elm);
      } else {
        $lesson.after($elm);
      }
      break;
    }
    default:
      this.$element.append($elm);
    }
    $('[data-toggle="tooltip"]').tooltip();

    this.handleEmptyShow();
    this._flushTaskNumber();
    this.clearPosition();
    this.afterAddItem($elm);
  }

  clearPosition() {
    this.position = '';
    this.type = '';
  }

  _deleteChapter() {
    //删除章节课时
    let self = this;
    this.$element.on('click', '.js-delete', function(evt) {
      let $this = $(this);
      let $parent = $this.closest('.task-manage-item');
      let text = self._getDeleteText($this);

      cd.confirm({
        title: Translator.trans('site.delete'),
        content: text,
        okText: Translator.trans('site.confirm'),
        cancelText: Translator.trans('site.cancel')
      }).on('ok', () => {
        if ('task' == $this.data('type') && $parent.siblings().length == 0) {
          $parent.closest('.js-task-manage-lesson').remove();
        }
        let container = $parent.parents('.js-lesson-container');
        $parent.remove();
        self._triggerAsTaskNumUpdated(container);
        self.handleEmptyShow();
        self._flushTaskNumber();
        $.post($this.data('url'), function(data) {
          notify('success', Translator.trans('site.delete_success_hint'));
          self.sortList();
        });
      });
    });
  }

  _getDeleteText($element) {
    // 获得删除章节课时时，提示文案
    if ('task' == $element.data('type')) {
      return Translator.trans('course.manage.task_delete_hint', { taskName: $element.data('name') });
    }
    return Translator.trans('course.manage.chapter_delete_hint', { name: $element.data('name') });
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
      isValidTarget: function($item, container) {
        return self._sortRules($item, container);
      },
      onDragStart: function(item, container, _super) {
        let offset = item.offset(),
          pointer = container.rootGroup.pointer;
        adjustment = {
          left: pointer.left - offset.left,
          top: pointer.top - offset.top
        };
        _super(item, container);
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
    }, (data) => {
      self.sortList();
    });
  }

  _sortRules($item, container) {
    // 任务课时内拖动
    if ($item.hasClass('js-task-manage-item') &&
      container.target.closest('.js-task-manage-lesson').attr('id') != $item.closest('.js-task-manage-lesson').attr('id')) {
      return false;
    }
    // 章节只能挂在总节点下
    if ($item.hasClass('js-task-manage-unit') || $item.hasClass('js-task-manage-chapter')) {
      if (!container.target.hasClass('sortable-list')) {
        return false;
      }
    }
    // 课时不能不能在课时下
    if ($item.hasClass('js-task-manage-lesson') && container.target.hasClass('js-lesson-box')) {
      return false;
    }

    return true;
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
    this.$element.find('.task-manage-item').each(function() {
      ids.push($(this).attr('id'));
    });
    $.post(this.$element.data('sortUrl'), { ids: ids }, (response) => {});
    this.sortablelist();
  }

  setShowNum($parentLi) {
    if ($parentLi.attr('show-num') == 0) {
      $parentLi.attr('show-num', 1);
    } else {
      $parentLi.attr('show-num', 0);
    }
  }


  sortablelist() {
    // 前台排序 章，课时，任务 的序号
    let sortableElements = ['.js-task-manage-lesson[show-num=1]', '.js-task-manage-chapter', '.js-task-manage-item[show-num=1]', '.js-task-manage-unit'];
    for (let j = 0; j < sortableElements.length; j++) {
      this._sortNumberByClassName(sortableElements[j]);
    }
    this._sortUnitNumber();
  }

  _sortNumberByClassName(name) {
    //前台排序 num 通用方法
    let num = 1;
    this.$element.find(name).each(function() {
      let $item = $(this);
      $item.find('.number').text(num++);
    });
  }

  _sortUnitNumber() {
    let num;
    this.$element.find('.js-task-manage-chapter').each(function() {
      let $unit = $(this).nextUntil('.js-task-manage-chapter').filter('.js-task-manage-unit');
      num = 1;
      $unit.each(function() {
        $(this).find('.number').text(num++);
      });
    });
  }

  _publish() {
    let self = this;
    const info = {
      class: '.js-publish-item, .js-delete, .js-lesson-unpublish-status',
      oppositeClas: '.js-unpublish-item',
      isHideUnPublish: $('#isHideUnPublish').hasClass('checked'),
      flag: false
    };
    this.$element.on('click', '.js-unpublish-item', (event) => {
      const $target = $(event.target);
      info.success = Translator.trans('course.manage.task_unpublish_success_hint');
      info.danger = Translator.trans('course.manage.task_unpublish_fail_hint') + ':';
      this.toggleOptional($target, self, info);
    });

    this.$element.on('click', '.js-publish-item', (event) => {
      const $target = $(event.target);
      info.success = Translator.trans('course.manage.task_publish_success_hint');
      info.danger = Translator.trans('course.manage.task_publish_fail_hint') + ':';
      this.toggleOptional($target, self, info);
    });
  }

  _flushTaskNumber() {
    if (!this.$taskNumber) {
      this.$taskNumber = $('#task-num');
    }

    let num = $('.js-settings-item.active').length;
    this.$taskNumber.text(num);
  }

  _createTask() {
    this.$element.on('click', '.js-create-task-btn', function(event) {
      let url = $(this).data('url');

      $.get(url, function(response) {
        if (response.code) {
          $('#modal').html('');
          $('#modal').append(response.html);
          $('#modal').modal({ 'backdrop': 'static', 'show': true });
        } else {
          cd.message({ type: 'danger', message: Translator.trans(response.message) });
        }
      }).fail(function(response) {
        cd.message({ type: 'danger', message: response.responseJSON.error.message });
      });
    });
  }

  _optional() {
    let self = this;
    const info = {
      class: '.js-set-optional',
      oppositeClas: '.js-unset-optional,.js-lesson-option-tag',
      success: Translator.trans('site.save_success_hint'),
      danger: Translator.trans('site.save_error_hint') + ':',
      flag: true
    };
    this.$element.on('click', '.js-set-optional', (event) => {
      const $target = $(event.target);
      self.toggleOptional($target, self, info);
    });

    this.$element.on('click', '.js-unset-optional', (event) => {
      const $target = $(event.target);
      self.toggleOptional($target, self, info);
    });
  }

  /*
   * 单任务课时才有预览课时的功能，实际上预览的是任务，同样编辑课时的功能实际上是编辑任务
   * 多任务课时才有重命名课时的功能
   */
  _initLessonTaskAction() {
    let btnRelations = { //key为显示的按钮，点击后，实际上点击的value中的按钮
      'js-lesson-preview-btn': 'js-hidden-lesson-preview-btn',
      'js-lesson-edit-btn': 'js-hidden-lesson-edit-btn',
      'js-lesson-rename-btn': 'js-hidden-lesson-rename-btn',
    };

    for (const displayedEleClass in btnRelations) {
      let actualClickedEleClass = btnRelations[displayedEleClass];
      $('#sortable-list').on('click', '.' + displayedEleClass, function() {
        let container = $(this).parents('.js-lesson-container');

        let taskIdStr = container.find('.js-task-manage-item').attr('id');
        //格式为 task-{taskId}

        let taskId = taskIdStr.split('-')[1]; // 第二部分即为taskId
        let jsActionBtn = container.find('.' + actualClickedEleClass);
        let updatedUrl = jsActionBtn.data('url').replace('%7BtaskId%7D', taskId);
        jsActionBtn.data('url', updatedUrl);
        if (jsActionBtn.data('toggle')) {
          jsActionBtn.click();
        } else {
          window.open(jsActionBtn.data('url'), '_blank');
        }
      });
    }
  }

  toggleOptional($target, self, info) {
    const $parentLi = $target.closest('.task-manage-item');
    const $dom = $parentLi.find(info.class);
    const $oppositeDom = $parentLi.find(info.oppositeClas);
    let isHideUnPublish = $('#isHideUnPublish').hasClass('checked');
    $.post($target.data('url'), (data) => {
      let setProperty = true;

      if (isHideUnPublish) {
        setProperty = self.checkShouldSetProperty($target, $parentLi);
      }

      $dom.toggleClass('hidden');
      $oppositeDom.toggleClass('hidden');

      if (isHideUnPublish) {
        if (setProperty) {
          const $displayTextDom = $parentLi.find('.display-text');
          $displayTextDom.toggleClass('hidden');
          self.setShowNum($parentLi);
          self.sortList();
        }
      } else {
        if (info.flag) {
          const $displayTextDom = $parentLi.find('.display-text');
          $displayTextDom.toggleClass('hidden');
          self.setShowNum($parentLi);
          self.sortList();
        }
      }

      cd.message({ type: 'success', message: info.success });
    }).fail(function(data) {
      cd.message({ type: 'danger', message: info.danger + data.responseJSON.error.message });
    });
  }

  checkShouldSetProperty($target, $parentLi) {
    const $publish = $parentLi.find('.js-publish-item');
    const $setOptional = $parentLi.find('.js-set-optional');

    let hiddenPublish = $publish.hasClass('hidden');
    let hiddenOptional = $setOptional.hasClass('hidden');

    let setProperty = true;

    if ($target.hasClass('js-unpublish-item')) {
      if (hiddenOptional) {
        setProperty = false;
      }
    } else if ($target.hasClass('js-publish-item')) {
      if (hiddenOptional) {
        setProperty = false;
      }
    } else if ($target.hasClass('js-set-optional')) {
      if (!hiddenPublish) {
        setProperty = false;
      }
    } else if ($target.hasClass('js-unset-optional')) {
      if (!hiddenPublish) {
        setProperty = false;
      }
    }

    return setProperty;

  }

  afterAddItem($elm) {
    console.log('afterAddItem');
  }

  /*
   * 如果课时下有多任务，显示任务，如果单任务，不显示任务
   * @param container 新增或删除的任务节点所在的js-lesson-container节点
   */
  _triggerAsTaskNumUpdated(container) {
    let lessonBox = container.find('.js-lesson-box');
    let isMulTasks = lessonBox.find('.js-task-manage-item').length > 1;

    if (isMulTasks) { // 多任务课时显示任务, 同时课时上会少一些按钮
      lessonBox.removeClass('hidden');
      container.find('.js-display-when-mul-tasks').removeClass('hidden');
      container.find('.js-display-when-single-task').addClass('hidden');
    } else { // 单任务课时不显示任务, 同时课时上会多一些按钮
      lessonBox.addClass('hidden');
      container.find('.js-display-when-mul-tasks').addClass('hidden');
      container.find('.js-display-when-single-task').removeClass('hidden');
      container.find('.js-task-title').html(container.find('.js-lesson-title').html());
    }

    this._triggerLessonIconAsTaskNumUpdated(container, isMulTasks);
  }

  _triggerLessonIconAsTaskNumUpdated(container, isMulTasks) {
    let lessonIconBtn = container.find('.js-lesson-icon');
    let classList = '';
    if (isMulTasks) {
      //多任务时，删除课时节点上的任务图标，如图文的图标
      classList = lessonIconBtn[0].classList;
    } else {
      //单任务时，课时节点上显示任务的图标
      let esIcon = container.find('.js-lesson-box').find('.es-icon');
      if (esIcon.length == 0) {
        classList = [];
      } else {
        classList = container.find('.js-lesson-box').find('.es-icon')[0].classList;
      }
    }

    for (let index = 0; index < classList.length; index++) {
      const className = classList[index];
      if (className.startsWith('es-icon-')) {
        if (isMulTasks) {
          lessonIconBtn.removeClass(className);
        } else {
          lessonIconBtn.addClass(className);
        }
      }
    }
  }
}