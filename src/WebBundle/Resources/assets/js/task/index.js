import Messenger from "es-messenger";
import SideBar from './widget/sidebar';
import LearnState from './widget/learn-state';

class TaskShow {
  constructor(element) {
    this.element = $(element);
    this.learnState = new LearnState(this.element);
    this.init();
  }

  init() {
    this.initPlugin();
    this.sidebar();
    this.bindActivityEmitterEvent();
  }

  initPlugin() {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover({
      html: true,
      trigger: 'click',
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
            if(event === 'finish'){
              this.onActivityFinish();
            }
            messenger.send(JSON.stringify({event: event, data: data}));
          })
          .fail((error) => {
            messenger.send(JSON.stringify({event: event, error: error}));
          })
      ;
    });
  }

  onActivityFinish() {
    console.log('activity.finish');
    this.learnState.btnLearnRender(true);
    //@ TODO 任务完成的方法
  }

  sidebar() {
    var sideBar = new SideBar({
      element:'.js-task-dashboard-page',
      activePlugins:['task',"note","question"],
      courseId: 1,
    });
  }
}

new TaskShow($('body'));




