//@TODO 重构 activity编辑页  依赖了file-chooser页面组件的元素
export const chooserUiOpen = () => {
  $('.file-chooser-bar').addClass('hidden');
  $('.file-chooser-main').removeClass('hidden');
};

export const chooserUiClose = () => {
  $('.file-chooser-main').addClass('hidden');
  $('.file-chooser-bar').removeClass('hidden');
};

export const showChooserType = ($item) => {
  $('#iframe-content').on('click', '.js-choose-trigger', function() {
    chooserUiOpen();
  });
};
