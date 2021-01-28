import MaterialLibChoose from './base/materiallib-choose';
import VideoImport from './base/import-video';
import CourseFileChoose from './base/coursefile-choose';
import UploadChooser from './base/upload-chooser';
import Emitter from 'component-emitter';

class FileChooser extends Emitter{
  constructor(options) {
    super();
    this.init();
  }

  init() {
    this.initTab();
    this.initFileChooser();
  }

  initTab() {
    $('#material a').click(function (e) {
      e.preventDefault();
      let $this = $(this);
      $this.find('[type="radio"]').prop('checked', 'checked');
      $this.closest('li').siblings('li').find('[type="radio"]').prop('checked', false);
      $this.tab('show');
    });

    if($('.js-import-video').data('link')){
      $('.js-import-video').click();
    }
  }

  initFileChooser() {
    const materialLibChoose = new MaterialLibChoose($('#chooser-material-panel'));
    const courseFileChoose = new CourseFileChoose($('#chooser-course-panel'));
    const videoImport = new VideoImport($('#import-video-panel'));
    const uploader = new UploadChooser($('#chooser-upload-panel'));
    materialLibChoose.on('select', file => this.fileSelect(file));
    courseFileChoose.on('select', file => this.fileSelect(file));
    videoImport.on('file.select', file => this.fileSelect(file));
    uploader.on('select', file => this.fileSelect(file));
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
