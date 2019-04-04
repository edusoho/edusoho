var load = window.ltc.load('es-ckeditor-highlight', 'es-ckeditor-highlight-zenburn.css', 'jquery', 'scrollbar');
load.then(function(){
  var context = window.ltc.getContext();
  window.ltc.api({
    name: 'getActivity',
    pathParams: {
      id: context.activityId
    }
  }, function(result) {
    var $content = $(result['content']);
    $('.text-activity-content').append($content);
    document.querySelectorAll('pre code').forEach((block) => {
      hljs.highlightBlock(block);
    });
  });

  $('#text-activity').perfectScrollbar();
  $('#text-activity').perfectScrollbar('update');
  
  if ($('#text-activity').data('disableCopy')) {
    document.oncontextmenu = 
    document.onselectstart = function() {
      return false;
    };
    if (window.sidebar) {
      document.onmousedown =
      document.oncut = 
      document.oncopy = function() {
        return false;
      };
      document.onclick = function() {
        return true;
      };
    }
  
    document.addEventListener('keydown', function (e) {
      if (e.keyCode === 83 && (navigator.platform.match('Mac') ? e.metaKey : e.ctrlKey)) {
        e.preventDefault();
        e.stopPropagation();
      }
    }, false);
  }
});
