import notify from 'common/notify';
import 'common/select2';
import 'app/js/classroom/category-select';
import 'app/js/classroom/classroom-create';

let editor_classroom_about = CKEDITOR.replace('about', {
    allowedContent: true,
    toolbar: 'Detail',
    filebrowserImageUploadUrl: $('#about').data('imageUploadUrl'),
    filebrowserFlashUploadUrl: $('#about').data('flashUploadUrl')
});

let validator = $('#classroom-set-form').validate({
  onkeyup: false,
  rules: {
    title: {
      required: true,
    }
  },
});

$('#classroom-save').click(()=>{
  validator.form();
})
