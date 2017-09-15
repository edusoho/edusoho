(function($) {
  $(document).on('click.cd.pic.review', '[data-toggle="pic-review"]', function() {
    let picUrl = $(this).data('url');
    window.open(picUrl);
  });

  $(document).on('click.cd.form.editable.data-api', '[data-toggle="form-editable"]', function() {
    let $this = $(this);
    let parent = $this.closest('[data-target="form-static-text"]');
    let $formGroup = $this.closest('.cd-form-group');
    parent.hide();
    $formGroup.find('[data-target="form-editable"]').show().find('input').focus().select();
  });
  
  $(document).on('click.cd.form.editable.cancel.data-api', '[data-dismiss="form-editable-cancel"]', function() {
    let $this = $(this);
    let parent = $this.closest('[data-target="form-editable"]');
    let $formGroup = $this.closest('.cd-form-group');
    parent.hide();
    let saveValue = $formGroup.find('input').data('save-value');
    $formGroup.find('input').val(saveValue);
    $formGroup.find('[data-target="form-static-text"]').show();
  });

})(jQuery);
