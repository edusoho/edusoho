define(function(require, exports, module) {
    require('echarts');
    var Widget = require('widget');

    var LineChart = Widget.extend({
        attrs: {
        },
        events: {
        },
        setup: function() {
            this._init();
        },
        _init: function() {
            var chart = echarts.init(this.element.get(0));

            option = {
                tooltip: {
                    trigger: 'axis'
                },
                title: {
                    left: 'center',
                    text: this.get('title'),
                },
                legend: {
                    top: '10%',  
                    data:[this.element.data('title1'), this.element.data('title2')]
                },
                toolbox: {
                    show: true,
                    feature: {
                        restore: {show: true},
                        saveAsImage: {show: true}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: this.element.data('date')
                },
                yAxis: [
                    {
                        name:this.element.data('ytitle1'),
                        type: 'value',
                        boundaryGap: [0, '100%']
                    },
                    {
                        name:this.element.data('ytitle2'),
                        type: 'value',
                        boundaryGap: [0, '100%']
                    }
                ],
                dataZoom: [{
                    type: 'inside',
                    start: 0,
                    end: 10
                }, {
                    start: 0,
                    end: 10
                }],
                series: [
                    {
                        name:this.element.data('title1'),
                        type:'line',
                        yAxisIndex:0,
                        smooth:true,
                        symbol: 'none',
                        sampling: 'average',
                        lineStyle: {
                            normal: {
                                color: '#97CAB2'
                            }
                        },
                        data: this.element.data('data1')
                    },
                    {
                        name:this.element.data('title2'),
                        type:'line',
                        yAxisIndex:1,
                        smooth:true,
                        symbol: 'none',
                        sampling: 'average',
                        lineStyle: {
                            normal: {
                                color: '#E43C59'
                            }
                        },
                        data: this.element.data('data2')
                    }
                ]
            };
            chart.setOption(option);
        }

    });
    
    module.exports = LineChart;

});