define(function(require, exports, module) {

    var noUiSlider = require('jquery.nouislider');
    require('jquery.nouislider-css');
    require('jquery.sortable');

    var Notify = require('common/bootstrap-notify');

    var CreateBase = {




        canBuild: function() {
            var $form = $("#testpaper-form"),
                isOk = true;

            $.ajax($form.data('buildCheckUrl'), {
                type: 'POST',
                async: false,
                data: $form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status != 'yes') {
                        var missingTexts = [];
                        var types = {
                            single_choice: '单选题',
                            choice: '多选题',
                            fill: '填空题',
                            determine: '判断题',
                            essay: '问答题',
                            material: '材料题'
                        }
                        $.each(response.missing, function(type, count) {
                            missingTexts.push(types[type] + '缺<strong>' + count + '</strong>道');
                        });
                        Notify.danger('课程题库题目数量不足，无法生成试卷：<br>' + missingTexts.join('，'), 5);
                        isOk = false;
                    }
                }
            });

            return isOk;
        },

        checkBuildCountAndScoreInputs: function() {
            var $form = $("#testpaper-form");
            var totalNumber = 0,
                isOk = true;

            $form.find('.item-number').each(function() {
                var number = $(this).val();
                if (!/^[0-9]*$/.test(number)) {
                    Notify.danger('题目数量只能填写数字');
                    $(this).focus();
                    isOk = false;
                    return false;
                }
                totalNumber += parseInt(number);
            });

            if (isOk) {
                if (totalNumber > 1000) {
                    isOk = false;
                    Notify.danger('试卷题目总数不能超过1000道。');
                    return isOk;
                }
            }

            $form.find('.item-score').each(function() {
                var score = $(this).val();
                if (!/^(([1-9]{1}\d*)|([0]{1}))(\.(\d){1})?$/.test(score)) {
                    Notify.danger('题目分值只能填写数字，且最多一位小数。');
                    $(this).focus();
                    isOk = false;
                    return false;
                }
            });

            return isOk;
        },

        initValidator: function(validator) {
            validator.addItem({
                element: '#test-name-field',
                required: true,
            });

            validator.addItem({
                element: '#test-description-field',
                required: true,
                rule: 'maxlength{max:500}',
            });

            validator.addItem({
                element: '#test-limitedTime-field',
                required: true,
                rule: 'integer'
            });
        }
    }

    module.exports = CreateBase;

});