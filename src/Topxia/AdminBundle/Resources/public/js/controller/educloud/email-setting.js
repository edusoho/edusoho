define(function(require, exports, module) {
	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);
	var Notify = require('common/bootstrap-notify');
    require('echarts-debug');

	exports.run = function() {
		// for (var i = 4; i >= 1; i--) {
  //           var id = '#article-property-tips'+i;
  //           var htmlId = id + '-html';
  //           $(id).popover({
  //               html: true,
  //               trigger: 'hover',//'hover','click'
  //               placement: 'left',//'bottom',
  //               content: $(htmlId).html()
  //           });
  //       };
  //       $("input[name='email-status']").data('status')=="used" ?$("[name='warning']").hide():"";
  //       $("[name='sign-update']").on('click',function(){
  //       	$("[name='submit-sign']").show();
  //       	$("[name='status']").hide();
  //           var validator = new Validator({
  //               element: '#email-form'
  //           });
  //           validator.addItem({
  //               element: '[name="sign"]',
  //               required: true,
  //               rule:'chinese_alphanumeric minlength{min:3} maxlength{max:8}',
  //               display: Translator.trans('签名'),
  //               errormessageRequired: Translator.trans('签名3-8字，建议使用汉字')
  //           });
  //       });
        var emailSendChart = echarts.init(document.getElementById('emailSendChart'));
        var items = app.arguments.items;

         var option = {
            title: {
                text: ''
            },
            tooltip: {},
            legend: {
                data:['时间']
            },
            xAxis: {
                data: items.date
            },
            yAxis: {},
            series: [{
                name: '发送量(条)',
                type: 'bar',
                data: items.count
            }],
            color:['#428BCA'],
            grid:{
                show:true,
                borderColor:'#fff',
                backgroundColor:'#fff'
            }
        };
        emailSendChart.setOption(option);
	}
	
});