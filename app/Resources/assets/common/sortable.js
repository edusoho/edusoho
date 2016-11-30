const sortList = (options) => {
  let defaultOptions = {
    element: '#sortable-list',
    distance: 20,
    itemSelector: "li.drag",
    // success: (response) => {},
  }

  let settings = Object.assign({}, defaultOptions, options);

  let $list = $(settings.element).sortable(Object.assign({}, settings, {

    onDrop: function (item, container, _super) {
      _super(item, container);

      let data = $list.sortable("serialize").get();

      //排序URL
      $.post($list.data('sortUrl'), {ids: data}, (response) => {

        settings.success ? settings.success(response) : document.location.reload();
        
      });
    },

    serialize: function(parent, children, isContainer) {
      return isContainer ? children : parent.attr('id');
    }

  }))
}


export default sortList;