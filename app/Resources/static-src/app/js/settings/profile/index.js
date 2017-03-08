let editor = CKEDITOR.replace('profile_about', {
  toolbar: 'Simple',
  filebrowserImageUploadUrl: $('#profile_about').data('imageUploadUrl')
});

$(".date").datetimepicker({
  autoclose: true,
  format: 'yyyy-mm-dd',
  minView: 'month'
});

$("#user-profile-form").validate({
  rules: {
    '[name="profile[truename]': {
      minlength: 4,
    },
    '[name="profile[title]"]': {
      maxlength: 24
    },
    '[name="profile[qq]"]': {
      qq: true
    }
  }
})