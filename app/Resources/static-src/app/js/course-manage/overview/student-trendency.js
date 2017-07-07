import DateRangePicker from 'app/common/daterangepicker';
export default class StudentTrendency {
    constructor() {
        new DateRangePicker('#js-student-trendency-date-range');
        this.init();
        this.data();
        this.show();
    }

    init() {
        this.studentTrendencyChart = echarts.init(document.getElementById('js-student-trendency-chart'));
    }

    data() {
        this.studentTrendencyChart.showLoading();

    }

    show() {
        let option = {
            tooltip : {
                trigger: 'axis',
                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                    type : 'none',        // 默认为直线，可选为：'line' | 'shadow'
                    shadowStyle: {
                        shadowColor: 'rgba(0, 0, 0, 0)',
                        shadowBlur: 0
                    },
                }
            },
            legend: {
                // 图表标题
                data: ['已完成', '学习中','未开始'],
                left: 100,
            },
            grid: {
                left: '0',
                right: '50px',
                bottom: '20px',
                containLabel: true,
            },
            xAxis:  {
                type: 'value',
                show: false,
                boundaryGap: false,
            },
            yAxis: {
                data: ['任务7','任务6','任务5','任务4','任务3','任务2','任务1'],
                axisLine: {
                    show: false
                },
                axisTick:{
                    show: false
                },
                boundaryGap: false,
            },
            series: [
                {
                    name: '已完成',
                    type: 'bar',
                    stack: '总量',
                    barWidth: 16,
                    label: {
                        normal: {
                            show: false,
                            position: 'insideRight'
                        }
                    },
                    data: [2, 4, 5, 5, 1, 6, 1],
                    itemStyle : {
                        normal: {
                            color: '#92D178',
                            label : {
                                show: true,
                                position: 'insideRight'
                            }
                        }
                    },
                },
                {
                    name: '学习中',
                    type: 'bar',
                    stack: '总量',
                    barWidth: 16,
                    label: {
                        normal: {
                            show: false,
                            position: 'insideRight'
                        }
                    },
                    data:[0, 4, 5, 1, 1, 6, 3],
                    itemStyle: {
                        normal: {
                            color: '#FECF7D'
                        }
                    },
                },
                {
                    name: '未开始',
                    type: 'bar',
                    stack: '总量',
                    barWidth: 16,
                    label: {
                        normal: {
                            show: false,
                            position: 'insideRight'
                        }
                    },
                    data: [2, 4, 2, 6, 10, 0, 7],
                    itemStyle: {
                        normal: {
                            color: '#D3D3D3'
                        }
                    },
                },
            ]
        };
        this.studentTrendencyChart.hideLoading();
        this.studentTrendencyChart.setOption(option);
    }
}