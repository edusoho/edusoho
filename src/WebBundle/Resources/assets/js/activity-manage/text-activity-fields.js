class Text{
    constructor(props) {
        this._init();
    }

    _init() {
        $('#condition-select').on('change',event=>this._change(event));
        this._inItStep2form();
        this._initEditorContent();
    }

    _initEditorContent() {
        var editor = CKEDITOR.replace('text-content-field', {
            toolbar: 'Full',
            filebrowserImageUploadUrl: $('#text-content-field').data('imageUploadUrl'),
            filebrowserFlashUploadUrl: $('#text-content-field').data('flashUploadUrl'),
            allowedContent: true,
            height: 300
        });
        editor.on('instanceReady', function (e) { 
            // console.log(editor.getData());
            var $parentiframe = $(window.parent.document).find('#task-manage-content-iframe');
            $parentiframe.height($parentiframe.contents().find('body').height());
        });

        editor.on( 'change', () => {    
            // this._getEditorContent(editor);
            $('[name="content"]').val(editor.getData());
        });
    }

    _getEditorContent(editor){
        editor.updateElement();
        var z = editor.getData();
        var x = editor.getData().match(/<embed[\s\S]*?\/>/g);
        if (x) {
            for (var i = x.length - 1; i >= 0; i--) {
               var y = x[i].replace(/\/>/g,"wmode='Opaque' \/>");
               var z =  z.replace(x[i],y);
            };
        }
        return z;
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
                content: 'required',
            },
            messages: {
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
                },

            },
        });
    }
}

new Text();


















 







