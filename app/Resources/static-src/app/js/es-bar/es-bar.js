import { Browser, isLogin } from 'common/utils';

class EsBar {
  constructor(prop) {
    this.ele = $(prop.ele);
    this.init();
  }

  init() {
    this.initEvent();

    if (Browser.ie10 || Browser.ie11 || Browser.edge) {
      this.ele.css( 'margin-right','16px');
    }

    if ( this.ele.find('[data-toggle="tooltip"]').length > 0) {
      this.ele.find('[data-toggle="tooltip"]').tooltip({container: '.es-bar'});
    }

    this.ele.find('.bar-menu-sns li.popover-btn').popover({
      placement: 'left',
      trigger: 'hover',
      html: true,
      content: function() {
        return $($(this).data('contentElement')).html();
      }
    });

    $('body').on('click', '.es-wrap', () => {
      if ($('.es-bar-main.active').length) {
        this.ele.animate({
          right: '-230px'
        },300).find('.bar-menu-top li.active').removeClass('active');
      }
    });

    this.goTop();
  }

  initEvent() {
    const $node = this.ele;
    $node.on('click', '.js-bar-shrink', event => this.onBarBhrink(event));
    $node.on('click', '.bar-menu-top li', event => this.onMenuTop(event));
    $node.on('click', '.btn-action >a', event => this.onBtnAction(event));
  }

  onBarBhrink(e) {
    const $this = $(e.currentTarget);
    $this.parents('.es-bar-main.active').removeClass('active').end().parents('.es-bar').animate({
      right: '-230px'
    },300);
    $('.bar-menu-top li.active').removeClass('active');
  }

  onMenuTop(e) {
    const $this = $(e.currentTarget);

    // 判断是否登录
    if(!isLogin()){
      this.isNotLogin();
      return;
    }

    this.ele.find('.bar-main-body').perfectScrollbar({wheelSpeed:50});

    if($this.find('.dot')) {
      $this.find('.dot').remove();  
    }

    if(!$this.hasClass('active')) {
      $this.siblings('.active').removeClass('active').end().addClass('active').parents('.es-bar').animate({
        right: '0'
      },300);
      this.clickBar($this);
      $($this.data('id')).siblings('.es-bar-main.active').removeClass('active').end().addClass('active');
    }else {
      $this.removeClass('active').parents('.es-bar').animate({
        right: '-230px'
      },300);
    }
  }

  onBtnAction(e) {
    const $this = $(e.currentTarget);
    const url = $this.data('url');

    $.get(url,function(html){
      $this.closest('.es-bar-main').html(html);
      $('.es-bar .bar-main-body').perfectScrollbar({wheelSpeed:50});
    });
  }

  clickBar($this) {
    if(typeof($this.find('a').data('url')) != 'undefined' ) {
      const url = $this.find('a').data('url');

      $.get(url,function(html){
        $($this.data('id')).html(html);
        $('.es-bar .bar-main-body').perfectScrollbar({wheelSpeed:50});
      });
    }
  }

  isNotLogin() {
    const $loginModal = $('#login-modal');

    $loginModal.modal('show');
    $.get($loginModal.data('url'), function(html){
      $loginModal.html(html);
    });
  }

  goTop() {
    const $gotop = $('.go-top');

    $(window).scroll(function(event) {
      const scrollTop = $(window).scrollTop();

      if(scrollTop>=300) {
        $gotop.addClass('show');

      }else if($gotop.hasClass('show')) {
        $gotop.removeClass('show');
      }
    });
    $gotop.click(function() {
      return $('body,html').animate({
        scrollTop: 0
      }, 300), !1;
    });
  }
}

export default EsBar;