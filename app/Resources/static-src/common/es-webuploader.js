import notify from 'common/notify';
import WebUploader from './fex-webuploader/webuploader.js';
import SWF_PATH from './fex-webuploader/Uploader.swf';

class EsWebUploader {
  constructor(option) {
    let self = this;
    let config = Object.assign({}, {
      fileSizeLimit: 2 * 1024 * 1024,
      type: '',
      fileInput: '',
      title: '上传',
      formData: {},
      accept: {
        title: 'Images',
        extensions: 'gif,jpg,jpeg,png,ico',
        mimeTypes: 'image/png,image/jpg,image/jpeg,imge/bmp,image/gif'
      },
      uploader: null,
      fileVal: 'file',
      element: $(option.element)
    }, option);

    this.onFileQueued = config.onFileQueued || this.onFileQueued;
    this.onUploadSuccess = config.onUploadSuccess || this.onUploadSuccess;

    let ele = $(config.element);
    let uploader = WebUploader.create({
      swf: SWF_PATH,
      server: app.uploadUrl, // 配置参见script_boot.html.twig
      pick: {
        id: '#' + ele.attr("id"),
        multiple: false
      },
      formData: $.extend(config.formData, {
        token: ele.data("uploadToken"),
        _csrf_token: $('meta[name=csrf-token]').attr('content')
      }),
      accept: config.accept,
      auto: true,
      fileNumLimit: 1,
      fileSizeLimit: config.fileSizeLimit,
      resize: false,
      compress: false
    });

    uploader.on('fileQueued', function(file) {
      console.log('fileQueued :', file);
      self.onFileQueued(file);
    });

    uploader.on('uploadSuccess', function(file, response) {
      console.log('uploadSuccess: ', file, response);
      self.onUploadSuccess(file, response);
    });
    uploader.on('uploadError', function(file, response) {
      console.log('uploadError : ', file, response);
      notify('error', '上传失败，请重试！');
    });

    uploader.on('error', function(type) {
      console.log('error : ', type);
      switch (type) {
        case "Q_EXCEED_SIZE_LIMIT":
          notify('error', '文件过大，请上传较小的文件！');
          break;
        case "Q_EXCEED_NUM_LIMIT":
          notify('error', '添加的文件数量过多！');
          break;
        case "Q_TYPE_DENIED":
          notify('error', '文件类型错误！');
          break;
        default:
          break;
      }
    });
  }

  onFileQueued(file) {
    //override it if you need
  }

  onUploadSuccess(file, response) {
    //override it if you need
  }
}

export default EsWebUploader;
