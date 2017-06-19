let $container = $('#course-dashboard-container');
let myChart = echarts.init(document.getElementById('course-dashboard-container'));
let studentNum = $container.data('studentNum');
let studentSplitNumber = getStudentSplitNumber(studentNum);

// 指定图表的配置项和数据
let option = {
    tooltip: {
        trigger: 'axis',
        formatter: formatter
    },
    legend: {
        data: ['学员数', '完成数', '完课率']
    },
    grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
    },
    xAxis: {
        type: 'category',
        boundaryGap: false,
        data: $container.data('days')
    },
    yAxis: [
        {
            name: '人数',
            type: 'value',
            minInterval: 1,
            splitNumber: studentSplitNumber,
            min: 0,
        },
        {
            name: '完课率',
            type: 'value',
            minInterval: 1,
            max: 100,
            axisLabel: {
                formatter: '{value}%'
            },
            min: 0
        }
    ],
    series: [
        {
            name:'学员数',
            type:'line',
            yAxisIndex: 0,
            showSymbol: false,
            smooth: true,
            itemStyle: {
                normal: {
                    color: '#FFC108'
                }
            },
            data: studentNum
        },
        {
            name:'完成数',
            type:'line',
            yAxisIndex: 0,
            showSymbol: false,
            smooth: true,
            itemStyle: {
                normal: {
                    color: '#4CAF50'
                }
            },
            data:$container.data('finishedNum')
        },
        {
            name:'完课率',
            type:'line',
            yAxisIndex: 1,
            showSymbol: false,
            smooth: true,
            itemStyle: {
                normal: {
                    color: '#2096F3'
                }
            },
            data:$container.data('finishedRate')
        }
    ],
};

function formatter(params) {
    let html = params[0].name + '</br>';
    for (let i = 0; i < params.length; i++) {
        let circle = '<span style="display:inline-block;margin-right:5px;'
        + 'border-radius:10px;width:9px;height:9px;background-color:' + params[i].color + '"></span>'
        + params[i].seriesName + ' : ' + params[i].value + '</br>';
        html += circle;
    };
    //changeSummary(params[0].dataIndex);
    return html;
}

function changeSummary(index) {
  let studentNumArr = $container.data('studentNum'),
    finishedNumArr = $container.data('finishedNum'),
    finishedRateArr = $container.data('finishedRate'),
    askNumArr = $container.data('askNum'),
    noteNumArr = $container.data('noteNum'),
    discussionNumArr = $container.data('discussionNum');
  $('.js-student-num').html(studentNumArr[index]);
  $('.js-finished-num').html(finishedNumArr[index]);
  $('.js-finished-rate').html(finishedRateArr[index] + '%');
  $('.js-ask-num').html(askNumArr[index]);
  $('.js-note-num').html(noteNumArr[index]);
  $('.js-discussion-num').html(discussionNumArr[index]);
}

// 使用刚指定的配置项和数据显示图表。
myChart.setOption(option);

$('.finisher-lesson-popover').popover({
  html: true,
  trigger: 'hover',
  placement: 'bottom',
  template: '<div class="popover tata-popover tata-popover-lg" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
  content: function () {
    let html = $(this).siblings('.popover-content').html();
    return html;
  }
});

function getStudentSplitNumber(studentNum) {
    studentSplitNumber = Math.max.apply(null, studentNum);

    if (studentSplitNumber == 0) {
        return 1;
    } else {
       return studentSplitNumber > 5 ? 5 : studentSplitNumber;
    }
}