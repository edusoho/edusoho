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

  $(document).on('change.cd.file.review', '[data-toggle="file-review"]', function() {
    let fr = new FileReader();
    let $this = $(this);
    let target = $this.data('target');
    let $target = $(target);

    fr.onload = function(e) {
      let src = e.target.result;
      $target.css('background-image', `url(${src})`);
      
      let html = '<div class="mask"></div>';

      $target.addClass('done').append(html);
    }

    fr.readAsDataURL(this.files[0]);
  });

})(jQuery);
