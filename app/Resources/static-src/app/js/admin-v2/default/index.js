import Cookies from 'js-cookie';

$('.js-show-toggle').on('click', (event) => {
  const $target = $(event.currentTarget);
  $('.js-steps').slideToggle();
  const value = $('.js-toggle-text').text() === '收起' ? '展开': '收起';
  $('.js-toggle-text').text(value);
  $target.find('i').toggleClass('es-icon-keyboardarrowup es-icon-keyboardarrowdown');
});

const showAdImage = ($cloudAd, img, res) => {
  const $img = $(img);
  const $box = $cloudAd.find('.modal-dialog');
  const boxWidth = $box.width() ? $box.width() : $(window).width() - 20;
  const WindowHeight = $(window).height();
  const width = img.width;
  let height = img.height;
  let marginTop = 0;
  if ((width / height) >= (4 / 3)) {
    height = width > boxWidth ? height / (width / boxWidth) : height * (boxWidth / width);
    marginTop = (WindowHeight - height) / 2;
  } else {
    height = WindowHeight > 600 ? 600 : WindowHeight * 0.9;
    $img.height(height);
    $img.width('auto');
    marginTop = (WindowHeight - height) / 2;
  }
  $cloudAd.find('a').attr('href', res.urlOfImage).append($img).css({'margin-top': marginTop});
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
    Cookies.set('cloud-ad', $cloudAd.find('img').attr('src'), {expires: 360 * 10});
  });
};

const src = $('.js-mini-program').data('src');
$('.js-mini-program').popover({
  trigger: 'hover',
  placement: 'bottom',
  title: '扫码打开小程序',
  template: '<div class="popover mini-program-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
  html: true,
  content: `<img src="${src}" width="200px">`
});

const loadFirst = () => {
  //六步任务助手
  if ($('.js-steps').length) {
    $.get($('.js-steps').data('url'), (html) => {
      $('.js-steps').html(html);
    });
  }

  // 数据概览
  if ($('.js-data-overview').length) {
    $.get($('.js-data-overview').data('url'), (html) => {
      $('.js-data-overview').html(html);
    });
  }

  // 快速入口
  if ($('.js-quick-entrance').length) {
    $.get($('.js-quick-entrance').data('url'), (html) => {
      $('.js-quick-entrance').html(html);
    });
  }

  //网校信息块
  if ($('.js-admin-info').length && $('.js-admin-info').data('url')) {
    $.get($('.js-admin-info').data('url'), (html) => {
      $('.js-admin-info').html(html);
    });
  }

  // 站长公告
  if ($('.announcement-from-platform').length && $('.announcement-from-platform').data('url')) {
    $.get($('.announcement-from-platform').data('url'), (html) => {
      $('.announcement-from-platform').html(html);
    });
  }
};

const loadSecond = () => {
  // 应用简介
  if ($('.application-intro').length && $('.application-intro').data('url')) {
    $.get($('.application-intro').data('url'), (html) => {
      $('.application-intro').html(html);
    });
  }

  //经营建议
  if ($('.js-admin-advice').length && $('.js-admin-advice').data('url')) {
    $.get($('.js-admin-advice').data('url'), (html) => {
      $('.js-admin-advice').html(html);
    });
  }

  //CHANGELOG
  if ($('.js-admin-changelog').length && $('.js-admin-changelog').data('url')) {
    $.get($('.js-admin-changelog').data('url'), (html) => {
      $('.js-admin-changelog').html(html);
    });
  }

  //QRCODE
  if ($('.js-admin-qrcode').length && $('.js-admin-qrcode').data('url')) {
    $.get($('.js-admin-qrcode').data('url'), (html) => {
      $('.js-admin-qrcode').html(html);
    });
  }
};

const loadStep = () => {
  loadFirst();
  setTimeout(() => {
    loadSecond();
  },1000);
};

loadStep();
window.onload = () => {
  showCloudAd();
};

$('.js-no-network').click(function () {
  cd.message({type: 'danger', 'message': Translator.trans('admin.can_not_link_data')});
});



