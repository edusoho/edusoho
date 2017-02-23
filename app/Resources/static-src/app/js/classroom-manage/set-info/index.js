import notify from 'common/notify';
import 'common/select2';
// import 'common/select2/3.4.1/select2.css';
// import 'app/js/classroom-manage/category-select';
import 'app/js/classroom-manage/classroom-create';

let editor_classroom_about = CKEDITOR.replace('about', {
    allowedContent: true,
    toolbar: 'Detail',
    filebrowserImageUploadUrl: $('#about').data('imageUploadUrl'),
    filebrowserFlashUploadUrl: $('#about').data('flashUploadUrl')
});

// $('[data-role="tree-select"], [name="categoryId"]').select2({
//     treeview: true,
//     dropdownAutoWidth: true,
//     treeviewInitState: 'collapsed',
//     placeholderOption: 'first'
//     // treeviewInitState: 'expanded'
//   });

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
