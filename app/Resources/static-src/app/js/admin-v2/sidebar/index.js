const sideBarData = $('.js-side-bar-data').data('value');
const code = $('.js-sidebar-container').data('code');
const data = {
  data: sideBarData,
  code: code
};
cd.layout({data});
