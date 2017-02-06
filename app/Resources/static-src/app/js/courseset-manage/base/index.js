import 'select2';
class Base {
  constructor() {
    this.init();
  }

  init() {
    //init ui components
    $('#tags').select2({
      ajax: {
        url: '/tag/match_jsonp#',
        dataType: 'json',
        quietMillis: 100,
        data: function(term, page) {
          return {
            q: term,
            page_limit: 10
          };
        },
        results: function(data) {
          var results = [];
          $.each(data, function(index, item) {
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
      initSelection: function(element, callback) {
        var data = [];
        $(element.val().split(",")).each(function() {
          data.push({
            id: this,
            name: this
          });
        });
        callback(data);
      },
      formatSelection: function(item) {
        return item.name;
      },
      formatResult: function(item) {
        return item.name;
      },
      formatSearching: '搜索中...',
      width: 'off',
      multiple: true,
      maximumSelectionSize: 20,
      placeholder: Translator.trans('请输入标签'),
      width: 'off',
      multiple: true,
      createSearchChoice: function() {
        return null;
      },
      maximumSelectionSize: 20
    });

    let $form = $('#courseset-form');
    let validator = $form.validate({
      onkeyup: false,
      rules: {
        title: {
          required: true,
          open_live_course_title: true
        }
      },
      messages: {
        title: "请输入有效的课程标题（直播公开课标题暂不支持<、>、\"、&、‘、’、”、“字符）"
      }
    });

    $.validator.addMethod("open_live_course_title", function(value, element, params) {
      if ($('#courseSetType').val() === 'liveOpen' && !/^[^(<|>|'|"|&|‘|’|”|“)]*$/.test(value)) {
        return false;
      } else {
        return true;
      }
    }, Translator.trans('直播公开课标题暂不支持<、>、\"、&、‘、’、”、“字符'));

    $('#courseset-base-submit').click(event => {
      if (validator.form()) {
        $(event.currentTarget).button('loading');
        $form.submit();
      }
    });
  }

}

new Base();
