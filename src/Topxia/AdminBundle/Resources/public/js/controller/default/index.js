define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('echarts');

    exports.run = function() {

        searchCount();

        //ajax 获取数据
        getData();

        //事件
        getDataSwitchEvent();

        popularCoursesEvent();
        remindTeachersEvent();

        //图表
        studyCountStatistic();
        payOrderStatistic();
        studyLessonCountStatistic()

    };

    var searchCount = function() {
        var totalWidth = $(".js-search-count").parent().width();
        var $countDom = $(".js-search-count");
        var totalCount = 0;

        $countDom.each(function(){
            totalCount += $(this).data('count');
        })

        $countDom.each(function(){
            var width = ($(this).data('count')/totalCount * totalWidth * 3 + 2).toFixed(2);
            $(this).width(width);
        })
    }

    var getData = function() {
        systemStatusData()
        .then(operationData)
        .then(popularCoursesData)
        .then(usersStatistic)
    }

    var getDataSwitchEvent = function() {
        DataSwitchEvent(usersStatistic);
    }


    var operationData = function() {
        var $this = $('#operation-analysis-table');
        return $.post($this.data('url'),function(html){
            $this.html(html);
        });
    }

    var systemStatusData = function() {
        var $this = $('#system-status');
        return $.post($this.data('url'),function(html){
            $this.html(html);

            $('.mobile-customization-upgrade-btn').click(function() {
                var $btn = $(this).button('loading');
                var postData = $(this).data('data');
                $.ajax({ 
                    url: $(this).data('url'),
                    data: postData,
                    type: 'post'
                }).done(function(data) {
                    $('.upgrade-status').html('<span class="label label-warning">'+Translator.trans('升级受理中')+'</span>');
                }).fail(function(xhr, textStatus) {
                    Notify.danger(xhr.responseJSON.error.message);
                }).always(function(xhr, textStatus) {
                    $btn.button('reset');
                });
            })

        });
    }

    var popularCoursesData = function() {
        var $this = $("#popular-courses-type");
        return $.get($this.data('url'), {dateType: $this.val()}, function(html) {
            $('#popular-courses-table').html(html);
        });
    }

    var usersStatistic =  function() {
        this.element = $("#user-statistic");
        var chart = echarts.init(this.element.get(0));

        var option = {
            tooltip: {
                trigger: 'axis'
            },
            legend: {
                data:['新增注册','活跃用户','流失用户']
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
                data: ['16/06/12','16/06/13','16/06/14','16/06/15','16/06/16','16/06/17','16/06/18']
            },
            yAxis: {
                type: 'value',
            },
            series: [
                {
                    name:'新增注册',
                    type:'line',
                    stack: '总量',
                    data:[120, 132, 101, 134, 90, 230, 210]
                },
                {
                    name:'活跃用户',
                    type:'line',
                    stack: '总量',
                    data:[220, 182, 191, 234, 290, 330, 310]
                },
                {
                    name:'流失用户',
                    type:'line',
                    stack: '总量',
                    data:[150, 232, 201, 154, 190, 330, 410]
                }
            ]
        };

        chart.showLoading();

        return $.get(this.element.data('url'),function(data){
            // console.log('data',data);
            
            chart.hideLoading();
            chart.setOption(option);
        })
    }

    var studyCountStatistic = function() {
        this.element = $("#study-count-statistic");
        var chart = echarts.init(this.element.get(0));
        var option = {
            tooltip: {
                trigger: 'axis'
            },
            legend: {
                data:['新增人次','付费人次','学习人次']
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
                data: ['16/06/12','16/06/13','16/06/14','16/06/15','16/06/16','16/06/17','16/06/18']
            },
            yAxis: {
                type: 'value',
            },
            series: [
                {
                    name:'新增人次',
                    type:'line',
                    stack: '总量',
                    data:[120, 132, 101, 134, 90, 230, 210]
                },
                {
                    name:'付费人次',
                    type:'line',
                    stack: '总量',
                    data:[220, 182, 191, 234, 290, 330, 310]
                },
                {
                    name:'学习人次',
                    type:'line',
                    stack: '总量',
                    data:[150, 232, 201, 154, 190, 330, 410]
                }
            ]
        };
        chart.setOption(option);
    }

    var payOrderStatistic = function() {
        this.element = $("#pay-order-statistic");
        var chart = echarts.init(this.element.get(0));

        var option = {
            tooltip : {
                trigger: 'item',
                formatter: "{a} <br/>{b} : {c} ({d}%)"
            },
            legend: {
                orient: 'vertical',
                right: 'right',
                top: 'center',
                data:['课程订单','班级订单','会员订单']
            },
            toolbox: {
                feature: {
                    saveAsImage: {}
                }
            },
            series: [
                {
                    name:'订单量',
                    type:'pie',
                    radus: '50%',
                    center:['40%','60%'],
                    data:[
                        {value: 100,name: '课程订单'},
                        {value: 200,name: '班级订单'},
                        {value: 300,name: '会员订单'}
                    ]
                }
            ]
        };
        chart.setOption(option);

    }

    var studyLessonCountStatistic = function() {
        this.element = $("#study-lesson-count-statistic");
        var chart = echarts.init(this.element.get(0));
        var option = {
            color: ['#428bca'],
            tooltip : {
                trigger: 'axis',
                axisPointer : {            
                    type : 'shadow'
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
            xAxis : [
                {
                    type : 'category',
                    data : ['16/06/12', '16/06/13', '16/06/14', '16/06/15', '16/06/16', '16/06/17', '16/06/18'],
                    axisTick: {
                        alignWithLabel: true
                    }
                }
            ],
            yAxis : [
                {
                    type : 'value'
                }
            ],

            series : [
                {
                    name:'学习课时数',
                    type:'bar',
                    // barWidth: '20',
                    data:[10, 52, 200, 334, 390, 330, 220]
                }
            ]
        };
        chart.setOption(option);
    }

    var DataSwitchEvent = function(callback) {
        $('.js-data-switch-button').on('click',function(){
            var $this = $(this);
            if(! $this.hasClass('btn-primary')) {
                $this.removeClass('btn-default').addClass('btn-primary')
                    .siblings().removeClass('btn-primary').addClass('btn-default');

                $this.parent().siblings('.js-data-switch-time').text($this.data('time'));

                $this.parents('.panel').find('.js-statistic-areas').data('url',$this.data('url'));

                callback();
            }
        })
    }


    var popularCoursesEvent = function () {
        $("#popular-courses-type").on('change', function() {
            popularCoursesData();
        });
    }

    var remindTeachersEvent = function() {
        $('.tbody').on('click', 'js-remind-teachers', function() {
            $.post($(this).data('url'), function(response) {
                Notify.success(Translator.trans('提醒教师的通知，发送成功！'));
            });
        });
    }

    function noticeModal() {
        var noticeUrl = $('#admin-notice').val();
        return $.post(noticeUrl, function(data){
            if (data['result']) {
                $('.modal').html(data['html']);
                $('.modal').modal({
                    backdrop:'static',
                    show:true
                });
            }
        })
    }

});