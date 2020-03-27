<template>
  <div class="course-detail__audio">
    <div
      v-show="!isEncryptionPlus"
      id="course-detail__audio--content"
      ref="audio"
      class="course-detail__audio--content"/>
    <div v-if="learnMode">
      <div class="course-detail__head--btn course-detail__head--activebtn" v-if="enableFinish">
        <i class="iconfont icon-markdone"></i>
        学过了
      </div>
      <div class="course-detail__head--btn" v-if="enableFinish" @click="toToast">完成条件</div>
    </div>
  </div>
</template>
<script>
import loadScript from 'load-script'
import { mapState } from 'vuex'
import Api from '@/api'
import { Toast } from 'vant'
import TaskPipe from '@/utils/task-pipe/index';

export default {
  data() {
    return {
      finishCondition: undefined,
      learnMode: false,
      enableFinish: false,
      isEncryptionPlus: false,
      currentTime: 0,
      startTime: 0,
      timeChangingList: [],
      taskPipe: undefined,
    }
  },
  computed: {
    ...mapState('course', {
      sourceType: state => state.sourceType,
      selectedPlanId: state => state.selectedPlanId,
      taskId: state => state.taskId,
      details: state => state.details,
      joinStatus: state => state.joinStatus,
      user: state => state.user
    })
  },
  created() {
    this.initTaskPipe();
    this.initPlayer()
  },
  beforeDestroy() {
    this.taskPipe.clearInterval();
  },
  /*
  * 试看需要传preview=1
  * eg: /api/courses/1/task_medias/1?preview=1
  */
  methods: {
    toToast() {
      if (this.finishCondition) {
        this.$toast({
          message: this.finishCondition.text,
          position: 'bottom'
        });
      }
    },
    watchTime() {
      if (this.isAndroid() && this.taskPipe) {
        return Math.floor(this.taskPipe.getDuration() / 60000);
      }
      let timeCount = this.currentTime - this.startTime;
      this.timeChangingList.forEach(item => {
        timeCount += (item.end - item.start);
      });
      return Math.floor(timeCount / 60);
    },
    isAndroid() {
      return !!navigator.userAgent.match(new RegExp("android", "i"));
    },
    initTaskPipe() {
      this.taskPipe = new TaskPipe({
        reportData: {
          courseId: this.selectedPlanId,
          taskId: this.taskId
        },
        formatReportData: (data) => {
          data.watchTime = this.watchTime()
          return data;
        }
      });
      this.taskPipe.on('courseData', (res) => {
        this.finishCondition = res.activity && res.activity.finishCondition;
      });
      this.taskPipe.on('report.finish', () => {
        this.enableFinish = true;
      })
    },
    getParams() {
      const canTryLookable = !this.joinStatus
      return canTryLookable ? {
        query: {
          courseId: this.selectedPlanId,
          taskId: this.taskId
        }, params: {
          preview: 1
        }
      } : {
        query: {
          courseId: this.selectedPlanId,
          taskId: this.taskId
        }
      }
    },
    async initPlayer() {
      this.$refs.audio && (this.$refs.audio.innerHTML = '')

      const player = this.$route.query
      // 试看判断
      // const canTryLookable = !this.joinStatus && Number(this.details.tryLookable)

      this.isEncryptionPlus = player.isEncryptionPlus
      if (player.isEncryptionPlus) {
        Toast('该浏览器不支持云视频播放，请下载App')
        return
      }
      const options = {
        id: 'course-detail__audio--content',
        user: this.user,
        playlist: player.playlist,
        template: player.text,
        autoplay: true,
        simpleMode: true
        // resId: media.resId,
        // poster: "https://img4.mukewang.com/szimg/5b0b60480001b95e06000338.jpg"
      }

      this.$store.commit('UPDATE_LOADING_STATUS', true)
      this.loadPlayerSDK().then(SDK => {
        this.learnMode = this.details.learnMode !== 'freeMode';
        this.enableFinish = !!this.details.enableFinish;
        this.$store.commit('UPDATE_LOADING_STATUS', false)
        const player = new SDK(options)
        player
        .on('ready', () => {
          this.taskPipe.clearInterval();
          this.taskPipe.initInterval();
        })
        player.on('datapicker.start', (e) => {
          this.timeChangingList.push({
            start: this.startTime,
            end: e.end,
          });
          this.startTime = e.start;
        })
        player.on('ended', () => {
          this.taskPipe.trigger('end');
        })
        player.on('timeupdate', (e) => {
          this.currentTime = e.currentTime;
          this.taskPipe.trigger('time', this.watchTime());
        })
      })
    },
    loadPlayerSDK() {
      if (!window.AudioPlayerSDK) {
        const scrptSrc = '//service-cdn.qiqiuyun.net/js-sdk/audio-player/sdk-v1.js?v=' +
          (Date.now() / 1000 / 60)
          // Cache SDK for 1 min.

        return new Promise((resolve, reject) => {
          loadScript(scrptSrc, (err) => {
            if (err) {
              reject(err)
            }
            resolve(window.AudioPlayerSDK)
          })
        })
      }
      return Promise.resolve(window.AudioPlayerSDK)
    }
  }
}
</script>
