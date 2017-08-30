import { imageScale } from './utils';

(function($) {
  $(document).on('change.cd.local.upload', '[data-toggle="local-upload"]', function() {
    let fr = new FileReader();
    let $this = $(this);
    let target = $this.data('target');
    let $target = $(target);

    let showType = $this.data('show-type') || 'background-image';

    fr.onload = function(e) {
      let src = e.target.result;


      if (showType === 'background-image') {
        $target.css('background-image', `url(${src})`);
        let html = '<div class="mask"></div>';

        $target.addClass('done').append(html);

      } else if (showType === 'image') {
        let image = new Image();
        image.onload = function() {
          let width = image.width;
          let height = image.height;
          
          let cropWidth = $this.data('crop-width');
          let cropHeight = $this.data('crop-height');

          let scale = imageScale(width, height, cropWidth, cropHeight);

          $(image).attr({
            'id': 'upload-source-img',
            'class': 'hidden',
            'data-natural-width': width,
            'data-natural-height': height,
            'width': scale.width,
            'height': scale.height
          });

          $this.after(image);

          let $target = $($this.data('target'));
          $target && $target.click();
        };

        image.src = src; 
      }
    }

    fr.readAsDataURL(this.files[0]);
  });

})(jQuery);