class QuestionBankSelectLink
{
  constructor($select1,$select2) {
    this.select1 = $select1;
    this.select2 = $select2;

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

    self.select2.empty();
    self.select2.select2('val', '');

    if (value == 0) {
      this.select2.hide();
      return;
    }

    $.post(url,{bankId:value},function(result){
      if (result != '') {
        let option = '';
        $.each(result,function(index,category){
          option += '<option value='+category.id+'>';
          for (var i=0;i < category.depth;i++) {
            option += ' ';
          }
          option += category.name+'</option>';
        });
        self.select2.append(option);
        self.select2.removeClass('hidden');
      } else {
        self.select2.addClass('hidden');
      }
    });
  }
}

export default QuestionBankSelectLink;