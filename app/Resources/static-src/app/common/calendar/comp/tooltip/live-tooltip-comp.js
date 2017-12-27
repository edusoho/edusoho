import BaseTooltipComp from 'app/common/calendar/comp/tooltip/base-tooltip-comp'

export default class LiveTooltip extends BaseTooltipComp {

  _getParamNames() {
    return ['event', 'startTime', 'endTime', 'date'];
  }

  _showTip(params, event, jsEvent) {
    console.log('show live tip', params);
  }

  _hideTip(params, event, jsEvent) {
    console.log('hide live tip', params);
  }
}