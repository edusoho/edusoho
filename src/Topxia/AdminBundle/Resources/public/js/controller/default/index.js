define(function (require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('echarts');
    var Cookie = require('cookie');

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
        //云广告
        showCloudAd();
        //升级新后台
        upgradeAdminV2();
    };

    var loadAjaxData = function () {
        systemStatusData()
            .then(siteOverviewData)
            //.then(usersStatistic);
    }

    var registerSwitchEvent = function () {

        DataSwitchEvent('.js-user-switch-button', usersStatistic);

        DataSwitchEvent('.js-study-switch-button', studyCountStatistic);

        DataSwitchEvent('.js-order-switch-button', payOrderStatistic);

        DataSwitchEvent('.js-task-switch-button', studyTaskCountStatistic);

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
                    $('.upgrade-status').html('<span class="label label-warning">' + Translator.trans('admin.index.upgrade_acceptance_hint') + '</span>');
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
                    data: [Translator.trans('admin.index.register_count'), Translator.trans('admin.index.active_user_count'), Translator.trans('admin.index.lost_user_count')]
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
                    data: response.xAxis.date
                },
                yAxis: {
                    type: 'value',
                },
                series: [
                    {
                        name: Translator.trans('admin.index.register_count'),
                        type: 'line',
                        data: response.series.registerCount
                    },
                    {
                        name: Translator.trans('admin.index.active_user_count'),
                        type: 'line',
                        data: response.series.activeUserCount
                    },
                    {
                        name: Translator.trans('admin.index.lost_user_count'),
                        type: 'line',
                        data: response.series.lostUserCount
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
        })

    }

    var studyTaskCountStatistic = function () {
        this.element = $("#study-task-count-statistic");
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
                Notify.success(Translator.trans('admin.index.notify_teacher_success'));
            });
        });
    }

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

    var showCloudAd = function () {
        var $cloudAd = $('#cloud-ad');
        $.get($cloudAd.data('url'),function(res){
            if (!!res.error) {
                return;
            }

            var img = new Image();
            if (Cookie.get('cloud-ad') == res.image) {
                return;
            }
            img.src = res.image;
            if (img.complete) {
                showAdImage($cloudAd, img, res);
            } else {
                img.onload = function () {
                    showAdImage($cloudAd, img, res);
                    img.onload = null;
                };
            };
        });

        $cloudAd.on('hide.bs.modal',function(){
            Cookie.set('cloud-ad',$cloudAd.find('img').attr('src'),{expires:360*10});
        })
    }

    var showAdImage = function($cloudAd, img, res) {
        var $img = $(img);
        var $box = $cloudAd.find('.modal-dialog');
        var boxWidth = $box.width() ? $box.width() : $(window).width()-20;
        var WindowHeight = $(window).height();

        var width = img.width;
        var height = img.height;
        var marginTop = 0;
        if ((width/height) >= (4/3)) {
            height = width > boxWidth ? height/(width/boxWidth) : height*(boxWidth/width);
            marginTop = (WindowHeight-height)/2;
        } else {
            height = WindowHeight > 600 ? 600 : WindowHeight*0.9;
            $img.height(height);
            $img.width('auto');
            marginTop =  (WindowHeight-height)/2;
        }

        $cloudAd.find('a').attr('href',res.urlOfImage).append($img).css({'margin-top': marginTop});
        $cloudAd.modal('show');
    }

    var upgradeAdminV2 = function () {

        var $upgradeBtn = $('.js-upgrade-admin-v2');
        $upgradeBtn.on('click',function () {
            if (!confirm(Translator.trans('admin.switch_new_version.confirm_message'))) {
                return ;
            }
            $upgradeBtn.button('loading');
            $.post($upgradeBtn.data('url'),function(res){
                if(res.status == 'error'){
                    Notify.danger(Translator.trans(res.message));
                }else if(res.status == 'success' && res.url){
                    window.location.href = res.url;
                }
            });
        });

        $('.js-no-permission-btn').on('click',function () {
            Notify.danger(Translator.trans('admin.switch_old_version.permission_error'));
        })
    }
});