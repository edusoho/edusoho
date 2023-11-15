function getUrlParameter(name) {
  name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
  var regex = new RegExp('[\\?&]' + name + '=([^&#]*)'),
      results = regex.exec(location.search);
  return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

const type = getUrlParameter('type');

$('.js-closed-title').html(Translator.trans(`exception.${type}.closed.content`))
// switch (type) {
//     case 'course':
//         $('.js-closed-title').html('12e3')
//       break;
//     case 'classroom':
//         $('.js-closed-title').html('12f3')
//       break;
//     case 'exercise':
//         $('.js-closed-title').html(Translator.trans('exception.exercise.closed.content'))
//       break;
//     default:
//         $('.js-closed-title').html('123')
//       break;
//   }