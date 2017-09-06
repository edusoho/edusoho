import { imageScale } from './utils';

(function($) {
  $(document).on('change.cd.local.upload', '[data-toggle="local-upload"]', function() {
    let fr = new FileReader();
    let $this = $(this);
    console.log(this, 'this', this.files[0]);
    let target = $this.data('target');
    let $target = $(target);

    let showType = $this.data('show-type') || 'background-image';

    fr.onload = function(e) {
      let src = e.target.result;

      $('.js-upload-image, .upload-source-img').removeClass('active');
      $this.addClass('active');

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
            'class': 'upload-source-img active hidden',
            'data-natural-width': width,
            'data-natural-height': height,
            'width': scale.width,
            'height': scale.height
          });
          $this.after(image);
        };
        
        image.src = src;
      }
      let $modal = $("#modal");
      $modal.load($this.data('uploadUrl')).modal('show');
    }

    fr.readAsDataURL(this.files[0]);
  });

  $(document).on('upload-image', '.js-upload-image.active' , function(e, cropOptions) {
    let $this = $(this);
    let fromData = new FormData();
    fromData.append('token', $this.data('token'));
    fromData.append('_csrf_token', $('meta[name=csrf-token]').attr('content'));
    fromData.append('file', this.files[0]);

    let uploadImage = function(ret){
      return new Promise(function(resolve, reject) {
        $.ajax({
          url: $this.data('fileUpload'),
          type: 'POST',
          cache: false,
          data: fromData,
          processData: false,
          contentType: false,
        }).done(function(data){
            resolve(data);
        });
      });
    }

    let cropImage = function(ret){
      return new Promise(function(resolve, reject) {
        $.post($this.data('crop'), cropOptions, function(data){
          console.log(data);
          resolve(data);
        });
      });
    };
    
    let saveAvatar = function(ret){
        return new Promise(function(resolve, reject) {
            $.post($this.data('uploadUrl'), function(data){
              console.log(data);
            });
        });
      }

    uploadImage().then(function(ret) {
      return cropImage(ret);
    }).then(function(ret) {
      return saveAvatar(ret);
    });

  });

})(jQuery);