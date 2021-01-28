import 'app/common/watermark';
import {isMobileDevice } from 'common/utils';

export const initScrollbar = ()=> {
  let $paneCard = $('.js-panel-card');
  $paneCard.perfectScrollbar();
  $paneCard.perfectScrollbar('update');
};

export const testpaperCardFixed =()=> {
  console.log('ok');
  if (isMobileDevice()) return;
  
  let $testpaperCard = $('.js-testpaper-card');
  if ($testpaperCard.length <= 0 ) {
    return;
  }
  let testpaperCard_top = $testpaperCard.offset().top;
  $(window).scroll(function() {
    let scrollTop = $(window).scrollTop();
    if (scrollTop >= testpaperCard_top) {
      $testpaperCard.addClass('affix');
    } else {
      $testpaperCard.removeClass('affix');
    }
  });
};

export const testpaperCardLocation = () => {
  $('.js-btn-index').click((event)=>{
    let $btn = $(event.currentTarget);
    if($('.js-testpaper-heading').length <= 0) {
      $btn.addClass('doing').siblings('.doing').removeClass('doing');
    }
  });
};

export const onlyShowError = ()=> {
  $('#showWrong').change((event)=>{
    let $current =$(event.currentTarget);
    $('.js-answer-notwrong').each(function (index,item) {  
      let $item = $($(item).data('anchor'));
      let $itemParent = $item.closest('.js-testpaper-question-block');
      if($current.prop('checked')) {
        $item.hide();
        if($itemParent.find('.js-testpaper-question:visible').length<=0) {
          $itemParent.hide();
        }
      }else {
        $item.show();
        $itemParent.show();
      }
    });
    initScrollbar();
  });
};

export const initWatermark = ()=> {
  let $testpaperWatermark = $('.js-testpaper-watermark');
  if ($testpaperWatermark.length > 0) {
    $.get($testpaperWatermark.data('watermark-url'), function(response){
      $testpaperWatermark.each(function(){
        $(this).WaterMark({
          'yPosition': 'center',
          'style': {'font-size':10},
          'opacity': 0.6,
          'contents': response
        });
      });
    });
  }
};