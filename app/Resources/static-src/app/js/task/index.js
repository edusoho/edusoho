import TaskSidebar from "./widget/sidebar";
import TaskUi from "./widget/task-ui";
import TaskEventEmitter from "./widget/task-event-emitter";
import Emitter from "common/es-event-emitter";

class TaskShow extends Emitter {
  constructor({ element, courseId, taskId, mode }) {
    super();
    this.element = $(element);
    this.courseId = courseId;
    this.taskId = taskId;
    this.mode = mode;
    this.eventEmitter = new TaskEventEmitter(this.element.find('#task-content-iframe'));
    this.ui = new TaskUi({
      element: '.js-task-dashboard-page',
    });

    this.init();
  }

  init() {
    this.initPlugin();
    this.initSidebar();

    if (this.mode != 'preview') {
      this.bindEvent();
    }
  }

  initPlugin() {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover({
      html: true,
      trigger: 'hover',
    });
  }

  bindEvent() {
    let learnedTime = 0;
    let minute = 60 * 1000;
    let timeStep = 1; // 分钟
    this.delay('doing', (timeStep) => {
      learnedTime = parseInt(timeStep) + parseInt(learnedTime);
      this.eventEmitter.emit('doing', {
        timeStep: timeStep,
        learnedTime: learnedTime,
        taskId: this.taskId
      }).then(response => {
        this.trigger('doing', timeStep);
        if(response.result.status == 'finish'
          && $('input[name="task-result-status"]', $('#js-hidden-data')).val() != 'finish') {
          this.ui.learnedWeakPrompt();
          this.ui.learned();
          this.sidebar.reload();
          $('input[name="task-result-status"]', $('#js-hidden-data')).val('finish');
        }
      })
    }, timeStep * minute);
    this.trigger('doing', timeStep);
    this.element.on('click', '#learn-btn', event => {
      $.post($('#learn-btn').data('url'), response => {
        $('#modal').modal('show');
        $('#modal').html(response);
        $('input[name="task-result-status"]', $('#js-hidden-data')).val('finish');
        this.ui.learned();
      })
    });

    this.eventEmitter.receive('finish', response => {
      if(response.result.status == 'finish' 
        && $('input[name="task-result-status"]', $('#js-hidden-data')).val() != 'finish') {
        this.ui.learnedWeakPrompt();
        this.ui.learned();
        $('input[name="task-result-status"]', $('#js-hidden-data')).val('finish');
      }
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
        right: px,
      }, time);
    })
    .on('fold', (px, time) => {
      this.element.find('#dashboard-content').animate({
        right: px,
      }, time);
    })
  }
}

new TaskShow({
  element: $('body'),
  courseId: $('body').find('#js-hidden-data [name="course-id"]').val(),
  taskId: $('body').find('#js-hidden-data [name="task-id"]').val(),
  mode: $('body').find('#js-hidden-data [name="mode"]').val()
});
