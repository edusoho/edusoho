<template>
<div class="course-detail__head">
  <div class="course-detail__head--img" v-show="sourceType === 'img'">
    <img :src="courseSet.cover.large" alt="">
  </div>
 <div id="course-detail__head--video" 
  ref="video"
  v-show="['video', 'audio'].includes(sourceType)"></div>
</div>

</template>
<script>
import loadScript from 'load-script';
import { mapState } from 'vuex';
import Api from '@/api'

export default {
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
      joinStatus: state => state.joinStatus
    })
  },
  watch: {
    taskId: {
      handler(v, oldVal) {
        ['video', 'audio'].includes(this.sourceType) && this.initPlayer();
      }
    }
  },
  methods: {
    getParams () {
      const canTryLookable = !this.joinStatus && Number(this.details.tryLookable)

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
      this.$refs.video.innerHTML = '';

      const player = await Api.getMedia(this.getParams());

      const media = player.media;
      const options = {
        id: 'course-detail__head--video',
        resId: media.resId,
        user: {},
        playlist: media.url,
        autoplay: true,
        poster: "https://img4.mukewang.com/szimg/5b0b60480001b95e06000338.jpg"
      };

      this.$store.commit('UPDATE_LOADING_STATUS', true);
      this.loadPlayerSDK().then(SDK => {
        this.$store.commit('UPDATE_LOADING_STATUS', false);
        const player = new SDK(options);
      })
    },
    loadPlayerSDK () {
      if (!window.VideoPlayerSDK) {

        const scrptSrc = `//service-cdn.qiqiuyun.net/js-sdk/video-player/sdk-v1.js?
          v=${Date.now() / 1000 / 60}`; // Cache SDK for 1 min.

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
