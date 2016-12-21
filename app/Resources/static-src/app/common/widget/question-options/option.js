import React,{ Component } from 'react';
import { trim } from '../../unit';


export default class Options extends Component {
  constructor(props) {
    super(props);
    this.state = {
      datas: this.props.datas,
    }
    this.editor = null;
    //value不需传递给付组件的state,因为value是属于自身组件的行为不应该去出发别的组件的Render；
  }
 
  componentDidMount() {
    console.log('componentDidMount');
    this.initCkeditor();
  }

  
  deleteOption(event) {
    this.props.deleteOption(event.currentTarget.id);
  }

  onChangeChecked(event) {
    this.props.changeOptionChecked(event.currentTarget.value);
  }

  initCkeditor(dataSourceUi) {
    if(!this.editor) {
      this.editor = CKEDITOR.replace(this.state.datas.optionId, {
        toolbar: 'Minimal',
        height: 120
      });
      let self = this;
      this.editor.on('change', function( event ) {   
        let data = this.getData();//内容
        self.updateInputValue(data);
      });
    }else {
      this.editor.setData(datas.inputValue);
    }
  }

  updateInputValue(inputValue) {
    this.state.datas.inputValue = inputValue;
    this.setState({
      datas: this.state.datas,
    })
  }

  render() {
    let isValidator = this.props.isValidator;
    let showDanger = this.props.isValidator && trim(this.state.datas.inputValue).length <= 0;
    let type = 'checkbox';
    if(this.props.isRadio) {
      type= 'radio';
    }
    return (
      <div className="form-group">
        <div className="col-sm-2 control-label">
          <label className="choice-label">{this.state.datas.optionLabel}</label>
        </div>
        <div className="col-sm-8 controls">
          <textarea className="form-control datas-input col-md-8" id={this.state.datas.optionId}  value={this.state.datas.inputValue} name={this.state.datas.optionId}></textarea>
          <div className="mtm">
            <label>
              <input type={type} name={this.state.datas.checked} value={JSON.stringify({id:this.state.datas.optionId,checked:this.state.datas.checked})}  checked={this.state.datas.checked} className="answer-checkbox" onChange = {(event)=>this.onChangeChecked(event)}/>正确答案 
            </label>
          </div>
          { showDanger && <p className="color-danger">请输入选项内容</p>}
        </div>
        <div className="col-sm-2">
          <a className="btn btn-default btn-sm"  href="javascript:;" id={`${this.state.datas.optionId}`} onClick={(event)=>this.deleteOption(event)}><i className="glyphicon glyphicon-trash"></i></a>
        </div>
      </div>
    )
  }
}