import DurationStorage from '../../../common/duration-storage';

let userId = $('#js-task-iframe').data('userId');
let fileId = $('#js-task-iframe').data('fileId');
let videoAskTime = $('#js-task-iframe').data('videoAskTime');
DurationStorage.set(userId, fileId, videoAskTime);
