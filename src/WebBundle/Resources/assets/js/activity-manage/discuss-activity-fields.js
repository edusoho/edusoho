class Discuss{
    constructor(props) {
        this.Init();
    }

    Init() {
        this._inItStep2form();
    }

    _inItStep2form() {
        var  $step2_form = $("#step2-form");
        var validator = $step2_form.validate({
            onkeyup: false,
            rules: {
                content: 'required',
            },
            messages: {
                content:"请输入说明"
            }
        });
        $step2_form.data('validator',validator);
    }

    _inItStep3form() {
        var $step3_form = $("#step3-form");
        var validator = $step3_form.validate({
            onkeyup: false,
            rules: {},
            messages: {}
        });
        $step3_form.data('validator',validator);
    }
}

new Discuss();


















 







