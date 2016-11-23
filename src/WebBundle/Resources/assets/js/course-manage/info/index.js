CKEDITOR.replace('course-about-field', {
  allowedContent: true,
  toolbar: 'Detail',
  filebrowserImageUploadUrl: $('#course-about-field').data('imageUploadUrl')
});


$('[data-role="tab"]').click(function(){
  let $this = $(this);
  let $tabContent =$($this.data('tab-content')).removeClass("hidden").siblings('[data-role="tab-content"]').addClass('hidden');
});