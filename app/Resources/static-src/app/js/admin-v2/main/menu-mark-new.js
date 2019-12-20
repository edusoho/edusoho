const markNeedMarkedMenus = () => {
  let localSetting = '';
  if (window.localStorage) {
    localSetting = window.localStorage.getItem('markMenuList') ? window.localStorage.getItem('markMenuList') : '';
    localSetting = localSetting.split(',');

    $('.js-new-corner-mark').each((index, item) => {
      if ($.inArray($(item).attr('id'), localSetting) < 0) {
        $(item).addClass('new');
      }
    });
  }

};

const clickEvent = () => {
  if (window.localStorage) {
    let localSetting = window.localStorage.getItem('markMenuList') ? window.localStorage.getItem('markMenuList') : '';
    localSetting = localSetting.split(',');

    $('.js-new-corner-mark').on('click', () => {
      let $self = $(event.currentTarget);
      if ($.inArray($self.attr('id'), localSetting) < 0) {
        localSetting.push($self.attr('id'));
        window.localStorage.setItem('markMenuList', localSetting.join(','));
      }
    });
  }
};

$(document).ready(() => {
  markNeedMarkedMenus();
  clickEvent();
});

