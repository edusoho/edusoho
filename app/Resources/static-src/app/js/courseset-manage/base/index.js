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
    let $form = $('#courseset-form');
    let validator = $form.validate({
      rules: {
        title: {
          maxlength: 100,
          required: {
            depends: function () {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
          open_live_course_title: true//@TODO只有直播课程和直播公开课程需要此验证
        },
        subtitle: {
          required: {
            depends: function () {
              $(this).val($.trim($(this).val()));
              return false;
            }
          }
        }
      },
      messages: {
        title: {
          required: "请输入有效的课程标题（直播公开课标题暂不支持<、>、\"、&、‘、’、”、“字符）"
        }
      }
    });
    $('#courseset-base-submit').click(event => {
      if (validator.form()) {
        $(event.currentTarget).button('loading');
        $form.submit();
      }
    });
  }

  initSelect2() {
    $('#tags').select2({
      ajax: {
        url: '/tag/match_jsonp#',
        dataType: 'json',
        quietMillis: 100,
        data: function (term, page) {
          return {
            q: term,
            page_limit: 10
          };
        },
        results: function (data) {
          let results = [];
          $.each(data, function (index, item) {
            results.push({
              id: item.name,
              name: item.name
            });
          });
          return {
            results: results
          };
        }
      },
      initSelection: function (element, callback) {
        let data = [];
        $(element.val().split(",")).each(function () {
          data.push({
            id: this,
            name: this
          });
        });
        callback(data);
      },
      formatSelection: function (item) {
        return item.name;
      },
      formatResult: function (item) {
        return item.name;
      },
      formatSearching: '搜索中...',
      multiple: true,
      maximumSelectionSize: 20,
      placeholder: Translator.trans('请输入标签'),
      width: 'off',
      createSearchChoice: function () {
        return null;
      }
    });
  }
}

new Base();
