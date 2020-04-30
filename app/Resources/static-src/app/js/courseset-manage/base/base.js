import Detail from './detail';

export default class Base {
  constructor(element) {
    this.init();
    this.detail = new Detail(element);
  }

  init() {
    this.initValidator();
    this.initTags();
  }

  initValidator() {
    const self = this;
    const $form = $('#title').closest('form');
    let $oldSummary = $('#courseset-summary-field').val();
    $form.validate({
      currentDom: '#courseset-base-submit',
      ajax: true,
      rules: {
        title: {
          byte_maxlength: 200,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
          course_title: true
        },
        subtitle: {
          maxlength: 50,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return false;
            }
          },
          course_title: true
        }
      },
      submitHandler: function(form){
        let $form = $(form);
        let settings = this.settings;
        let $btn = $(settings.currentDom);
        let $isCoursesSummaryEmpty = $form.data('value');
        let $newSummary = $('#courseset-summary-field').val();
        if (!$btn.length) {
          $btn = $(form).find('[type="submit"]');
        }
        if ($isCoursesSummaryEmpty == 1 && $newSummary != '' && $newSummary != $oldSummary) {
          cd.confirm({
            title: Translator.trans('course_set.manage.operation_hint'),
            content: Translator.trans('course_set.manage.courseset_summary_operation_hint'),
            okText: Translator.trans('site.confirm'),
            cancelText: Translator.trans('site.cancel'),
          }).on('ok', () => {
            self.savePost(form, settings);
          });
        } else {
          self.savePost(form, settings);
        }
      },
      submitSuccess: (data) => {
        cd.message({ type: 'success', message: Translator.trans('site.save_success_hint') });
        window.location.reload();
      }
    });
  }

  savePost(form, settings) {
    let $form = $(form);
    let $btn = $(settings.currentDom);
    $btn.button('loading');
    $.post($form.attr('action'), $form.serializeArray(), (data) => {
      $btn.button('reset');
      settings.submitSuccess(data);
    }).error((data) => {
      $btn.button('reset');
      settings.submitError(data);
    });
  }

  // 通用标签选择组件
  initTags() {
    const $tags = $('#tags');
    $tags.select2({
      ajax: {
        url: $tags.data('url'),
        dataType: 'json',
        quietMillis: 500,
        data (term, page) {
          return {
            q: term,
            page_limit: 10
          };
        },
        results (data) {
          console.log(data);
          return {
            results: data.map((item) => {
              return { id: item.name, name: item.name };
            })
          };
        }
      },
      initSelection (element, callback) {
        const data = [];
        $(element.val().split(',')).each(function () {
          data.push({
            id: this,
            name: this
          });
        });
        callback(data);
      },
      formatSelection (item) {
        return item.name;
      },
      formatResult (item) {
        return item.name;
      },
      formatNoMatches: function() {
        return Translator.trans('validate.tag_required_not_found_hint');
      },
      formatSearching: function() {
        return Translator.trans('site.searching_hint');
      },
      multiple: true,
      maximumSelectionSize: 20,
      placeholder: Translator.trans('course_set.manage.tag_required_hint'),
      width: 'off',
      createSearchChoice () {
        return null;
      }
    });
  }
}