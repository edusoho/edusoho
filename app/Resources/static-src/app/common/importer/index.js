import notify from 'common/notify';
import { arrayToJson } from 'common/utils';
import Progress from './progress';

class Importer {
  constructor(props) {
    Object.assign(this, {
      container: '#importer-app',

      form: '#importer-form',
      confirmEl: '#importer-confirm',
      progressEl: '#importer-progress',
      progressTemplate: '#importer-progress-template',

      verifyBtn: '#import-verify-btn',
      importBtn: '#import-btn',

      rules: {},
      importData: [],
      formData: {},
    }, props);

    this.$container = $(this.container);
    this.$form = $(this.form);
    this.$progressTemplate = $(this.progressTemplate);

    this.init();
  }

  init() {
    this.events();
  }

  events() {
    this.$container.on('change', 'input[type=file]', event => this.onSelectFile(event));
    this.$container.on('click', this.verifyBtn, event => this.onVerify(event));
    this.$container.on('click', '.js-reselect', event => this.onReSelect(event));
    this.$container.on('click', this.importBtn, event => this.onImport(event));
  }

  onSelectFile(event) {
    const filename = $(event.currentTarget).val();
    if (filename === '') {
      return;
    }
    this.$container.find('.js-filename').val(filename);
  }

  onVerify(event) {
    let self = this;
    let validatior = this.$form.validate({
      rules: self.rules,
      submitHandler(form) {
        let $form = $(form);
        let $btn = $(self.verifyBtn);
        $btn.button('loading');

        self.formData = arrayToJson($form.serializeArray());

        $.ajax({
          type: 'POST',
          url: $form.attr('action'),
          processData: false,
          contentType: false,
          cache: false,
          data: new FormData($form[0])
        }).done((res) => {
          $btn.button('reset');
          const status = res.status;
          const eventType = 'on' + status.charAt(0).toUpperCase() + status.substr(1);

          console.log(eventType);
          self[eventType](res);
        }).fail((res) => {
          $btn.button('reset');
          console.log('error:', res);
        });
      }
    });

    if(validatior.form()) {
      this.$form.submit();
    }
  }

  onReSelect(event) {
    this.$container.find(this.confirmEl).remove();
    this.$form.show();
  }

  onDanger(res) {
    notify(res.status, res.message);
  }

  onError(res) {
    const source = `
      <div id="importer-confirm">
        <div class="alert alert-danger">
          {{#errors}}{{error}}<br>{{/errors}}
        </div>
        <div class="text-right">
          <button type="button" class="btn btn-primary js-reselect">
            ${Translator.trans('importer.import_reselect_btn')}
          </button>
        </div>
      </div>
    `;

    const errors = [];
    res.errorInfo.map((item) => {
      errors.push({
        error: item
      });
    });

    const data = {
      errors: errors
    };

    this.addTemplate(source, data);
  }

  onSuccess(res) {
    let source = `
      <div id="importer-confirm">
        <div class="alert alert-success">
          ${Translator.trans('importer.import_verify_tips_start')}
          {{importData.length}}
          ${Translator.trans('importer.import_verify_tips_end')}
        </div>
        <div class="text-right">
          <button type="button" class="btn btn-default mrm js-reselect">${Translator.trans('importer.import_back_btn')}</button>
          <button type="button" class="btn btn-primary" id="import-btn">${Translator.trans('importer.import_confirm_btn')}</button>
        </div>
      </div>
    `;

    this.importData = res.importData;
    this.chunkNum = res.chunkNum ? res.chunkNum : 8;
    this.addTemplate(source, res);
  }

  addTemplate(source, data) {
    const template = Handlebars.compile(source);
    const result = template(data);

    this.$form.hide();
    this.$container.append(result);
  }

  onImport(event) {
    const source = this.$progressTemplate.html();
    const template = Handlebars.compile(source);
    const result = template();
    this.$container.html(result);

    new Progress({
      importData: this.importData,
      $container: this.$container,
      formData: this.formData,
      chunkSize: this.chunkNum
    });
  }
}

export default Importer;