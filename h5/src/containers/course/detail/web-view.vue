<template>
  <div class="web-view">
    <!-- web-view -->
    <div id="player"></div>
  </div>
</template>
<script>
import loadScript from 'load-script';
import QiQiuYun from 'qiqiuyun-sdk';

export default {
  created() {
    const resNo = '8629e127cac2471aa5402637eda4a0e1';
    const secretKey = '9e64ca5b4a446e9a0999d982';
    const token = `private:${QiQiuYun.generateToken(resNo, secretKey)}`;
    const playerSDKUri = `//oilgb9e2p.qnssl.com/js-sdk/sdk-v1.js?v=${~~(Date.now()/1000/60)}`;

    loadScript(playerSDKUri, (err) => {
      if (err) throw err;

      new window.QiQiuYun.Player({
        id: 'player',  // 用于初始化的DOM节点id
        playServer: 'resource-play.cg-dev.cn',
        // resNo, // 想要播放的资源编号
        // token, // 请求播放的认证token
        source: {
          type: 'document',
          args: {
            encryptKey: "004df92f42664a88",
            html: "http://ese4a2b4c2d9ql-pub.pub.codeages.net/coursematerial-250/20180329204631-arbLDR4EMXRi8UyP/f9c45a3b586b93ad_html?e=1528166861&token=6uEqT5vwuQIM4p1vkf7bk6GHshgNofDb_SYHvabL:8B-USAsXKasRiLoqL06t_dcEF18=",
            iv: "4dad6588c0e44a05"
          }
        },
      });
    });
  }
}
</script>
