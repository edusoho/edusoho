import { dateFormat } from 'app/common/unit';
let $savedMessage = $('[data-role=saved-message]');
dateFormat();
const saveRedmineLoading = () => {
  $savedMessage.html(Translator.trans('task.plugin_redmine_save_hint')).show();
};

const saveRedmineSuccess = () => {
  let date = new Date().Format('yyyy-MM-dd hh:mm:ss');
  $savedMessage.html(Translator.trans('task.plugin_redmine_save_success_hint', {date: date})).show();
  setTimeout(() => {
    $savedMessage.hide();
  }, 3000);
};

export {
  saveRedmineLoading,
  saveRedmineSuccess,
};