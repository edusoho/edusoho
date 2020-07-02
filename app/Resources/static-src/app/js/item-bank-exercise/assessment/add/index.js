import Selector from "../../../question-bank/common/selector";

class BatchAddAssessmentExercise {
  constructor() {
    this.table = $('.js-testpaper-html');
    this.element = $('#batch-add');
    this.selector = new Selector(this.table);
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    this.element.on('click', (event) => {
      this.onBatchAdd(event);
    });
  }

  onBatchAdd(event) {
    let $target = $(event.currentTarget);
    let name = $target.data('name');
    let ids = this.selector.toJson();
    if (ids.length === 0) {
      cd.message({type: 'danger', message: Translator.trans('site.data.uncheck_name_hint', {'name': name})});
      return;
    }

    cd.confirm({
      title: Translator.trans('site.data.add_title_hint', {'name': name}),
      content: Translator.trans('site.data.add_check_name_hint', {'name': name}),
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.close'),
    }).on('ok', () => {
      $.post($target.data('url'), {ids: ids}, function (response) {
        if (response) {
          cd.message({type: 'success', message: Translator.trans('site.add_success_hint')});
          window.location.reload();
        } else {
          cd.message({type: 'danger', message: Translator.trans('site.add_fail_hint')});
        }
      }).error(function (error) {
        cd.message({type: 'danger', message: Translator.trans('site.add_fail_hint')});
      });
    });
  }
}

new BatchAddAssessmentExercise();


