import SideBar from './sidebar.js'
class TaskShow {
  constructor(name) {
    this.name = name;
    // this.init();
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
      activePlugins:["note"],
      courseId: 1,
    });
  }
}


for(var i = 0 ; i< 10; i++) {
  var person = new TaskShow('name'+i);
  person.init();
  console.log(person);
}




