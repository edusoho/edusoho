/**
         * Created by Simon on 2016/11/18.
         */
import 'store';

class DurationStorage {

  static set(userId, fileId, duration) {
    var durations = store.get('durations', {});
    if (!durations || !(durations instanceof Array)) {
      durations = new Array();
    }

    var value = userId + '-' + fileId + ':' + duration;
    if (durations.length > 0 && durations.slice(durations.length - 1, durations.length)[0].indexOf(userId + '-' + fileId) > -1) {
      durations.splice(durations.length - 1, durations.length);
    }
    if (durations.length >= 20) {
      durations.shift();
    }
    durations.push(value);
    store.set('durations', durations);
  }

  static get(userId, fileId) {
    var durationTmpArray = store.get('durations', {});
    if (durationTmpArray) {
      for (var i = 0; i < durationTmpArray.length; i++) {
        var index = durationTmpArray[i].indexOf(userId + '-' + fileId);
        if (index > -1) {
          var key = durationTmpArray[i];
          return parseFloat(key.split(':')[1]);
        }
      }
    }
    return 0;
  }

  static del(userId, fileId) {
    var key = userId + '-' + fileId;
    var durationTmpArray = store.get('durations');
    if (!durationTmpArray) {
      return;
    }
    for (var i = 0; i < durationTmpArray.length; i++) {
      var index = durationTmpArray[i].indexOf(userId + '-' + fileId);
      if (index > -1) {
        durationTmpArray.splice(i, 1);
      }
    }
    store.set('durations', durationTmpArray);
  }
}

export default DurationStorage;
