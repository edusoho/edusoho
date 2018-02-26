import 'jquery-sortable';

const sortList = (options, callback = (data) => {}) => {
  let defaultOptions = {
    element: '#sortable-list',
    distance: 20,
    itemSelector: "li.drag",
    ajax: true,
  };

  let settings = Object.assign({}, defaultOptions, options);
  let $element = settings.element instanceof jQuery ? settings.element : $(settings.element);
  let adjustment;
  let $list = $element.sortable(Object.assign({}, settings, {
    onDrop: function (item, container, _super) {
      _super(item, container);
      let data = $list.sortable("serialize").get();
      callback(data);
      if(settings.ajax) {
        $.post($list.data('sortUrl'), { ids: data }, (response) => {
          settings.success ? settings.success(response) : document.location.reload();
        });
      }
      item.removeClass('task-dragged-rotate');
    },
    serialize: function(parent, children, isContainer) {
      return isContainer ? children : parent.attr('id');
    },
     // set item relative to cursor position
    onDragStart: function (item, container, _super) {
      let offset = item.offset(),
          pointer = container.rootGroup.pointer;
      adjustment = {
        left: pointer.left - offset.left,
        top: pointer.top - offset.top
      };
      item.addClass('task-dragged-rotate');
      _super(item, container);
    },
    onDrag: function (item, position) {
      item.css({
        left: position.left - adjustment.left,
        top: position.top - adjustment.top
      });
    },
  }));
};


export default sortList;