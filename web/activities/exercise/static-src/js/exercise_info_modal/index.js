const $ecerciseMode = $('.js-show-exercise-create-btn')
const $modal = $('#modal')

$ecerciseMode.on('click', ()=> {
  $ecerciseMode.hide()
  $('.js-exercise-create-btn').removeClass('hidden');
  $('.js-show-exercise-create').hide()
  $('.js-exercise-create').removeClass('hidden')
  $('.js-exercise-schedule')[0].innerHTML = '2'
})

$('.js-close-modal').on('click', () => {
  $modal.modal('hide');
})