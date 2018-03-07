const Select = (element, type, options) => {
  let config = {};
  /**
   * type 类型
   * element 对象
   * opyions 配置项
   */
  if (type === 'remote') {
    config = {
      ajax: {
        url: $(element).data('url'),
        dataType: 'json',
        quietMillis: 100,
        data(term, page) {
          return {
            q: term,
            page_limit: 10
          };
        },
        results(data) {
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
      initSelection(element, callback) {
        let data = [];
        $(element.val().split(',')).each(function() {
          data.push({
            id: this,
            name: this
          });
        });
        callback(data);
      },
      formatSelection(item) {
        return item.name;
      },
      formatResult(item) {
        return item.name;
      },
      width: 400,
      multiple: true,
      placeholder: Translator.trans('validate.tag_required_hint'),
      createSearchChoice() {
        return null;
      },
      maximumSelectionSize: 20
    };
  }

  $(element).select2(Object.assign(config, options));
};

export default Select;