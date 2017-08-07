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
        url: $(element).data('url') + '#',
        dataType: 'json',
        quietMillis: 100,
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
      width: 400,
      multiple: true,
      placeholder: Translator.trans("请输入标签"),
      multiple: true,
      createSearchChoice: function() {
        return null;
      },
      maximumSelectionSize: 20
    }
  }
  
  $(element).select2(Object.assign(config, options));
};

export default Select;