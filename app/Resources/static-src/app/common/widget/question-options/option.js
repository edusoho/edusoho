import React,{ Component } from 'react';

export default class Options extends Component {
  constructor(props) {
    super(props);
  }

  componentDidMount() {
    console.log('componentDidMount');
    this.initCkeditor();
  }

  deleteOption(event) {
    this.props.deleteOption(event.currentTarget.id);
  }

  onChange(event) {
    this.props.changeOption(event.currentTarget.value);
  }

  initCkeditor(dataSourceUi) {
    let item =this.props.item;
    if(!item.editor) {
      let editor = CKEDITOR.replace(item.optionId, {
        toolbar: 'Minimal',
        height: 120
      });
      let self = this;
      editor.on('change', function( event ) {   
        let data = this.getData();//内容
        self.updateInputValue(item.optionId,data);
      });
      item.editor = editor;
      $(`[name='${item.optionId}']`).rules("add", { required: true, messages: { required: "请输入选项内容"} });
    }else {
      item.editor.setData(item.inputValue);
    }
  }

  updateInputValue(id,inputValue) {
    this.props.updateInputValue(id,inputValue);
  }

  render() {
    let item = this.props.item;
    return (
      <div className="form-group">
        <div className="col-sm-2 control-label">
          <label className="choice-label">{item.optionLabel}</label>
        </div>
        <div className="col-sm-8 controls">
          <textarea className="form-control item-input col-md-8" id={item.optionId}  value={item.inputValue} name={item.optionId}></textarea>
          <div className="mtm"><label><input type="radio" name={item.checked}  checked={item.checked} className="answer-checkbox" value={JSON.stringify({id:item.optionId,checked:item.checked})} onChange = {(event)=>this.onChange(event)}/>正确答案</label></div>
        </div>
        <div className="col-sm-2">
          <a className="btn btn-default btn-sm"  href="javascript:;" id={`${item.optionId}`} onClick={(event)=>this.deleteOption(event)}><i className="glyphicon glyphicon-trash"></i></a>
        </div>
      </div>
    )
  }
}