export default class taskDetail{
    constructor($chart) {
        this.$chart = $chart;
        this.init();
    }

    init(){
        this.taskChart = echarts.init(this.$chart[0]);
        let option = this._getInitOptions();
        this.taskChart.setOption(option);
        this._update();
    }

    _update(){
        let self = this;
        this.taskChart.showLoading();
        let url = self.$chart.data('url');
        $.get(url, function(html){
            let $dataSource = $(html);
            self.$chart.next().html($dataSource);
            let chartData = self.getChartData($dataSource);
            self.updateChart(chartData);
            self.taskChart.hideLoading();
        })
    }

    getChartData($dataSource){
        let finishedNum = $dataSource.data('finishedNum');
        let learnNum = $dataSource.data('learnNum');
        let studentNum = $dataSource.data('studentNum');
        let alias =  $dataSource.data('alias');
        let finishRate = [],undoNum = [];
        for(let i in finishedNum){
            let num = studentNum-learnNum[i]-finishedNum[i];
            undoNum.push(num);
            finishRate.push(finishedNum[i]/studentNum);
        }
        return {
            finishedNum: finishedNum,
            learnNum: learnNum,
            finishRate: finishRate,
            undoNum: undoNum,
            alias: alias,
        }
    }

    updateChart(chartData){
        this.taskChart.setOption({
            yAxis: {
                data: chartData.alias,
            },
            series: [                {
                name: '已完成',
                data: chartData.finishedNum,
            },
                {
                    name: '学习中',
                    data:chartData.learnNum,
                },
                {
                    name: '未开始',
                    data: chartData.undoNum,
                },]
        });
    }

    _getInitOptions() {
        return {
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
                splitLine:{
                    show:false
                },
            },
            yAxis: {
                splitLine:{
                    show:false
                },
                data: [

                ],
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
                    data: [],
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
                    data:[],
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
                    data: [],
                    itemStyle: {
                        normal: {
                            color: '#D3D3D3'
                        }
                    },
                },
            ]
        };
    }
}