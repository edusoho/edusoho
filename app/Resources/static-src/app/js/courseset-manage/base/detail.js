export default class detail {
  constructor(element) {
    this.$from = $('#title').closest('form');
    this.btn = element;
    this.init();
    console.log(element);
  }

  init() {
    this.submitForm();
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
    this.btn.click(() => {
      $('#courseset-summary-field').val(this.editor.getData());
      if (this.validator.form()) {
        this.$from.submit();
      }
    });
  }
}