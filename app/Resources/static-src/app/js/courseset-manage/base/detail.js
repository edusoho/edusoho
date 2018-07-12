export default class detail {
  constructor() {
    this.$from = $('#courseset-detail-form');
    this.init();
  }

  init() {
    this.initCkeditor();
  }

  initCkeditor() {
    let self = this;
    self.editor = CKEDITOR.replace('summary', {
      allowedContent: true,
      toolbar: 'Detail',
      fileSingleSizeLimit: app.fileSingleSizeLimit,
      filebrowserImageUploadUrl: $('input[name="summary"]').data('imageUploadUrl')
    });

    self.editor.on('blur', () => {
      $('#courseset-summary-field').val(self.editor.getData());
      self.validator.form();
    });
  }

  submitForm() {
    this.validator = this.$from.validate({
      rules: {
        summary: {
          ckeditor_maxlength: 10000,
        }
      }
    });

    $('#detail-submit').click(() => {
      $('#courseset-summary-field').val(this.editor.getData());
      if (this.validator.form()) {
        this.$from.submit();
      }
    });
  }
}