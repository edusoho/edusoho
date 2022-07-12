import { throttle } from 'lodash';

export default class BatchManage {
  constructor(element) {
    this.$element = $(element);
    this.batchOperate = {
      status: 'none', // editing || none
      permission: [],
      chosenItems: [],
    };
    this._defaultEvent();
  }

  _defaultEvent() {
    this.calcOperatePanelPosition();
    this.toggleBatchOperate();
    this.singleChooseItem();
    this.batchChooseItem();
    this.batchDelete();
    this.batchCancelPublish();
    this.batchPublish();
  }

  calcOperatePanelPosition () {
    const $box = $('.cd-main__body');
    const $header = $('.js-task-list-header');
    const $batchOperate = $('.js-batch-operate-panel');
    const $batchOperateSlot = $('.js-batch-operate-panel__slot');
    const $window = $(window);

    $window.on('resize scroll', throttle(function () {
      const pageYOffset = window.pageYOffset;
      const windowHeight = document.documentElement.clientHeight;
      const { height: boxHeight } = $box[0].getBoundingClientRect();
      const boxOffsetTop = $box.offset().top;
      const boxToWindowBottomDistance = windowHeight + pageYOffset - boxHeight - boxOffsetTop;

      if (boxToWindowBottomDistance <= 0) {
        $batchOperate.addClass('fixed');
        $batchOperateSlot.removeClass('hidden');
      } else {
        $batchOperate.removeClass('fixed');
        $batchOperateSlot.addClass('hidden');
      }
    }, 300));
  }

  toggleBatchOperate () {
    const $switchBtn = $('.js-task-list-header .js-batch-operate-switch');

    $switchBtn.on('click', (event) => {
      this.batchOperate.status = this.batchOperate.status === 'none' ? 'editing' : 'none';
      $switchBtn.toggleClass('hidden');

      if (this.batchOperate.status === 'editing') {
        this.startBatchOperate();
      } else {
        this.endBatchOperate();
      }
    });
  }

  startBatchOperate () {
    this.$element.find('.js-chapter-operation').removeClass('hidden');
    $('.js-batch-operate-panel').removeClass('hidden');
    this.batchOperate.chosenItems = [];
    $('.js-task-list-header').find('.js-lesson-create-btn,.js-batch-add,.js-add-chapter-unit').attr('disabled', true);
    $('.js-task-list-header').find('.js-add-chapter-unit .caret').hide();
  }

  endBatchOperate () {
    this.$element.find('.js-chapter-operation').addClass('hidden').removeClass('checked');
    $('.js-batch-operate-panel').addClass('hidden');
    this.batchOperate.chosenItems = [];
    $('.js-task-list-header').find('.js-lesson-create-btn,.js-batch-add,.js-add-chapter-unit').attr('disabled', false);
    $('.js-task-list-header').find('.js-add-chapter-unit .caret').show();
  }

  // 单选
  singleChooseItem (event) {
    this.$element.on('click', '.js-chapter-operation', (event) => {
      event.stopPropagation();
      const $target = $(event.target);

      if (!$target.hasClass('js-chapter-operation')) return;

      $target.toggleClass('checked');

      const { id, type } = $target.data(); // type: chapter、lesson、unit
      const isChecked = $target.hasClass('checked');
      const index = this.batchOperate.chosenItems.findIndex(item => item.id === id);

      if (index > -1 && !isChecked) {
        this.batchOperate.chosenItems.splice(index, 1);
      } else if (index === -1 && isChecked) {
        this.batchOperate.chosenItems.push({ id, type });
      }

      this.updateBatchBtnStatus();
    });
  }

  batchChooseItem (event) {
    const allItemTypes = ['chapter', 'unit', 'lesson'];

    this.$element.on('click', '.js-batch-choose', (event) => {
      const $target = $(event.target);
      const types = $target.data('types').split(',');
      const leftTypes = allItemTypes.filter(type => types.indexOf(type) === -1);

      this.toggleChooseAllItemByType(types);
      $target.toggleClass('active');

      leftTypes.forEach(type => this.cancelChooseAllItemByType(type));
      this.$element.find(`.js-batch-choose[data-types="${leftTypes.join(',')}"]`).removeClass('active');
      this.updateBatchBtnStatus();
    });
  }

  toggleChooseAllItemByType (types) {
    let isAll = true;
    const { chosenItems } = this.batchOperate;

    types.forEach(type => {
      const $chosenItems = this.$element.find(`.js-chapter-operation.checked[data-type=${type}]`);
      const $allItems = this.$element.find(`.js-chapter-operation[data-type=${type}]`);

      if ($chosenItems.length !== $allItems.length) isAll = false;
    });

    types.forEach(type => {
      if (isAll) {
        this.cancelChooseAllItemByType(type);
      } else {
        this.chooseAllItemByType(type);
      }
    });
    this.updateBatchBtnStatus();
  }

  chooseAllItemByType (type) {
    const $items = this.$element.find(`.js-chapter-operation[data-type=${type}]`);

    $items.each((index1, element) => {
      const $element = $(element);
      const { id } = $element.data(); // type: chapter、lesson、unit
      const index = this.batchOperate.chosenItems.findIndex(item => item.id === id);

      if (index === -1) {
        this.batchOperate.chosenItems.push({id, type});
        $element.addClass('checked');
      }
    });
  }

  cancelChooseAllItemByType (type) {
    const $items = this.$element.find(`.js-chapter-operation[data-type=${type}]`);

    $items.each((index1, element) => {
      const $element = $(element);
      const { id } = $element.data(); // type: chapter、lesson、unit
      const index = this.batchOperate.chosenItems.findIndex(item => item.id === id);

      if (index > -1) {
        this.batchOperate.chosenItems.splice(index, 1);
        $element.removeClass('checked');
      }
    });
  }

  // 批量删除
  batchDelete () {
    const deleteUrl = $('#course_manage_lesson_batch_delete').val();

    this.$element.on('click', '.js-batch-delete', () => {
      const { status, permission } = this.batchOperate;
      const $target = $(event.target);
      let chosenItems = this.batchOperate.chosenItems;

      if (status === 'none' || permission.indexOf('delete') === -1) return;

      const isDeleteLesson = chosenItems.every(item => item.type === 'lesson');
      
      if (isDeleteLesson) {
        chosenItems = this.clearDeletedLessons();
        chosenItems = chosenItems.filter(item => !this.getPublishStatusById(item.id)); 
      }
      
      cd.confirm({
        title: Translator.trans('site.delete'),
        content: this.getDeleteText(isDeleteLesson, chosenItems.length),
        okText: Translator.trans('site.confirm'),
        cancelText: Translator.trans('site.cancel'),
        className: 'task-manage-batch-delete',
        autoClose: false,
      }).on('ok', (callback) => {
        const lessonIds = chosenItems.map(item => item.id);

        $('button[data-toggle="cd-confirm-ok"]').text(Translator.trans('site.deleting'));

        $.post(deleteUrl, { lessonIds }).then(res => {
          if (Array.isArray(res)) {
            res.forEach(id => $(`#chapter-${id}`).remove());
          }

          this.clearChosenItems();
          cd.message({ type: 'success', message: Translator.trans('site.delete_success_hint') });
          setTimeout(() => this.updateBatchBtnStatus());
        }).catch(function(data) {
          const failMessage = Translator.trans('site.delete_fail_hint: Delete failed') + ':';

          cd.message({ type: 'danger', message: failMessage + ':' + data.responseJSON.error.message });
        }).done(() => {
          callback();
        });
      });
    });
  }

  getDeleteText(isDeleteLesson, length) {
    if (isDeleteLesson) {
      return Translator.trans('course.manage.task_batch_delete_hint', { length });
    }
    
    return Translator.trans('course.manage.chapter_batch_delete_hint', { length });
  }

  clearDeletedLessons () {
    const { chosenItems } = this.batchOperate;

    const resultItems = chosenItems.filter(item => $(`#chapter-${item.id}`).length > 0);

    if (resultItems.length !== chosenItems.length) {
      this.batchOperate.chosenItems = resultItems;
      this.updateBatchBtnStatus();
    }

    return this.batchOperate.chosenItems;
  }

  // 批量发布
  batchPublish () {
    const publishUrl = $('#course_manage_lesson_batch_publish').val();

    this.$element.on('click', '.js-batch-publish', (event) => {
      const { status, permission, chosenItems } = this.batchOperate;

      if (status === 'none' || permission.indexOf('publish') === -1) return;

      const lessonIds = chosenItems.map(item => item.id);
      const $target = $(event.target);
      
      $target.button('loading');
      $.post(publishUrl, { lessonIds }).then(res => {
        if (Array.isArray(res)) {
          res.forEach(id => {
            const $parentLi = $(`#chapter-${id}`);
            $parentLi.find('.js-publish-item, .js-delete, .js-lesson-unpublish-status').addClass('hidden');
            $parentLi.find('.js-unpublish-item').removeClass('hidden');
          });
        }
        cd.message({ type: 'success', message: Translator.trans('course.manage.task_publish_success_hint') });
        $target.button('reset');

        setTimeout(() => this.updateBatchBtnStatus());
      }).catch(function(data) {
        const failMessage = Translator.trans('course.manage.task_unpublish_fail_hint') + ':';

        $target.button('reset');
        cd.message({ 
          type: 'danger', 
          message: failMessage + data.responseJSON.error.message 
        });
      });
    });
  }

  // 批量取消发布
  batchCancelPublish () {
    const unPublishUrl = $('#course_manage_lesson_batch_unpublish').val();

    this.$element.on('click', '.js-batch-cancel-publish', () => {
      const { status, permission, chosenItems } = this.batchOperate;
  
      if (status === 'none' || permission.indexOf('cancelPublish') === -1) return;

      const lessonIds = chosenItems.map(item => item.id);
      const $target = $(event.target);

      $target.button('loading');
      $.post(unPublishUrl, { lessonIds }).then(res => {
        if (Array.isArray(res)) {
          res.forEach(id => {
            const $parentLi = $(`#chapter-${id}`);
            $parentLi.find('.js-publish-item, .js-delete, .js-lesson-unpublish-status').removeClass('hidden');
            $parentLi.find('.js-unpublish-item').addClass('hidden');
          });
        }
        cd.message({ type: 'success', message: Translator.trans('course.manage.task_unpublish_success_hint') });
        $target.button('reset');

        setTimeout(() => this.updateBatchBtnStatus());
      }).catch(function(data) {
        const failMessage = Translator.trans('course.manage.task_unpublish_fail_hint') + ':';

        $target.button('reset');
        cd.message({ type: 'danger', message: failMessage + data.responseJSON.error.message });
      });
    });
  }

  // 更新按钮状态
  updateBatchBtnStatus () {
    // type: chapter、lesson、unit
    const $chosenNumber = this.$element.find('.js-chosen-number');
    const chosenItems = this.batchOperate.chosenItems;
    const hasChapter = chosenItems.findIndex(({type}) => type === 'chapter') > -1;
    const hasLesson = chosenItems.findIndex(({type}) => type === 'lesson') > -1;
    const hasUnit = chosenItems.findIndex(({type}) => type === 'unit') > -1;
    const $batchPublishBtn = this.$element.find('.js-batch-publish');
    const $batchCancelPublishBtn = this.$element.find('.js-batch-cancel-publish');
    const $batchDeleteBtn = this.$element.find('.js-batch-delete');
    const defaultDisabled = !(hasChapter || hasLesson || hasUnit);

    $chosenNumber.text(chosenItems.length);
    // 删除 -- 章和课时、节和课时、章节和课时
    // 发布 -- 只有课时
    // 取消发布 -- 只有课时
    $batchPublishBtn.attr('disabled', defaultDisabled);
    $batchCancelPublishBtn.attr('disabled', defaultDisabled);
    $batchDeleteBtn.attr('disabled', defaultDisabled);
    this.batchOperate.permission = defaultDisabled ? [] : ['publish', 'cancelPublish', 'delete'];

    if (hasLesson && (hasChapter || hasUnit)) {
      $batchPublishBtn.attr('disabled', true);
      $batchCancelPublishBtn.attr('disabled', true);
      $batchDeleteBtn.attr('disabled', true);
      this.batchOperate.permission = [];
    }

    // 没有课时，章和节二者有其一
    if (!hasLesson && (hasChapter || hasUnit)) {
      this.batchOperate.permission = ['delete'];
      $batchPublishBtn.attr('disabled', true);
      $batchCancelPublishBtn.attr('disabled', true);
    }

    // 只有课时
    if (hasLesson && !hasChapter && !hasUnit) {
      const isAllPublish = chosenItems.every(({ id }) => this.getPublishStatusById(id));
      const isAllUnPublish = chosenItems.every(({ id }) => !this.getPublishStatusById(id));

      if (isAllPublish) {
        $batchDeleteBtn.attr('disabled', true);
        $batchPublishBtn.attr('disabled', true);
        this.batchOperate.permission = ['cancelPublish'];
      } else if (isAllUnPublish) {
        $batchCancelPublishBtn.attr('disabled', true);
        this.batchOperate.permission = ['publish', 'delete'];
      }
    }
  }

  clearChosenItems () {
    this.batchOperate.chosenItems = [];
  }

  // true -- 已发布；false -- 未发布
  getPublishStatusById (id) {
    const $unPublishStatus = $(`#chapter-${id}`).find('.js-lesson-unpublish-status.hidden'); // 未发布隐藏了 === 已发布

    return $unPublishStatus.length > 0;
  }
}
