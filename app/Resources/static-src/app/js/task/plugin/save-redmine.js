import { dateFormat } from 'app/common/unit';
let $savedMessage = $('[data-role=saved-message]');
dateFormat();
const saveRedmineLoading = () => {
  $savedMessage.html(Translator.trans('正在保存...')).show();
}

const saveRedmineSuccess = () => {
  let data = new Date().Format('yyyy-MM-dd hh:mm:ss');
  $savedMessage.html(Translator.trans(`保存于：${data}`)).show();
  setTimeout(() => {
    $savedMessage.hide();
  }, 3000);
}

export {
  saveRedmineLoading,
  saveRedmineSuccess,
}