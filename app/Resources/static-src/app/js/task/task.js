import TaskSidebar from "./widget/sidebar";
import TaskUi from "./widget/task-ui";
import TaskPipe from "./widget/task-pipe";
import Emitter from "common/es-event-emitter";

export default class TaskShow extends Emitter {
  constructor({element, mode}) {
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
  }

  initPlugin() {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover({
      html: true,
      trigger: 'hover'
    });
  }

  initLearnBtn() {
    this.element.on('click', '#learn-btn', event => {
      $.post($('#learn-btn').data('url'), response => {
        $('#modal').modal('show');
        $('#modal').html(response);
        $('input[name="task-result-status"]', $('#js-hidden-data')).val('finish');
	      let $nextBtn = $('.js-next-mobile-btn');
	      if($nextBtn.data('url')) {
		      $nextBtn.removeClass('disabled').attr('href', $nextBtn.data('url'));
	      }
        this.ui.learned();
      });
    });
  }

  initTaskPipe() {
    this.eventEmitter = new TaskPipe(this.element.find('#task-content-iframe'));
    this.eventEmitter.addListener('finish', response => {
      this._receiveFinish(response);
    });
  }

  _receiveFinish(response) {
    if ($('input[name="task-result-status"]', $('#js-hidden-data')).val() != 'finish') {
      $.get($(".js-learned-prompt").data('url'), html => {
        $(".js-learned-prompt").attr('data-content', html);
        this.ui.learnedWeakPrompt();
        this.ui.learned();
        this.sidebar.reload();
        let $nextBtn = $('.js-next-mobile-btn');
	      if($nextBtn.data('url')) {
		      $nextBtn.removeClass('disabled').attr('href', $nextBtn.data('url'));
	      }
        $('input[name="task-result-status"]', $('#js-hidden-data')).val('finish');
      });
    }
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
      });
  }
}
