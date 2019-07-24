// import QuestionPicker from '../../../common/component/question-picker';
import BatchSelect from '../../common/widget/batch-select';
import DeleteAction from '../../common/widget/delete-action';
import { shortLongText } from '../../common/widget/short-long-text';
import SelectLinkage from 'app/js/question-manage/widget/select-linkage.js';
import 'store';
const QUESTION_IMPORT_INTRO = 'QUESTION_IMPORT_INTRO';
class QuestionManage
{
  constructor($container) {

    this.$container = $container;
    if (!store.get(QUESTION_IMPORT_INTRO)) {
      store.set(QUESTION_IMPORT_INTRO, true);
      this.importIntro();
    }

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

// new QuestionPicker($('#quiz-table-container'), $('#quiz-table'));
new BatchSelect($('#quiz-table-container'));
new DeleteAction($('#quiz-table-container'));
shortLongText($('#quiz-table-container'));
new SelectLinkage($('[name="courseId"]'),$('[name="lessonId"]'));
new QuestionManage($('#quiz-table-container'));


