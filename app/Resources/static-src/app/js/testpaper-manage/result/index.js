
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
      name: '分数区间',
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
      name: '人数',
      minInterval: 1
    }
  ],
  series : [
    {
      name:'首次成绩得分人数',
      type:'bar',
      data:data.yFirstNum
    },
    {
      name:'最优成绩得分人数',
      type:'bar',
      data:data.yMaxNum
    }
  ]
});