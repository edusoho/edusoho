export default class Comp {

  generateEventValues(singleResult) {
    let compParamNames = this._generateParamNames();
    let event = {};
    for (let i = 0; i < compParamNames.length; i++) {
      let fieldName = compParamNames[i];
      let originalParaName = this._getOriginalParamName(fieldName);
      event[fieldName] = singleResult[originalParaName];
    }

    return this._appendAdditionalAttr(event);
  }

  registerAction(options) {
    return options;
  }

  _getOriginalParamName(paramName) {
    return paramName.split(this._getParamPrefix() + '___')[1];
  }

  _getFormatedParamName(paramName) {
    return this._getParamPrefix() + '___' + paramName;
  }

  _generateParamNames() {
    if (typeof this.formatedParamNames == 'undefined') {
      let paramNames = [];
      let compParams = this._getParamNames();
      for (let i = 0; i < compParams.length; i++) {
        paramNames.push(this._getFormatedParamName(compParams[i]));
      }
      this.formatedParamNames = paramNames;
    }
    return this.formatedParamNames;
  }

  _generateParams(event) {
    let params = {};
    let paramNames = this._generateParamNames();
    for (let i = 0; i < paramNames.length; i++) {
      let paramName = paramNames[i];
      let originalParamName = this._getOriginalParamName(paramName);
      params[originalParamName] = event[paramName];
    }
    return params;
  }

  _appendAdditionalAttr(event) {
    return event;
  }

  _getParamNames() {
    alert('BaseTooltip: _getParamNames not implemented');
  }

  _getParamPrefix() {
    alert('Comp: _getParamPrefix not implemented');
  }
}