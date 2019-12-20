import Cookies from 'js-cookie';

let localUpgradeNotice = window.localStorage.getItem('upgradeNotice') ? window.localStorage.getItem('upgradeNotice') : '';
if ($('.js-notice').val() == true && localUpgradeNotice != 1) {
  window.localStorage.setItem('upgradeNotice', '1');
  $('.js-upgrade-notice').removeClass('hidden');
}

$('.js-show-toggle').on('click', (event) => {
  const $target = $(event.currentTarget);
  $('.js-steps').slideToggle();
  const value = $('.js-toggle-text').text() === Translator.trans('site.data.collapse') ? Translator.trans('site.data.expand'): Translator.trans('site.data.collapse');
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

const loadFirst = () => {
  //六步任务助手
  const $stepsDom = $('.js-steps');
  if ($stepsDom.length) {
    $.get($stepsDom.data('url'), (html) => {
      $stepsDom.html(html);
    });
  }

  // 数据概览
  const $overviewDom = $('.js-data-overview');
  if ($overviewDom.length) {
    $.get($overviewDom.data('url'), (html) => {
      $overviewDom.html(html);
    });
  }

  // 快速入口
  const $entranceDom = $('.js-quick-entrance');
  if ($entranceDom.length) {
    $.get($entranceDom.data('url'), (html) => {
      $entranceDom.html(html);
    });
  }

  //网校信息块
  const $infoDom = $('.js-admin-info');
  if ($infoDom.length && $infoDom.data('url')) {
    $.get($infoDom.data('url'), (html) => {
      $infoDom.html(html);
      const src = $('.js-mini-program').data('src');
      $('.js-mini-program').popover({
        trigger: 'hover',
        placement: 'bottom',
        title: Translator.trans('admin_v2.homepage.mini_program.title'),
        template: '<div class="popover mini-program-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
        html: true,
        content: `<img src="${src}" width="200px">`
      });
    });
  }

  // 站长公告
  const $announcementDom = $('.js-announcement');
  if ($announcementDom.length && $announcementDom.data('url')) {
    $.get($announcementDom.data('url'), (html) => {
      $announcementDom.html(html);
    });
  }
};


const loadSecond = () => {
  // 应用推荐
  const $applicationDom = $('.application-intro');
  if ($applicationDom.length && $applicationDom.data('url')) {
    $.get($applicationDom.data('url'), (html) => {
      $applicationDom.html(html);
    });
  }

  //经营建议
  const $adviceDom = $('.js-admin-advice');
  if ($adviceDom.length && $adviceDom.data('url')) {
    $.get($adviceDom.data('url'), (html) => {
      $adviceDom.html(html);
    });
  }

  //CHANGELOG
  const $changelogDom = $('.js-admin-changelog');
  if ($changelogDom.length && $changelogDom.data('url')) {
    $.get($changelogDom.data('url'), (html) => {
      $changelogDom.html(html);
    });
  }

  //QRCODE
  const $qrcodeDom = $('.js-admin-qrcode');
  if ($qrcodeDom.length && $qrcodeDom.data('url')) {
    $.get($qrcodeDom.data('url'), (html) => {
      $qrcodeDom.html(html);
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
