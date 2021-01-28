import './jquery.autocomplete';

let autocomplete = ({ element, valueKey, url }) => {
  let $element = $(element);
  $element.autocomplete({
    appendMethod: 'replace',
    valueKey: valueKey || 'nickname',
    source: [
      function( q, add ) {
        if (!q) return;
        let source = [];
        let getUrl = url  || $element.data('auto-url');
        
        $.get(`${getUrl}?q=${q}`, (data) => {
          if (data) {
            data.map((item) => {
              source.push(item[this.valueKey]);
            });
          }
          add(source);
        });
      },
    ],
  });
};

export default autocomplete;