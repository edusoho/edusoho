define(function(require, exports, module) {
    require('echarts');
    var Widget = require('widget');

    var BarChart = Widget.extend({
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
                toolbox: {
                    feature: {
                        restore: {show: true},
                        saveAsImage: {show: true}
                    }
                },
                legend: {
                    data:[this.element.data('title1'), this.element.data('title2')]
                },
                xAxis: [
                    {
                        type: 'category',
                        data: this.element.data('date')
                    }
                ],
                yAxis: [
                    {
                        name:this.element.data('ytitle1'),
                        type: 'value'
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
                        type:'bar',
                        data:this.element.data('data1')
                    },
                    {
                        name:this.element.data('title2'),
                        type:'bar',
                        data:this.element.data('data2')
                    }
                ]
            };

            chart.setOption(option);
        }

    });
    
    module.exports = BarChart;

});