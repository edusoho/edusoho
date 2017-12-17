import Comp from 'app/common/calendar/comp/comp'

export default class BaseTooltipComp extends Comp {

  registerAction(options) {
    let current = this;
    options['eventMouseover'] = function(event, jsEvent, view) {
      current._showTip(current._generateParams(event), event, jsEvent);
    }
    options['eventMouseout'] = function(event, jsEvent, view) {
      current._hideTip(current._generateParams(event), event, jsEvent);
    }
    return options;
  }

  _showTip(params, event, jsEvent) {
    alert('BaseTooltip: showTip not implemented');
  }

  _hideTip(params, event, jsEvent) {
    alert('BaseTooltip: hideTip not implemented');
  }

  _getParamPrefix() {
    return 'tooltip';
  }
}