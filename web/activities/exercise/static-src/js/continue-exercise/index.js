class continueExercisesBack {
  constructor() {
    this.modal = '#modal';
    this.init();
  }

  init() {
    this.continueExercise()
    this.continueExerciseClose()
  }

  // 返回上一页
  continueExercise() {
    $('.js-continue-exercise-back').on('click', ()=> {
      window.history.back()
    })
  }

  // 关闭弹框
  continueExerciseClose() {
    const $modal =  $(this.modal);
    $('.js-continue-exercise-close').on('click', ()=> {
      $modal.modal('hide');
    });
  }
}

new continueExercisesBack();