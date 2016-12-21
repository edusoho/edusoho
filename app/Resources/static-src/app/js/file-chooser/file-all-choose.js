import UploadChooser from './base/upload-chooser';
import Emitter from 'es6-event-emitter';

class FileAllChoose extends Emitter {

  constructor($uploaderPanel) {
    super();
    this.uploaderPanel = $uploaderPanel;
    this.init();
  }

  init() {
    this.initFileChooser();
  }

  initFileChooser() {
    const uploader = new UploadChooser(this.uploaderPanel);
    uploader.on('select', file => this.fileSelect(file));
  }

  fileSelect(file) {
    this.trigger('select', file);
  }

}

export default FileAllChoose ;