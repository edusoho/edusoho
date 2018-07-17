export default class detail {
  constructor(element) {
    this.$form = $('#title').closest('form');
    this.$btn = element;
    this.$replaceCkeditor = $('#courseset-summary-field');
    this.init();
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
      this.$replaceCkeditor.val(self.editor.getData());
      self.validator.form();
    });
  }

  submitForm() {
    this.validator = this.$form.validate({
      rules: {
        summary: {
          ckeditor_maxlength: 10000,
        }
      }
    });
    this.$btn.click(() => {
      this.$replaceCkeditor.val(this.editor.getData());
      if (this.validator.form()) {
        this.$form.submit();
      }
    });
  }
}