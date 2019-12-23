define(function (require, exports, module) {

  var Notify = require('common/bootstrap-notify');
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);
  require('echarts');

  exports.run = function () {
    popover();
    //图表
    courseExplore();
    studyCountStatistic();
    payOrderStatistic();
    studyTaskCountStatistic()
    //事件
    registerSwitchEvent();
    //提醒教师
    remindTeachersEvent();
    //热门搜索
    cloudHotSearch();
    //ajax 获取数据
    loadAjaxData();
  };

  var loadAjaxData = function () {
    siteOverviewData();
  };

  var registerSwitchEvent = function () {

    DataSwitchEvent('.js-study-switch-button', studyCountStatistic);

    DataSwitchEvent('.js-order-switch-button', payOrderStatistic);

    DataSwitchEvent('.js-task-switch-button', studyTaskCountStatistic);

    DataSwitchEvent('.js-course-switch-button', courseExplore);

  };

  //热门搜索
  var cloudHotSearch = function () {
    var totalWidth = $('.js-cloud-search').parent().width();
    var $countDom = $('.js-cloud-search');
    var totalCount = 0;

    $countDom.each(function () {
      totalCount += $(this).data('count');
    });

    $countDom.each(function () {
      var width = ($(this).data('count') / totalCount * totalWidth * 3 + 2).toFixed(2);
      $(this).width(width);
    });
  };

  //网站概览
  var siteOverviewData = function () {
    var $this = $('#site-overview-table');
    return $.post($this.data('url'), function (html) {
      $this.html(html);
    });
  };

  var studyCountStatistic = function () {
    this.element = $('#study-count-statistic');
    var chart = echarts.init(this.element.get(0));
    chart.showLoading();
    return $.get(this.element.data('url'), function (datas) {
      var option = {
        tooltip: {
          trigger: 'axis'
        },
        legend: {
          data: [Translator.trans('admin.index.new_order_count'), Translator.trans('admin.index.new_paid_order_count')]
        },
        grid: {
          left: '3%',
          right: '6%',
          bottom: '3%',
          containLabel: true
        },
        toolbox: {
          feature: {
            saveAsImage: {}
          }
        },
        xAxis: {
          type: 'category',
          boundaryGap: false,
          data: datas.xAxis.date
        },
        yAxis: {
          type: 'value',
        },
        series: [
          {
            name: Translator.trans('admin.index.new_order_count'),
            type: 'line',
            data: datas.series.newOrderCount
          },
          {
            name: Translator.trans('admin.index.new_paid_order_count'),
            type: 'line',
            data: datas.series.newPaidOrderCount
          }
        ],
        color: ['#46C37B', '#428BCA']
      };
      chart.hideLoading();
      chart.setOption(option);
    });
  };

  var payOrderStatistic = function () {
    this.element = $('#pay-order-statistic');
    var chart = echarts.init(this.element.get(0));


    chart.showLoading();
    return $.get(this.element.data('url'), function (data) {

      var option = {
        tooltip: {
          trigger: 'item',
          formatter: '{a} <br/>{b} : {c} ({d}%)'
        },
        legend: {
          orient: 'vertical',
          right: 'right',
          top: 'center',
          data: [Translator.trans('admin.index.course_order'), Translator.trans('admin.index.classroom_order'), Translator.trans('admin.index.vip_order')]
        },
        toolbox: {
          feature: {
            saveAsImage: {}
          }
        },
        series: [
          {
            name: Translator.trans('admin.index.order_count'),
            type: 'pie',
            radius: ['50%', '75%'],
            center: ['40%', '50%'],
            data: data
          }
        ],
        color: ['#1467BF', '#4EBECD', '#FFD2A1']
      };

      chart.hideLoading();
      chart.setOption(option);
    });
  };

  var studyTaskCountStatistic = function () {
    this.element = $('#study-task-count-statistic');
    var chart = echarts.init(this.element.get(0));

    chart.showLoading();
    return $.get(this.element.data('url'), function (response) {
      var option = {
        color: ['#428BCA'],
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: ''
          }
        },
        toolbox: {
          feature: {
            saveAsImage: {}
          }
        },
        grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true
        },
        xAxis: [
          {
            type: 'category',
            data: response.xAxis.date,
            axisTick: {
              alignWithLabel: true
            }
          }
        ],
        yAxis: [
          {
            type: 'value'
          }
        ],

        series: [
          {
            name: Translator.trans('admin.index.finished_task_count'),
            type: 'bar',
            barWidth: '16',
            data: response.series.finishedTaskCount
          }
        ]
      };

      chart.hideLoading();
      chart.setOption(option);
    });
  };

  //课程排行榜
  var courseExplore = function () {
    var $element = $('#course-explore-list');
    $.get($element.data('url'), function (html) {
      $element.html(html);
    })
  }

  var DataSwitchEvent = function (selecter, callback) {
    $(selecter).on('click', function () {
      var $this = $(this);
      if (!$this.hasClass('btn-primary')) {
        $this.removeClass('btn-default').addClass('btn-primary')
          .siblings().removeClass('btn-primary').addClass('btn-default');

        $this.parent().siblings('.js-data-switch-time').text($this.data('time'));

        $this.parents('.panel').find('.js-statistic-areas').data('url', $this.data('url'));

        callback();
      }
    })
  }

  var remindTeachersEvent = function () {
    $('.js-course-question-list').on('click', '.js-remind-teachers', function () {
      $.post($(this).data('url'), function (response) {
        Notify.success(Translator.trans('admin.index.notify_teacher_success'));
      });
    });
  };

  var popover = function () {
    $('.js-today-data-popover').popover({
      html: true,
      trigger: 'hover',
      placement: 'bottom',
      template: '<div class="popover tata-popover tata-popover-lg" role="tooltip"><div class="popover-content"></div></div>',
      content: function() {
        var html = $(this).siblings('.popover-content').html();
        return html;
      }
    });

    $('.js-data-popover').popover({
      html: true,
      trigger: 'hover',
      placement: 'bottom',
      template: '<div class="popover tata-popover" role="tooltip"><div class="popover-content"></div></div>',
      content: function() {
        var html = $(this).siblings('.popover-content').html();
        return html;
      }
    });
  }


});
