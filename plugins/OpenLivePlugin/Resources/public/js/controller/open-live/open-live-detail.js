define(function(require, exports, module) {
  require("jquery.bootstrap-datetimepicker");
  var Notify = require('common/bootstrap-notify');
  require('../common/echarts.min.js');

  exports.run = function() {
    liveMemberAnalysisChart();
    liveOnlineNumChart();

    $('.js-visitor-reports').on('click', '.pagination li', function () {
      var url = $(this).data('url');
      if (typeof (url) !== 'undefined') {
        $.get(url, function (data) {
          $('.js-visitor-reports').html(data);
        });
      }
    });
    var reportSearchBtn = $('.js-visitor-report-search');
    reportSearchBtn.on('click', function () {
      $(this).button('submiting').attr('disabled', true);
      $.get($(this).data('searchUrl'), {visitorNickname: $('#visitorNickname').val()}, function(res) {
        if (res.length > 0) {
          $('.js-visitor-reports').html(res);
        } else {
          Notify.danger(Translator.trans('admin.setting.operation_fail_hint'));
        }
        reportSearchBtn.html('查询');
        reportSearchBtn.attr('disabled', false);
      }).error(function(){
        Notify.danger(Translator.trans('admin.setting.operation_fail_hint'));
        reportSearchBtn.html('查询');
        reportSearchBtn.attr('disabled', false);
      });
    });

    let startDateInput = $("#startDate");
    let endDateInput = $("#endDate");
    startDateInput.datetimepicker({
      autoclose: true,
    }).on('changeDate', function() {
      endDateInput.datetimepicker('setStartDate', startDateInput.val().substring(0, 16));
    });
    startDateInput.datetimepicker('setEndDate', endDateInput.val().substring(0, 16));

    endDateInput.datetimepicker({
      autoclose: true,
    }).on('changeDate', function() {
      startDateInput.datetimepicker('setEndDate', endDateInput.val().substring(0, 16));
    });
    endDateInput.datetimepicker('setStartDate', startDateInput.val().substring(0, 16));
  };

  var liveMemberAnalysisChart = function () {
    var myChart = echarts.init(document.getElementById('analysis-line-data'));
    var option = {
      color: ['#428bca','#2f4554','#91c7ae','#61a0a8', '#d48265'],
      tooltip: {
        trigger: 'item',
        formatter: '{a} <br/>{b} : {c}'
      },
      series: [
        {
          name: '引流转化分析',
          type: 'funnel',
          right: '20%',
          data: $('#analysis-line-data').data('lineData')
        }
      ]
    };
    myChart.setOption(option);
  };

  var liveOnlineNumChart = function() {
    var lineData = $('#line-data').data('lineData');
    var myChart = echarts.init(document.getElementById('line-data'));
    var option = generateLineShowOption(lineData);
    myChart.setOption(option);

    var onlineSearchBtn = $('.js-online-num-search');
    onlineSearchBtn.on('click', function () {
      $(this).button('submiting').attr('disabled', true);
      $.get($(this).data('searchUrl'), {startDate: $('#startDate').val(), endDate: $('#endDate').val()}, function(res) {
        myChart.setOption(generateLineShowOption(res));
        onlineSearchBtn.html('查询');
        onlineSearchBtn.attr('disabled', false);
      }).error(function(){
        Notify.danger(Translator.trans('admin.setting.operation_fail_hint'));
        onlineSearchBtn.html('查询');
        onlineSearchBtn.attr('disabled', false);
      });
    });
  }

  var generateLineShowOption = function (lineData) {
    return {
      tooltip: {
        trigger: 'axis'
      },
      legend: {
        orient: 'vertical',
        top: '10%',
        right: '4%',
        z: 99,
        icon: 'roundRect',
        data: lineData.legend
      },
      grid: {
        right: '20%',
        bottom: '3%',
        containLabel: true
      },
      xAxis: {
        type: 'category',
        boundaryGap: false,
        data: lineData.xAxis
      },
      yAxis: {
        type: 'value',
        max: lineData.yMax
      },
      series: lineData.series
    };
  };

});