$('#approval-form').validate({
  rules: {
    idcard: 'required idcardNumber',
    truename: {
      required: true,
      chinese: true,
      maxlength: 25,
      minlength: 2
    },
    faceImg: 'required isImage limitSize',
    backImg: 'required isImage limitSize'
  },
})