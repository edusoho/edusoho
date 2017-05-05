let validator = $('#login-form').validate({
  rules: {
    _username:{
      required: true,
    },
    _password: {
      required: true,
    }
  }
})