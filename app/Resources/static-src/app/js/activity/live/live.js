import ActivityEmitter from "../activity-emitter";
export default class LiveShow {
  constructor() {
    this.init();
  }

  init() {

    let hasRoom = $('#lesson-live-content').data('hasRoom') == '1';
    if(!hasRoom){
      $("#lesson-live-content").find('.lesson-content-text-body').html(
        `<div class='live-show-item'>
        <p class='title'>直播说明</p>
        <p>直播教室尚未创建！</p>
        </div>`
      );
      $("#lesson-live-content").show();
      return;
    }

    let activityData = JSON.parse($('#activity-data').html());
    let startTime = parseInt(activityData.startTime);
    let endTime = parseInt(activityData.endTime);
    let nowDate = parseInt(activityData.nowDate);
    this.liveStartTimeFormat = activityData.startTimeFormat;
    this.liveEndTimeFormat = activityData.endTimeFormat;
    let courseId = activityData.fromCourseId;
    let activityId = activityData.id;
    // let replayStatus = activityData.ext.replayStatus || 'ungenerated';
    this.summary = $('#activity-summary').text();
    this.$liveNotice = `<div class="live-show-item">
          <p class="title">直播时间</p>
          <p>直播将于${this.liveStartTimeFormat}开始，于${this.liveEndTimeFormat}结束<p>
          (请在课前10分钟内提早进入)
         </div>`
    this.iID = null;
    if (this.iID) {
      clearInterval(iID);
    }
    this.intervalSecond = 0;
    this.entry_url = location.protocol + "//" + location.hostname + '/course/' + courseId + '/activity/' + activityId + '/live_entry';
    this.generateHtml();
    var millisecond = 0;
    if (endTime > nowDate) {
      millisecond = 1000;
    }
    this.iID = setInterval(()=> {
      this.generateHtml();
    }, millisecond);

    $("#lesson-live-content").show();
    this.started = false;
  }

  entryLiveRoom() {
    let that = this;
    console.log('startLive', this.started, this.entry_url);
    if (!this.started) {
      this.started = true;
      let emitter = new ActivityEmitter();
      emitter.emit('start', {}).then(() => {
        console.log('live.start');
      }).catch((error) => {
        console.error(error);
      });
    }
    window.open(this.entry_url, '_blank');
  }

  generateHtml() {
    let activityData = JSON.parse($('#activity-data').text());
    let startTime = parseInt(activityData.startTime);
    let endTime = parseInt(activityData.endTime);
    let nowDate = parseInt(activityData.nowDate);
    nowDate = nowDate + this.intervalSecond;
    let startLeftSeconds = parseInt(startTime - nowDate);
    let endLeftSeconds = parseInt(endTime - nowDate);
    let days = Math.floor(startLeftSeconds / (60 * 60 * 24));
    let modulo = startLeftSeconds % (60 * 60 * 24);
    let hours = Math.floor(modulo / (60 * 60));
    modulo = modulo % (60 * 60);
    let minutes = Math.floor(modulo / 60);
    let seconds = modulo % 60;
    let $replayGuid = Translator.trans('老师们：');

    if (activityData.ext.liveProvider == 1) {
      $replayGuid += `${Translator.trans('录制直播课程时，需在直播课程间点击')}
          <span class='color-info'>${Translator.trans('录制面板')}</span>，${Translator.trans('，录制完成后点击')}
          <span class='color-info'>${Translator.trans('暂停')}</span>${Translator.trans('结束录播，录播结束后在')}
          <span class='color-info'>${Translator.trans('录播管理')}</span>${Translator.trans('界面生成回放。')}。
        `;
    } else {
      $replayGuid += `${Translator.trans('直播平台')}
        <span class='color-info'>${Translator.trans('下课后')}</span>${Translator.trans('且')}
        <span class='color-info'>${Translator.trans('直播时间')}</span>${Translator.trans('结束后，在课时管理的')}
        <span class='color-info'>${ Translator.trans('录播管理')}</span>${Translator.trans('点击生成回放。')}
        `
    }
    $replayGuid = `<div class='live-show-item'>${$replayGuid}</div>`;
    let $countDown = this._getCountDown(days, hours, minutes, seconds);
    let $btn = '';

    if (0 < startLeftSeconds && startLeftSeconds < 7200) {
      this.$liveNotice = `<div class="live-show-item">
          <p class="title">直播时间</p>
          <p>直播将于${this.liveStartTimeFormat}开始，于${this.liveEndTimeFormat}结束<p>
          (请在课前10分钟内提早进入)
         </div>`
      $btn = `<div class='live-show-item'>
          <a class='btn btn-primary js-start-live' href='javascript:;'
            onclick='$(liveShow.entryLiveRoom())'>
            ${ Translator.trans('进入直播教室')}
          </a>
        </div>`;
      if (activityData.isTeacher) {
        $btn += $replayGuid
      }
    }
    if (startLeftSeconds <= 0) {
      clearInterval(this.iID);
      $countDown = '';
      this.$liveNotice = `<div class='live-show-item'>
          <p class="title">直播时间</p>
          直播已经开始，直播将于${this.liveEndTimeFormat}结束。
        </div>`;
      $btn = `<div class='live-show-item'>
          <a class='btn btn-primary js-start-live' href='javascript:;'
            onclick='$(liveShow.entryLiveRoom())'>
            ${ Translator.trans('进入直播教室')}
          </a>
        </div>`;
      if (activityData.isTeacher) {
        $btn += $replayGuid;
      }
    }
    if (endLeftSeconds <= 0) {
      $countDown = "";
      $btn = '';
      this.$liveNotice = `<div class='live-show-item'>
          <i class='es-icon es-icon-xinxi color-danger icon-live-end'></i>
          ${Translator.trans('直播已经结束')}
        </div>`
      if (activityData.replays && activityData.replays.length > 0) {
        $.each(activityData.replays, function (i, n) {
          $btn += "<a class='btn btn-primary btn-replays' href='" + n.url + "' target='_blank'>" + n.title + "</a>";
        });
        $btn = `<div class='live-show-item'>${$btn}</div>`;
      }
    }

    let $content = `${this.$liveNotice} ${$countDown}
      <div class='live-show-item'>
        <p class='title'>直播说明</p>
        ${this.summary}
      </div>${$btn}`;
    $("#lesson-live-content").find('.lesson-content-text-body').html($content);
    this.intervalSecond++;
  }

  _getCountDown(days, hours, minutes, seconds) {
    let content = '';
    content += days ? days + Translator.trans(' 天 ') : "";
    content += hours ? hours + Translator.trans(' 小时 ') : "";
    content += minutes ? minutes + Translator.trans(' 分钟 ') : "";
    content += seconds ? seconds + Translator.trans(' 秒 ') : "";
    content = `<div class='live-show-item'>
      <p class='title'>${Translator.trans('倒计时')}</p>
      <span class="color-warning">${content}</span>
    </div>`;
    return content;
  }
}