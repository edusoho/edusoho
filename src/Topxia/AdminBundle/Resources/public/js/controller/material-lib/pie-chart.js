define(function(require, exports, module) {
    require('echarts');
    var Widget = require('widget');

    var PieChart = Widget.extend({
        attrs: {
        },
        events: {
        },
        setup: function() {
            this._init();
        },
        _init: function() {
            var pieChart = echarts.init(this.element.get(0));
            option = {
                title : {
                    text: this.get('title'),
                    x:'right'
                },
                // tooltip : {
                //     trigger: 'item',
                //     formatter: "{a} <br/>{b} : {c}GB ({d}%)"
                // },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    formatter: function(name) {
                        var data = pieChart.getOption().series[0].data;
                        var size = 0;
                        var per = 0;
                        $.each(data, function(i, n){
                            if (n.name == name) {
                                size = n.value;
                                per = n.per;
                                return;
                            };
                        });

                        return name+':'+size+'GB'+'  '+per;
                    },
                    data: [Translator.trans('admin.material_lib.video'),Translator.trans('admin.material_lib.audio'),Translator.trans('admin.material_lib.picture'),Translator.trans('admin.material_lib.document'),Translator.trans('PPT'),Translator.trans('admin.material_lib.other')]
                },
                series : [
                    {
                        name: this.get('title'),
                        type: 'pie',
                        radius : '55%',
                        center: ['50%', '60%'],
                        data:this.get('data'),
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }
                ]
            };

            pieChart.setOption(option);
        }
    });
    
    module.exports = PieChart;

});