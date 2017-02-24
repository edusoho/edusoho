import TaskSidebar from "./widget/sidebar";
import TaskUi from "./widget/task-ui";
import TaskEventEmitter from "./widget/task-event-emitter";

class TaskShow extends Emitter {
  constructor({element, courseId, taskId, mode, isMember}) {
    super();
    this.element = $(element);
    this.courseId = courseId;
    this.taskId = taskId;
    this.mode = mode;
    this.isMember = isMember;
    this.eventEmitter = new TaskEventEmitter(this.element.find('#task-content-iframe'));
    this.ui = new TaskUi({
      element: '.js-task-dashboard-page'
    });

    this.init();
  }

  init() {
    this.initPlugin();
    this.initSidebar();
    if (this.mode != 'preview' && this.isMember) {
      this.bindEvent();
    }
  }

  bindEvent() {

    this.element.on('click', '#learn-btn', event => {
      $.post($('#learn-btn').data('url'), response => {
        $('#modal').modal('show');
        $('#modal').html(response);
        $('input[name="task-result-status"]', $('#js-hidden-data')).val('finish');
        this.ui.learned();
      });
    });

    // 接收活动的finish事件
    this.eventEmitter.receive('finish', response => {
      this.receiveFinish(response);
    });

  }

  receiveFinish(response) {
    // response.result.status == 'finish'
    //     &&
    if ( $('input[name="task-result-status"]', $('#js-hidden-data')).val() != 'finish') {
      // 盘点是任务式学习还是自由式学习
      $.get($(".js-learned-prompt").data('url'), html => {
        $(".js-learned-prompt").attr('data-content', html);
        this.ui.learnedWeakPrompt();
        this.ui.learned();
        this.sidebar.reload();
        $('input[name="task-result-status"]', $('#js-hidden-data')).val('finish');
      });
    }
  }

  initPlugin() {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover({
      html: true,
      trigger: 'hover'
    });
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

new TaskShow({
  element: $('body'),
  courseId: $('body').find('#js-hidden-data [name="course-id"]').val(),
  taskId: $('body').find('#js-hidden-data [name="task-id"]').val(),
  mode: $('body').find('#js-hidden-data [name="mode"]').val(),
  isMember: $('body').find('#js-hidden-data [name="isMember"]').val()
});
