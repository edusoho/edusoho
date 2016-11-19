class ThreadShowWidget {
    constructor(props) {
        this.element = props.element;
        this.init();
    }

    init() {
        $(this.element).on('reload',this.onReload)
    }

    onReload() {
        let validator = $('[data-role=post-form]').Validator({
            onkeyup: false,
            rules: {
                'question[title]': {
                    required: true,
                }
            }
        });

        if(validator.form()) {
            var $form = this.element;
            $.post($form.attr('action'), $form.serialize(), function(html) {
                $('[data-role=post-list]').append(html);
                var number = parseInt($('[data-role=post-number]').text());
                $('[data-role=post-number]').text(number+1+'');
                $form.find('textarea').val('');
            }).error(function(response){
                var response = $.parseJSON(response.responseText);
                Notify.danger(response.error.message);
            });

            return false;
        }
    }
}


export default ThreadShowWidget;