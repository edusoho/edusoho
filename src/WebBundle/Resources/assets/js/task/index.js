import SideBar from './sidebar';

class TaskShow {
  constructor() {
    this.init();
  }

  init() {
    this._initPlugin();
    this._sidebar();
  }

  _initPlugin() {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover({
      html: true,
      trigger: 'hover',
    });
  }

  _sidebar() {
    var sideBar = new SideBar({
      element:'.dashboard-sidebar-content',
      activePlugins:["note","question"],
      courseId: 1,
    });
  }
}

new TaskShow();




