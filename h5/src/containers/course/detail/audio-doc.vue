<template>
  <div class="course-detail__audio">
    <div id="course-detail__audio--content"
      class="course-detail__audio--content"
      ref="audio"
      v-show="!isEncryptionPlus">
    </div>
  </div>
</template>
<script>
import loadScript from 'load-script';
import { mapState } from 'vuex';
import Api from '@/api'
import { Toast } from 'vant';

export default {
  data() {
    return {
      isEncryptionPlus: false
    };
  },
  computed: {
    ...mapState('course', {
      sourceType: state => state.sourceType,
      selectedPlanId: state => state.selectedPlanId,
      taskId: state => state.taskId,
      details: state => state.details,
      joinStatus: state => state.joinStatus,
      user: state => state.user,
    })
  },
  created() {
    this.initPlayer();
  },
  /*
  * 试看需要传preview=1
  * eg: /api/courses/1/task_medias/1?preview=1
  */
  methods: {
    getParams () {
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
    async initPlayer (){
      this.$refs.audio && (this.$refs.audio.innerHTML = '');

      const player = this.$route.query;
      console.error(this.$route);
      // 试看判断
      // const canTryLookable = !this.joinStatus && Number(this.details.tryLookable)

      this.isEncryptionPlus = player.isEncryptionPlus;
      if (player.isEncryptionPlus) {
        Toast('该浏览器不支持云视频播放，请下载App')
        return;
      }
      const options = {
        id: 'course-detail__audio--content',
        user: this.user,
        playlist: player.url,
        template: player.text,
        autoplay: true,
        simpleMode: true
        // resId: media.resId,
        // poster: "https://img4.mukewang.com/szimg/5b0b60480001b95e06000338.jpg"
      };

      this.$store.commit('UPDATE_LOADING_STATUS', true);
      this.loadPlayerSDK().then(SDK => {
        this.$store.commit('UPDATE_LOADING_STATUS', false);
        const player = new SDK(options);
      })
    },
    loadPlayerSDK () {
      if (!window.AudioPlayerSDK) {

        const scrptSrc = '//service-cdn.qiqiuyun.net/js-sdk/audio-player/sdk-v1.js?v='
          + (Date.now() / 1000 / 60);
          // Cache SDK for 1 min.

        return new Promise((resolve, reject) => {
          loadScript(scrptSrc, (err) => {
            if (err) {
              reject(err);
            }
            resolve(window.AudioPlayerSDK);
          });
        });
      }
      return Promise.resolve(window.AudioPlayerSDK);
    },
  }
}
</script>
