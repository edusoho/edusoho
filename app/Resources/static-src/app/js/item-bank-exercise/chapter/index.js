class ChapterToogle {

  constructor() {
    this.toogle();
  }

  toogle(){
    cd.onoff({
      el: '#switch'
    }).on('change', (value) => {
      $.get($('#canOpen').val(), function (data) {
        if (data){
          $.post($('#openUrl').val(), {exerciseId:$('#exerciseId').val(),chapterEnable: value}, function () {
            location.reload();
          });
        }else {
          cd.message({ type: 'danger', message: Translator.trans('item_bank_exercise.module.switch.danger') });
          setTimeout(function () {
            window.location.reload();
          }, 1000);
        }
      });
    });
  }

}
new ChapterToogle();