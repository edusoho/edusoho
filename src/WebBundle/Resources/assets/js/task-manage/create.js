import loadAnimation from 'common/load-animation'

class Editor {
    constructor($modal) {
        this.$element = $modal;
        this.$task_manage_content = $('#task-manage-content');
        this.$task_manage_type = $('#task-manage-type');
        this.$frame = null;
        this.$iframe_body = null;
        this.iframe_jQuery = null;
        this.iframe_name = 'task-manage-content-iframe';
        this.mode = this.$task_manage_type.data('editorMode');
        this.type = this.$task_manage_type.data('editorType');
        this.step = 1;
        this.loaded = false;
        this.contentUrl = '';
        this._init();
        this._initEvent();
    }

    _initEvent() {
        $(this.$element).on('click', '#course-tasks-next', event=>this._onNext(event));
        $(this.$element).on('click', '#course-tasks-prev', event=>this._onPrev(event));
        if (this.mode != 'edit') {
            $(this.$element).on('click', '.js-course-tasks-item', event=>this._onSetType(event));
        }
        $(this.$element).on('click', '#course-tasks-submit', event=>this._onSave(event));
    }

    _init() {
        this._inItStep1form();
        if (this.mode == 'edit') {
            this.contentUrl = this.$task_manage_type.data('editorStep2Url');
            this.step = 2;
            this._switchPage();
        }
    }

    _onNext(e) {
        if (this.step === 3 || !this._validator(this.step)) {
            return;
        }
        this.step += 1;
        this._switchPage();
    }

    _onPrev() {
        if (this.step === 1 || !this._validator(this.step)) {
            return;
        }
        this.step -= 1;
        this._switchPage();
    }

    _onSetType(event) {
        const $this = $(event.currentTarget).addClass('active');
        $this.siblings().removeClass('active');
        let type = $this.data('type');
        $('[name="mediaType"]').val(type);
        this.contentUrl = $this.data('contentUrl');
        ( this.type !== type ) ? this.loaded = false : this.loaded = true;
        this.type = type;
        this._renderNext(true);
    }

    _onSave() {
        if (!this._validator(this.step)) {
            return;
        }
        let length = this._getLength();
        let postData = $('.js-hidden-data')
            .map((index, node) => {
                let name = $(node).attr('name');
                let value = $(node).val();
                return {name: name, value: value}
            })
            .filter((index, obj) => {
                return obj.value !== '' || obj.value !== null;
            })
            .get()
            .concat($('#step1-form').serializeArray())
            .concat(this.$iframe_body.find('#step2-form').serializeArray())
            .concat(this.$iframe_body.find("#step3-form").serializeArray())
            .concat([
                {name: 'mediaType', value: this.type},
                {name: 'length', value: length}
            ]);

            console.log(this.$iframe_body.find('#step2-form').serializeArray());
        $.post(this.$task_manage_type.data('saveUrl'), postData)
            .done((response) => {
                this.$element.modal('hide');
                // location.reload();
            })
            .fail((response) => {
                this.$element.modal('hide');
            });
    }

    _switchPage() {
        this._renderStep(this.step);
        this._renderContent(this.step);
        this._rendStepIframe(this.step);
        this._rendButton(this.step);
        if (this.step == 2 && !this.loaded) {
            this.loaded = true;
            this._initIframe();
        }
    }

    _getLength() {
        let postData = this.$iframe_body.find('#step2-form').serializeArray()
        let minute = 0;
        let second = 0;
        postData.forEach(function (element) {
            if (element.name == 'minute') {
                minute = parseInt(element.value);
            }
            if (element.name == 'second') {
                second = parseInt(element.value);
            }
        });
        return minute * 60 + second;
    }

    _initIframe() {
        let html = '<iframe class="'+this.iframe_name+'" id="'+this.iframe_name+'" name="'+this.iframe_name+'" scrolling="no" src="'+this.contentUrl+'"</iframe>';
        this.$task_manage_content.html(html).show(); 
        this.$frame = $('#'+this.iframe_name);
        let loadiframe = (a) => {
            let validator = {};
            this.iframe_jQuery = this.$frame[0].contentWindow.$;
            this.$iframe_body = this.$frame.contents().find('body').addClass('task-iframe-body');
            this.$frame.height(this.$iframe_body.height());
            this._rendButton(2);
            this.$iframe_body.find("#step2-form").data('validator', validator)
            this.$iframe_body.find("#step3-form").data('validator', validator); 
        };
        this.$frame.load(loadAnimation(loadiframe,this.$task_manage_content));
    }

    _inItStep1form() {
        let $step1_form = $("#step1-form");
        let validator = $step1_form.validate({
            onkeyup: false,
            rules: {
                title: {
                    required: true,
                },
                content: 'required',
            },
            messages: {
                title: "请输入标题",
                content: "请输入内容"
            }
        });
        $step1_form.data('validator', validator);
    }

    _validator(step) {
        let validator = null;
        if (step === 1) {
            validator = $("#step1-form").data('validator');
        } else if (this.$iframe_body) {
            var $from = this.$iframe_body.find("#step" + step + "-form");
            validator = this.iframe_jQuery.data($from[0], 'validator');
        }
        if (validator && !validator.form()) {
            this.$frame ? this.$frame.height(this.$iframe_body.height()) : "";
            return false;
        }
        return true;
    }

    _rendButton(step) {
        if (step === 1) {
            this._renderPrev(false);
            this._rendSubmit(false);
            this._renderNext(true);
        } else if (step === 2) {
            if (this.mode != 'edit') {
                this._rendSubmit(true);
                this._renderPrev(true);
                this._renderNext(true);
                return;
            }
            this._renderPrev(false);
            if (!this.loaded) {
                this._rendSubmit(false);
                this._renderNext(false);
                return;
            }
            this._rendSubmit(true);
            this._renderNext(true);
        } else if (step === 3) {
            this._renderNext(false);
            this._renderPrev(true);
        }
    }

    _rendStepIframe(step) {
        if (!this.$iframe_body) {
            return;
        }
        (step === 2) ? this.$iframe_body.find(".js-step2-view").addClass('active') : this.$iframe_body.find(".js-step2-view").removeClass('active');
        (step === 3) ? this.$iframe_body.find(".js-step3-view").addClass('active') : this.$iframe_body.find(".js-step3-view").removeClass('active');
    }

    _renderStep(step) {
        $('#task-manage-step').find('li:eq(' + (step - 1) + ')').addClass('doing').prev().addClass('done').removeClass('doing');
        $('#task-manage-step').find('li:eq(' + (step - 1) + ')').next().removeClass('doing').removeClass('done');
    }

    _renderContent(step) {
        (step === 1 ) ? this.$task_manage_type.show() : this.$task_manage_type.hide();
        (step !== 1 ) ? this.$task_manage_content.show() : this.$task_manage_content.hide();
    }

    _renderNext(show) {
        show ? $("#course-tasks-next").removeClass('hidden').removeAttr("disabled") : $("#course-tasks-next").addClass('hidden');
    }

    _renderPrev(show) {
        show ? $("#course-tasks-prev").removeClass('hidden') : $("#course-tasks-prev").addClass('hidden');
    }

    _rendSubmit(show) {
        show ? $("#course-tasks-submit").removeClass('hidden') : $("#course-tasks-submit").addClass('hidden');
    }

}

new Editor($('#modal'));