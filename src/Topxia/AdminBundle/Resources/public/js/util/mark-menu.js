define(function(require, exports, module) {
  var markMenus = ['menu_admin_app', 'menu_admin_cloud_data_lab_manage'], ids = '';
  for (index in markMenus) {
      ids+= '#'+ markMenus[index]+',';
  }

  ids = ids.substring(0,ids.length-1);
  var $menus = $(ids);


  if (window.localStorage) {
    var localSetting = window.localStorage.getItem('markMenuList');
    if (!localSetting) {
      localSetting = '';
    }
    localSetting = localSetting.split(',') 

    $menus.on('click', function(){
        localSetting.push(this.id);
        window.localStorage.setItem('markMenuList', localSetting.join(','));
    })
  }

  $menus.addClass('new');
});

