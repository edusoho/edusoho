import MaterialLibChoose from './base/materiallib-choose';
import Emitter from 'component-emitter';

class ReplayChooser extends Emitter{
  constructor(options) {
    super();
    this.init();
  }

  init() {
    this.initFileChooser();
    $('#keywordType').on('change',function(){
      let val = $(this).val();
      $('input[name=keywordType]').val(val);
    });
  }

  initFileChooser() {
    const materialLibChoose = new MaterialLibChoose($('#chooser-material-panel'));
    materialLibChoose.on('select', replay => this.fileSelect(replay));
  }

  fileSelect(replay) {
    this.fillTitle(replay);
    this.emit('select', replay);
    $('#minute').attr('disabled',true);
    $('#second').attr('disabled',true);
  }

  fillTitle(replay){
    let $title = $('#title');
    if ($title.length > 0 && $title.val()=='') {
      let title = replay.name.substring(0,replay.name.lastIndexOf('.') !== -1 ? replay.name.lastIndexOf('.') : replay.name.length);
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

export default ReplayChooser;
