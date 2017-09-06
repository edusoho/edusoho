import { imageScale } from './utils';
import notify from 'common/notify';

(function($) {
  let normalUpload = function($input, src) {
      let $target = $($input.data('target'));
      $target.css('background-image', `url(${src})`);
      if ($target.find('.mask').length == 0) {
        let html = '<div class="mask"></div>';
        $target.addClass('done').append(html);
      }
  }
  let cropUpload = function($input, src) {
    let $modal = $('#modal');
    $('.js-upload-image, .upload-source-img').removeClass('active');
    $input.addClass('active');
      let image = new Image();
      image.onload = function() {
        let width = image.width;
        let height = image.height;
        let cropWidth = $input.data('crop-width');
        let cropHeight = $input.data('crop-height');

        let scale = imageScale(width, height, cropWidth, cropHeight);
        $(image).attr({
          'class': 'upload-source-img active hidden',
          'data-natural-width': width,
          'data-natural-height': height,
          'width': scale.width,
          'height': scale.height,
        });
        $input.after(image);
      };
      image.src = src;
      $modal.load($input.data('saveUrl')).modal('show');
  }

  $(document).on('change.cd.local.upload', '[data-toggle="local-upload"]', function() {
    let fr = new FileReader();
    let $this = $(this);
    let showType = $this.data('show-type') || 'background-image';

    fr.onload = function(e) {
      let src = e.target.result;
      if (showType === 'background-image') {
        normalUpload($this, src);
      } else if (showType === 'image') {
        cropUpload($this, src);
      }
    }

    fr.readAsDataURL(this.files[0]);
  });

  $(document).on('upload-image', '.js-upload-image.active' , function(e, cropOptions) {
    let $this = $(this);
    let $modal = $("#modal");
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
          resolve(data);
        });
      });
    };
    
    let saveAvatar = function(ret){
      return new Promise(function(resolve, reject) {
          $.post($this.data('saveUrl'),{images: ret}, function(data){
              if (data.image) {
                $($this.data('targeImg')).attr('src', data.image);
                notify('success', Translator.trans('site.upload_success_hint'));
                $modal.modal('hide');
              }
          }).error(function() { notify('danger', Translator.trans('site.upload_fail_retry_hint')); })
      });
    }

    uploadImage().then(function(ret) {
      return cropImage(ret);
    }).then(function(ret) {
      return saveAvatar(ret);
    });

  });

})(jQuery);