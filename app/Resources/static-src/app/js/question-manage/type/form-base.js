import AttachmentActions from '../../attachment/widget/attachment-actions';
// import postal from 'postal';

class QuestionFormBase {
  constructor($form){
    this.$form = $form;
    this.titleFieldId = 'question-stem-field';
    this.analysisFieldId = 'question-analysis-field';
    this.validator = null;
    this.titleEditorToolBarName = 'Minimal';

    this._init();

    this.attachmentActions = new AttachmentActions($form);
  }

  _init() {
    this._initEvent();
    this._initValidate();
  }

  _initEvent() {
    this.$form.on('click','[data-role=submit]',event=>this.submitForm(event));
  }

  submitForm(event) {
    // console.log("postal");
    
    // // let submitType = $(event.currentTarget).data('submission');
    // // this.$form.find('[name=submission]').val(submitType);
    // postal.publish({
    //   channel: "orders",
    //   topic: "item.add",
    //   data: {
    //       sku: "AZDTF4346",
    //       qty: 21
    //   }
    // });


    // var subscription = postal.subscribe({
    //   channel: "orders",
    //   topic: "item.add",
    //   callback: function(data, envelope) {
    //     console.log(data);
    //       // `data` is the data published by the publisher. 
    //       // `envelope` is a wrapper around the data & contains 
    //       // metadata about the message like the channel, topic, 
    //       // timestamp and any other data which might have been 
    //       // added by the sender. 
    //   }
    // });
  }

  _initValidate() {
    let validator = this.$form.validate({
        onkeyup: false,
        rules: {
          '[data-role="target"]': {
            required: true,
          },
          difficulty: {
            required: true,
          },
          stem: {
            required: true,
          },
          score: {
            required: true,
            number:true,
            max:999,
            min:0
          }
        },
        messages: {
            '[data-role="target"]': "请选择从属",
            difficulty : "请选择难度"
        },
        submitHandler(){
          
        }
    });
    this.validator = validator;
  }

  initTitleEditor() {
    let $target = $('#'+this.titleFieldId);
    let editor = CKEDITOR.replace(this.titleFieldId, {
      toolbar: this.titleEditorToolBarName,
      filebrowserImageUploadUrl: $target.data('imageUploadUrl'),
      height: $target.height()
    });

    editor.on( 'change', () => {    
      $target.val(editor.getData());
    });
  }

  initAnalysisEditor() {
    let $target = $('#'+this.analysisFieldId);
    let editor = CKEDITOR.replace(this.analysisFieldId, {
      toolbar: this.titleEditorToolBarName,
      filebrowserImageUploadUrl: $target.data('imageUploadUrl'),
      height: $target.height()
    });

    editor.on( 'change', () => {    
      $target.val(editor.getData());
    });
  }

  set titleEditorToolBarName(toolbarName) {
    this._titleEditorToolBarName = toolbarName;
  }
 
  get titleEditorToolBarName() {
    return this._titleEditorToolBarName;
  }
}

export default QuestionFormBase;