import BatchSelect from 'app/common/widget/batch-select';
new BatchSelect($('.js-assessment-container'));

class AssessmentModule {
  constructor() {
    this.element = $('.js-all-container');
    this.init();
  }

  init() {
    this.element.on('click', '.js-batch-delete', event => this.batchDelete(event));
    this.element.on('click', '.js-delete-single', event => this.deleteSingle(event));
    this.initChange();
  }

  batchDelete(event) {
    let $target = $(event.currentTarget);
    if (this.element.find('[data-role="batch-item"]:checked').length > 0) {
      let ids = [];
      this.element.find('[data-role="batch-item"]:checked').each(function () {
        ids.push(this.value);
      });

      cd.confirm({
        title: Translator.trans('item_bank_exercise.assessment_module.assessment_delete.title'),
        content: Translator.trans('item_bank_exercise.assessment_module.assessment_delete'),
        okText: Translator.trans('site.confirm'),
        cancelText: Translator.trans('site.close'),
      }).on('ok', () => {
        $.post($target.data('url'), {ids: ids}, function (response) {
          if (response) {
            cd.message({type: 'success', message: Translator.trans('site.delete_success_hint')});
            location.reload();
          } else {
            cd.message({type: 'danger', message: Translator.trans('site.delete_fail_hint')});
          }
        }).error(function (error) {
          cd.message({type: 'danger', message: Translator.trans('site.delete_fail_hint')});
        });
      });
    }
  }

  deleteSingle(event) {
    let $target = $(event.currentTarget);
    cd.confirm({
      title: Translator.trans('item_bank_exercise.assessment_module.assessment_delete.title'),
      content: Translator.trans('item_bank_exercise.assessment_module.assessment_delete'),
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.close'),
    }).on('ok', () => {
      $.post($target.data('url'), function (response) {
        if (response) {
          cd.message({type: 'success', message: Translator.trans('site.delete_success_hint')});
          location.reload();
        } else {
          cd.message({type: 'danger', message: Translator.trans('site.delete_fail_hint')});
        }
      }).error(function (error) {
        cd.message({type: 'danger', message: Translator.trans('site.delete_fail_hint')});
      });
    });
  }

  initChange() {
    cd.onoff({
      el: '#switch'
    }).on('change', (value) => {
      $.get($('#canOpen').val(), function (data) {
        if (data){
          $.post($('#switch').data('url'), {assessmentEnable: value}, function () {
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

new AssessmentModule();
