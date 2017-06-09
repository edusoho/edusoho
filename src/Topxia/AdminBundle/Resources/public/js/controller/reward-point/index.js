define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);

  exports.run = function(options) {
    $('.js-task-reward-point').hover(function () {
      $(this).find("span").addClass('hidden');
      $(this).find("a").removeClass('hidden');

      let $btn = $(this).find("a");
        $btn.click(function () {
          $(this).addClass('hidden');
          $(this).next().removeClass('hidden');

          $(this).next().change(function () {
            let taskRewardPoint = $(this).val();
            let courseId = $(this).data('id');
            let url = $(this).data('url');
              $.ajax({
                  url: url,
                  data: {
                      courseId: courseId,
                      taskRewardPoint: taskRewardPoint
                  },
                  success: function (response) {
                      history.go(0);
                  }
              })

            });
          })
      }, function () {
        $(this).find("a").addClass('hidden');
        $(this).find("span").removeClass('hidden');
      });

    $('.js-reward-point').hover(function () {
      $(this).find("span").addClass('hidden');
      $(this).find("a").removeClass('hidden');

      let $btn = $(this).find("a");
      $btn.click(function () {
        $(this).addClass('hidden');
        $(this).next().removeClass('hidden');

        $(this).next().change(function () {
          let rewardPoint = $(this).val();
          let courseId = $(this).data('id');
          let url = $(this).data('url');

            $.ajax({
              url: url,
              data: {
                courseId: courseId,
                rewardPoint: rewardPoint
              },
              success: function (response) {
                history.go(0);
              }
            })
          });
        })
      }, function () {
      $(this).find("a").addClass('hidden');
      $(this).find("span").removeClass('hidden');
    });
  };
});


