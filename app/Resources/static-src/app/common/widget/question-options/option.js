import React,{ Component } from 'react';

export default class Options extends Component {
  constructor(props) {
    super(props);
  }

  render() {
    return(
      this.props.dataSource.map((item,index)=>{
        <div className="form-group">
          <div className="col-sm-2 control-label">
            <label class="choice-label" for={`option-${item.optionId}`}>{item.optionLabel}</label>
          </div>
          <div className="col-sm-8 controls">
            <textarea className="form-control item-input col-md-8" id={`option-${item.optionId}`} name="choices[]" data-display="" data-image-upload-url={ imageUploadUrl } data-image-download-url={imageDownloadUrl}></textarea>
            <p className="mtm"><label><input type="checkbox" checked={item.checked} className="answer-checkbox"/>正确答案</label></p>
          </div>
          <div class="col-sm-2" style="padding-left:0;">
            <a class="btn btn-default btn-sm delete-choice mlm" data-role="delete-choice" href="javascript:void(null)"><i class="glyphicon glyphicon-trash"></i></a>
          </div>
        </div>
      })  
    )
  }
}