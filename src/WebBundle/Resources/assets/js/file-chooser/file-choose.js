import MaterialLibChoose from './base/materiallib-choose';
import VideoImport from './base/import-video';
import CourseFileChoose from './base/coursefile-choose';
import UploadChooser from './base/upload-chooser';
import Emitter from 'es6-event-emitter';

class FileChooser extends Emitter {

  constructor(options) {
    super();
    this.init();

  }

  init() {
    this.initFileChooser();
    this.initTab();
  }

  initTab() {
    $("#material a").click(function (e) {
      e.preventDefault();
      var $this = $(this);
      $this.find('[type="radio"]').prop('checked', 'checked');
      $this.closest('li').siblings('li').find('[type="radio"]').prop('checked', false);
      $this.tab('show')
    });
  }

  initFileChooser() {
    const materialLibChoose = new MaterialLibChoose($('#chooser-material-panel'));
    const courseFileChoose = new CourseFileChoose($('#chooser-course-panel'));
    const videoImport = new VideoImport($('#import-video-panel'));
    // const uploader = new UploadChooser($('#chooser-upload-panel'));
    materialLibChoose.on('select', this.fileSelect.bind(this));
    courseFileChoose.on('select', this.fileSelect.bind(this));
    videoImport.on('file.select', this.fileSelect.bind(this));
    // uploader.on('select', this.fileSelect.bind(this));
  }

  fileSelect(file) {
    this.trigger('select', file);
  }

}

export default FileChooser ;
