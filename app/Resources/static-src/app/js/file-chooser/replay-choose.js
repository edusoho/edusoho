import MaterialLibChoose from './base/materiallib-choose';
import Emitter from 'component-emitter';

class FileChooser extends Emitter{
  constructor(options) {
    super();
    this.init();
  }

  init() {
    this.initFileChooser();
  }

  initFileChooser() {
    const materialLibChoose = new MaterialLibChoose($('#chooser-material-panel'));
    materialLibChoose.on('select', file => this.fileSelect(file));
  }

  fileSelect(file) {
    this.fillTitle(file);
    this.emit('select', file);
  }

  fillTitle(file){
    let $title = $('#title');
    if ($title.length > 0 && $title.val()=='') {
      let title = file.name.substring(0,file.name.lastIndexOf('.') !== -1 ? file.name.lastIndexOf('.') : file.name.length);
      $title.val(title);
    }
  }
  static openUI() {
    $('.file-chooser-bar').addClass('hidden');
    $('.file-chooser-main').removeClass('hidden');
  }

  static closeUI() {
    $('.file-chooser-main').addClass('hidden');
    $('.file-chooser-bar').removeClass('hidden');
  }
}

export default FileChooser;
