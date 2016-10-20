define(function (require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('echarts');

    exports.run = function () {
        //热门搜索
        cloudHotSearch();

        //ajax 获取数据
        loadAjaxData();

        //事件
        registerSwitchEvent();

        //提醒教师
        remindTeachersEvent();

        //图表
        studyCountStatistic();
        payOrderStatistic();
        studyLessonCountStatistic()

    };


    var loadAjaxData = function () {
        systemStatusData()
            .then(siteOverviewData)
            .then(usersStatistic)
            .then(courseExplore);
    }

    var registerSwitchEvent = function () {

        DataSwitchEvent('.js-user-switch-button', usersStatistic);

        DataSwitchEvent('.js-study-switch-button', studyCountStatistic);

        DataSwitchEvent('.js-order-switch-button', payOrderStatistic);

        DataSwitchEvent('.js-lesson-switch-button', studyLessonCountStatistic);

        DataSwitchEvent('.js-course-switch-button', courseExplore);

    }

    //热门搜索
    var cloudHotSearch = function () {
        var totalWidth = $(".js-cloud-search").parent().width();
        var $countDom = $(".js-cloud-search");
        var totalCount = 0;

        $countDom.each(function () {
            totalCount += $(this).data('count');
        })

        $countDom.each(function () {
            var width = ($(this).data('count') / totalCount * totalWidth * 3 + 2).toFixed(2);
            $(this).width(width);
        })
    }

    //系统状态
    var systemStatusData = function () {
        var $this = $('#system-status');
        return $.post($this.data('url'), function (html) {
            $this.html(html);

            $('.mobile-customization-upgrade-btn').click(function () {
                var $btn = $(this).button('loading');
                var postData = $(this).data('data');
                $.ajax({
                    url: $(this).data('url'),
                    data: postData,
                    type: 'post'
                }).done(function (data) {
                    $('.upgrade-status').html('<span class="label label-warning">' + Translator.trans('升级受理中') + '</span>');
                }).fail(function (xhr, textStatus) {
                    Notify.danger(xhr.responseJSON.error.message);
                }).always(function (xhr, textStatus) {
                    $btn.button('reset');
                });
            })

        });
    }

    //网站概览
    var siteOverviewData = function () {
        var $this = $('#site-overview-table');
        return $.post($this.data('url'), function (html) {
            $this.html(html);
        });
    }

    /*初始化静态数据*/
    var usersStatistic = function () {
        this.element = $("#user-statistic");
        var chart = echarts.init(this.element.get(0));

        chart.showLoading();

        return $.get(this.element.data('url'), function (response) {
            var option = {
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ['新增注册', '活跃用户', '流失用户']
                },
                grid: {
                    left: '3%',
                    right: '4%',
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
                    data: response.date
                },
                yAxis: {
                    type: 'value',
                },
                series: [
                    {
                        name: '新增注册',
                        type: 'line',
                        data: response.register
                    },
                    {
                        name: '活跃用户',
                        type: 'line',
                        data: response.active
                    },
                    {
                        name: '流失用户',
                        type: 'line',
                        data: response.unActive
                    }
                ],
                color: ['#46C37B', '#428BCA', '#DD4646']
            };

            chart.hideLoading();
            chart.setOption(option);
        })
    }

    var studyCountStatistic = function () {
        this.element = $("#study-count-statistic");
        var chart = echarts.init(this.element.get(0));
        chart.showLoading();
        return $.get(this.element.data('url'), function (datas) {
            var option = {
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ['新增订单', '付费订单']
                },
                grid: {
                    left: '3%',
                    right: '4%',
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
                    data: datas.date
                },
                yAxis: {
                    type: 'value',
                },
                series: [
                    {
                        name: '新增订单',
                        type: 'line',
                        data: datas.new
                    },
                    {
                        name: '付费订单',
                        type: 'line',
                        data: datas.feePaid
                    }
                ],
                color: ['#46C37B', '#428BCA']
            };
            chart.hideLoading();
            chart.setOption(option);
        })
    }

    var payOrderStatistic = function () {
        this.element = $("#pay-order-statistic");
        var chart = echarts.init(this.element.get(0));


        chart.showLoading();
        return $.get(this.element.data('url'), function (data) {

            var option = {
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    right: 'right',
                    top: 'center',
                    data: ['课程订单', '班级订单', '会员订单']
                },
                toolbox: {
                    feature: {
                        saveAsImage: {}
                    }
                },
                series: [
                    {
                        name: '订单量',
                        type: 'pie',
                        radius: ['50%', '75%'],
                        center: ['40%', '60%'],
                        data: data
                    }
                ],
                color: ['#1467BF', '#4EBECD', '#FFD2A1']
            };

            chart.hideLoading();
            chart.setOption(option);
        })

    }

    var studyLessonCountStatistic = function () {
        this.element = $("#study-lesson-count-statistic");
        var chart = echarts.init(this.element.get(0));

        chart.showLoading();
        return $.get(this.element.data('url'), function (response) {
            var option = {
                color: ['#428bca'],
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
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
                        data: response.date,
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
                        name: '学习课时数',
                        type: 'bar',
                        barWidth: '19',
                        data: response.data
                    }
                ],
                color: ['#428BCA']
            };

            chart.hideLoading();
            chart.setOption(option);
        })
    }

    //课程排行榜
    var courseExplore = function () {
        var $element = $("#course-explore-list");
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
                Notify.success(Translator.trans('提醒教师的通知，发送成功！'));
            });
        });
    }

    function noticeModal() {
        var noticeUrl = $('#admin-notice').val();
        return $.post(noticeUrl, function (data) {
            if (data['result']) {
                $('.modal').html(data['html']);
                $('.modal').modal({
                    backdrop: 'static',
                    show: true
                });
            }
        })
    }

});