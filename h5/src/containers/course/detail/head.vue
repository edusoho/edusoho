<template>
<div class="course-detail__head">
  <div class="course-detail__head--img" v-if="sourceType === 'img'">
    <img :src="courseSet.cover.large" alt="">
  </div>
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
    sourceType: {
      immediate: true,
      handler(v) {
        ['video', 'radio'].includes(v) && this.initPlayer(v);
      }
    }
  },
  methods: {
    initPlayer (type){
      const options = {
        id: 'course-detail__head--video',
        resId: '',
        user: {},
        // playlist: 'http://ese2a3b1c3d55k.pri.qiqiuyun.net/course-task-1/20180621030306-7ks1a405yug48kog?e=1530687514&token=ExRD5wolmUnwwITVeSEXDQXizfxTRp7vnaMKJbO-:hMWXT4qoehVwiMUBgR9ketcNvYA=',
        playlist: 'http://ese2a3b1c3d55k.pri.qiqiuyun.net/course-task-1/20180621030306-7ks1a405yug48kog?e=1530687514&token=ExRD5wolmUnwwITVeSEXDQXizfxTRp7vnaMKJbO-:hMWXT4qoehVwiMUBgR9ketcNvYA=',
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
