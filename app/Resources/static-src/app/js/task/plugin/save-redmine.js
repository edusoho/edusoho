import { dateFormat } from 'app/common/unit';
import notify from 'common/notify';

let $savedMessage = $('[data-role=saved-message]');
dateFormat();
const saveRedmineLoading = () => {
  $savedMessage.html(Translator.trans('task.plugin_redmine_save_hint')).show();
};

const saveRedmineSuccess = () => {
  let date = new Date().Format('yyyy-MM-dd hh:mm:ss');
  $savedMessage.html(Translator.trans('task.plugin_redmine_save_success_hint', {date: date})).show();
  notify('success', Translator.trans('site.save_success_hint'));
  setTimeout(() => {
    $savedMessage.hide();
  }, 3000);
};

const saveRedmineClear = () => {
  $savedMessage.html('');
};

export {
  saveRedmineLoading,
  saveRedmineSuccess,
  saveRedmineClear,
};