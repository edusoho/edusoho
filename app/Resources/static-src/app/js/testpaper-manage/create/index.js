import sortList from 'common/sortable';

class TestpaperForm{
  constructor($form) {

    this.$form = $form;
    this.$description = this.$form.find('[name="description"]');
    this.validator = null;
    this._initEvent();
    this._initEditor();
    this._initValidate();
    this._initSortList();
  }

  _initEvent() {
    this.$form.on('click','[data-role="submit"]',event=>this._submit(event));

  }

  _initEditor() {
    let editor = CKEDITOR.replace(this.$description.attr('id'), {
      toolbar: 'Simple',
      filebrowserImageUploadUrl: this.$description.data('imageUploadUrl'),
      height: 100
    });
    editor.on( 'change', () => {    
      this.$description.val(editor.getData());
    });
  }

  _initValidate() {
    this.validator = this.$form.validate({
      rules:{
        name:{
          required:true
        },
        description:{
          required:true,
          maxlength:500
        },
        limitedTime:{
          min:0,
          max:10000,
          digits:true
        },
        mode:{
          required:true
        },
        range:{
          required:true
        }
      },
      messages:{
        name:"请输入试卷名称",
        description:"请输入试卷描述",
        mode:"请选择生成方式",
        range:"请选择出题范围"
      }
    });
    this.$form.find('.testpaper-question-option-item').each(function(){
      let self = $(this);
      self.find('[data-role="count"]').rules('add',{
        min:0,
        max:function(){
          return parseInt(self.find('[role="questionNum"]').text());
        },
        digits:true
      })

      self.find('[data-role="score"]').rules('add',{
        min:0,
        max:100,
        digits:true
      })

      if (self.find('[data-role="missScore"]').length > 0) {
        self.find('[data-role="missScore"]').rules('add',{
          min:0,
          max:function(){
            return parseInt(self.find('[data-role="score"]').val());
          },
          digits:true
        })
      }
    })

  }

  _initSortList() {
    sortList({
      element:'#testpaper-question-options',
      itemSelector:'.testpaper-question-option-item',
      handle: '.question-type-sort-handler',
      isAjax:false
    });
  }

  _submit(event){
    let status = this.validator.form();
    if (status) {
      this.$form.submit();
    }
  }
}

new TestpaperForm($('#testpaper-form'));