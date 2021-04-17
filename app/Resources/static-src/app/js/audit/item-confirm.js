export default class ItemConfirm {

  constructor(prop) {
    this.element = $(prop.element);
    this.dataRole = prop.dataRole;
    this.itemConfirm();
  }

  itemConfirm() {

    const $dataRole = this.dataRole;

    this.element.on('click', '[data-role=item-' + $dataRole + ']', function() {
      let $btn = $(this);
        $('#modal').html('');
        $.get($btn.data('url'), function(res) {
            $('#modal').modal('show').html(res);
        });
    });
  }
}