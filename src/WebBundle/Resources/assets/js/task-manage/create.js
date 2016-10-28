class Editor {
    constructor($modal) {
        this.$element = $modal;
        this.$task_manage_content = $('#task-manage-content');
        this.$task_manage_type = $('#task-manage-type');
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
        $(this.$element).on('click', '.js-course-tasks-item', event=>this._onSetType(event));
        $(this.$element).on('click', '#course-tasks-submit', event=>this._onSave(event));
    }

    _init() {
        if (this.mode === 'edit') {
            this.contentUrl = this.$task_manage_type.data('editorStep2Url');
            this.step = 2;
            this._switchPage();
        }
    }

    _onNext(e) {
        if (this.step === 3 || (this.step != 1 && !this._validator(this.step))) {
            return;
        }
        this.step += 1;
        this._switchPage();
    }

    _onPrev() {
        if(this.step === 1 || !this._validator(this.step)) {
            return;
        }
        this.step -= 1;
        this._switchPage();
    }

    _onSetType(event) {
        var $this = $(event.currentTarget).addClass('active');
        $this.siblings().removeClass('active');
        var type = $this.data('type');
        $('[name="mediaType"]').val(type);
        this.contentUrl = $this.data('contentUrl');
        ( this.type !== type ) ? this.loaded == false : this.loaded == true; 
        this.type = type;
        this._renderNext(true);
    }

    _onSave() {
        if(!this._validator(this.step)) {
            return;
        }
        let self = this;
        var postData = $('.js-hidden-data')
            .map((index, node) => {
                var name = $(node).attr('name');
                var value = $(node).val();
                return {name: name, value: value}
            })
            .filter((index, obj) => {
                return obj.value !== '';
            })
            .get()
            .concat(this.$iframe_body.find('#step2-form').serializeArray())
            .concat(this.$iframe_body.find("#step3-form").serializeArray())
            .concat([
                {name: 'mediaType', value: this.type}
            ]);
        $.post(this.$task_manage_type.data('saveUrl'), postData)
            .done((response) => {
                this.$element.modal('hide');
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
        if (this.step == 2 && !this.loaded ) {
            this.loaded = true;
            this._initIframe();
        } 
    }

    _initIframe() {
        var html = '<iframe class="'+this.iframe_name+'" id="'+this.iframe_name+'" name="'+this.iframe_name+'" src="'+this.contentUrl+'"</iframe>';
        this.$task_manage_content.html(html); 
        var iframewindow = document.getElementById(this.iframe_name).contentWindow || iframe;
        $(iframewindow).load(()=>{
            var $iframe = $('#'+this.iframe_name);
            this.iframe_jQuery = $iframe[0].contentWindow.$;
            this.$iframe_body = $iframe.contents().find('body').addClass('task-iframe-body');
            this._rendButton(2);
        });
    }

    _validator(index) {
        if(!this.loaded) {
            return;
        }
        var $from =  this.$iframe_body.find("#step"+index+"-form");
        var validator = this.iframe_jQuery.data($from[0], 'validator');
        if(validator && !validator.form()) {
            return false;
        }
        return true;
    }

    _rendButton(step) {
        if(step===1) {
            this._renderPrev(false);
            this._rendSubmit(false);
            this._renderNext(true);
        }else if(step===2) {
            if(this.mode != 'edit') {
                this._rendSubmit(true);
                this._renderPrev(true);
                this._renderNext(true); 
                return;
            }
            this._renderPrev(false);
            if(!this.loaded) {
                this._rendSubmit(false);
                this._renderNext(false); 
                return;
            }
            this._rendSubmit(true);
            this._renderNext(true); 
        }else if(step===3) {
            this._renderNext(false);
            this._renderPrev(true);
        }
    }

    _rendStepIframe(step) {
        if(!this.loaded) {
            return;
        }
        (step === 2) ? this.$iframe_body.find(".js-step2-view").addClass('active') : this.$iframe_body.find(".js-step2-view").removeClass('active');
        (step === 3) ? this.$iframe_body.find(".js-step3-view").addClass('active') : this.$iframe_body.find(".js-step3-view").removeClass('active');
    }

    _renderStep(index) {
        $('#task-manage-step').find('li:eq('+ (index-1) +')').addClass('doing').siblings().removeClass('doing');
    }

    _renderContent(step) {
        (step === 1 ) ? this.$task_manage_type.show() : this.$task_manage_type.hide();
        (step !== 1 ) ? this.$task_manage_content.show(): this.$task_manage_content.hide();
    }

    _renderNext(show) {
        show ? $("#course-tasks-next").removeClass('hidden') : $("#course-tasks-next").addClass('hidden');
    }

    _renderPrev(show) {
        show ? $("#course-tasks-prev").removeClass('hidden') : $("#course-tasks-prev").addClass('hidden');
    }

    _rendSubmit(show) {
        show ? $("#course-tasks-submit").removeClass('hidden') : $("#course-tasks-submit").addClass('hidden');
    }

}

new Editor($('#modal'));