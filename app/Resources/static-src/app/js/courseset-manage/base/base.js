import Detail from './detail';

export default class Base {
  constructor() {
    this.init();
    this.detail = new Detail();
  }

  init() {
    this.initValidator();
    this.initTags();
  }

  initValidator() {
    const $form = $('#title').closest('form');
    const validator = $form.validate({
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
    });

    $('#courseset-base-submit').click((event) => {
      if (validator.form()) {
        $(event.currentTarget).button('loading');
        $form.submit();
      }
    });
  }

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
        return '未搜索到标签，请网校管理员通过【管理后台】-【课程】-【标签管理】进行设置。';
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