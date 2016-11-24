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
      trigger: 'click',
    });
  }

  bindEvent() {
    let minute = 6 * 1000;
    let timeStep = 2; // 分钟
    this.delay('doing', () => {
      let eventUrl = this.element.find('#task-content-iframe').data('eventUrl');
      if (eventUrl === undefined) {
        return;
      }
      let postData = {
        eventName: 'doing',
        data: {
          taskId: this.taskId,
        }
      };
      $.post(eventUrl, postData).done((currentTime) => {
        this.eventEmitter.emit('doing', {currentTime: currentTime});
        this.trigger('doing');
      });
    }, timeStep * minute);

    this.trigger('doing');

    this.bindEmitterEvent();
  }

  bindEmitterEvent() {
    this.eventEmitter.receive('finish', (data) => {
      this.onActivityFinish(data);
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




