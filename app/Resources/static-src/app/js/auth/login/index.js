import Face from "./face";
import Drag from "app/common/drag";
require("app/common/xxtea.js");

let $form = $("#login-form");
let drag = $("#drag-btn").length
  ? new Drag($("#drag-btn"), $(".js-jigsaw"), {
      limitType: "user_login"
    })
  : null;
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
    dragCaptchaToken: {
      required: Translator.trans("auth.register.drag_captcha_tips")
    },
    _username: {
      required: Translator.trans("auth.register.name_required_error_hint")
    },
    _password: {
      required: Translator.trans("auth.register.password_required_error_hint")
    }
  }
});
$("#login-form").keypress(function(e) {
  if (e.which == 13) {
    $(".js-btn-login").trigger("click");
    e.preventDefault(); // Stops enter from creating a new line
  }
});

$(".js-btn-login").click(event => {
  const inputCheckbox = $('input[name="agree_policy"]').prop("checked");
  $(event.currentTarget).button("loadding");
  const username = $form.find("#login_username").val();
  const password = $form.find("#login_password").val();

  const encryptedUsername = window.XXTEA.encryptToBase64(username, "EduSoho");
  const encryptedPassword = window.XXTEA.encryptToBase64(password, "EduSoho");

  const formData = $form.serializeArray();

  const fieldsToUpdate = {
    _username: encryptedUsername,
    _password: encryptedPassword
  };

  formData.forEach(function(field) {
    if (fieldsToUpdate.hasOwnProperty(field.name)) {
      field.value = fieldsToUpdate[field.name];
    }
  });

  if (!validator.form()) return;

  if (inputCheckbox || inputCheckbox == undefined) {
    $.post(
      $form.attr("action"),
      $.param(formData),
      function(response) {
        window.location.replace("/");
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

    return;
  }

  // $("#modal").modal({backdrop:'static'}); // 点击遮罩关闭弹框
  const $modal = $("#modal");
  $modal.load("/login/agreement");
  $modal.modal("show");

  $modal.on("click", ".js-agree-register", () => {
    $('input[name="agree_policy"]').prop("checked", true);
    $modal.modal("hide");

    $.post(
      $form.attr("action"),
      $.param(formData),
      function(response) {
        window.location.replace("/");
      },
      "json"
    ).error(function(jqxhr, textStatus, errorThrown) {
      const json = jQuery.parseJSON(jqxhr.responseText);
      $form
        .find(".alert-danger")
        .html(Translator.trans(json.message))
        .show();
      drag.initDragCaptcha();
    });
  });

  $modal.on("click", ".js-close-modal", () => {
    $modal.modal("hide");
  });
});

$(".receive-modal").click();

if ($(".js-sts-login-link").length) {
  new Face({
    element: $(".js-login-main")
  });
}

$('.open-eye').on('click', function () {
  $('#login_password').attr('type', 'password');
  $('.open-eye').hide();
  $('.close-eye').show();
})

$('.close-eye').on('click', function () {
  $('#login_password').attr('type', 'text');
  $('.close-eye').hide();
  $('.open-eye').show();
})
