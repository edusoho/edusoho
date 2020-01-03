class QuestionBankSelectLink
{
  constructor($select1,$select2, object) {
    this.select1 = $select1;
    this.select2 = $select2;
    this.object = object;

    this._initEvent();
  }

  _initEvent() {
    this.select1.on('change', event => this._selectChange(event));
  }

  _selectChange(event)
  {
    let url = this.select1.data('url');
    let value = this.select1.val();
    let self = this;

    if (value == 0) {
      this.select2.hide();
      return;
    }

    $.post(url,{bankId:value, isTree: true},function(result){
      if (result != '') {
        self.select2.find('.js-treeview-data').text(JSON.stringify(result));
        self.select2.find('.js-treeview-ipt').val('');
        self.select2.find('.js-treeview-text').val('');
        new window.$.CheckTreeviewInput({
          $elem: self.select2,
          disableNodeCheck: true,
          saveColumn: 'id',
          transportChildren: true,
        });
        self.select2.removeClass('hidden');
      } else {
        self.select2.addClass('hidden');
      }
    });
  }
}

export default QuestionBankSelectLink;