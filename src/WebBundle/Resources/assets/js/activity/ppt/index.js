import Emitter from "common/es-event-emitter";
import screenfull from "screenfull";
import ActivityEmitter from "../activity-emitter";

class PPT extends Emitter {
  constructor({element, slides, watermark}) {
    super();

    this.element = $(element);
    this.slides = slides || [];
    this.watermark = watermark || '';
    this._KEY_ACTION_MAP = {
      37: this._onPrev,
      39: this._onNext,
      35: this._onLast,
      36: this._onFirst
    };
    this.total = 0;
    this._page = 0;
    this.placeholder = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC";
    this._bindEvents();
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

    this.trigger('change', {
          current: currentPage,
          before: beforePage
        }
    );
  }

  _init() {
    this.total = this.slides.length;
    this.element.find('.total').text(this.total);

    let html = this.slides.reduce((html, src, index) => {
      html += `<img data-src="${src}" class="slide" data-page="${index + 1}">`;
      return html;
    }, '');

    this.element.find('.slide-player-body').html(html);

    this.watermark && this.element.append(`<div class="slide-player-watermark">${this.watermark}</div>`);

    $(document).on('keydown', (event) => {
      this._KEY_ACTION_MAP[event.keyCode] && this._KEY_ACTION_MAP[event.keyCode].call(this);
    });

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
    this.element.on('click', '.goto-next', this._onNext.bind(this));
    this.element.on('click', '.goto-prev', this._onPrev.bind(this));
    this.element.on('click', '.goto-first', this._onFirst.bind(this));
    this.element.on('click', '.goto-last', this._onLast.bind(this));
    this.element.on('click', '.fullscreen', this._onFullScreen.bind(this));
    this.element.on('change', '.goto-page', this._onChangePage.bind(this));
    let self = this;
    this.on('change', ({current, before}) => {
      if (current == self.total) {
        self.trigger('end');
      }
    });
  }

  _onNext() {
    if (this.page === this.total) {
      this.trigger('end');
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
    this.page = this.total
  }

  _onFullScreen(event) {
    if (!screenfull.enabled) {
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

let watermarkUrl = $('#activity-ppt-content').data('watermarkUrl');
let emitter = new ActivityEmitter();
let createPPT = (watermark) => {
  let ppt = new PPT({
    element: '#activity-ppt-content',
    slides: $('#activity-ppt-content').data('slides').split(','),
    watermark: watermark
  });

  return ppt.once('end', () => {
    emitter.emit('finish').then(() => {
      console.log('ppt.finish');
    }).catch((error) => {
      console.error(error);
    });
  });
};

if (watermarkUrl === undefined) {
  let ppt = createPPT();
} else {
  $.get(watermarkUrl)
      .then((watermark) => {
        let ppt = createPPT(watermark);
      })
      .fail(error => {
        console.error(error);
      });
}
