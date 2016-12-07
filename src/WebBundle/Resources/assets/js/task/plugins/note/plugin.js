import BasePlugin from '../base-plugin';
import NotePane from './pane';

class NotePlugin extends BasePlugin {
  constructor(props) {
    super(props);
    this.code = 'note';
    this.name = Translator.trans('笔记');
    this.iconClass = 'es-icon es-icon-edit';
    this.api = {
      init: '../../lessonplugin/note/init',
      save: '../../lessonplugin/note/save'
    };
    this.pane = null;
  }
  execute() {
    if (!this.pane) {
      this.pane = new NotePane({
        element: this.toolbar.createPane(this.code),
        code: this.code,
        toolbar: this.toolbar,
        plugin: this
      });
    }
    this.pane.show();
  }
}

export default NotePlugin;
