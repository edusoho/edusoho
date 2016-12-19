import React,{ Component } from 'react';

export default class Options extends Component {
  constructor(props) {
    super(props);
  }

  deleteOption(event) {
    console.log(event);
    this.props.deleteOption(event);
  }

  componentDidMount() {
    this.props.dataSourceUi.map((item,index)=>{
      var editor = CKEDITOR.replace(item.optionId, {
        toolbar: 'Minimal',
        height: 120
      });
    });
  }

  onChange(event) {
    console.log(event);
    this.props.changeOption(event);
  }

  render() {
    return (
      <div className="question-options">
      {
        this.props.dataSourceUi.map((item,index)=>{
        return (
          <div className="form-group" key={index}>
            <div className="col-sm-2 control-label">
              <label className="choice-label">{item.optionLabel}</label>
            </div>
            <div className="col-sm-8 controls">
              <textarea className="form-control item-input col-md-8" id={`${item.optionId}`} name="choices[]" data-display="" ></textarea>
              <p className="mtm"><label><input type="checkbox" name={item.checked}  checked={item.checked} className="answer-checkbox" value={{id:item.optionId,checked:checked}} onChange = {(event)=>this.onChange(event)}/>正确答案</label></p>
            </div>
            <div className="col-sm-2">
              <a className="btn btn-default btn-sm"  href="javascript:;" id={`${item.optionId}`} onClick={(event)=>this.deleteOption(event)}><i className="glyphicon glyphicon-trash"></i></a>
            </div>
          </div>)
        })
      }
      </div>
    )
  }
}