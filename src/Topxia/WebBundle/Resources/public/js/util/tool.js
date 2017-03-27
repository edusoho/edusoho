define(function(require, exports, module) {
  var Tool = {
    /**
     * Convert second to time format
     * @param  {Munber} sec :The second number you want to convert;
     * @return {String}     time string like hh:mm:ss
     */
    sec2Time: function(sec) {
      var time = "";
      var h = parseInt((sec % 86400) / 3600);
      var s = parseInt((sec % 3600) / 60);
      var m = sec % 60;
      if (h > 0) {
        time += h + ':';
      }
      if (s.toString().length < 2) {
        time += '0' + s + ':';
      } else {
        time += s + ':';
      }
      if (m.toString().length < 2) {
        time += '0' + m;
      } else {
        time += m;
      }
      return time;
    },

    /**
     * Convert time string('hh:mm:ss') to second number;
     * @param  {String} time :The time string('hh:mm:ss') you want to convert;
     * @return {Number}      second number;
     */
    time2Sec: function(time) {
      var arry = time.split(':');
      var sec = 0;
      for (var i = 0; i < arry.length; i++) {
        if (arry.length > 2) {
          if (i == 0) {
            sec += arry[i] * 3600;
          }
          if (i == 1) {
            sec += arry[i] * 60;
          }
          if (i == 2) {
            sec += parseInt(arry[i]);
          }
        }
        if (arry.length <= 2) {
          if (i == 0) {
            sec += arry[i] * 60;
          }
          if (i == 1) {
            sec += parseInt(arry[i]);
          }
        }
      }
      return sec;
    }
  }

  module.exports = Tool;
})
