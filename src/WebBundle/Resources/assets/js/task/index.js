import SideBar from './widget/sidebar';
import TaskUi from './widget/task-ui';
import TaskEventEmitter from './widget/task-event-emitter';
import Emitter from 'common/es-event-emitter'

class TaskShow extends Emitter {
  constructor({element, courseId, taskId, mode}) {
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
    this.sidebar();
    if(this.mode != 'preview'){
      this.bindEvent();
    }
  }

  bindEvent(){
    let learnedTime = 0;
    let minute = 60 * 1000;
    let timeStep = 2; // 分钟
    this.delay('doing', (timeStep) => {

      learnedTime = parseInt(timeStep) + parseInt(learnedTime);
      this.eventEmitter.emit('doing', {
        timeStep: timeStep,
        learnedTime: learnedTime,
        taskId: this.taskId
      }).then(response => {
        this.trigger('doing', timeStep);
        if(response.result.status == 'finish') {
          this.ui.learnedWeakPrompt();
          this.ui.learned();
        }
      })
    }, timeStep * minute);

    this.trigger('doing', timeStep);

    this.element.on('click', '#learn-btn', event => {
      console.log(event);
      $.post($('#learn-btn').data('url'), response => {
          $('#modal').modal('show');
          $('#modal').html(response);
          this.ui.learned();
      })
    });

    this.eventEmitter.receive('finish', response => {
      if(response.result.status == 'finish') {
        this.ui.learnedWeakPrompt();
        this.ui.learned();
      }
    });

  }

  sidebar() {
    this.sideBar = new SideBar({
      element: '.js-task-dashboard-page',
      activePlugins: ['task'],
      courseId: this.courseId,
      taskId: this.taskId,
    });
  }
}

new TaskShow({
  element: $('body'),
  courseId: $('body').find('#js-hidden-data [name="course-id"]').val(),
  taskId: $('body').find('#js-hidden-data [name="task-id"]').val(),
  mode: $('body').find('#js-hidden-data [name="mode"]').val()
});




