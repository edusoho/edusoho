export default class ShortLongText {

  constructor(prop) {
    this.element = $(prop.element);
    this.shortText();
    this.longText();
  }

  shortText() {
    this.element.on('click', '.short-text', function() {
      let $short = $(this);
      $short.slideUp('fast').parents('.short-long-text').find('.long-text').slideDown('fast');
    });
  }

  longText() {
    this.element.on('click', '.long-text', function() {
      let $long = $(this);
      $long.slideUp('fast').parents('.short-long-text').find('.short-text').slideDown('fast');
    });
  }
}