export default class BaseChart{
  constructor($chart) {
    this.$chart = $chart;
    this.$form = $($chart.data('form'));
    this.init();
  }

  init(){
    this.update();
    this.legend();
    this.pageEvent();
  }

  update(url = ''){
    let self = this;
    let data = this.$form.serialize();
    url = url||this.$chart.data('url')+'?'+data;
    self.showLoading();
    $.get(url,function(html){
      self.$chart.html(html);
      self.$chart.find('[data-toggle=\'popover\']').popover();
    });
  }

  pageEvent(){
    let self = this;
    this.$chart.on('click', '.pagination a', function(){
      let $this = $(this);
      let url = $this.attr('href');
      self.update(url);
      return false;
    });
  }

  legend(){
    let self = this;
    this.$chart.on('click', '.js-legend-btn',function() {
      let $this = $(this);
      $this.toggleClass('active');
      self.legendEvent($this);
    });
  }

  legendEvent($btn){
    console.log($btn);
    console.log('图表导航事件');
  }

  showLoading()
  {
    let loading = `<div class="pvl mvl text-center">
            <img width="50" height="50" src="/assets/img/default/loading.gif" />
        </div>`;

    this.$chart.html(loading);
  }
}