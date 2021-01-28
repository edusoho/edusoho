export default class View {
  constructor() {
    this.init();
  }

  init() {
    let $form = $('#certificate-template-form');
    let certificateBase64Url = $('[name=certificateBase64Url]').val();
    if ($form.length !== 0) {
      $.post(certificateBase64Url, $form.serialize(), (resp) => {
        $('.js-loading-text').remove();
        $('#certificateImg').attr('src', 'data:image/png;base64,' + resp).removeClass('hidden');
      });
    }
  }
}

new View();
