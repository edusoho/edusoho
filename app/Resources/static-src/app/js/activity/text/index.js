$('#text-activity').perfectScrollbar();
$('#text-activity').perfectScrollbar('update');

if ($('#text-activity').data('disableCopy')) {
  document.onselectstart = new Function('return false');
  document.oncontextmenu = new Function('return false');
  if (window.sidebar) {
    document.onmousedown = new Function('return false');
    document.onclick = new Function('return true');
    document.oncut = new Function('return false');
    document.oncopy = new Function('return false');
  }
  document.addEventListener('keydown', function (e) {
    if (e.keyCode === 83 && (navigator.platform.match('Mac') ? e.metaKey : e.ctrlKey)) {
      e.preventDefault();
      e.stopPropagation();
    }
  }, false);
}