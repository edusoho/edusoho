import MaterialLibChoose from './materiallib-choose';
import CourseFileChoose from './coursefile-choose';
import Emitter from 'component-emitter';

class FileChooser extends Emitter {
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
      $('.js-batch-create-content').find('[data-role=batch-item],[data-role=batch-select]').prop('checked', false);
      $this.closest('li').siblings('li').find('[type="radio"]').prop('checked', false);
      $this.tab('show');
    });
  }

  initFileChooser() {
    new MaterialLibChoose($('#chooser-material-panel'));
    new CourseFileChoose($('#chooser-course-panel'));
  }

  fileSelect(file) {
    this.fillTitle(file);
    this.emit('select', file);
  }

  fillTitle(file) {
    let $title = $('#title');
    if ($title.length > 0 && $title.val() == '') {
      let title = file.name.substring(0, file.name.lastIndexOf('.'));
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
