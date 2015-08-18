define(function(require, exports, module) {

    var Widget = require('widget');
    var Validator = require('bootstrap.validator');
    var saveModule = require('../../../../homework/js/controller/homework/save.js');
    var InitIndexActiveModule = require('../../../../homework/js/controller/homework/active.js');
    require('ckeditor');
    var Notify = require('common/bootstrap-notify');
    var changeAnswers = {};
    var changeTeacherSay = {};

    Validator.addRule(
        'maxScoreCheck',
        function (options) {
            var field=options.element;
            return field.data('maxScore')>=field.val();
        }, "评分不能超过题目分数"
    );

    exports.run = function() {
        var validator = new Validator({
            element: '#review-form',
            triggerType: 'change',
            onFormValidated: function(error){
                // if (error) {
                //     return false;
                // }
                // $('#course-create-btn').button('submiting').addClass('disabled');
            }
        });

        $('input.score').each(function(index,item){
            var maxScore=$(item).data('maxScore');
            if(maxScore && parseInt(maxScore)>0){
                validator.addItem({
                    element: '[name="'+$(item).attr('name')+'"]',
                    required: true,
                    rule: 'maxScoreCheck'
                });
            }
        });

        var v = new View({
            element: '#homework-set'
        }).render();

        $('#modal').on('show.bs.modal', function (e) {
            var items= validator.items;
            for(var i=0;i < items.length;i++){
                var result=items[i].execute();
            }

            // $('input.score').each(function(index,item){
            //     var field=$(item);
            //     if(!field.val()){
            //         var position=$('.question-7').offset();
            //         $(document).scrollTop(position.top-10);
            //         Notify.danger("作业未批改完");
            //         return ;
            //     }
            //     // var $item = $(item);
            //     // teacherSay.push($item.val());
            //     // questionIds.push($item.data('questionId'));
            // });
            e.preventDefault();
            return false;
        })
    };

    var View = Widget.extend({
        setup: function() {
        },

        events: {
            'click #submit-review-btn': 'onSubmit',
            'click .question-index': 'onClickSetCard'
        },

        onSubmit: function(event) {
            // if (!confirm('确认要提交作业批改吗？')) return false;
            // var $btn = $(event.currentTarget);
            // saveModule.save($btn,'list');
        },

       onClickSetCard: function(event) {
            var position = $('.question-'+$(event.currentTarget).data('anchor')).offset();
            $(document).scrollTop(position.top-10);
        }
    });
});