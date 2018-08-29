<template>
  <div class="course-detail__head">
    <div class="course-detail__head--img"
      v-show="sourceType === 'img' || isEncryptionPlus">
      <img :src="courseSet.cover.large" alt="">
    </div>
    <div id="course-detail__head--video"
      ref="video"
      v-show="['video', 'audio'].includes(sourceType) && !isEncryptionPlus">
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
  props: {
    courseSet: {
      type: Object,
      default: {}
    }
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
  watch: {
    taskId: {
      immediate: true,
      handler(v, oldVal) {
        if (['video', 'audio'].includes(this.sourceType)) {
          window.scrollTo(0, 0);
          this.initPlayer();
        }
      }
    }
  },
  methods: {
    getParams () {
      return {
        query: {
          courseId: this.selectedPlanId,
          taskId: this.taskId
        }
      }
    },
    async initPlayer (){
      this.$refs.video && (this.$refs.video.innerHTML = '');

      const player = await Api.getMedia(this.getParams())
      // 试看判断
      // const canTryLookable = !this.joinStatus && Number(this.details.tryLookable)

      this.isEncryptionPlus = player.media.isEncryptionPlus;
      if (player.media.isEncryptionPlus) {
        Toast('该浏览器不支持云视频播放，请下载App')
        return;
      }
      const media = player.media;
      const options = {
        id: 'course-detail__head--video',
        user: this.user,
        playlist: media.url,
        autoplay: true
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
      if (!window.VideoPlayerSDK) {

        const scrptSrc = '//service-cdn.qiqiuyun.net/js-sdk/video-player/sdk-v1.js?v='
          + (Date.now() / 1000 / 60);
          // Cache SDK for 1 min.

        return new Promise((resolve, reject) => {
          loadScript(scrptSrc, (err) => {
            if (err) {
              reject(err);
            }
            resolve(window.VideoPlayerSDK);
          });
        });
      }
      return Promise.resolve(window.VideoPlayerSDK);
    },
  }
}
</script>
