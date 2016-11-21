import SideBar from './widget/sidebar';
import LearnState from './widget/learn-state';


class TaskShow {
  constructor() {
    this.init();
  }

  init() {
    this.initPlugin();
    this.sidebar();
  }

  initPlugin() {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover({
      html: true,
      trigger: 'hover',
    });
  }

  sidebar() {
    var sideBar = new SideBar({
      element:'.dashboard-sidebar-content',
      activePlugins:["note","question"],
      courseId: 1,
    });

    var learnState = new LearnState ({
      element:'.js-task-dashboard-page',
    });
  }
}

new TaskShow();




