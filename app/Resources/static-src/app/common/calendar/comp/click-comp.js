import Comp from './comp';

/**
 * 左键点击，跳转
 * 如 new ClickComp('{url}')
 */
export default class ClickComp extends Comp {

  constructor(url) {
    super();
    this.url = url;
    this._generateParamNamesPerUrl();
  }

  registerAction(options) {
    let current = this;
    options['eventClick'] = function(event, jsEvent, view) {
      window.open(current._generateClickUrl(event));
    };
    return options;
  }

  _appendAdditionalAttr(event) {
    event['className'] = ['calendar_clickable'];
    return event;
  }

  _getParamNames() {
    return this.paramNames;
  }

  _getParamPrefix() {
    return 'click';
  }

  _generateParamNamesPerUrl() {
    if (typeof this.paramNames == 'undefined') {
      let segs = this.url.split('{');
      this.paramNames = [];
      for (let i = 0; i < segs.length; i++) {
        let seg = segs[i];
        if (seg.indexOf('}') != -1) {
          this.paramNames.push(seg.split('}')[0]);
        }
      }
    }

    return this.paramNames;
  }

  _generateClickUrl(event) {
    let paramNames = this._generateParamNamesPerUrl();
    let generatedUrl = this.url;
    for (let i = 0; i < paramNames.length; i++) {
      let paramName = paramNames[i];
      generatedUrl = generatedUrl.replace(
        '{' + paramName + '}',
        event[this._getFormatedParamName(paramName)]
      );
    }
    return generatedUrl;
  }
}