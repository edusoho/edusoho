class Live {
	constructor(props) {
        this.init();
    }
    init(){

        $("#startTime").daterangepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });

        // $('#datetimepicker').datetimepicker({
        //     format: 'yyyy-mm-dd hh:ii'
        // });
        // $("#startTime").datetimepicker({
		//     language: 'zh-CN',
		//     autoclose: true,
		//     format: 'yyyy-mm-dd',
		//     minView: 'month'
		// });
		// $("#startTime").datetimepicker('setStartDate', new Date());
    	this._initStep2Form();
    }

    _initStep2Form() {
        var  $step2_form = $("#step2-form");
        var validator = $step2_form.validate({
            onkeyup: false,
            rules: {
                startTime: {
                	required: true,
                	dateISO: true
                },
                length: {
                	required: true,
                	digits: true,
                	max: 300
                },
                remark: {
                	maxlength: 1000
                },
            },
            messages: {
                // startTime:"请选择开始时间",
                // length: '直播长度在1-300分钟之间'
            }
        });
        $step2_form.data('validator',validator);
    }

    _initStep3Form() {
        var $step3_form = $("#step3-form");
        var validator = $step3_form.validate({
            onkeyup: false,
            rules: {},
            messages: {}
        });
        $step3_form.data('validator',validator);
    }

}

new Live();