import Cookies from 'js-cookie';
import Api from 'common/api';

export default class HeaderNav {
  constructor() {
    this.isClicked = false;
    this.init();
  }

  init() {
    this.initEvent();
    this.initNotification();
    this.bindEvent();
  }

  initEvent() {
    $('body').on('click', '.js-user-nav-dropdown', (event) => {
      event.stopPropagation();
    });
    const pcSwitcher = $('.js-switch-pc');
    const mobileSwitcher = $('.js-switch-mobile');
    if (pcSwitcher.length) {
      pcSwitcher.on('click', () => {
        Cookies.set('PCVersion', 1);
        window.location.reload();
      });
    }
    if (mobileSwitcher.length) {
      mobileSwitcher.on('click', () => {
        Cookies.remove('PCVersion');
        window.location.reload();
      });
    }
  }

  initNotification() {
    const $informItem = $('.js-user-inform');
    const isShow = $informItem.css('display') === 'block';
    const $newNotification = $('.js-inform-newNotification');
    if ($informItem.length && isShow) {
      this.api('newNotification', $newNotification, true);
    }
  }

  bindEvent() {
    $('.js-inform-tab').on('click', (event) => this.changeTab(event));
    $('.js-user-nav-dropdown').on('click', '.js-inform-notification', (event) => this.toNotification(event));
    $('.js-back').on('click', () => this.mobileHistory());
  }

  changeTab(event) {
    const $target = $(event.target);
    this.isClicked = true;
    event.preventDefault();
    $target.tab('show');
    const type = $target.data('type');
    const isActive = $target.hasClass('active');
    const $conversation = $('.js-inform-conversation');
    const $newNotification = $('.js-inform-newNotification');
    const isEmpty = $('.tab-pane.active').find('.js-inform-empty').length;
    const $dom = (type === 'conversation') ? $conversation : $newNotification;
    const flag = (type === 'conversation') ? false : true;

    if (!isEmpty && !isActive) {
      this.api(type, $dom, flag);
    }

    $target.addClass('active').siblings().removeClass('active');
  }

  api(type, $dom, flag) {
    const self = this;
    Api[type]['search']({
      before() {
        self.loadingShow();
      }
    }).then((res) => {
      this.informShow($dom, res, flag);
    }).catch((res) => {
      // 异常捕获
      console.log('catch', res.responseJSON.message);
    });
  }

  informShow($dom, res, flag) {
    if (this.isClicked) {
      $dom.empty();
    }
    const $loading = $('.tab-pane.active').find('.js-inform-loading');
    $loading.addClass('hidden');
    $('.js-inform-dropdown-body').css('overflow-y', 'auto');
    $dom.append(res);
    if (flag) {
      $dom.find('.notification-footer').addClass('hidden');
      $dom.find('.pull-left').addClass('hidden');
    }
  }

  loadingShow() {
    const $loadingDom = $('.tab-pane.active').find('.js-inform-loading');
    const loading = cd.loading();
    $loadingDom.removeClass('hidden');
    $('.js-inform-dropdown-body').css('overflow-y', 'hidden');
    $loadingDom.html(loading);
  }

  toNotification(event) {
    const $item = $(event.currentTarget);
    window.location.href = $item.data('url');
  }

  mobileHistory() {
    if (history.length !== 1) {
      history.go(-1);
    } else {
      location.href = '/';
    }
  }
}