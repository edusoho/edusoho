import {hiddenUnpublishTask, addLesson} from './../header-util';
import BaseManage from './../BaseManage';
import { TaskListHeaderFixed } from 'app/js/course-manage/help';

class DefaultManage extends BaseManage {
  constructor($container) {
    super($container);
    this._defaultEvent();
    this.batchOperate = {
      status: 'none', // editing || none
      chosenItems: [],
    }
  }

  _defaultEvent() {
    this._showLesson();
    this.calcOperatePanelPosition()
    this.toggleBatchOperate();
    this.batchChooseItem();
  }

  _sortRules($item, container) {
    return true;
  }

  _showLesson() {
    this.$element.find('.js-task-manage-item').first().addClass('active').find('.js-settings-list').stop().slideDown(500);
    this.$element.on('click', '.js-item-content', (event) => {
      let $this = $(event.currentTarget);
      let $li = $this.closest('.js-task-manage-item');
      if ($li.hasClass('active')) {
        $li.removeClass('active').find('.js-settings-list').stop().slideUp(500);
      }
      else {
        $li.addClass('active').find('.js-settings-list').stop().slideDown(500);
        $li.siblings('.js-task-manage-item.active').removeClass('active').find('.js-settings-list').hide();
      }
    });
  }

  afterAddItem($elm) {
    if ($elm.find('.js-item-content').length > 0) {
      $elm.find('.js-item-content').trigger('click');
    }
    $('[data-toggle="popover"]').popover({
      html: true
    });
  }

  calcOperatePanelPosition () {
    const $box = $('.cd-main__body')
    const $header = $('.js-task-list-header')
    const $batchOperate = $('.js-batch-operate-panel')
    const $batchOperateSlot = $(".js-batch-operate-panel__slot")
    const $window = $(window)

    $window.on('resize', function () {})
    $window.on('scroll', function () {
      const pageYOffset = window.pageYOffset
      const windowHeight = document.documentElement.clientHeight
      const { height: boxHeight } = $box[0].getBoundingClientRect()
      const boxOffsetTop = $box.offset().top
      const boxToWindowBottomDistance = windowHeight + pageYOffset - boxHeight - boxOffsetTop
      console.log(boxToWindowBottomDistance)
      if (boxToWindowBottomDistance <= 0) {
        $batchOperate.addClass('fixed')
        $batchOperateSlot.removeClass('hidden')
      } else {
        $batchOperate.removeClass('fixed')
        $batchOperateSlot.addClass('hidden')
      }
    })
  }

  toggleBatchOperate () {
    const $switchBtn = $('.js-task-list-header .js-batch-operate-switch')

    $switchBtn.on('click', (event) => {
      this.batchOperate.status = this.batchOperate.status === 'none' ? 'editing' : 'none'
      $switchBtn.toggleClass('hidden')

      if (this.batchOperate.status === 'editing') {
        this.startBatchOperate()
      } else {
        this.endBatchOperate()
      }
    })
  }

  startBatchOperate () {
    this.$element.find('.js-chapter-operation').removeClass('hidden')
    $('.js-batch-operate-panel').removeClass('hidden')
    this.batchOperate.chosenItems = []
  }

  endBatchOperate () {
    this.$element.find('.js-chapter-operation').addClass('hidden').removeClass('checked')
    $('.js-batch-operate-panel').addClass('hidden')
    this.batchOperate.chosenItems = []
  }

  // 选择
  batchChooseItem (event) {
    this.$element.on('click', '.js-chapter-operation', function (event) {
      const $target = $(event.target)
      const chosenItems = this.batchOperate.chosenItems
      const { id, type } = $target.data() // type: chapter、lesson、unit
      const isChecked = $target.hasClass('checked')
      const index = chosenItems.findIndex(item => item.id === id)

      if (index > -1 && !isChecked) {
        chosenItems.splice(index, 1)
      } else if (index === -1 && isChecked) {
        chosenItems.push({ id, type })
      }

      this.updateBatchBtnStatus()
    })
  }

  // 批量删除
  batchDelete () {
    const { status } = this.batchOperate

    if (status === 'none') return
  }

  // 批量发布
  batchPublish () {
    const { status } = this.batchOperate

    if (status === 'none') return
  }

  // 批量取消发布
  batchCancelPublish () {
    const { status } = this.batchOperate

    if (status === 'none') return
  }

  // 更新按钮状态
  updateBatchBtnStatus () {
    // type: chapter、lesson、unit
    const chosenItems = this.batchOperate.chosenItems
    const hasChapter = chosenItems.findIndex(({type}) => type === 'chapter') > -1
    const hasLesson = chosenItems.findIndex(({type}) => type === 'lesson') > -1
    const hasUnit = chosenItems.findIndex(({type}) => type === 'unit') > -1

    // 删除 -- 章和课时、节和课时、章节和课时
    // 发布 -- 只有课时
    // 取消发布 -- 只有课时
  }
}

new DefaultManage('#sortable-list');
hiddenUnpublishTask();
addLesson();
TaskListHeaderFixed();
