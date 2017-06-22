import SelectLinkage from 'app/js/question-manage/widget/select-linkage.js';

class Exercise {
  constructor($form) {
    this.$element = $form;
    this.validator2 = null;
    this._setValidateRule();
    this._init();
    this._initEvent();
    
  }

  _init() {
    this._inItStep2form();
    this.fix();
  }

  _initEvent() {
  }

  _inItStep2form() {
    let $step2_form = $("#step2-form");

    this.validator2 = $step2_form.validate({
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
          course_title: true,
        },
        itemCount: {
          required: true,
          positiveInteger: true,
          min: 1,
          max: 9999
        },
        range: {
          required: true,
        },
        difficulty: {
          required: true
        },
        'questionTypes[]': {
          required: true,
          remote: {
            url: $('[name="checkQuestion"]').data('checkUrl'),
            type: "post",
            dataType: "json",
            async: false,
            data: {
              itemCount: function () {
                return $('[name="itemCount"]').val();
              },
              range: function () {
                let range = {}
                let courseId = $('[name="range[courseId]"]').val();
                range.courseId = courseId;
                if ($('[name="range[lessonId]"]').length > 0) {
                  let lessonId = $('[name="range[lessonId]"]').val();
                  range.lessonId = lessonId;
                }

                return JSON.stringify(range);
              },
              difficulty: function () {
                return $('[name="difficulty"]').val();
              },
              types: function () {
                let types = [];
                $('[name="questionTypes\[\]"]:checked').each(function () {
                  types.push($(this).val());
                });
                return types;
              }
            }
          }
        }
      },
      messages: {
        required: "请填写标题",
        range: "题目来源",
        itemCount: {
          required: '请填写题目个数',
          positiveInteger: '请输入正整数',
          min: '题目个数无效',
          max: '题目个数过大'
        },
        difficulty: "请选择难易程度",
        'questionTypes[]': {
          required: "请选择题型",
          remote: "题目数量不足"
        },
      }
    });

    $step2_form.data('validator', this.validator2);
  }

  _inItStep3form() {
    var $step3_form = $("#step3-form");
    var validator = $step3_form.validate({
      onkeyup: false,
      rules: {
        finishCondition: {
          required: true,
        },
      },
      messages: {
        finishCondition: "请输完成条件",
      }
    });
    $step3_form.data('validator', validator);
  }

  _setValidateRule() {
    $.validator.addMethod("positiveInteger", function (value, element) {
      return this.optional(element) || /^[1-9]\d*$/.test(value);
    }, $.validator.format("必须为正整数"));

  }

  fix() {
    $('.js-question-type').click(() => {
      this.validator2.form();
    })
  }
}

new Exercise($('#step2-form'));
new SelectLinkage($('[name="range[courseId]"]'), $('[name="range[lessonId]"]'));

checkQuestionNum();

$('[name="range[courseId]"]').change(function () {
  checkQuestionNum();
})

$('[name="range[lessonId]"]').change(function () {
  checkQuestionNum();
})

$('[name="difficulty"]').change(function () {
  checkQuestionNum();
})

function checkQuestionNum() {
  let url = $('[name="range[courseId]"]').data('checkNumUrl');
  let courseId = $('[name="range[courseId]"]').val();
  let lessonId = $('[name="range[lessonId]"]').val();
  let difficulty = $('[name="difficulty"]').val();

  $.post(url, { courseId: courseId, lessonId: lessonId, difficulty: difficulty }, function (data) {
    $('[role="questionNum"]').text(0);

    $.each(data, function (i, n) {
      $("[type='" + i + "']").text(n.questionNum);
    });
  })
}
