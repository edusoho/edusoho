class Text{
    constructor(props) {
        this.Init();
    }

    Init() {
        this._inItStep2form();
        $('#condition-select').on('change',event=>this._change(event));
        // var editor = CKEDITOR.replace('text-content-field', {
        //     toolbar: 'Full',
        //     filebrowserImageUploadUrl: $('#text-content-field').data('imageUploadUrl'),
        //     filebrowserFlashUploadUrl: $('#text-content-field').data('flashUploadUrl'),
        //     allowedContent: true,
        //     height: 300
        // });
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
        var  $step2_form = $("#step2-form");
        var validator = $step2_form.validate({
            onkeyup: false,
            rules: {
                title: {
                  required: true,
                },
                content: 'required',
            },
            messages: {
                title: "请输入标题",
                content:"请输入内容"
            }
        });
        $step2_form.data('validator',validator);
    }

    _inItStep3form() {
        var $step3_form = $("#step3-form");
        var validator = $step3_form.validate({
            onkeyup: false,
            rules: {
                'condition_detail': {
                    required: true,
                },
            },
            messages: {
                condition_detail: "请输完成条件",
            }
        });
        $step3_form.data('validator',validator);
    }
}

new Text();


















 







