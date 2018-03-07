class InputEdit {
  constructor(props) {
    this.el = props.el;
    this.parent = props.parent || document;

    this.$el = $(this.el);

    this.editBtn = props.editBtn || '.input-edit__edit-btn';
    this.saveBtn = props.saveBtn || '.input-edit__save-btn';
    this.cancelBtn = props.cancelBtn || '.input-edit__cancel-btn';

    this.success = props.success || this.success;
    this.fail = props.fail || this.fail;

    this.init();
  }

  init() {
    this.event();
  }

  event() {
    let $parent = $(this.parent);

    $parent.on('click', this.editBtn, event => this.edit(event));

    $parent.on('click', this.saveBtn, event => this.save(event));

    $parent.on('click', this.cancelBtn, event => this.cancel(event));
  }

  edit(event) {
    let $this = $(event.currentTarget);

    $this.parent().hide();

    this.$el.find('.input-edit__edit-dom').show()
      .find('.input-edit__input').focus().select();
  }

  cancel(event) {
    let $this = $(event.currentTarget);

    this.$el.find('.input-edit__edit-dom').hide();

    let text = this.$el.find('.input-edit__static-text').text();
    this.$el.find('.input-edit__input').val(text);

    this.$el.find('.input-edit__static-dom').show();
  }

  save(event) {
    let $this = $(event.currentTarget);
    let url = $this.data('url');
    let inputName = $this.data('input-name');

    let data = {};
    data[inputName] = $(`input[name=${inputName}]`).val();

    $this.button('loading');

    $.post(url, data)
      .always(() => {
        $this.button('reset');
      })
      .done((data) => {
        let $input = this.$el.find('.input-edit__input');

        this.$el.find('.input-edit__static-text').text($input.val());

        this.$el.find('.input-edit__edit-dom').hide();
        
        this.$el.find('.input-edit__static-dom').show();

        this.success(data);
      })
      .fail((data) => {
        this.fail(data);
      });
  }

  success(data) {
    console.log('success');
  }
  fail(data) {
    console.log('fail');
  }
}

export default InputEdit;