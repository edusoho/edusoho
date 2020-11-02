/*
respository: https://github.com/bellorchid/pasteimage
*/
'use strict';

(function () {
  CKEDITOR.plugins.add('pasteimage', {
    lang: 'en,zh,zh-cn',
    requires: 'uploadwidget',

    onLoad: function () {
      CKEDITOR.addCss(
        '.cke_upload_uploading img{' +
        'opacity: 0.3' +
        '}'
      );
    },

    init: function (editor) {
      CKEDITOR.scriptLoader.load(CKEDITOR.getUrl('plugins/pasteimage/filesize.js'));
      // Do not execute this paste listener if it will not be possible to upload file.
      if (!CKEDITOR.plugins.clipboard.isFileApiSupported) {
        return;
      }

      var fileTools = CKEDITOR.fileTools,
        uploadUrl = fileTools.getUploadUrl(editor.config, 'image') || '';

      if (!uploadUrl) {
        return;
      }

      // Handle images which are available in the dataTransfer.
      fileTools.addUploadWidget(editor, 'pasteimage', {
        supportedTypes: /image\/(jpeg|png|gif|bmp)/,

        uploadUrl: uploadUrl,

        fileToElement: function () {
          var img = new CKEDITOR.dom.element('img');
          img.setAttribute('src', loadingImage);
          return img;
        },

        parts: {
          img: 'img'
        },

        onUploading: function (upload) {
          // Show the image during the upload.
          this.parts.img.setAttribute('src', upload.data);
        },

        onUploaded: function (upload) {
          // Width and height could be returned by server (http://dev.ckeditor.com/ticket/13519).
          var $img = this.parts.img.$;

          // Set width and height to prevent blinking.
          this.replaceWith( '<img src="' + upload.url + '" ' + '">' );
        }
      });

      // Handle images which are not available in the dataTransfer.
      // This means that we need to read them from the <img src="data:..."> elements.
      editor.on('paste', function (evt) {
        // For performance reason do not parse data if it does not contain img tag and data attribute.
        if (!evt.data.dataValue.match(/<img[\s\S]+data:/i)) {
          return;
        }

        var data = evt.data,
          // Prevent XSS attacks.
          tempDoc = document.implementation.createHTMLDocument(''),
          temp = new CKEDITOR.dom.element(tempDoc.body),
          imgs, img, i;

        // Without this isReadOnly will not works properly.
        temp.data('cke-editable', 1);

        temp.appendHtml(data.dataValue);

        imgs = temp.find('img');

        for (i = 0; i < imgs.count(); i++) {
          img = imgs.getItem(i);

          // Image have to contain src=data:...
          var isDataInSrc = img.getAttribute('src') && img.getAttribute('src').substring(0, 5) == 'data:',
            isRealObject = img.data('cke-realelement') === null;

          // We are not uploading images in non-editable blocs and fake objects (http://dev.ckeditor.com/ticket/13003).
          if (isDataInSrc && isRealObject && !img.data('cke-upload-id') && !img.isReadOnly(1)) {
            var loader = editor.uploadRepository.create(img.getAttribute('src'));
            loader.upload(uploadUrl);

            fileTools.markElement(img, 'pasteimage', loader.id);

            fileTools.bindNotifications(editor, loader);
          }
        }

        data.dataValue = temp.getHtml();
      });
      editor.on('fileUploadRequest', function (evt) {
        var lang = editor.lang.pasteimage;
        var fileLoader = evt.data.fileLoader,
          fileSize = editor.config.fileSingleSizeLimit || 10;
        var fileByteSize = fileSize * 1024 * 1024;

        if (fileLoader.file.size > fileByteSize) {
          alert(lang.single_file_max_size_tip + filesize(fileByteSize));
          evt.cancel();
        }

        evt.data.requestData.uploadMode = 'paste';
      });
    }
  });

  // jscs:disable maximumLineLength
  // Black rectangle which is shown before image is loaded.
  // var loadingImage = 'data:image/gif;base64,R0lGODlhDgAOAIAAAAAAAP///yH5BAAAAAAALAAAAAAOAA4AAAIMhI+py+0Po5y02qsKADs=';
  // jscs:enable maximumLineLength
  var loadingImage = '';

  /**
   * The URL where images should be uploaded.
   *
   * @since 4.5
   * @cfg {String} [imagePasteUploadUrl='' (empty string = disabled)]
   * @member CKEDITOR.config
   */
})();