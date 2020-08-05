export default class StepTwo {
  constructor() {
    this.init();
  }

  init() {
    $('[name=styleType]').on('change', () => {
      let type = $('[name=styleType]:checked').val();
      if ('horizontal' === type) {
        $('.js-horizontal').removeClass('hidden');
        $('.js-vertical').addClass('hidden');
      } else {
        $('.js-horizontal').addClass('hidden');
        $('.js-vertical').removeClass('hidden');
      }
    });
  }
}

new StepTwo();
