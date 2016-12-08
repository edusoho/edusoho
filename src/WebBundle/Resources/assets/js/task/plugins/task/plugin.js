import BasePlugin from '../base-plugin';
import TaskPane from './pane';

class TaskPlugin extends BasePlugin {
  constructor(props) {
    super(props);
    this.code = 'task';
    this.name = Translator.trans('课程');
    this.iconClass = 'es-icon es-icon-menu';
    this.api = {
      list: '../../lessonplugin/lesson/list'
    };
    this.pane = null;
  }
  execute() {
    if (!this.pane) {
      this.pane = new TaskPane({
        element: this.toolbar.createPane(this.code),
        code: this.code,
        toolbar: this.toolbar,
        plugin: this
      });
    }
    this.pane.show();
  }
}

export default TaskPlugin;
