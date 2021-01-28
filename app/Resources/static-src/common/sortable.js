import 'es-jquery-sortable';

const sortList = (options, callback = () => {}) => {
  let defaultOptions = {
    element: '#sortable-list',
    distance: 20,
    itemSelector: 'li.drag',
    ajax: true,
    onDrop: function (item, container, _super) {
      _super(item, container);
      let data = $list.sortable('serialize').get();
      callback(data);
      if(settings.ajax) {
        $.post($list.data('sortUrl'), { ids: data }, (response) => {
          settings.success ? settings.success(response) : document.location.reload();
        });
      }
    },
  };

  let settings = Object.assign({}, defaultOptions, options);
  let $element = settings.element instanceof jQuery ? settings.element : $(settings.element);
  let $list = $element.sortable(Object.assign({}, settings, {
    serialize: function(parent, children, isContainer) {
      return isContainer ? children : parent.attr('id');
    },
  }));
};


export default sortList;