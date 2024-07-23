import notify from 'common/notify';

$('.js-start-exam').on('click', event => {
  const endTime = $('input[name="endTime"]').val() * 1000;

  if (endTime <= Date.now() && endTime != 0) {
    event.preventDefault();

    notify('danger', Translator.trans('validate.endTime.validity'));

    setTimeout(() => {
      window.location.reload();
    }, 2000);
  }
});
