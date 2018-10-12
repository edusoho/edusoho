<template>
  <!-- 文档播放器 -->
  <div class="web-view">
    <e-loading v-if="isLoading"></e-loading>
    <!-- web-view -->
    <div id="player" v-show="media !== 'text'"></div>
    <div class="media-text" ref="text" v-show="media === 'text'">
    </div>
  </div>
</template>
<script>
import loadScript from 'load-script';
import Api from '@/api';
import { mapState, mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';
import { Toast } from 'vant';

export default {
  data () {
    return {
      media: '',
      isPreview: this.$route.query.preview,
    }
  },
  computed: {
    ...mapState('course', {
      details: state => state.details,
      joinStatus: state => state.joinStatus
    }),
    ...mapState({
      isLoading: state => state.isLoading
    })
  },
  async mounted () {
    const player = await Api.getMedia(this.getParams()).catch(err => {
      Toast(err.message);
      return Promise.reject(err);
    });
    if (['ppt', 'doc'].includes(this.media)) {
      this.initPlayer(player)
    } else {
      // text类型不需要播放器
      this.$refs.text.innerHTML = player.media.content;
      this.setNavbarTitle(player.media.title)
    }
  },
  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE
    }),
    /*
    * 试看需要传preview=1
    * eg: /api/courses/1/task_medias/1?preview=1
    */
    getParams () {
      const { courseId, taskId, type } = this.$route.query;
      const canTryLookable = !this.joinStatus && this.isPreview
      this.media = type;

      return canTryLookable ? {
        query: {
          courseId,
          taskId,
        }, params: {
          preview: 1
        }
      } : {
        query: {
          courseId,
          taskId
        }
      }
    },
    initPlayer(player) {
      const media = player.media;
      const playerSDKUri = '//service-cdn.qiqiuyun.net/js-sdk/sdk-v1.js?v='
       + (Date.now()/1000/60);

      loadScript(playerSDKUri, (err) => {
      if (err) throw err;

      new window.QiQiuYun.Player({
        id: 'player',  // 用于初始化的DOM节点id
        // playServer: 'resource-play.cg-dev.cn',
        resNo: media.resId, // 想要播放的资源编号
        token: media.token, // 请求播放的认证token
        source: {
          type: player.mediaType,
          args: media
        },
      });
    });
    }
  }
}
</script>
