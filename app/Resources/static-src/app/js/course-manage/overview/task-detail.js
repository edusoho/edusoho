export default class taskDetail{
    constructor($chart) {
        this.$chart = $chart;
        this.init();
    }

    init(){
        this.taskChart = echarts.init(this.$chart[0]);
        let option = this._getInitOptions();
        this.taskChart.setOption(option);
        this.update();
    }

    update(url=''){
        let self = this;
        this.taskChart.showLoading();
        url = url||self.$chart.data('url');
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
        this.chartTitles = $dataSource.data('titles');
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
            series: [{
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
        this._resize(chartData.alias.length);
    }

    _resize(length){
        console.log(length);
        this.$chart.height(35 * length + 70);
        this.taskChart.resize();
    }

    _getInitOptions() {
        let self = this;
        return {
            tooltip : {
                trigger: 'axis',
                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                    type : 'none',        // 默认为直线，可选为：'line' | 'shadow'
                    shadowStyle: {
                        shadowColor: 'rgba(0, 0, 0, 0)',
                        shadowBlur: 0
                    },
                },
                formatter: function (params) {
                    let html = '';
                    let title = '';
                    for(let i in params) {
                        let param = params[i];
                        if (title == '') {
                            title = self.chartTitles[params[i].dataIndex];
                        }
                        html += `<span style="display:inline-block;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:${param.color}"></span>`;
                        html += param.seriesName +" : " + param.data + '<br/>';
                    }
                    return  title + '<br/>'+ html;
                },
                backgroundColor: ['#fff'],
                textStyle: {
                    color: ['#666'],
                }
            },
            legend: {
                // 图表标题
                data: ['已完成', '学习中','未开始'],
                left: 0,
                top: 0,
                height: 70,
            },
            grid: {
                left: '0',
                right: '50px',
                bottom: '0',
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
                axisLabel: {
                    //文字和纵坐标间距
                    margin: 15,
                }
            },
            series: [
                {
                    name: '已完成',
                    type: 'bar',
                    stack: '总量',
                    barWidth: 18,
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
                    barWidth: 18,
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
                    barWidth: 18,
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