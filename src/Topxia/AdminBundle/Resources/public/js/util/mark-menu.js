define(function(require, exports, module) {
  var markMenus = ['menu_admin_app', 'menu_admin_cloud_data_lab_manage'], ids = '';
  for (index in markMenus) {
      ids+= '#'+ markMenus[index]+',';
  }
  ids = s.substring(0,s.length-1);
  $(ids).each(function(element) {
    var $this = $(this);
    $this.addClass('new');
  }, this);
});