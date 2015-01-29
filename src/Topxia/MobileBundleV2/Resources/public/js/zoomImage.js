/*
*自动缩放图片
*/
      function getScreenWidth() {
        var width, winWidth, winHeight;
        if (window.innerWidth)
            winWidth = window.innerWidth;
        else if ((document.body) && (document.body.clientWidth))
            winWidth = document.body.clientWidth;
        // 获取窗口高度
        if (window.innerHeight)
            winHeight = window.innerHeight;
        else if ((document.body) && (document.body.clientHeight))
            winHeight = document.body.clientHeight;
            // 通过深入 Document 内部对 body 进行检测，获取窗口大小
        if (document.documentElement && document.documentElement.clientHeight  && document.documentElement.clientWidth){
            winHeight = document.documentElement.clientHeight;
            winWidth = document.documentElement.clientWidth;
        }

        switch (window.orientation) {
            case 0:
              width = parseInt(winWidth);
              break;
            case 90:
            case - 90 :
                width = parseInt(winHeight);
                break
            default:
                width = parseInt(winWidth);
        }
        width = width * 0.8;
        return width
      }
      function zoomImage(img, width) {
        var oldH = img.height;
        var oldW = img.width;
        img.width = width;
        img.height = width / oldW * oldH
      }
      function adaptationImage() {
        var width = getScreenWidth();
        var imgs = document.getElementsByTagName('img');
        for (var i = 0; i < imgs.length; i++) {
          zoomImage(imgs[i], width)
        }
      }

      function autoZoomImage()
      {
            var imageArray = new Array();
            var imgs = document.getElementsByTagName('img');
            for (var i = 0; i < imgs.length; i++) {
              var img = imgs[i];
              img.addEventListener('load',
                function() {
                  var width = getScreenWidth();
                  zoomImage(this, width)
                }
              );
              img.alt = i;
              imageArray.push(img.src);
              console.log(img.src);
              img.addEventListener('click',
              function() {
                //window.location = 'imageIndexNUrls://?' + this.alt + '.partation.' + imageArray.join('.partation.');
                //window.jsobj.showImages(this.alt,imageArray);
                navigator.cordovaUtil.showImages(this.alt, imageArray);
              })
            }
            window.addEventListener('orientationchange',
            function() {
              adaptationImage()
            },
            false);
      }