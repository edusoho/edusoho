import notify from 'common/notify';
import BatchSelect from '../../common/widget/batch-select';
import DeleteAction from '../../common/widget/delete-action';
import 'store';
const TESTPAPER_IMPORT_INTRO = 'TESTPAPER_IMPORT_INTRO';

class TestpaperManage
{
  constructor($container) {

    this.$container = $container;
    this._initEvent();
    this._init();
    if (!store.get(TESTPAPER_IMPORT_INTRO)) {
      store.set(TESTPAPER_IMPORT_INTRO, true);
      this.importIntro();
    }

  }

  _initEvent() {
    this.$container.on('click','.open-testpaper,.close-testpaper',event=>this.testpaperAction(event));

  }

  _init() {

  }

  testpaperAction(event) {
    let $target = $(event.currentTarget);
    let $tr = $target.closest('tr');

    if (!confirm($target.attr('title'))) {
      return ;
    }

    $.post($target.data('url'), function(html){
      notify('success', Translator.trans('testpaper_manage.save_success_hint'));
      $tr.replaceWith(html);
    }).error(function(){
      notify('danger', Translator.trans('testpaper_manage.save_error_hint'));
    });
  }

  importIntro() {
    const doneLabel = Translator.trans('document.import.skip_btn');
    const customClass = 'import-intro';
    $('.js-import-btn').attr('data-toggle','toggle'); // 禁止按钮点击效果
    introJs().setOptions({
      steps: [{
        element: '.js-import-btn',
        intro: Translator.trans('document.import.intro_hover'),
      }],
      skipLabel: doneLabel,
      doneLabel: doneLabel,
      showBullets: false,
      tooltipPosition: 'down',
      showStepNumbers: false,
      exitOnEsc: false,
      exitOnOverlayClick: false,
      tooltipClass: customClass
    }).start();
    $(".introjs-button.introjs-skipbutton").on('click', function () {
      $('.js-import-btn').attr('data-toggle','modal'); // 解禁按钮点击行为
    });
  }

}

let $container = $('#quiz-table-container');
new TestpaperManage($container);
new BatchSelect($container);
new DeleteAction($container);