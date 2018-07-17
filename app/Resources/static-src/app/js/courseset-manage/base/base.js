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
    const $form = $('#title').closest('form');
    const validator = $form.validate({
      currentDom: '#courseset-base-submit',
      ajax: true,
      rules: {
        title: {
          maxlength: 30,
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
      submitSuccess: (data) => {
        cd.message({ type: 'success', message: Translator.trans('site.save_success_hint') });
      }
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