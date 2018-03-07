import BaseChart from './base-chart.js';

export default class StudentDetail extends BaseChart{
  constructor($chart) {
    super($chart);
    this.chartEvent();
  }

  chartEvent(){
    let self = this;

    this.$form.find('select').change(function(){
      self.update();
    });

    let $nameSearch = this.$form.find('.js-name-search');
    $nameSearch.on('keypress',function(event){
      if (13 === event.keyCode) {
        self.update();
        return false;
      }
    });

    $nameSearch.next().on('click',function(){
      self.update();
    });
  }

  legendEvent($btn){
    this.$chart.find($btn.data('barClass')).parent().toggleClass('hide');
  }
}