define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);

  exports.run = function(options) {

    $('.reward').hover(function () {
       $(this).parent("tr").find("span").addClass('hidden');
       if ($(this).parent("tr").find('.taskRewardPoint').is(':hidden')) {
           $(this).parent("tr").find("a").removeClass('hidden');
       }
    },function () {
        $(this).parent("tr").find("a").addClass('hidden');
        if ($(this).parent("tr").find('.taskRewardPoint').is(':hidden')) {
            $(this).parent("tr").find("span").removeClass('hidden');
        }
    });

    $('.js-task-reward-point').hover(function () {
      $(this).find("span").addClass('hidden');
      if ($(this).find('.taskRewardPoint').is(':hidden')) {
        $(this).find("a").removeClass('hidden');
      }

      let $span = $(this).find("span");
      let $btn = $(this).find("a");
        $btn.click(function () {
          $(this).addClass('hidden');
          $(this).next().removeClass('hidden');

          $(this).next().change(function () {
            let taskRewardPoint = $(this).val();
            let id = $(this).data('id');
            let url = $(this).data('url');

            $.post(url, {id: id, taskRewardPoint: taskRewardPoint}, function(result){
              if (result.success) {
                $span.siblings().addClass('hidden');
                $span.removeClass('hidden');
                $span.text(taskRewardPoint);
              } else {
                Notify.warning(Translator.trans(result.message));
              }
            }).error(function(){
              Notify.danger(Translator.trans('编辑失败'));
            });

            });
          })
      }, function () {
        $(this).find("a").addClass('hidden');
        if ($(this).find('.taskRewardPoint').is(':hidden')) {
          $(this).find("span").removeClass('hidden');
        }
      });

    $('.js-reward-point').hover(function () {
      $(this).find("span").addClass('hidden');
      if ($(this).find('.rewardPoint').is(':hidden')) {
        $(this).find("a").removeClass('hidden');
      }

      let $span = $(this).find("span");
      let $btn = $(this).find("a");
      $btn.click(function () {
        $(this).addClass('hidden');
        $(this).next().removeClass('hidden');

        $(this).next().change(function () {
          let rewardPoint = $(this).val();
          let id = $(this).data('id');
          let url = $(this).data('url');

          $.post(url, {id: id, rewardPoint: rewardPoint}, function(result){
            if (result.success) {
              $span.siblings().addClass('hidden');
              $span.removeClass('hidden');
              $span.text(rewardPoint);
            } else {
              Notify.warning(Translator.trans(result.message));
            }
          }).error(function () {
            Notify.danger(Translator.trans('编辑失败！'));
          });
          });
        })
      }, function () {
      $(this).find("a").addClass('hidden');
      if ($(this).find('.rewardPoint').is(':hidden')) {
        $(this).find("span").removeClass('hidden');
      }
    });
  };
});


