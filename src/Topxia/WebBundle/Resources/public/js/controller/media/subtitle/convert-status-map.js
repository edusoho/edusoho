define(function(require, exports, module) {
  /**
   * Convert Subtitle Status from English Status to Chiness Status
   * @param  {String} EnglishStatus 
   * @return {String} Chiness Stauts
   */

  var statusMap = {
    waiting: '等待转码',
    doing: '正在转码',
    success: '转码成功',
    error: '转码失败',
    none: '等待转码'
  }

  module.exports = function(englishStatus) {
    return statusMap[englishStatus];
  }
  
})
