import React,{ Component } from 'react';


export default class Options extends Component {
  constructor(props) {
    super(props);
    let datas = this.props.datas;
    this.state = {
      datas: datas,
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
    console.log('event',event.currentTarget.checked,event.currentTarget.id);
    this.props.changeOptionChecked(event.currentTarget.id);
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
    this.state.datas.inputValue = inputValue;
    this.setState({
      datas: this.state.datas,
    })
  }

  render() {
    let showDanger = this.props.isValidator && this.state.datas.inputValue.length <= 0;
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
          <textarea className="form-control datas-input col-md-8" id={this.state.datas.optionId}  defaultValue={this.state.datas.inputValue} name='choices[]'></textarea>
          <div className="mtm">
            <label>
              <input type={type} name='answer[]' id={this.state.datas.optionId} value={this.props.index}  checked={this.state.datas.checked} className="answer-checkbox" onChange = {(event)=>this.onChangeChecked(event)}/>正确答案 
            </label>
          </div>
          <p className={showDanger ? 'color-danger' : 'hidden'}>请输入选项内容</p>
        </div>
        <div className="col-sm-2">
          <a className="btn btn-default btn-sm"  href="javascript:;" id={this.state.datas.optionId} onClick={(event)=>this.deleteOption(event)}><i className="glyphicon glyphicon-trash"></i></a>
        </div>
      </div>
    )
  }
}