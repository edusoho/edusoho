import Cookies from 'js-cookie';

$('.js-show-toggle').on('click', (event) => {
  const $target = $(event.currentTarget);
  $('.js-steps').slideToggle();
  const value = $('.js-toggle-text').text() === '收起' ? '展开': '收起';
  $('.js-toggle-text').text(value);
  $target.find('i').toggleClass('es-icon-keyboardarrowup es-icon-keyboardarrowdown');
});

const $modal = $('#functionModal');
const $html = $('html');
$modal.on('shown.bs.modal', (e) =>  {
  $html.css('overflow', 'hidden');
}).on('hidden.bs.modal', (e) => {
  $html.css('overflow', 'scroll');
});


$('.js-entrance-list').on('click', '.js-function-choose', (event) => {
  const $target = $(event.currentTarget);
  $target.toggleClass('active');
});

let currentItem = [];

$('.js-save-btn').on('click', (event) => {
  if ($('.js-function-choose.active').length > 7) {
    cd.message({type: 'danger', message: '最多设置7个快捷入口位'});
    return;
  }
  const $quickItem = $('.js-function-body').find('.js-function-choose');
  $quickItem.each(item => {
    const code = $(item).data('link');
    const flag = $(item).hasClass('active');
    if (flag && !currentItem.includes(code)) {
      currentItem.push(code);
    }
  });
  $modal.modal('hide');
});


const showAdImage = ($cloudAd, img, res) => {
  const $img = $(img);
  const $box = $cloudAd.find('.modal-dialog');
  const boxWidth = $box.width() ? $box.width() : $(window).width()-20;
  const WindowHeight = $(window).height();
  const width = img.width;
  let height = img.height;
  let marginTop = 0;
  if ((width/height) >= (4/3)) {
    height = width > boxWidth ? height/(width/boxWidth) : height*(boxWidth/width);
    marginTop = (WindowHeight-height)/2;
  } else {
    height = WindowHeight > 600 ? 600 : WindowHeight * 0.9;
    $img.height(height);
    $img.width('auto');
    marginTop = (WindowHeight - height)/2;
  }
  $cloudAd.find('a').attr('href',res.urlOfImage).append($img).css({'margin-top': marginTop});
  $cloudAd.modal('show');
};

const showCloudAd = () => {
  const $cloudAd = $('#cloud-ad');
  $.get($cloudAd.data('url'), (res) => {
    if (res.error) {
      return;
    }
    const img = new Image();
    if (Cookies.get('cloud-ad') == res.image) {
      return;
    }
    img.src = res.image;
    if (img.complete) {
      showAdImage($cloudAd, img, res);
    } else {
      img.onload = () => {
        showAdImage($cloudAd, img, res);
        img.onload = null;
      };
    }
  });

  $cloudAd.on('hide.bs.modal', () => {
    Cookies.set('cloud-ad', $cloudAd.find('img').attr('src'),{ expires:360*10 });
  });
};

window.onload = () => {
  showCloudAd();
};
