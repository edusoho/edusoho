import { initEditor } from '../editor'

class Discuss{
    constructor(props) {
        this._init();
    }

    _init() {
        initEditor($('[name="content"]'));
        this._inItStep2form();
    }

    _inItStep2form() {
        var $step2_form = $("#step2-form");
        var validator = $step2_form.data('validator');
        validator = $step2_form.validate({
            rules: {
                title: {
                    required: true,
                    maxlength: 50,
                },
                media: 'required',
            },
        });
    }
}

new Discuss();


















 







