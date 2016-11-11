import {initEditor} from '../editor'

class Text{
    constructor(props) {
        this._init();
    }

    _init() {
        $('#condition-select').on('change',event=>this._change(event));
        this._inItStep2form();
        initEditor($('[name="content"]'));
    }

    _change(event) {
        var val = $(event.currentTarget).children('option:selected').val();
        if(val != 'auto') {
            $("#condition-group").addClass('hidden');
            return;
        }
        this._inItStep3form();
        $("#condition-group").removeClass('hidden');
    }

    _inItStep2form() {
        var $step2_form = $("#step2-form");
        var validator = $step2_form.data('validator');
        validator = $step2_form.validate({
            onkeyup: false,
            rules: {
                title:'required',
                content: 'required',
            },
            messages: {
                title:'请输入标题',
                content:"请输入内容"
            }
        });
    }

    _inItStep3form() {
        var $step3_form = $("#step3-form");
        var validator = $step3_form.data('validator');
        validator = $step3_form.validate({
            onkeyup: false,
            rules: {
                'condition_detail': {
                    required: true,
                    digits:true,
                    max:300,
                },

            },
        });
    }
}

new Text();


















 







