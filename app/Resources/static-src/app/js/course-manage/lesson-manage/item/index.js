import {hiddenUnpublishTask, addLesson} from './../header-util';
import BaseManage from './../BaseManage';
import { TaskListHeaderFixed } from 'app/js/course-manage/help';

class DefaultManage extends BaseManage {
  constructor($container) {
    super($container);
    this.closeNum = $('.js-task-manage-close-num')
    this.$batchPublishBtn = $('.js-task-all-published');
    this.$batchCancelPublishBtn = $('.js-task-all-unpublished');
    this.$element = $($container);
    this.batchOperate = {
      chosenItems: [],
    };
    this._defaultEvent();
  }

  _defaultEvent() {
    this._showLesson();
    this.onClickCheckbox()
    this.batchPublish()
    this.chooseAllItem()
    this.batchAllPublish()
    this.batchAllunPublish()
    this.taskManageClear()
  }

  _sortRules($item, container) {
    return false;
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

  // 校验是添加内容/删除内容
  verifyExist($elements, id) {
    const index = this.batchOperate.chosenItems.findIndex(item => item === id);
    if (index > -1) {
      this.batchOperate.chosenItems.splice(index, 1);
    } else if (index === -1) {
      this.batchOperate.chosenItems.push( $elements.data('id') );
    }
  }

  // 判断是添加类名还是删除类名
  judgeCheckBoxClass($element, $parent, unitId) { // 参数 $element 当前, $parent 父级, unitId 父级Id
    if ($element.hasClass('checked') && !$parent.hasClass('checked')) {
      $element.removeClass('checked');
      this.verifyExist($element, unitId)
    } else if(!$element.hasClass('checked') && $parent.hasClass('checked')) {
      $element.addClass('checked');
      this.verifyExist($element, unitId)
    }
  }

  // 单选 
  onClickCheckbox() {
    const itemsLength = this.$element.find('.js-task-manage-items').length;
    const $closestItem = $('.js-chapter-all')
    const $closeCheckBox = this.$element.find('.js-chapter-operation')

    $('.js-chapter-operation').on('click', (event) => {
      const $target = $(event.target);
      if (!$target.hasClass('js-chapter-operation')) return;
      $target.toggleClass('checked');
      
      const { id } = $target.data();
      const parent = $target.data('id')
      // 循环获取是否有子集
      $closeCheckBox.each((index, element) => {
        const $element = $(element);
        const unitId = $element.data('id')
        const { parentid } = $element.data();

        // 判断是否有子集，并且不是顶级结构
        if(parent === parentid && parent !== 0) {
          this.judgeCheckBoxClass($element, $target, unitId)

          $closeCheckBox.each((index, elements) =>{
            const $elements = $(elements, unitId)
            if(unitId === $elements.data('parentid')) {
              this.judgeCheckBoxClass($elements, $element, $elements.data('id'))
            }
          })
        }
      })
      
      const isChecked = $target.hasClass('checked');
      const index = this.batchOperate.chosenItems.findIndex(item => item === id);
      // 判断是否记录数据
      if (index > -1 && !isChecked) {
        this.batchOperate.chosenItems.splice(index, 1);
      } else if (index === -1 && isChecked) {
        this.batchOperate.chosenItems.push( id );
      }

      // 单选触发 全选勾选
      if(itemsLength === this.batchOperate.chosenItems.length) {
        $closestItem.toggleClass('checked')
      } else if($closestItem.hasClass('checked')) {
        $closestItem.removeClass('checked')
      }
      // 勾选数量展示
      this.closeNum[0].innerHTML = this.batchOperate.chosenItems.length
      // 清除按钮的显示
      if (this.batchOperate.chosenItems.length > 0) {
        $('.js-task-manage-clear').removeClass('hidden');
      } else {
        $('.js-task-manage-clear').addClass('hidden');
      }
      // 按钮禁用联动
      this.$batchPublishBtn.attr('disabled', this.batchOperate.chosenItems.length > 0 ? false : true);
      this.$batchCancelPublishBtn.attr('disabled', this.batchOperate.chosenItems.length > 0 ? false : true);
    });
  }

  // 发布、取消发布
  batchPublish() {
    $('.js-item-publish').on('click', (event)=> {	
      const $target = $(event.target);
      const { id, publishurl, status } = $target.data();

      $.post(publishurl, { ids: id.toString().split(',') }).then(res => {
        if(status === 'unpublished' && res.success === true) {
          cd.message({ type: 'success', message: Translator.trans('course.manage.task_publish_success_hint') });
        } else if(status === 'published'&& res.success === true) {
          cd.message({ type: 'success', message: Translator.trans('course.manage.item_task_unpublish_success_hint') });
        }
        if(res.success === false) {
          cd.message({ type: 'warning', message: res.message });
        }
        setTimeout(() => {
          window.location.reload();
        },1000)

      }).catch(function(data) {
        cd.message({ 
          type: 'danger', 
          message: data.responseJSON.error.message 
        });
      });
    })
  }

  // 全选以及反选
  chooseAllItem() {
    $('.js-chapter-all').on('click',(event) => {
      const $target = $(event.target);
      const $chosenItems = this.$element.find('.js-task-manage-items');
      $chosenItems.each((index1, element) => {
        const $element = $(element);
        const { id } = $element.data();
        const index = this.batchOperate.chosenItems.findIndex(item => item === id);

        if (!$target.hasClass('checked') && index === -1) {
          this.batchOperate.chosenItems.push( id );
          this.closeNum[0].innerHTML = this.batchOperate.chosenItems.length
        } 
        $element.addClass('active');
      })
      if ($target.hasClass('checked')) {
        this.closeNum[0].innerHTML = 0
        this.batchOperate = {
          chosenItems:[]
        }
        
        $('.js-task-manage-items .js-item-contents').removeClass('checked')
        $('.js-task-manage-items .js-item-contents .js-chapter-operation').removeClass('checked')
      } else {
        $('.js-task-manage-items .js-item-contents').addClass('checked')
        $('.js-task-manage-items .js-item-contents .js-chapter-operation').addClass('checked')
      }

      if (this.batchOperate.chosenItems.length > 0) {
        $('.js-task-manage-clear').removeClass('hidden');
      } else {
        $('.js-task-manage-clear').addClass('hidden');
      }

      this.$batchPublishBtn.attr('disabled', this.batchOperate.chosenItems.length > 0 ? false : true);
      this.$batchCancelPublishBtn.attr('disabled', this.batchOperate.chosenItems.length > 0 ? false : true);
      $target.toggleClass('checked');
    });
  }

  //批量发布
  batchAllPublish() {
    $('.js-task-all-published').on('click', (event)=> {
      const publishurl = $('input[name="exercise_publish"]').val()
      console.log(this.batchOperate.chosenItems);
      $.post(publishurl, { ids: this.batchOperate.chosenItems }).then(res => {
        cd.message({ type: 'success', message: Translator.trans('course.manage.task_publish_success_hint') });

        if(res.success === false) {
          cd.message({ type: 'warning', message: res.message });
        }

        setTimeout(() => {
          window.location.reload();
        },1000)

      }).catch(function(data) {
        cd.message({ 
          type: 'danger', 
          message: data.responseJSON.error.message 
        });
      });
    })
  }

  //批量取消
  batchAllunPublish() {
    $('.js-task-all-unpublished').on('click', (event)=> {
      const publishurl = $('input[name="exercise_unpublish"]').val()

      $.post(publishurl, { ids: this.batchOperate.chosenItems }).then(res => {
        cd.message({ type: 'success', message: Translator.trans('course.manage.item_task_unpublish_success_hint') });

        if(res.success === false) {
          cd.message({ type: 'warning', message: res.message });
        }

        setTimeout(() => {
          window.location.reload();
        },1000)

      }).catch(function(data) {
        cd.message({ 
          type: 'danger', 
          message: data.responseJSON.error.message 
        });
      });
    })
  }

  // 清除按钮操作
  taskManageClear() {
    $('.js-task-manage-clear').on('click', ()=> {
      $('.js-chapter-all').removeClass('checked')
      $('.js-task-manage-items .js-item-contents').removeClass('checked')
      $('.js-task-manage-items .js-item-contents .js-chapter-operation').removeClass('checked')
      this.batchOperate = {
        chosenItems:[]
      }
      $('.js-task-manage-clear').addClass('hidden');
      this.closeNum[0].innerHTML = 0
      this.$batchPublishBtn.attr('disabled', this.batchOperate.chosenItems.length > 0 ? false : true);
      this.$batchCancelPublishBtn.attr('disabled', this.batchOperate.chosenItems.length > 0 ? false : true);
    })
  }
  
}

new DefaultManage('#sortable-list');
hiddenUnpublishTask();
addLesson();
TaskListHeaderFixed();
