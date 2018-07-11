<template>
  <div class="web-view">
    <!-- web-view -->
    <div id="player"></div>
  </div>
</template>
<script>
import loadScript from 'load-script';
import QiQiuYun from 'qiqiuyun-sdk';
import Api from '@/api'

export default {
  async created () {
    const { courseId, taskId } = this.$route.params;
    const player = await Api.getMedia({query: { courseId,taskId }});
    console.log(player, 'player')
    this.initPlayer(player);
  },
  methods: {
    initPlayer(player) {
      const media = player.media;
      const playerSDKUri = `//oilgb9e2p.qnssl.com/js-sdk/sdk-v1.js?v=${~~(Date.now()/1000/60)}`;

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
