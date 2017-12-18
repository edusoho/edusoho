define(function(require, exports, module) {
    require('jquery.sortable');
    exports.run = function() {
      var themeManage = $('body').data('themeManage', themeManage);
      var $themeEditContent = $('.js-theme-component');

      var $list = $(".module-item-list").sortable({
        distance: 20,
        itemSelector: '.theme-edit-item',
        onDrop: function (item, container, _super) {
            _super(item, container);
            sortList($list);
            themeManage.getElement().trigger('save_sort');
        },
        serialize: function(parent, children, isContainer) {
            return isContainer ? children : parent.attr('id');
        }
      });

      $("#theme-edit-content .theme-edit-block").on('click', '.item-edit-btn', function(){
          themeManage.setCurrentItem($(this).parents('li.theme-edit-item'));
      });


      $themeEditContent.on("click", '.check-block', function(event){
          event.stopPropagation();
          themeManage.setCurrentItem($(this).parents('li.theme-edit-item'));
          
          if ($(this).prop('checked') == true) {
              $(this).parents('li').find('.item-edit-btn,.item-set-btn').show();
          } else {
              $(this).parents('li').find('.item-edit-btn,.item-set-btn').hide();
          }

          themeManage.getElement().trigger('save_config');
      });


      var sortList = function($list) {
        var data = $list.sortable("serialize").get();
        $.post($list.data('sortUrl'), {ids:data}, function(response){
            var lessonNum = chapterNum = unitNum = 0;
        });
      };

    }
});