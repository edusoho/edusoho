
let myChart = echarts.init(document.getElementById('line-data'));
let data = $.parseJSON($('#data').val());

myChart.setOption({
  color: ['#0E4D93','#687F92'],
  tooltip : {
    trigger: 'axis',
    axisPointer : {
      type : 'shadow'
    }
  },
  grid: {
    bottom: '5%',
    containLabel: true
  },
  xAxis : [
    {
      type: 'category',
      name: Translator.trans('homework_manage.result_graph.status_distribution'),
      nameLocation: 'middle',
      nameGap: 25,
      data: data.xScore,
      axisTick: {
        alignWithLabel: true
      }
    }
  ],
  yAxis : [
    {
      type: 'value',
      name: Translator.trans('testpaper_manage.result_graph.person_num'),
      minInterval: 1
    }
  ],
  series : [
    {
      name:Translator.trans('homework_manage.result_graph.first_status_num'),
      type:'bar',
      data:data.yFirstNum
    },
    {
      name:Translator.trans('homework_manage.result_graph.max_status_num'),
      type:'bar',
      data:data.yMaxNum
    }
  ]
});