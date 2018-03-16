import swfobject from 'es-swfobject';
import 'app/common/watermark';

class DocPlayer {
  constructor({ element, swfUrl, pdfUrl, watermarkOptions, canCopy }) {
    this.element = $(element);
    this.swfUrl = swfUrl || '';
    this.pdfUrl = pdfUrl || '';
    this.swfPlayerWidth = '100%';
    this.swfPlayerHeight = '100%';
    this.swfPlayerUrl = '';
    this.watermarkOptions = watermarkOptions || '';
    this.canCopy = canCopy || false;
    this.init();

    console.log(watermarkOptions);
  }

  init() {
    if (this.isSupportHtml5() && !this.isIE9()) {
      this.initPDFJSViewer();
    } else {
      this.initSwfViewer();
    }
    this.onFullScreen();
  }

  onFullScreen(docPlayer) {
    window.onmessage=function(e){
      console.log(e.data);
      if(e == null || e == undefined ){
        return;
      }
      var isPageFullScreen = e.data;
      if(typeof(isPageFullScreen) != 'boolean'){
        return ;
      }
      var docContent =  $('#task-content-iframe', window.parent.document);
      if (isPageFullScreen) {
        docContent.removeClass('screen-full');
        docContent.width('100%');
      }else{
        docContent.addClass('screen-full');
        docContent.width( window.document.body.offsetWidth+'px');
      }
    };
  }

  isIE9() {
    return navigator.appVersion.indexOf('MSIE 9.') != -1;
  }

  isSupportHtml5() {
    return $.support.leadingWhitespace;
  }

  initPDFJSViewer() {
    $('html').attr('dir', 'ltr');

    let src = app.cloudOldDocumentSdkUrl + '#' + this.pdfUrl;

    if (!this.canCopy) {
      src += '#false';
    }

    let $iframe = `<iframe id="doc-pdf-player" class="task-content-iframe" 
     src="${src}" style="width:100%;height:100%;border:0px" 
     allowfullscreen="" webkitallowfullscreen="">
      </iframe>`;
    this.element.append($iframe);

    this.addWatermark();
  }

  initSwfViewer() {
    $.html(`<div id="website"><p align="center" class="style1">${Translator.trans('site.flash_not_install_hint')}</p></div>`);

    let flashVars = {
      doc_url: decodeURI(this.swfUrl.value)
    };

    let params = {
      bgcolor: '#efefef',
      allowFullScreen: true,
      wmode: 'window',
      allowNetworking: 'all',
      allowscriptaccess: 'always',
      autoPlay: false
    };

    let attributes = {
      id: 'website'
    };

    swfobject.embedSWF(
      this.swfPlayerUrl,
      'website',
      this.swfPlayerWidth,
      this.swfPlayerHeight,
      '9.0.45',
      null,
      flashVars,
      params,
      attributes
    );

    this.addWatermark();
  }

  addWatermark() {
    this.watermarkOptions && this.element.WaterMark(this.watermarkOptions);
  }
}


export default DocPlayer;
