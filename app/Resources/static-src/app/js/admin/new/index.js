let sideBarData = $('#js-side-bar-data').data('value');
let code = $('.cd-sidebar-container').data('code');
console.log(code);
let data = {
  title:'用户',
  data: sideBarData,
  code: code
};
cd.layout({data});
