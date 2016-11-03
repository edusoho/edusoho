class Discuss{
    constructor(props) {
        this.Init();
    }

    Init() {
        this._inItStep2form();
    }

    _inItStep2form() {
        var $step2_form = $("#step2-form");
        var validator = $step2_form.data('validator');
        validator = $step2_form.validate({
            onkeyup: false,
            rules: {
                content: 'required',
            },
            messages: {
                content:"请输入说明"
            }
        });
    }
}

new Discuss();


















 







