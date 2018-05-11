import Register from './register';
import Drag from 'app/common/drag';
new Register();

if ($('#drag-btn').length) {
  new Drag($('#drag-btn'), $('.js-jigsaw'));
}

