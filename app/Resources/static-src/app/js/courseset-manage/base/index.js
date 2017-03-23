import 'select2';
class Base {
  constructor() {
    this.init();
  }

  init() {
    this.initValidator();
    this.initSelect2();
  }

  initValidator() {
    const $form = $('#courseset-form');
    const validator = $form.validate({
      rules: {
        title: {
          maxlength: 100,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
          open_live_course_title: true// @TODO只有直播课程和直播公开课程需要此验证
        },
        subtitle: {
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return false;
            }
          }
        }
      },
      messages: {
        title: {
          required: '请输入有效的课程标题（直播公开课标题暂不支持<、>、"、&、‘、’、”、“字符）'
        }
      }
    });
    $('#courseset-base-submit').click((event) => {
      if (validator.form()) {
        $(event.currentTarget).button('loading');
        $form.submit();
      }
    });
  }

  initSelect2() {
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
      formatSearching: '搜索中...',
      multiple: true,
      maximumSelectionSize: 20,
      placeholder: Translator.trans('请输入标签'),
      width: 'off',
      createSearchChoice () {
        return null;
      }
    });
  }
}

new Base();
