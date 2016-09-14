define(function(require, exports, module) {
    require("jquery.bootstrap-datetimepicker");
    var PieChart = require('./pie-chart');
    var LineChart = require('./line-chart');
    var BarChart = require('./bar-chart');
    var Widget = require('widget');

    var StatsWidget = Widget.extend({
        attrs: {
        },
        events: {
           'click .stats-summary .piece': 'onClickPiece'
        },
        setup: function() {
            this._initTime();
            this._initEcharts('#total-chart');
        },
        onClickPiece: function(event) {
            var $target = $(event.currentTarget);
            $target.closest('.stats-summary').find('.active').removeClass('active');
            $target.addClass('active');
            $target.closest('.materiallib-stats').find('.chart.active').removeClass('active');
            this.$($target.data('target')).addClass('active');
            window.location.href = $target.data('target');
            this._initEcharts($target.data('target'));
        },
        _initTime: function() {
            $("#startDate").datetimepicker({
                autoclose: true
            }).on('changeDate',function(){
                $("#endDate").datetimepicker('setStartDate',$("#startDate").val().substring(0,16));
            });

            $("#startDate").datetimepicker('setEndDate',$("#endDate").val().substring(0,16));

            $("#endDate").datetimepicker({
                autoclose: true
            }).on('changeDate',function(){

                $("#startDate").datetimepicker('setEndDate',$("#endDate").val().substring(0,16));
            });

            $("#endDate").datetimepicker('setStartDate',$("#startDate").val().substring(0,16));
        },
        _initEcharts: function(id) {
            if (id == '#total-chart' && !this.spacePieChart) {
                this.spacePieChart = new PieChart({
                    element: '#space-pie-chart',
                    data: this.$('#space-pie-chart').data('data'),
                    title: Translator.trans('存储空间使用分布')
                });

                this.flowPieChart = new PieChart({
                    element: '#flow-pie-chart',
                    data: this.$('#flow-pie-chart').data('data'),
                    title: Translator.trans('流量使用分布')
                });

                this.totalLineChart = new BarChart({
                    element: '#total-line-chart',
                    title: Translator.trans('空间/流量详情')
                });
            }

            if (id == '#video-chart' && !this.videoLineChart ) {
                this.videoLineChart = new LineChart({
                    element: '#video-line-chart',
                    title: Translator.trans('视频详情')
                });
            }

            if (id == '#audio-chart' && !this.audioLineChart) {
                this.audioLineChart = new LineChart({
                    element: '#audio-line-chart',
                    title: Translator.trans('音频详情')
                });
            }

            if (id == '#document-chart' && !this.docLineChart) {
                this.docLineChart = new LineChart({
                    element: '#document-line-chart',
                    title: Translator.trans('文档详情')
                });
            }
        }
    });

    exports.run = function() {
        new StatsWidget({
            element: '#materiallib-stats'
        });
    }

});
