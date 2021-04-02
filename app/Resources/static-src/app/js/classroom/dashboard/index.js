$(document).ready(function () {
  $(window).resize();
});
$(window).resize(function () {
  isNavTabsScroll();
});

function isNavTabsScroll () {
  const $navTabs = $('.nav-tabs');
  const width = Math.ceil($navTabs.width());
  if (width < $navTabs[0].scrollWidth) {
    $navTabs.addClass('nav-tabs-scroll') ;
    $('.nav-tabs-scroll').perfectScrollbar();
    $('.nav-tabs-scroll').perfectScrollbar('update');
  } else {
    $navTabs.removeClass('nav-tabs-scroll');
  }
}
 
