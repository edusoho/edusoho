define(function (require, exports, module) {
    var Widget = require('widget');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    var TaskEditor = Widget.extend({
        attrs: {
            element: '#modal',
            type: '',
            step: 1,
            validator: null,
            loaded: false
        },

        events: {
            'click #course-tasks-next': '_onNext',
            'click #course-tasks-prev': '_onPrev',
            'click .js-course-tasks-item': '_onSetType',
            'click #course-tasks-submit': '_onSave'
        },

        _onSetType: function (e) {
            var $this = $(e.currentTarget).addClass('active');
            $this.siblings().removeClass('active');
            $('#course-tasks-next').removeAttr('disabled');
            var type = $this.data('type');
            $('[name="mediaType"]').val(type);

            if (this.get('type') !== type) {
                this.set('loaded', false);
                this.set('type', type);
            }
        },

        _onNext: function (event) {
            var step = this.get('step');
            if (step >= 3) {
                return;
            }
            this.set('step', step + 1);
            this._switchPage();
        },

        _onPrev: function (event) {
            var step = this.get('step');
            if (step <= 1) {
                return;
            }
            this.set('step', step - 1);
            this._switchPage();
        },

        _switchPage: function () {
            var _self = this;
            var step = this.get('step');
            if (step == 1) {
                $("#task-type").show();
                $(".js-step2-view").removeClass('active');
                $(".js-step3-view").removeClass('active');
            } else if (step == 2) {
                !this.get('loaded') && $('.tab-content').load($(".js-course-tasks-item a").data('content-url'), function () {
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
        },
        _initStep2: function () {
            var validator = new Validator({
                element: '#step2-form',
                autoSubmit: false,
                onFormValidated: function (error) {
                    if (error) {
                        return false;
                    }
                }
            });

            this.set('step2-validator', validator);
            this.set('validator', this.get('step2-validator'));
            this.set('loaded', true);
        },
        _initStep3: function () {

            if (this.get('step3-validator') !== undefined) {
                this.set('validator', this.get('step3-validator'));
            }

            var validator = new Validator({
                element: '#step3-form',
                autoSubmit: false,
                onFormValidated: function (error) {
                    if (error) {
                        return false;
                    }
                }
            });

            this.set('step3-validator', validator);
            this.set('validator', this.get('step3-validator'));
        },

        _onSave: function (event) {
            var self = this;

            var hideData = $(".js-hidden-data")
                .map(function () {
                    var key = $(this).attr('name');
                    var value = $(this).val();
                    return {name: key, value: value};
                }).filter(function (index, obj) {
                    return obj.value !== '';
                }).get();

            var data = hideData
                .concat($('#step2-form').serializeArray())
                .concat($("#step3-form").serializeArray());

            $.post($('.js-editor-save-url').data('url'), data)
                .done(function () {
                    self.element.modal('hide');
                })
                .fail(function () {

                })
        }
    });

    exports.run = function () {
        var editor = new TaskEditor();
        $('#task-editor').data('editor', editor);
    };
});