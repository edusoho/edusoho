import SideBar from "./sidebar";
import Messenger from "es-messenger";

class TaskShow {
  constructor(element) {
    this.element = $(element);

    this.init();
  }

  init() {
    this._initPlugin();
    this._sidebar();
    this.bindActivityEmitterEvent();
  }

  _initPlugin() {
    this.element.find('[data-toggle="tooltip"]').tooltip();
    this.element.find('[data-toggle="popover"]').popover({
      html: true,
      trigger: 'hover',
    });
  }

  bindActivityEmitterEvent() {
    let messenger = new Messenger('parent', 'ActivityEvent');
    let $iframe = this.element.find('#task-content-iframe');
    messenger.addTarget($iframe.get(0).contentWindow, 'task-content-iframe');
    messenger.listen(message => {
      let {event, data} = JSON.parse(message);
      let eventUrl = $iframe.data('eventUrl');

      let postData = data;

      if (postData === undefined) {
        postData = {};
      }

      postData['eventName'] = event;

      $.post(eventUrl, postData)
          .then(({event, data}) => {
            messenger.send(JSON.stringify({event: event, data: data}));
          })
          .fail((error) => {
            messenger.send(JSON.stringify({event: event, error: error}));
          })
      ;
    });
  }

  onActivityFinish() {
    //@ TODO 任务完成的方法
  }

  _sidebar() {
    var sideBar = new SideBar({
      element: this.element.find('.dashboard-sidebar-content'),
      activePlugins: ["note"],
      courseId: 1,
    });
  }
}

new TaskShow($('body'));




