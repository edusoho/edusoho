<template>
  <div class="course-detail__head">
    <div class="course-detail__nav--btn" @click="viewAudioDoc" v-if="textContent" v-show="['audio'].includes(sourceType) && !isEncryptionPlus && !isCoverOpen">
      文稿
    </div>
    <div class="course-detail__nav--cover web-view" :class="{ opened: isCoverOpen }" v-if="textContent" v-show="['audio'].includes(sourceType) && !isEncryptionPlus">
      <div class="media-text" v-html="textContent"></div>
      <div class="course-detail__nav--cover-control" v-show="isCoverOpen" @click="handlePlayer">
        <i class="h5-icon" :class="!isPlaying ? 'h5-icon-bofang' : 'h5-icon-zanting'"></i>
      </div>
      <div class="course-detail__nav--cover-close-btn" @click="hideAudioDoc">
        <i class="van-icon van-icon-arrow van-nav-bar__arrow"></i>
      </div>
    </div>
    <div class="course-detail__head--img"
      v-show="sourceType === 'img' || isEncryptionPlus">
      <img :src="courseSet.cover.large" alt="">
      <div v-if="seckillActivities">秒杀倒计时 xxxx</div>
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
      isEncryptionPlus: false,
      mediaOpts: {},
      isCoverOpen: false,
      isPlaying: false,
      player: null
    };
  },
  props: {
    courseSet: {
      type: Object,
      default: () => {
        return {};
      },
    },
    seckillActivities: {
      type: Object,
      default: null
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
    }),
    textContent() {
      return this.mediaOpts.text;
    }
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
  /*
  * 试看需要传preview=1
  * eg: /api/courses/1/task_medias/1?preview=1
  */
  methods: {
    viewAudioDoc() {
       this.isCoverOpen = true;
    },
    hideAudioDoc() {
      this.isCoverOpen = false;
    },
    handlePlayer() {
      if (this.isPlaying) {
        return this.player && this.player.pause();
      }
      return this.player && this.player.play();
    },
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
        autoplay: true,
        disableFullscreen: this.sourceType === 'audio',
        isAudio: this.sourceType === 'audio'
        // resId: media.resId,
        // poster: "https://img4.mukewang.com/szimg/5b0b60480001b95e06000338.jpg"
      };
      this.mediaOpts = Object.assign({
        text: player.media.text
      }, options) ;

      this.$store.commit('UPDATE_LOADING_STATUS', true);
      this.loadPlayerSDK().then(SDK => {
        this.$store.commit('UPDATE_LOADING_STATUS', false);
        const player = new SDK(options);
        player.on('playing', () => {
          this.isPlaying = true;
        });
        player.on('paused', () => {
          this.isPlaying = false;
        });
        this.player = player;
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
