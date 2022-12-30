import TaskSidebar from './widget/sidebar';
import TaskUi from './widget/task-ui';
import TaskPipe from './widget/task-pipe';
import Emitter from 'common/es-event-emitter';
import PagedCourseLesson from 'app/js/courseset/show/paged-course-lesson';
import 'store';

export default class TaskShow extends Emitter {
  constructor({ element, mode }) {
    super();
    this.element = $(element);
    this.mode = mode;

    this.ui = new TaskUi({
      element: '.js-task-dashboard-page'
    });

    this.init();
  }

  init() {
    this.initPlugin();
    this.initSidebar();
    if (this.mode != 'preview') {
      this.initTaskPipe();
      this.initLearnBtn();
    }
    this.initLearnContent();
    this.initPlaySequence();

  }

  initPlaySequence() {
    if (!$('.play-continue-switch')) {
      return ;
    }
    let playMediaSequence = (undefined === store.get('PLAY_MEDIA_SEQUENCE')) ? 1 : store.get('PLAY_MEDIA_SEQUENCE');
    store.set('PLAY_MEDIA_SEQUENCE', playMediaSequence);
    if (playMediaSequence === 0) {
      $('.js-play-sequence').removeClass('checked');
    }
    $('.play-continue-switch').removeClass('hidden');


    $('.js-play-sequence').on('click', (event) => {
      const $target = $(event.target);
      if ($target.hasClass('checked')) {
        $target.removeClass('checked');
        store.set('PLAY_MEDIA_SEQUENCE', 0);
      } else {
        $target.addClass('checked');
        store.set('PLAY_MEDIA_SEQUENCE', 1);
      }
    });
  }

  initPlugin() {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover({
      html: true,
      trigger: 'hover'
    });
  }

  initLearnBtn() {
    this.element.on('click', '#learn-btn', () => {
      $.post($('#learn-btn').data('url'), response => {
        $('#modal').modal('show');
        $('#modal').html(response);
        $('input[name="task-result-status"]', $('#js-hidden-data')).val('finish');
        let $nextBtn = $('.js-next-mobile-btn');
        if ($nextBtn.data('url')) {
          $nextBtn.removeClass('disabled').attr('href', $nextBtn.data('url'));
        }
        this.ui.learned();
        if ($('.js-learn-content').find('.js-finish-tip').length > 0) {
          $('.js-learn-content').find('.js-finish-tip').html(Translator.trans('activity.manage.finished_tips'));
          this.initLearnContent();
        }
      });
    });
  }

  initTaskPipe() {
    this.eventEmitter = new TaskPipe(this.element.find('#task-content-iframe'));
    this.eventEmitter.addListener('finish', response => {
      this._receiveFinish(response);
    });
    this.eventEmitter.addListener('start', response => {
      this._receiveDoing(response);
    });
  }

  _receiveFinish(response) {

    if ($('input[name="task-result-status"]', $('#js-hidden-data')).val() != 'finish') {
      $.get($('.js-learned-prompt').data('url'), html => {
        $('.js-learned-prompt').attr('data-content', html);
        this.ui.learnedWeakPrompt();
        this.ui.learned();
        this.sidebar.reload();
        let $nextBtn = $('.js-next-mobile-btn');
        if ($nextBtn.data('url')) {
          $nextBtn.removeClass('disabled').attr('href', $nextBtn.data('url'));
        }
        $('input[name="task-result-status"]', $('#js-hidden-data')).val('finish');
        this._dealSequence(response, 'prompt');

      });
    } else {
      this._dealSequence(response);
    }
  }

  _dealSequence(response, trigger = 'player') {
    //自动下一课时
    console.log(response);
    let sequence = store.get('PLAY_MEDIA_SEQUENCE', undefined);
    if (sequence === 1) {
      const nextTaskUrl = this.element.find('#task-content-iframe').data('nextTaskUrl');
      const nextTask = this.element.find('#task-content-iframe').data('nextTask');

      if (nextTaskUrl && nextTask.type && ['audio', 'video'].includes(nextTask.type)) {
        if (trigger === 'prompt' && response.playerEnd === true) {
          window.location.href = nextTaskUrl;
          return;
        }
        let finishEvent = response.waitingEventData && response.waitingEventData['finish'] ? response.waitingEventData['finish']['data']['playerMsg'] : {};
        let playerCurrentTime = finishEvent.currentTime||0;
        let playerDuration = finishEvent.duration||0;
        //player.ended 事件不一定是播放到最后一秒，所以必须判断是否播放轴到了最后
        if (playerCurrentTime !== 0 && playerDuration !== 0 && (playerDuration - playerCurrentTime < 2)) {
          window.location.href = nextTaskUrl;
        }
      }
    }

  }

  _receiveDoing(response) {
    if ($('.js-learn-content').find('.js-finish-time').length > 0) {
      let time = $('.js-learn-content').find('.js-finish-tip').data('time');
      let remainTime = time > response.learnedTime ? Math.ceil((time - response.learnedTime) / 60) : 0;
      $('.js-learn-content').find('.js-finish-time').html(remainTime + Translator.trans('activity.live.minute'));
      this.initLearnContent();
    }
  }

  initLearnContent() {
    $('.js-learn-prompt').attr('data-content', $('.js-learn-content').html());
  }

  initSidebar() {
    this.sidebar = new TaskSidebar({
      element: this.element.find('#dashboard-sidebar'),
      url: this.element.find('#js-hidden-data [name="plugins_url"]').val()
    });
    this.sidebar
      .on('popup', (px, time) => {
        this.element.find('#dashboard-content').animate({
          right: px
        }, time);
      })
      .on('fold', (px, time) => {
        this.element.find('#dashboard-content').animate({
          right: px
        }, time);
      })
      .on('task-list-loaded', ($paneBody) => {
        new PagedCourseLesson({
          'afterFirstLoad': function() {
            const $box = $paneBody.parent();
            const boxHeight = $box.height();
            const bodyHeight = $paneBody.height();
            const $activeItem = $paneBody.find('.task-item.active');
            const top = $activeItem.position().top;
            const standardPosition = (boxHeight - $activeItem.height()) / 2;

            if ((bodyHeight - top) < standardPosition) {
              console.log('位置靠近底部，top偏移', top - standardPosition);
              console.log(bodyHeight - boxHeight);
              $box.scrollTop(bodyHeight - boxHeight);
              return;
            }
            if (top > standardPosition) {
              console.log('位置大于标准位置时，top偏移', top - standardPosition);
              console.log(top, standardPosition);
              $box.scrollTop(top - standardPosition);
            }
          },
          'displayItem': {
            'key': 'taskId',
            'value': $('.js-hidden-current-task-id').html()
          },
          displayAllImmediately: true
        });
      });
  }
}
