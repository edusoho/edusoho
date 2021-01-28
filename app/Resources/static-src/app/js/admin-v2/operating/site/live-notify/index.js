let $form = $('#live-notify-setting-form');
let validator = $form.validate({
  rules: {
    preTime: {
      required: true,
      positive_integer: true,
      min: 1,
    },
  }
});