import ThreadShowWidget from 'app/js/thread/thread-show';

let element = '#detail-content';

let threadShowWidget = new ThreadShowWidget({
  element: element
});
let copyEnabled = $('.js-article-copy-body').data('copy');
if (copyEnabled) {
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


$(element).on('click', '.js-article-like', function() {
  const $self = $(this);
  if ($self.hasClass('color-primary')) {
    $.post($self.data('cancelLikeUrl'), function(article) {
      $('.article-content').find('.js-like-num').html(article.upsNum);
    }).always(function(){
      $self.removeClass('color-primary');
      $self .closest('.icon-favour').removeClass('active');
    });
  } else {
    $.post($self.data('likeUrl'), function(article) {
      $('.article-content').find('.js-like-num').html(article.upsNum);
    }).always(function(){
      $self.addClass('color-primary');
      $self.closest('.icon-favour').addClass('active');
    });
              
  }
});

