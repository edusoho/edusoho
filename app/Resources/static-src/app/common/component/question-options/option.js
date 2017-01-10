import React,{ Component } from 'react';

export default class Options extends Component {
  constructor(props) {
    super(props);
    console.log(this.props.datas);
    this.state = {
      inputValue: this.props.datas.inputValue,
    }
    this.editor = null;
    //value不需传递给付组件的state,因为value是属于自身组件的行为不应该去触发别的组件的Render；
  }
 
  componentDidMount() {
    this.initCkeditor();
  }

  deleteOption(event) {
    console.log(this.state.inputValue);
    this.state.inputValue = "";
    this.editor.setData("");
    console.log(this.state.inputValue);
    this.props.deleteOption(event.currentTarget.attributes["data-option-id"].value);
  }

  onChangeChecked(event) {
    this.props.changeOptionChecked(event.currentTarget.attributes["data-option-id"].value);
  }

  initCkeditor(dataSourceUi) {
    if(!this.editor) {
      this.editor = CKEDITOR.replace(this.props.datas.optionId, {
        toolbar: 'Minimal',
        height: 120
      });
      let self = this;
      this.editor.on('change', function( event ) {   
        let data = this.getData();//内容
        setTimeout(()=>{
          self.updateInputValue(data);
        },100)
      });
    }else {
      this.editor.setData(datas.inputValue);
    }
  }

  updateInputValue(inputValue) {
    this.props.validatorDatas.Options[this.props.datas.optionId] = inputValue.length > 0 ? 1 : 0;
    if(inputValue.length <=0 ) {
      this.props.publishMessage(false);
    }
    this.state.inputValue = inputValue;
    this.setState({
      datas: this.state.inputValue,
    })
  }

  render() {
    let showDanger = this.props.isValidator && this.state.inputValue.length <= 0;
    let type = 'checkbox';
    if(this.props.isRadio) {
      type= 'radio';
    }
    console.log("reder childe");
    console.log(this.state.inputValue);
    return (
      <div className="form-group">
        <div className="col-sm-2 control-label">
          <label className="choice-label control-label-required">{this.props.datas.optionLabel}</label>
        </div>
        <div className="col-sm-8 controls">
          <textarea className="form-control datas-input col-md-8" id={this.props.datas.optionId}  defaultValue={this.state.inputValue} name='choices[]' value={this.state.inputValue}></textarea>
          <div className="mtm">
            <label>
              <input type={type} name='answer[]' data-option-id={this.props.datas.optionId} value={this.props.index}  checked={this.props.datas.checked} className="answer-checkbox" onChange = {(event)=>this.onChangeChecked(event)}/>正确答案 
            </label>
          </div>
          <p className={showDanger ? 'color-danger' : 'hidden'}>请输入选项内容</p>
        </div>
        <div className="col-sm-2">
          <a className="btn btn-default btn-sm" data-option-id={ this.props.datas.optionId }  onClick={(event)=>this.deleteOption(event)} href="javascript:;"><i className="glyphicon glyphicon-trash"></i></a>
        </div>
      </div>
    )
  }
}