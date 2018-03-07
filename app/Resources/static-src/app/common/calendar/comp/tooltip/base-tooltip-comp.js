import Comp from '../comp';

export default class BaseTooltipComp extends Comp {

  registerAction(options) {
    let current = this;
    options['eventMouseover'] = function(event, jsEvent, view) {
      current._showTip(current._generateParams(event), event, jsEvent);
    };
    options['eventMouseout'] = function(event, jsEvent, view) {
      current._hideTip(current._generateParams(event), event, jsEvent);
    };
    return options;
  }

  _showTip(params, event, jsEvent) {
    alert('BaseTooltip: _showTip not implemented');
  }

  _hideTip(params, event, jsEvent) {
    alert('BaseTooltip: _hideTip not implemented');
  }

  _getParamPrefix() {
    return 'tooltip';
  }
}