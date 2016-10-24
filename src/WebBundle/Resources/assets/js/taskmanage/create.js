class ProductSelect {
    constructor(prop) {
        this.elem = $(prop.elem);
        this.type = '';
        this.step = 1;
        this.validator = null;
        this.loaded = false;
        this.init();
    }
    init(){
        $(this.elem).on('click','#course-tasks-next',this._onNext.bind(this));
        $(this.elem).on('click','#course-tasks-prev',this._onPrev.bind(this));
        $(this.elem).on('click','.js-course-tasks-item',this._onSetType.bind(this));
        $(this.elem).on('click','#course-tasks-submit',this._onSave.bind(this));
    }
    _onNext(e) {
        if (this.step >= 3) {
            return;
        }
        this.step++;
        console.log(this.step);
        this._switchPage();   
    }
    _onPrev() {
        if (this.step <= 1) {
            return;
        }
        this.step--;
        this._switchPage();

    }
    _onSetType(e) {
        var $this = $(e.currentTarget).addClass('active');
        $this.siblings().removeClass('active');
        $('#course-tasks-next').removeAttr('disabled');
        var type = $this.data('type');
        $('[name="mediaType"]').val(type);

        if (this.type !== type) {
            this.loaded = false;
            this.type = type;
        }
    }
    _onSave() {
        console.log("_onSave");
    }

    _switchPage() {
        var _self = this;
        var step = this.step;
        if (step == 1) {
            $("#task-type").show();
            $(".js-step2-view").removeClass('active');
            $(".js-step3-view").removeClass('active');
        } else if (step == 2) {
            !this.loaded && $('.tab-content').load($(".js-course-tasks-item a").data('content-url'), function () {
                _self._initStep2();
            });
            $("#task-type").hide();
            $(".js-step2-view").addClass('active');
            $(".js-step3-view").removeClass('active');

        } else if (step == 3) {
            $(".js-step3-view").addClass('active');
            $(".js-step2-view").removeClass('active');
            _self._initStep3();
        }
    }
    _initStep2() {
        console.log("_initStep2");
        // var validator = new Validator({
        //     element: '#step2-form',
        //     autoSubmit: false,
        //     onFormValidated: function (error) {
        //         if (error) {
        //             return false;
        //         }
        //     }
        // });

        // this.set('step2-validator', validator);
        // this.set('validator', this.get('step2-validator'));
        // this.set('loaded', true);
    }
    _initStep3 () {
        console.log("_initStep3");
        // if (this.get('step3-validator') !== undefined) {
        //     this.set('validator', this.get('step3-validator'));
        // }
        // var validator = new Validator({
        //     element: '#step3-form',
        //     autoSubmit: false,
        //     onFormValidated: function (error) {
        //         if (error) {
        //             return false;
        //         }
        //     }
        // });
        // this.set('step3-validator', validator);
        // this.set('validator', this.get('step3-validator'));
    }
}

new ProductSelect({
    elem: '#modal'
});