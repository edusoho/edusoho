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
                    title: Translator.trans('admin.material_lib.space_pie_chart_title')
                });

                this.flowPieChart = new PieChart({
                    element: '#flow-pie-chart',
                    data: this.$('#flow-pie-chart').data('data'),
                    title: Translator.trans('admin.material_lib.flow_pie_chart_title')
                });

                this.totalLineChart = new BarChart({
                    element: '#total-line-chart',
                    title: Translator.trans('admin.material_lib.total_line_chart_title')
                });
            }

            if (id == '#video-chart' && !this.videoLineChart ) {
                this.videoLineChart = new LineChart({
                    element: '#video-line-chart',
                    title: Translator.trans('admin.material_lib.video_line_chart_title')
                });
            }

            if (id == '#audio-chart' && !this.audioLineChart) {
                this.audioLineChart = new LineChart({
                    element: '#audio-line-chart',
                    title: Translator.trans('admin.material_lib.audio_line_chart_title')
                });
            }

            if (id == '#document-chart' && !this.docLineChart) {
                this.docLineChart = new LineChart({
                    element: '#document-line-chart',
                    title: Translator.trans('admin.material_lib.document_line_chart_title')
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
