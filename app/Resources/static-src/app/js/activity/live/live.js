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
        <p class='title'>${Translator.trans('site.activity.live.content_title')}</p>
        <p>${Translator.trans('activity.liva.not_created_notice')}！</p>
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
          <p class="title">${Translator.trans('activity.live.notice_title')}</p>
           ${Translator.trans('activity.live.default_notice', {'startTimeFormat': this.liveStartTimeFormat, 'endTimeFormat': this.liveEndTimeFormat})}
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
    let replayGuid = '';

    if (activityData.ext.liveProvider == 1) {
      replayGuid = Translator.trans('activity.live.replay_guid_1');
    } else {
      replayGuid = Translator.trans('activity.live.replay_guid');
    }
    replayGuid = `<div class='live-show-item'>${replayGuid}</div>`;
    let $countDown = this._getCountDown(days, hours, minutes, seconds);
    let $btn = '';

    if (0 < startLeftSeconds && startLeftSeconds < 7200) {
      this.$liveNotice = `<div class="live-show-item">
          <p class="title">${Translator.trans('activity.live.notice_title')}</p>
          ${Translator.trans('activity.live.default_notice', {'startTimeFormat': this.liveStartTimeFormat, 'endTimeFormat': this.liveEndTimeFormat})}
         </div>`
      $btn = `<div class='live-show-item'>
          <a class='btn btn-primary js-start-live' href='javascript:;'
            onclick='$(liveShow.entryLiveRoom())'>
           ${Translator.trans('activity.live.entry_live_room')}
          </a>
        </div>`;
      if (activityData.isTeacher) {
        $btn += replayGuid
      }
    }
    if (startLeftSeconds <= 0) {
      clearInterval(this.iID);
      $countDown = '';
      this.$liveNotice = `<div class='live-show-item'>
          <p class="title">${Translator.trans('activity.live.notice_title')}</p>
          ${Translator.trans('activity.live.started_notice', {'endTimeFormat': this.liveEndTimeFormat})}
        </div>`;
      $btn = `<div class='live-show-item'>
          <a class='btn btn-primary js-start-live' href='javascript:;'
            onclick='$(liveShow.entryLiveRoom())'>
            ${ Translator.trans('activity.live.entry_live_room')}
          </a>
        </div>`;
      if (activityData.isTeacher) {
        $btn += replayGuid;
      }
    }
    if (endLeftSeconds <= 0) {
      $countDown = "";
      $btn = '';
      this.$liveNotice = `<div class='live-show-item'>
          <i class='es-icon es-icon-xinxi color-danger icon-live-end'></i>
          ${Translator.trans('activity.live.ended_notice')}
        </div>`
      if (activityData.replays && activityData.replays.length > 0) {
        $.each(activityData.replays, function (i, n) {
          $btn = "<a class='btn btn-primary btn-replays' href='" + n.url + "' target='_blank'>" + n.title + "</a>";
        });
        $btn = `<div class='live-show-item'>${$btn}</div>`;
      }
    }

    let $content = `${this.$liveNotice} ${$countDown}
      <div class='live-show-item'>
        <p class='title'>${Translator.trans('activity.live.content_title')}</p>
        ${this.summary}
      </div>${$btn}`;
    $("#lesson-live-content").find('.lesson-content-text-body').html($content);
    this.intervalSecond++;
  }

  _getCountDown(days, hours, minutes, seconds) {
    let content = '';
    content += days ? Translator.trans('site.date_format_dhis', {'days': days, 'hours': hours, 'minutes': minutes, 'seconds': seconds}) : "";
    content += hours ? Translator.trans('site.date_format_his', {'hours': hours, 'minutes': minutes, 'seconds': seconds}) : "";
    content += minutes ? Translator.trans('site.date_format_is', {'minutes': minutes, 'seconds': seconds}) : "";
    content += seconds ? Translator.trans('site.date_format_s', {'seconds': seconds}) : "";
    content = `<div class='live-show-item'>
      <p class='title'>${Translator.trans('activity.live.count_down_title')}</p>
      <span class="color-warning">${content}</span>
    </div>`;
    return content;
  }
}