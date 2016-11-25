import SideBar from './widget/sidebar';
import TaskUi from './widget/task-ui';
import TaskEventEmitter from './widget/task-event-emitter';
import Emitter from 'common/es-event-emitter'

class TaskShow extends Emitter {
  constructor({element, courseId, taskId}) {
    super();
    this.element = $(element);
    this.courseId = courseId;
    this.taskId = taskId;
    this.eventEmitter = new TaskEventEmitter(this.element.find('#task-content-iframe'));
    this.ui = new TaskUi({
      element: '.js-task-dashboard-page',
    });

    this.init();
  }

  init() {
    this.initPlugin();
    this.sidebar();
    this.bindEvent();
  }

  initPlugin() {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover({
      html: true,
      trigger: 'hover',
    });
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
      }).then(data => {
        this.trigger('doing', timeStep);
      })
    }, timeStep * minute);

    this.trigger('doing', timeStep);

    this.element.on('click', '.js-btn-learn', event => {
      this.eventEmitter.emit('finish', {taskId: this.taskId}).then(() => {
        this.ui.learned();
        //@TODO 弹框
      })
    });
    this.bindEmitterEvent();
  }

  bindEmitterEvent() {
    this.eventEmitter.receive('finish', (data) => {
      this.onActivityFinish();
    });
  }

  onActivityFinish(transition) {
    if (transition === 'url') {

    }
    this.ui.learnedWeakPrompt();
    this.ui.learned();
  }

  sidebar() {
    this.sideBar = new SideBar({
      element: '.dashboard-sidebar-content',
      activePlugins: ["note", "question"],
      courseId: this.courseId,
    });
  }
}

new TaskShow({
  element: $('body'),
  courseId: $('body').find('#hidden-data [name="course-id"]').val(),
  taskId: $('body').find('#hidden-data [name="task-id"]').val()
});




