class ExerciseAdd {
  constructor() {
    this.init();
  }

  init() {
    let $form = $('#exercise-add-form');

    $('#exercise-add-submit').click(function (event) {
      $form.submit();
    });
  }
}

new ExerciseAdd();
