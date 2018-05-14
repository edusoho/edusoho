import Create from './create';
import Drag from 'app/common/drag';

if ($('#drag-btn').length) {
  new Drag($('#drag-btn'), $('.js-jigsaw'));
}
new Create();
