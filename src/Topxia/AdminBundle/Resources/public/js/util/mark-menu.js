define(function(require, exports, module) {
  var markMenus = ['menu_admin_app', 'menu_admin_cloud_data_lab_manage'], ids = '';
  var localSetting = getLocalSetting(markMenus);

  clickEvent();

  function clickEvent() {
    for (index in markMenus) {
      if ($.inArray(markMenus[index], localSetting) == -1) {
        ids+= '#'+ markMenus[index]+',';
      }
    }

    ids = ids.substring(0,ids.length-1);
    var $menus = $(ids);
    $menus.addClass('new');
    if (window.localStorage) {
      $menus.on('click', function(){
          if ($.inArray(this.id, localSetting) == -1) {
            $(this).removeClass('new');
            localSetting.push(this.id);
            window.localStorage.setItem('markMenuList', localSetting.join(','));
          }
      })
    }
  }

  function getLocalSetting(markMenus) {
    var newLocalSetting = [];
    if (window.localStorage) {
      localSetting = window.localStorage.getItem('markMenuList') ? window.localStorage.getItem('markMenuList') : '';
      localSetting = localSetting.split(',');
      var newLocalSetting = [];
      for (index in localSetting) {
        if ($.inArray(localSetting[index], markMenus) > -1) {
          newLocalSetting.push(localSetting[index]);
        }
      }
    }

    return newLocalSetting;
  }
});

