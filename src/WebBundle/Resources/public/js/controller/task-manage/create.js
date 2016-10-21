
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
            setStep2:function(validator) {
                return validator;
            },
            setStep3: function(validator) {
                return validator;
            }
        },

        events: {
            'click #course-tasks-next': 'onNext',
            'click #course-tasks-prev': 'onPrev',
            'click .js-course-tasks-item': 'changeType',
        },


        changeType:function(e) {
            var $this = $(e.currentTarget).addClass('active');
            $this.siblings().removeClass('active');
            $('#course-tasks-next').removeAttr('disabled');
            $('[name="mediaType"]').val($this.data('type'));
        },

        onNext: function (event) {
            var step = this.get('step');
            if (step >= 3) {
                return;
            }
            this.set('step', step + 1);
            this._switchPage();
        },

        onPrev: function (event) {
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
            if(step==2) {
                $("#task-editor").load($(".js-course-tasks-item a").data('content-url'),function(){
                    $("#task-type").hide();
                    _self._setStep2();
                   
                });
            }else if(step==3) {
                $(".tab-pane.js-course-tasks-pane.active").removeClass('active').next().addClass('active');
                _self._setStep3();
            }
        },
        _setStep2:function() {
            var validator = new Validator({
                element: '#step2-from',
                autoSubmit: false,
                onFormValidated: function(error) {
                    if (error) {
                        return false;
                    }
                }
            });
            $("#task-editor").data('validator1',validator);
            console.log( $("#task-editor").data('validator1'));
        },
        _setStep3:function() {
            var validator = new Validator({
                    element: '#step2-from',
                    autoSubmit: false,
                    onFormValidated: function(error) {
                        if (error) {
                            return false;
                        }
                    }
                });
            $("task-editor").data('validator1',validator);
        },
    });
    new TaskEditor();
    module.exports = TaskEditor;
});