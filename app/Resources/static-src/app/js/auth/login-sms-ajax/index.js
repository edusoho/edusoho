import Drag from "app/common/drag";
import Api from "common/api";
import notify from "common/notify";
import { countDown } from "app/common/new-count-down.js";
import Coordinate from "app/common/coordinate";

let $form = $("#login-sms-form");
let drag = $("#drag-btn").length
  ? new Drag($("#drag-btn"), $(".js-jigsaw"), {
      limitType: "user_login"
    })
  : null;

let smsToken = "";
let validator = $form.validate({
  rules: {
    mobile: {
      required: true,
      phone: true
    },
    sms_code: {
      required: true,
      unsigned_integer: true,
      rangelength: [6, 6]
    },
    dragCaptchaToken: {
      required: true
    }
  },
  messages: {
    dragCaptchaToken: {
      required: Translator.trans("auth.register.drag_captcha_tips")
    },
    sms_code: {
      required: Translator.trans("auth.password_reset.sms_code_required_hint"),
      rangelength: Translator.trans(
        "auth.password_reset.sms_code_validate_hint"
      )
    }
  }
});

$(".js-btn-login").click(event => {
  if (!validator.form()) return;

  const inputCheckbox = $('input[name="agree_policy"]').prop("checked");
  const user_terms = $('input[name="user_terms_enable"]').val();
  const agree_policy = $('input[name="agree_policy_enable"]').val();

  if (!inputCheckbox && inputCheckbox != undefined) {
    if (user_terms === "opened" && agree_policy === "opened") {
      notify(
        "warning",
        Translator.trans("user.login.read.agree.policy.and.user.terms")
      );
    }

    if (user_terms === "opened" && agree_policy != "opened") {
      notify("warning", Translator.trans("user.login.read.user.terms"));
    }

    if (agree_policy === "opened" && user_terms != "opened") {
      notify("warning", Translator.trans("user.login.read.agree.policy"));
    }

    return;
  }

  $(event.currentTarget).button("loadding");
  $form.submit();
});

let smsEvent = () => {
  let $smsCode = $(".js-sms-send");
  $smsCode.click(() => {
    let coordinate = new Coordinate();
    const encryptedPoint = coordinate.getCoordinate(
      event,
      $("meta[name=csrf-token]").attr("content")
    );
    if (
      validator.element($('[name="dragCaptchaToken"]')) &&
      validator.element($('[name="mobile"]'))
    ) {
      if ($smsCode.hasClass("disabled")) {
        return;
      }
      $smsCode.addClass("disabled");

      Api.fastLoginSms
        .send({
          data: {
            type: "sms_login",
            mobile: $("#mobile").val(),
            allowNotExistMobile: 0,
            encryptedPoint: encryptedPoint,
            dragCaptchaToken: $('[name="dragCaptchaToken"]').val()
          }
        })
        .then(res => {
          notify(
            "success",
            Translator.trans("notify.sms_send_success.message")
          );
          $smsCode.removeClass("disabled");
          countDown($(".js-sms-send"), $("#js-fetch-btn-text"), 120);
          $('[name="sms_token"]').val(res.smsToken);
        })
        .catch(() => {
          $smsCode.removeClass("disabled");
          drag.initDragCaptcha();
        });
    }
  });
};
smsEvent();

const $loginModal = $("#login-modal");
$("#pwd-login").click(event => {
  $.get($loginModal.data("url"), function(html) {
    $loginModal.html(html);
  });
});
