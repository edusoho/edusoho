import BasePlugin from '../base-plugin';
import QuestionPane from './pane';

class QuestionPlugin extends BasePlugin {
  constructor(props) {
    super(props);
    this.code = 'question';
    this.name = Translator.trans('问答');
    this.iconClass = 'es-icon es-icon-help';
    this.api = {
      init: '../../lessonplugin/question/init',
      list: '../../lessonplugin/question/list',
      show: '../../lessonplugin/question/show',
      create: '../../lessonplugin/question/create',
      answer: '../../lessonplugin/question/answer'
    };
    this.pane = null;
  }

  execute() {
    if (!this.pane) {
      this.pane = new QuestionPane({
        element: this.toolbar.createPane(this.code),
        code: this.code,
        toolbar: this.toolbar,
        plugin: this
      });
    }
    this.pane.show();
  }
}
export default QuestionPlugin;
