class Editor {
    constructor($modal) {
        var $editor = $modal.find('#task-manage-editor');
        this.mode = $editor.data('editorMode');
        this.elem = $modal;
        this.$task_manage_content = $('#task-manage-content');
        this.$iframe_content = null;
        this.type = $editor.data('editorType');
        this.step = 1;
        this.loaded = false;
        this._contentUrl = '';
        this._saveUrl = $editor.data('saveUrl');
        this._init();
        this._initEvent();
    }

    _initEvent() {
        $(this.elem).on('click', '#course-tasks-next', event=>this._onNext(event));
        $(this.elem).on('click', '#course-tasks-prev', event=>this._onPrev(event));
        $(this.elem).on('click', '.js-course-tasks-item', event=>this._onSetType(event));
        $(this.elem).on('click', '#course-tasks-submit', event=>this._onSave(event));
    }

    _init() {
        if (this.mode === 'edit') {
            this._contentUrl = $("#task-manage-editor").data('editorStep2Url');
            this.step = 2;
            this._switchPage();
        }
    }

    _onNext(e) {
        if (this.step >= 3) {
            return;
        }
        if(this.step != 1  && !this._validator(this.step)) {
            return;
        }
        this.step += 1;
        this._switchPage();
    }

    _onPrev() {
        if (this.step <= 1 || (this.mode === 'edit' && this.step <= 2)) {
            return;
        }
        if(this.step != 1 && !this._validator(this.step)) {
            return;
        }
        this.step -= 1;
        this._switchPage();
    }

    _onSetType(e) {
        var $this = $(e.currentTarget).addClass('active');
        $this.siblings().removeClass('active');
        $('#course-tasks-next').removeAttr('disabled');
        var type = $this.find('a').data('type');
        $('[name="mediaType"]').val(type);
        this._contentUrl = $this.find('a').data('contentUrl');
        if (this.type !== type) {
            this.loaded = false;
            this.type = type;
        }
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
            .concat(this.$iframe_content.find('#step2-form').serializeArray())
            .concat(this.$iframe_content.find("#step3-form").serializeArray())
            .concat([
                {name: 'mediaType', value: this.type}
            ]);
        $.post(this._saveUrl, postData)
            .done((response) => {
                this.elem.modal('hide');
            })
            .fail((response) => {
                this.elem.modal('hide');
            });
    }

    _switchPage() {
        var $type = $("#task-manage-type"),
            $next = $("#course-tasks-next"),
            $prev = $("#course-tasks-prev"),
            $iframe = $('#task-manage-content-iframe').contents(),
            $submit = $('#course-tasks-submit'),
            $step = $('#task-manage-step');
        if (this.step == 1) {
            $step.find('li:eq(0)').addClass('doing').siblings().removeClass('doing');
            $type.show();
            this.$task_manage_content.hide();
            $prev.attr('disabled','disabled');
            $submit.attr('disabled','disabled');
        } else if (this.step == 2) {
            $step.find('li:eq(1)').addClass('doing').siblings().removeClass('doing');
            $next.attr('disabled','disabled');
            $type.hide();
            this.$task_manage_content.show();
            $iframe.find(".js-step3-view").removeClass('active');
            $iframe.find(".js-step2-view").addClass('active');
            if(this.mode === 'edit') {
                $prev.attr('disabled','disabled');
            }
            if(!this.loaded) {
                this.loaded = true;
                this._initIframe();
                return;
            }
            $next.removeAttr('disabled');
            $prev.removeAttr('disabled');
            $submit.removeAttr('disabled');
        } else if (this.step == 3) {
            $step.find('li:eq(2)').addClass('doing').siblings().removeClass('doing');
            $iframe.find(".js-step3-view").addClass('active');
            $iframe.find(".js-step2-view").removeClass('active');
            $next.attr('disabled','disabled');
            $prev.removeAttr('disabled');
        }
    }

    _initIframe() {
        var html = '<iframe class="task-manage-content-iframe" id="task-manage-content-iframe" name="task-manage-content-iframe" src="'+this._contentUrl+'"</iframe>';
        this.$task_manage_content.html(html); 
        var iframewin = document.getElementById('task-manage-content-iframe').contentWindow || iframe;
        $(iframewin).load(()=>{
            $("#course-tasks-next").removeAttr('disabled');
            $("#course-tasks-prev").removeAttr('disabled');
            $('#course-tasks-submit').removeAttr('disabled');
            var $iframe = $('#task-manage-content-iframe');
            var windowjQuery = $iframe[0].contentWindow.$;
            this.$iframe_content = $iframe.contents().find('body').addClass('task-iframe-body').find('#iframe-content');
        });
    }

    _validator(index) {
        var $iframe = $('#task-manage-content-iframe');
        var windowjQuery = $iframe[0].contentWindow.$;
        var $from =  this.$iframe_content.find("#step"+index+"-form");
        var validator = windowjQuery.data($from[0], 'validator');
        if(validator && !validator.form()) {
            return false;
        }
        return true;
    }
}

new Editor($('#modal'));