class InputEdit {
  constructor(props) {
    this.el = props.el;
    this.parent = props.parent || document;

    this.$el = $(this.el);

    this.data = props.data;

    this.editBtn = props.editBtn || '.cd-input-edit__edit-btn';
    this.saveBtn = props.saveBtn || '.cd-input-edit__save-btn';
    this.cancelBtn = props.cancelBtn || '.cd-input-edit__cancel-btn';

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

    this.$el.find('.cd-input-edit__edit-dom').show()
      .find('.cd-input-edit__input').focus().select();
  }

  cancel(event) {
    let $this = $(event.currentTarget);

    this.$el.find('.cd-input-edit__edit-dom').hide();

    let text = this.$el.find('.cd-input-edit__static-text').text();
    this.$el.find('.cd-input-edit__input').val(text);

    this.$el.find('.cd-input-edit__static-dom').show();
  }

  save(event) {
    let $this = $(event.currentTarget);
    let url = $this.data('url');

    $this.button('loading');

    $.post(url, this.data)
      .always(() => {
        $this.button('reset');
      })
      .done((data) => {
        let $input = this.$el.find('.cd-input-edit__input');

        this.$el.find('.cd-input-edit__static-text').text($input.val());

        this.$el.find('.cd-input-edit__edit-dom').hide();
        
        this.$el.find('.cd-input-edit__static-dom').show();

        this.success(data);
      })
      .fail((data) => {
        this.fail(data);
      })
  }

  success(data) {
    console.log('success')
  }
  fail(data) {
    console.log('fail')
  }
}

export default InputEdit;