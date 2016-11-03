class Live {
	constructor(props) {
        this.init();
    }
    init(){
        $('#startTime').datetimepicker({
            format: 'yyyy-mm-dd hh:ii',
            language:"zh",
        });
    	this._initStep2Form();
    }

    _initStep2Form() {
        var $step2_form = $("#step2-form");
        var validator = $step2_form.data('validator',validator);
        validator = $step2_form.validate({
            onkeyup: false,
            rules: {
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
}

new Live();