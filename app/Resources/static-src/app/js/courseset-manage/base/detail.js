export default class detail {
  constructor(element) {
    this.$element = $(element);
    this.formId = $(element).data('form');
    this.btnId = $(element).data('button');
    this.$form = $(`#${this.formId}`);
    this.$btn = $(`#${this.btnId}`);
    this.uploadUrl = this.$element.data('imageUploadUrl');
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
      filebrowserImageUploadUrl: this.uploadUrl
    });

    self.editor.on('blur', () => {
      this.$element.val(self.editor.getData());
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
      this.$element.val(this.editor.getData());
      if (this.validator.form()) {
        this.$form.submit();
      }
    });
  }
}