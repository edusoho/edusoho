define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    var validator = require('bootstrap.validator');

    exports.run = function() {

        $("#startDate").datetimepicker({
            autoclose: true
        }).on('changeDate', function() {
            $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));
        });

        $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));

        $("#endDate").datetimepicker({
            autoclose: true
        }).on('changeDate', function() {
            $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));
        });

        $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));

        $('.js-pay-way-popover').popover({
            html: true,
            trigger: 'hover',
            placement: 'bottom',
            template: '<div class="popover tata-popover tata-popover-lg" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
            content: function() {
                var html = $(this).siblings('.popover-content').html();
                return html;
            }
        });

        $.ajax({
            url: '/api/mall_info',
            headers: {
                Accept: 'application/vnd.edusoho.v2+json'
            }
        }).done(function(resp) {
            if (resp.isShow && resp.isInit) {
                $(".js-is-sass").removeClass('hidden')
                $('.js-mall-order').attr('href', $('.js-mall-order').data('url'))
            }
        });
      $('.order-js-export-btn').on('click', async function () {

        const $form = $('#order-search-form');

        // 获取表单内的所有参数并转为对象
        const rawParams = Object.fromEntries(new FormData($form[0]));

        // 添加额外的参数
        rawParams.start = rawParams.start || 0;

        // 过滤掉 undefined 的参数
        const params = Object.fromEntries(
          Object.entries(rawParams).filter(([_, v]) => v !== undefined)
        );
        // 发起请求
        try {
          const query = new URLSearchParams(params).toString();
          const verificationResponse = await fetch(`/secondary/verification?exportFileName=order&targetFormId=order&${query}`);
          const html = await verificationResponse.text();
          $('modal').empty();
          // 显示 modal
          $('#modal').html(html).modal('show');
        } catch (error) {
          console.error('请求出错:', error);
        }
      });

    };

});
