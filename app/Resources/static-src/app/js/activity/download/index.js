import PptPlayer from '../../../common/ppt-player';
import ActivityEmitter from "../activity-emitter";


let emitter = new ActivityEmitter();
let $content = $('#activity-ppt-content');

$(".download-activity-list").on('click', 'a', function () {
  $(this).attr('href', $(this).data('url'))
  emitter.emit('download',{fileId:$(this).data('fileId')});
})

