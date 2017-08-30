(function($) {
  $(document).on('click.cd.radio', '[data-toggle="radio"]', function() {
    let $this = $(this);
    $this.siblings().removeClass('checked');
    $this.addClass('checked');
  });

  $(document).on('click.cd.pic.review', '[data-toggle="pic-review"]', function() {
    let picUrl = $(this).data('url');
    window.open(picUrl);
  });

  $(document).on('click.cd.form.editable', '[data-toggle="form-editable"]', function() {
    let $this = $(this);
    let parent = $this.closest('[data-target="form-static-text"]');
    let $formGroup = $this.closest('.cd-form-group');
    parent.hide();
    $formGroup.find('[data-target="form-editable"]').show();
  });

  $(document).on('click.cd.form.editable.close', '[data-dismiss="form-editable-close"]', function() {
    let $this = $(this);
    let parent = $this.closest('[data-target="form-editable"]');
    let $formGroup = $this.closest('.cd-form-group');
    parent.hide();
    $formGroup.find('[data-target="form-static-text"]').show();
  });

})(jQuery);
