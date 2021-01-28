import 'store';

export default class LessonIntro {
  constructor() {
    this._defaultEvent();
  }

  _defaultEvent() {
    if (store.get('NormalLessonIntro') === undefined) {
      this.showAdImage();
      store.set('NormalLessonIntro', '1');
    }
    this.closeModal();
  }

  closeModal() {
    $('#js-intro').on('click', function(e) {
      $('#js-intro').modal('hide');
    });
  }

  showAdImage() {
    let $intro = $('#js-intro');
    let img = new Image();
    let local = $('.js-data-local').data('local');
    if (local === 'en') {
      img.src = '/assets/img/lessonintro/lessonintro-en.jpg';
    } else {
      img.src = '/assets/img/lessonintro/lessonintro.jpg';
    }
    var $img = $(img);
    $intro.find('.modal-body').append($img);
    $intro.modal('show');
  }
}