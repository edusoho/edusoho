import BaseChart from './base-chart.js';

export default class StudentDetail extends BaseChart{
  constructor($chart) {
    super($chart);
    this.chartEvent();
  }

  chartEvent(){
    let self = this;
    let $jsSearchBtn = this.$form.find('.js-task-detail-search');
    $jsSearchBtn.prev().on('keypress',function(event){
      if (13 === event.keyCode) {
        self.update();
        return false;
      }
    });

    $jsSearchBtn.on('click',function(){
      self.update();
    });
  }

  legendEvent($btn){
    this.$chart.find($btn.data('barClass')).toggleClass('width-hide');
  }
}