export let questionSubjectiveRemask = ($element) => {
  let hasSubjective = false;
  let html = '';
  const $subjectiveRemask = $('#task-create-content-iframe').contents().find('.js-subjective-remask');

  $element.find('tbody tr').each(function() {
    let type = $(this).data('type');
    console.log(type);
    if (type == 'essay') {
      hasSubjective = true;
    }
  });
  console.log(hasSubjective);
  if(hasSubjective || $element.find('tbody tr').length == 0) {
    $subjectiveRemask.html('');
    return;
  }

  console.log($subjectiveRemask);

  if($subjectiveRemask.data('type') == 'homework') {
    html = Translator.trans('activity.homework_manage.objective_question_hint');
  }
  else {
    html = Translator.trans('activity.homework_manage.pass_objective_question_hint');
  }
  $subjectiveRemask.html(html).removeClass('hidden');
};