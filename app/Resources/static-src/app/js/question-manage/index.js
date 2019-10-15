// import QuestionPicker from '../../../common/component/question-picker';
import BatchSelect from '../../common/widget/batch-select';
import BaseDeleteAction from '../../common/widget/delete-action';
import {shortLongText} from '../../common/widget/short-long-text';
import SelectLinkage from 'app/js/question-manage/widget/select-linkage.js';
import 'store';

const QUESTION_IMPORT_INTRO = 'QUESTION_IMPORT_INTRO';

class QuestionManage {
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
    $('.js-import-btn').attr('data-toggle', 'toggle'); // 禁止按钮点击效果
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
    $('.introjs-button.introjs-skipbutton').on('click', function () {
      $('.js-import-btn').attr('data-toggle', 'modal'); // 解禁按钮点击行为
    });
  }

}

class DeleteAction extends BaseDeleteAction {
  _itemDelete(event) {
    let $btn = $(event.currentTarget);

    let name = $btn.data('name');
    let self = this;
    let content = '<br><div class="help-block">' + Translator.trans('course.question_manage.manage.delete_tips') + '</div>';

    cd.confirm({
      title: Translator.trans('site.data.delete_title_hint', {'name': name}),
      content: Translator.trans('site.data.delete_name_hint', {'name': name}) + content,
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.close'),
    }).on('ok', () => {
      $.post($btn.data('url'), function () {
        if ($.isFunction(self.onSuccess)) {
          self.onSuccess.call(self.$element);
        } else {
          $btn.closest('[data-role=item]').remove();
          cd.message({type: 'success', message: Translator.trans('site.delete_success_hint')});
          window.location.reload();
        }
      });
    });
  }

  _batchDelete(event) {
    let $btn = $(event.currentTarget);
    let name = $btn.data('name');
    let content = '<br><div class="help-block">' + Translator.trans('course.question_manage.manage.delete_tips') + '</div>';

    let ids = [];
    this.$element.find('[data-role="batch-item"]:checked').each(function () {
      ids.push(this.value);
    });

    if (ids.length == 0) {
      cd.message({type: 'danger', message: Translator.trans('site.data.uncheck_name_hint', {'name': name})});
      return;
    }

    cd.confirm({
      title: Translator.trans('site.data.delete_title_hint', {'name': name}),
      content: Translator.trans('site.data.delete_check_name_hint', {'name': name}) + content,
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.close'),
    }).on('ok', () => {
      $.post($btn.data('url'), {ids: ids}, function () {
        window.location.reload();
      });
    });
  }
}

// new QuestionPicker($('#quiz-table-container'), $('#quiz-table'));
new BatchSelect($('#quiz-table-container'));
new DeleteAction($('#quiz-table-container'));
shortLongText($('#quiz-table-container'));
new SelectLinkage($('[name="courseId"]'), $('[name="lessonId"]'));
new QuestionManage($('#quiz-table-container'));


