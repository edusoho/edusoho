import ActivityEmitter from "../activity-emitter";

class LiveShow {

  constructor() {
    this.init();
  }

  init() {
    let that = this;
    let activityData = JSON.parse($('#activity-data').text());
    console.log('activityData : ', activityData);
    let liveStartTimeFormat = activityData.startTimeFormat;
    let liveEndTimeFormat = activityData.endTimeFormat;
    let startTime = parseInt(activityData.startTime);
    let endTime = parseInt(activityData.endTime);
    let nowDate = parseInt(activityData.nowDate);
    let summary = $('#activity-summary').text();

    let courseId = activityData.fromCourseId;
    let activityId = activityData.id;
    let replayStatus = activityData.ext.replayStatus || 'ungenerated';

    // let $liveNotice = "<p>" + Translator.trans('直播将于%liveStartTime%开始，于%liveEndTime%结束，请在课前10分钟内提早进入。', {
    //     liveStartTime: '<strong>' + liveStartTimeFormat + '</strong>',
    //     liveEndTime: '<strong>' + liveEndTimeFormat + '</strong>'
    //   }) + "</p>";
    let $liveNotice = `<div class="live-show-item">
          <p class="title">直播时间</p>
          <p>直播将于${liveStartTimeFormat}开始，于${liveEndTimeFormat}结束<p>
          (请在课前10分钟内提早进入)
         </div>`

    let iID;
    if (iID) {
      clearInterval(iID);
    }

    let intervalSecond = 0;
    this.entry_url = location.protocol + "//" + location.hostname + '/course/' + courseId + '/activity/' + activityId + '/live_entry';

    function generateHtml() {

      nowDate = nowDate + intervalSecond;
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
        // $replayGuid += Translator.trans('录制直播课程时，需在直播课程间点击');
        // $replayGuid += "<span style='color:red'>" + Translator.trans('录制面板') + "</span>";
        // $replayGuid += Translator.trans('，录制完成后点击');
        // $replayGuid += "<span style='color:red'>" + Translator.trans('暂停') + "</span>";
        // $replayGuid += Translator.trans('结束录播，录播结束后在');
        // $replayGuid += "<span style='color:red'>" + Translator.trans('录播管理') + "</span>";
        // $replayGuid += Translator.trans('界面生成回放。');
        $replayGuid += `${Translator.trans('录制直播课程时，需在直播课程间点击')}
          <span class='color-info'>${Translator.trans('录制面板')}</span>，${Translator.trans('，录制完成后点击')}
          <span class='color-info'>${Translator.trans('暂停')}</span>${Translator.trans('结束录播，录播结束后在')}
          <span class='color-info'>${Translator.trans('录播管理')}</span>${Translator.trans('界面生成回放。')}。
        `;
      } else {
        // $replayGuid += Translator.trans('直播平台') + "<span style='color:red'>" + Translator.trans('下课后') + "</span>" +
        //   + "<span style='color:red'>" + Translator.trans('直播时间') + "</span>" + 
        // Translator.trans('结束后，在课时管理的');
        // $replayGuid += "<span style='color:red'>" + Translator.trans('录播管理') + "</span>";
        // $replayGuid += Translator.trans('点击生成回放。');

        $replayGuid += `${Translator.trans('直播平台')}
        <span class='color-info'>${Translator.trans('下课后')}</span>${Translator.trans('且')}
        <span class='color-info'>${Translator.trans('直播时间')}</span>${Translator.trans('结束后，在课时管理的')}
        <span class='color-info'>${ Translator.trans('录播管理')}</span>${Translator.trans('点击生成回放。')}
        `
      }

      $replayGuid = `<div class='live-show-item'>${$replayGuid}</div>`;

      let $countDown = that._getCountDown(days, hours, minutes, seconds);

      if (0 < startLeftSeconds && startLeftSeconds < 7200) {
        // $liveNotice = "<p>" + Translator.trans('直播将于%liveStartTime%开始，于%liveEndTime%结束，请在课前10分钟内提早进入。', {
        //     liveStartTime: '<strong>' + liveStartTimeFormat + '</strong>',
        //     liveEndTime: '<strong>' + liveEndTimeFormat + '</strong>'
        //   }) + "</p>";
        $liveNotice = `<div class="live-show-item">
          <p class="title">直播时间</p>
          <p>直播将于${liveStartTimeFormat}开始，于${liveEndTimeFormat}结束<p>
          (请在课前10分钟内提早进入)
         </div>`
        if (activityData.isTeacher) {
          $countDown = $replayGuid + $countDown;
          // $countDown = "<p>" + $countDown + "<a class='btn btn-primary js-start-live' href='javascript:;' onclick='liveShow.entryLiveRoom()'>" + Translator.trans('进入直播教室') + "</a><br><br></p>";
        } else {
          // $countDown = "<p>" + $countDown + "<a class='btn btn-primary js-start-live' href='javascript:;' onclick='liveShow.entryLiveRoom()'>" + Translator.trans('进入直播教室') + "</a><br><br></p>";
        }
        $countDown = `
          ${$countDown} 
          <div class='live-show-item'>
            <a class='btn btn-primary js-start-live' href='javascript:;' 
              onclick='$(liveShow.entryLiveRoom())'>
              ${ Translator.trans('进入直播教室')}
            </a>
          </div>`;
      }
      if (startLeftSeconds <= 0) {
        clearInterval(iID);
        // $liveNotice = "<p>" + Translator.trans('直播已经开始，直播将于%liveEndTime%', { liveEndTime: '<strong>' + liveEndTimeFormat + '</strong>' }) + "</p>";
        $liveNotice = `<div class='live-show-item'>
        直播已经开始，直播将于${liveEndTimeFormat}结束。
        </div>`;

        if (!!activityData.isTeacher) {
          $countDown = $replayGuid;
          // $countDown += "<p><a class='btn btn-primary js-start-live' href='javascript:;' onclick='liveShow.entryLiveRoom()'>" + Translator.trans('进入直播教室') + "</a><br><br></p>";
        } else {
          // $countDown = "<p><a class='btn btn-primary js-start-live' href='javascript:;' onclick='liveShow.entryLiveRoom()'>" + Translator.trans('进入直播教室') + "</a><br><br></p>";
        }
        $countDown = `
          ${$countDown} 
          <div class='live-show-item'>
            <a class='btn btn-primary js-start-live' href='javascript:;' 
              onclick='$(liveShow.entryLiveRoom())'>
              ${ Translator.trans('进入直播教室')}
            </a>
          </div>`;
      }
      if (endLeftSeconds <= 0) {
        // $liveNotice = "<p class='color-danger'>" + Translator.trans('直播已经结束') + "</p>";
        $liveNotice = `<p>${Translator.trans('直播已经结束')}</p>`
        $countDown = "";
        if (activityData.replays && activityData.replays.length > 0) {
          $.each(activityData.replays, function (i, n) {
            $countDown += "<a class='btn btn-primary' href='" + n.url + "' target='_blank'>" + n.title + "</a>&nbsp;&nbsp;";
          });
        }
      }

      // let $content = $countDown + $liveNotice + '<p class="mt10">' + summary + '</p>';
      let $content = `${$liveNotice} ${$countDown }
      <div class='live-show-item'>
        <p class='title'>直播说明</p>
        ${summary}
      </div>`;
      $("#lesson-live-content").find('.lesson-content-text-body').html($content);

      intervalSecond++;
    }

    generateHtml();
    if (endTime > nowDate) {
      iID = setInterval(generateHtml, 1000);
    }

    $("#lesson-live-content").show();
    $("#lesson-live-content").scrollTop(0);

    that.started = false;
  }

  entryLiveRoom() {
    let that = this;
    console.log('startLive', that.started, that.entry_url);
    if (!that.started) {
      that.started = true;
      let emitter = new ActivityEmitter();
      emitter.emit('start', {}).then(() => {
        console.log('live.start');
      }).catch((error) => {
        console.error(error);
      });
    }
    window.open(that.entry_url, '_blank');
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

window.liveShow = new LiveShow();
