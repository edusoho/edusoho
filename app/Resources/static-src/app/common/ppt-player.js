import Emitter from 'common/es-event-emitter';
import screenfull from 'es-screenfull';

export default class PPT extends Emitter {
  constructor({element, slides, watermark}) {
    super();
    this.element = $(element);
    this.slides = slides || [];
    this.watermark = watermark || '';
    this._KEY_ACTION_MAP = {
      37: this._onPrev,  // ←
      39: this._onNext,  // →
      38: this._onLast,  // ↑
      40: this._onFirst  // ↓
    };
    this.total = this.slides.length;
    this._page = 0;
    this.placeholder = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC';
    this._init();
  }

  get page() {
    return this._page;
  }

  set page(newPage) {
    let beforePage = this.page;
    let currentPage = newPage;

    if (currentPage > this.total) {
      this.element.find('.goto-page').val(currentPage);
      this._page = currentPage;
    }

    if (currentPage < 1) {
      this.element.find('.goto-page').val(beforePage);
      this._page = beforePage;
    }

    if (beforePage) {
      this.element.find('.slide-player-body .slide').eq(beforePage - 1).removeClass('active');
    }

    let $currentSlide = this._getSlide(currentPage);

    if ($currentSlide.attr('src')) {
      $currentSlide.addClass('active');
    } else {
      $currentSlide.load(() => {
        if (this._page != $currentSlide.data('page')) {
          return;
        }
        $currentSlide.addClass('active');
      });
      $currentSlide.attr('src', $currentSlide.data('src'));
    }

    this._lazyLoad(currentPage);

    this.element.find('.goto-page').val(currentPage);

    this._page = currentPage;

    this.emit('change', {
      current: currentPage,
      before: beforePage
    });
  }

  _render() {
    let html = `
      <div class="slide-player">
        <div class="slide-player-body loading-background"></div>
        <div class="slide-notice">
          <div class="header">{{ 'site.data_last_picture'|trans }}
            <button type="button" class="close">×</button>
          </div>
        </div>
      
        <div class="slide-player-control clearfix">
          <a href="javascript:" class="goto-first">
            <span class="glyphicon glyphicon-step-backward"></span>
          </a>
          <a href="javascript:" class="goto-prev">
            <span class="glyphicon glyphicon-chevron-left"></span>
          </a>
          <a href="javascript:" class="goto-next">
            <span class="glyphicon glyphicon-chevron-right"></span>
          </a>
          <a href="javascript:" class="goto-last">
            <span class="glyphicon glyphicon-step-forward"></span>
          </a>
          <a href="javascript:" class="fullscreen">
            <span class="glyphicon glyphicon-fullscreen"></span>
          </a>
          <div class="goto-page-input">
            <input type="text" class="goto-page form-control input-sm" value="1">&nbsp;/&nbsp;
              <span class="total"></span>
          </div>
        </div>
      </div>`;

    this.element.html(html);

    this.element.find('.total').text(this.total);

    let slidesHTML = this.slides.reduce((html, src, index) => {
      html += `<img data-src="${src}" class="slide" data-page="${index + 1}">`;
      return html;
    }, '');

    this.element.find('.slide-player-body').html(slidesHTML);
    this.watermark && this.element.append(`<div class="slide-player-watermark">${this.watermark}</div>`);
  }

  _init() {
    this._render();
    this._bindEvents();
    this._onFirst();
  }


  _lazyLoad(page) {
    for (let currentPage = page; currentPage < page + 4; currentPage++) {
      if (currentPage > this.total) {
        break;
      }

      let $slide = this._getSlide(currentPage);
      $slide.attr('src') || $slide.attr('src', $slide.data('src'));
    }
  }

  _getSlide(page) {
    return this.element.find('.slide-player-body .slide').eq(page - 1);
  }

  _bindEvents() {
    $(document).on('keydown', (event) => {
      this._KEY_ACTION_MAP[event.keyCode] && this._KEY_ACTION_MAP[event.keyCode].call(this);
    });

    this.element.on('click', '.goto-next', event => this._onNext(event));
    this.element.on('click', '.goto-prev', event => this._onPrev(event));
    this.element.on('click', '.goto-first', event => this._onFirst(event));
    this.element.on('click', '.goto-last', event => this._onLast(event));
    this.element.on('click', '.fullscreen', event => this._onFullScreen(event));
    this.element.on('change', '.goto-page', event => this._onChangePage(event));
    let self = this;
    this.on('change', ({current, before}) => {
      if (current == self.total) {
        self.emit('end',{page: this.total});
      }
    });
  }

  _onNext() {
    if (this.page === this.total) {
      this.emit('end',{page: this.total});
      return;
    }

    this.page++;
  }

  _onPrev() {
    if (this.page == 1) {
      return;
    }

    this.page--;
  }

  _onFirst() {
    this.page = 1;
  }

  _onLast() {
    this.page = this.total;
  }

  _onFullScreen() {
    const isIOS = !!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
    if (!screenfull.enabled) {
      if(isIOS) {
        $('#task-content-iframe', parent.document).toggleClass('ios-full-screen');
      }
      return;
    }
    if (screenfull.isFullscreen) {
      screenfull.toggle();
    } else {
      screenfull.request();
    }
  }

  _onChangePage(e) {
    this.page = $(e.target).val();
  }
}
