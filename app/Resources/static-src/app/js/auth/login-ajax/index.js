import Face from "../login/face";
import Drag from "app/common/drag";
import notify from "common/notify";

require("app/common/xxtea.js");

let $form = $("#login-ajax-form");
let drag = $("#drag-btn").length
  ? new Drag($("#drag-btn"), $(".js-jigsaw"), {
      limitType: "user_login"
    })
  : null;
let $btn = $(".js-submit-login-ajax");
let validator = $form.validate({
  rules: {
    _username: {
      required: true
    },
    _password: {
      required: true
    },
    dragCaptchaToken: {
      required: true
    }
  },
  messages: {
    _username: {
      required: Translator.trans("auth.login.username_required_error_hint")
    },
    _password: {
      required: Translator.trans("auth.login.password_required_error_hint")
    },
    dragCaptchaToken: {
      required: Translator.trans("auth.register.drag_captcha_tips")
    }
  }
});

$btn.click(event => {
  var username = $form.find("#ajax-username").val();
  var password = $form.find("#ajax-password").val();
  const encryptedUsername = window.XXTEA.encryptToBase64(username, "EduSoho");
  const encryptedPassword = window.XXTEA.encryptToBase64(password, "EduSoho");
  var formData = $form.serializeArray();
  var fieldsToUpdate = {
    _username: encryptedUsername,
    _password: encryptedPassword
  };
  formData.forEach(function(field) {
    if (fieldsToUpdate.hasOwnProperty(field.name)) {
      field.value = fieldsToUpdate[field.name];
    }
  });
  if (validator.form()) {
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
    $.post(
      $form.attr("action"),
      $.param(formData),
      function(response) {
        $btn.button("loading");
        window.location.reload();
      },
      "json"
    ).error(function(jqxhr, textStatus, errorThrown) {
      var json = jQuery.parseJSON(jqxhr.responseText);
      $form
        .find(".alert-danger")
        .html(Translator.trans(json.message))
        .show();
      drag.initDragCaptcha();
    });
  }
});

if ($(".js-sts-login-link").length) {
  new Face({
    element: $(".js-login-modal"),
    target: ".js-login-form, .modal-footer"
  });
}

const $loginModal = $("#login-modal");
$("#sms-login").click(event => {
  $.get($(event.currentTarget).data("url"), function(html) {
    $loginModal.html(html);
  });
});
