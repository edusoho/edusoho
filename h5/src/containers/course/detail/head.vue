<template>
<div class="course-detail__head">
  <div class="course-detail__head--img" v-if="sourceType === 'img'">
    <img :src="courseSet.cover.large" alt="">
  </div>
  {{sourceType}}
 <div id="course-detail__head--video" v-if="sourceType === 'video'"></div>
</div>

</template>
<script>
import loadScript from 'load-script';
import { mapState } from 'vuex';

export default {
  props: {
    courseSet: {
      type: Object,
      default: {}
    },
    type: {
      type: String,
      default: 'img'
    }
  },
  computed: {
    ...mapState('course', {
      sourceType: state => state.sourceType
    })
  },
  watch: {
    sourceType(v) {
      v === 'video' && this.initPlayer();
    }
  },
  mounted() {
    this.type === 'video' && this.initPlayer();
  },
  methods: {
    initPlayer (){
      const options = {
        id: 'course-detail__head--video',
        playlist: 'https://ghub.club/playlist/playlist.m3u8',
        autoplay: true,
        poster: 'https://img4.mukewang.com/szimg/5b0b60480001b95e06000338.jpg'
      };

      this.loadPlayerSDK().then(SDK => {
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
