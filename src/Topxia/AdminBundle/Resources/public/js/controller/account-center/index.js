define(function (require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('echarts');

    exports.run = function () {
        $('.js-week-data-popover').popover({
            html: true,
            trigger: 'hover',
            placement: 'bottom',
            template: '<div class="popover tata-popover tata-popover-lg" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
            content: function() {

                var html = $(this).siblings('.popover-content').html();
                return html;
            }
        });

        $('.js-data-popover').popover({
            html: true,
            trigger: 'hover',
            placement: 'bottom',
            template: '<div class="popover tata-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
            content: function() {

                var html = $(this).siblings('.popover-content').html();
                return html;
            }
        });

        //ajax 获取数据
        loadAjaxData();

        //图表
        payExplore();
        productExplore();
        financeCountStatistic();

        //事件
        registerSwitchEvent();


    };

    var loadAjaxData = function () {
        siteOverviewData();
    }

    var registerSwitchEvent = function () {

        DataSwitchEvent('.js-finance-switch-button', financeCountStatistic);
        DataSwitchEvent('.js-pay-switch-button', payExplore);
        DataSwitchEvent('.js-product-switch-button', productExplore);

    }

    //网站概览
    var siteOverviewData = function () {
        var $this = $('#site-overview-table');
        return $.post($this.data('url'), function (html) {
            $this.html(html);
        });
    }

    //财务统计
    var financeCountStatistic = function () {
        this.element = $("#finance-count-statistic");
        var chart = echarts.init(this.element.get(0));
        chart.showLoading();

        return $.get(this.element.data('url'), function (datas) {
            var option = {
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: [Translator.trans('admin.account_center.cash_income'), Translator.trans('admin.account_center.coin_income')]
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
                        name: Translator.trans('admin.account_center.cash_income'),
                        type: 'line',
                        data: datas.series.cashAmounts
                    },
                    {
                        name: Translator.trans('admin.account_center.coin_income'),
                        type: 'line',
                        data: datas.series.coinAmounts
                    }
                ],
                color: ['#46C37B', '#428BCA']
            };
            chart.hideLoading();
            chart.setOption(option);
        })
    }

    //消费排行榜
    var payExplore = function () {
        var $element = $("#user-pay-list");
        $.get($element.data('url'), function (html) {
            $element.html(html);
        })
    }

    //产品
    var productExplore = function () {
        var $element = $("#product-consume-list");
        $.get($element.data('url'), function (html) {
            $element.html(html);
        })
    }

    //异步数据请求绑定
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

});