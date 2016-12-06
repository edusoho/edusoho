export const  chooserUiOpen =   () => {
  $('.file-chooser-bar').addClass('hidden');
  $('.file-chooser-main').removeClass('hidden');
}

export  const chooserUiClose = () => {
  $('.file-chooser-main').addClass('hidden');
  $('.file-chooser-bar').removeClass('hidden');
}

export const showChooserType = () => {
  $('#iframe-content').on('click','.file-chooser-bar',function() {
    chooserUiOpen();
    console.log("ok");
  })
}


