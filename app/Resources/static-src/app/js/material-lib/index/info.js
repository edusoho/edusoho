import notify from 'common/notify';

export default class Info {
  constructor(options) {
    this.element = options.element;
    this.callback = options.callback;
    this.init();
  }
  init() {
    this.initEvent();
    this._initTag();
  }
  initEvent() {
    $('#info-form').on('submit', (event) => {
      this.onSubmitInfoForm(event);
    })
  }
  _initTag() {
    const $tags = $('#infoTags');
    $tags.select2({
      ajax: {
        url: $tags.data('url'),
        dataType: 'json',
        quietMillis: 500,
        data: function(term, page) {
          return {
            q: term,
            page_limit: 10
          };
        },
        results: function(data) {
          let results = [];
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
        let data = [];
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
      width: 'off',
      multiple: true,
      placeholder: Translator.trans('请输入标签'),
      multiple: true,
      createSearchChoice: function() {
        return null;
      },
      maximumSelectionSize: 20
    });
  }
  onSubmitInfoForm(event) {
    let $target = $(event.currentTarget);
    $target.find('#info-save-btn').button('loading');
    $.ajax({
     type: 'POST',
     url: $target.attr('action'),
     data: $('#info-form').serialize()

    }).done(function() {
      notify('success', Translator.trans('保存成功！'));

    }).fail(function() {
      notify('danger', Translator.trans('保存失败！'));

    }).always(function() {
      $target.find('#info-save-btn').button('reset');
    });

    event.preventDefault();
  }
}
