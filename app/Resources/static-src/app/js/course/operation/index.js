const $unfavorite = $('#unfavorite-btn');
const $favorite = $('#favorite-btn');

const bindOperation = ($needHideBtn, $needShowBtn) => {
  $needHideBtn.click(() => {
    const url = $needHideBtn.data('url');
    if (!url) {
      return;
    }

    $.post(url)
        .done((success) => {
          if (!success) return;
          $needShowBtn.show();
          $needHideBtn.hide();
        });
  })
};

bindOperation($unfavorite, $favorite);
bindOperation($favorite, $unfavorite);