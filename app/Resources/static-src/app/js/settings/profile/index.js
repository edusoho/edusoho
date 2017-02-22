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
    // title: 
  }
})