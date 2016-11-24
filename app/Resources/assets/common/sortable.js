const sortList = (element,options,callback) => {
  let defaultOptions = {
    distance: 20,
    itemSelector: "li.drag",
  }

  let $list = $(element).sortable(Object.assign({}, defaultOptions, options, {

    onDrop: function (item, container, _super) {
      _super(item, container);
      
      let data = $list.sortable("serialize").get();

      //排序URL
      $.post($list.data('sortUrl'), {ids: data}, (response) => {

        callback ? callback(response) : document.location.reload();
        
      });
    },

    serialize: function(parent, children, isContainer) {
      return isContainer ? children : parent.attr('id');
    }

  }))
}


export default sortList;