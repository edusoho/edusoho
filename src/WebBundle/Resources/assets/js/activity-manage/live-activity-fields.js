class Live {
	constructor(props) {
        this._init();
    }
    _init(){
        this._dateTimePicker();
    	this._initStep2Form();
    }
    _initStep2Form() {
        var $step2_form = $("#step2-form");
        var validator = $step2_form.data('validator',validator);
        validator = $step2_form.validate({
            onkeyup: false,
            rules: {
                title: {
                    required: true,
                },
                startTime: {
                	required: true,
                	date: true
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
        });
    }

    _dateTimePicker() {
        let $starttime = $('#startTime');
        $starttime.datetimepicker({
            format: 'yyyy-mm-dd hh:ii',
            language:"zh",
            autoclose: true
        });
        $starttime.datetimepicker('setStartDate',new Date());
    }
}

new Live();