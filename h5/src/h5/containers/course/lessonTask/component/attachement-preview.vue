<template>
  <div style="margin-top: 8px;">
    <div v-if="disableVideo && isMedia" class="attachment-preview">
      <img :src="disableVideoIcon" style="width: 24px;margin-right: 8px;" />
      <div class="text-overflow text-12" style="flex: 1;color: #999;">
        <template v-if="storageSetting.securityVideoPlayer && !this.isWechat()">
          {{ $t('attachment.disableVideo') }}
        </template>
        <template v-else>
          {{ $t('attachment.disableVideo2') }}
        </template>
      </div>
    </div>
    
    <div v-else-if="!isImmediatePreview" class="attachment-preview">
      <img :src="iconSrc" class="attachment-preview__icon" />
      
      <div class="text-overflow" style="flex: 1;">{{ attachment.file_name }}</div>
      
      <div v-if="attachment.convert_status !== 'success'" class="attachment-preview__status">
        {{ resourceStatus[attachment.convert_status] }}
      </div>

      <img
        v-if="attachment.file_type === 'document' && attachment.convert_status === 'success'"
        width="30"
        height="30"
        style="margin-left: 8px;"
        @click="previewDoc"
        src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAMAAAAM7l6QAAAAVFBMVEXl5uzm5+
        vm5+vn5+ff3+cAAAD////l5uv09PZtbm7V1tbAwcGsrKxiZWXg4OC2t7ehoqJ3eXltb2/q6uqXmJhYWlrq6+uCg4PL
        y8u2traMjo6Bg4MzJRRJAAAABnRSTlPvv68gIACj4loSAAAAp0lEQVQoz83TyxaDIAwE0IgyEl6Cgrb2//+zuAbp6c7
        Z3iSbnCE5DjS3IyZJIyncRNFEouitl8PohPo8P4e91vsdK+scc46myXuM+hoyzrY4JqjDxRc2fte8sEIKyjsN7faKVw
        NkBYSyeoSKDws4DyRzjVbss8ey2sSAZVRcTMPbRSHwVnPxfBr9smvRFmMLH+bTPPOhfzP1eejVQPwqkRzF3A4Nk/wCd
        XwYc/1RucQAAAAASUVORK5CYII="
      />
    </div>

    <div v-else-if="isMedia" :id="'player' + attachment.id + '-' + randomId"></div>

    <van-popup
      v-if="!isMedia"
      v-model="isShow"
      closeable
      :close-on-click-overlay="true"
      close-icon="close"
      position="bottom"
      get-container="body"
      :style="{ height: '70%' }"
    >
      <div style="height: 52px;display: flex;justify-content: center;align-items: center;color: #999;">{{ attachment.file_name }}</div>
      <div :id="'player' + attachment.id + '-' + randomId" style="width: 100%;height: calc(100% - 52px)"></div>
    </van-popup>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState } from 'vuex';

const icons = {
  "audio": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFYAAABqCAMAAAARMFYTAAAAWlBMVEUAAABgt19guV5gt1xguV6Z5JeV5JVgu15gul1guV1gul5guV5guV1gr2BguV5gt1pguVxguV2K2Ydgu11gu111xXBgr1Bgul6Z5JeL2ol8z3p2y3RowWaD1IHYnUSrAAAAF3RSTlMAIIBAoO8w779g399vEJAwUK/vr3AwEFCtqHYAAAFrSURBVGje7dhbbsMgEIVhwG4B39Mbdtzuf5t1IlUnkotcmzMPafgX8GmEeGBQ8vXe7Oj9T+aHsWFXL89qu8aGnU1P264Ju5tGuAQV7JbrwyF2y62OsXAJw4LdcF8PshuuTWDjrg4JLFwaC5fLwuWycLksXC4Ll8vC5bJwuSxcLguXy8LlsnC5LFwuCzeR/Rojbho7jzE3iQ1j1E1i57ibwoZz3E1hw+cUc8Eeg+fzr4Glltn/zXa6aDpnyWz5sxENsAlsoW5qfU1lkaGyyMmwgwyr74r1PNbY4Drfq6WiIrEY0LrSEe9tGVBmM5vZzGY2s2JsK8KWSoJ1LZN1Ta8vLSiPrQuFaGzdKgHWaiXBdkqELdasI7B6zVYi0/aBwJrI0bJvQsNZTiu9UlNYuB77x5sjrtLVySyVp+ruFv/MZjazj8AqK8M6GXaQYVXNVusrW7BZj09IXngIGL56zdPO1zbqJl1SYGvai/YNvdG0cw9LsZcAAAAASUVORK5CYII=",
  "video": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFYAAABqCAMAAAARMFYTAAAAaVBMVEUAAACHaM+KaNSJatPLvOvKuuqHaNOKatSLatWMatSLadSMatWAcM+LadSLatOObtOLatSMatSKadKLa9SLadSKatSLaNO7peSKatOMataPcM+LatTLvOu7p+Wrk9+njt6VdtizneKfg9tHAHakAAAAG3RSTlMAIGCA7zBAn9/vcH8Qr4Awz59Qv7+QQO+QbxCNFqUZAAABZklEQVRo3u3Yy1LDMAyFYdkFaqdp06QXLkpa4P0fEqabAwyOk1jadPSvPd9CG9si/Rq/mlEzyaw3kWf1/Ej5XiPPbHjIuxue3dDDFVDB5lzPi9icG5axGdfzMjbjbheyGTcWsGnXcQELV4yFK8vClWXhyrJwZVm4sixcWRauLAtXloUry8KVZeHKsnBlWbiF7EefcMvYa59yi1juk24R+5l2S1i+pN0Slt+HlAt2GXy9/BtY0Yw11lhjjZ3IxhefqAr8u+D3U9l9Tel2/LNTDSHHOhrriIOxgpBltzRahQE0NINd07Qf/bYmBbYikmfDmTRYRxpsSypspcN6Y429sScdNjoVloNTYTl6FZZ5I8pWuPPcHDa+jbIHnAxdnkXthN00BtFkWBT8UyJ/+Hufdi3f52PJWGONNdZYY41VYinqsEcdttNhKUir4caepVmPpY5gKyK48iqRF5gvdk3IrYMIuqvpuy+fb/cAONL0ZgAAAABJRU5ErkJggg==",
  "ppt": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADoAAABGCAMAAACqhXegAAAAWlBMVEUAAADfYDDdXjDxt6LfXzHeXzHeXzHbXDDeXzHcXTDfYDDeXjDdXjDfYDDeXzHaXjDfXjHfXjLcYDDbYDDtpYzcYDDPYDDeXzHwt6LurJTokXDicknvspvroYYvBnudAAAAF3RSTlMAIICfv9+QQO9gMKBwEM8wsH9gQJ9QEEr+jEsAAAEESURBVFjD7dXJrsIwDIXhhJuEdGS64DK9/2tCjpCQ2gLOgQ2o/86LT17Uasy77WoX5Vn/j+TCy4v2fw9klJe0G7XWi4KO2lo0dNS2Sjq0jSjp0FolhSUpLElhSQpLUliSwpIUlqSwLIXV00PXs3p67AYWlFgLq6RyPJ96FpTrR2iw5lZTbqscuuz9DCo9nZleM5LCchQ5Lb0viq4A3eRStIJ1DJVNGgNFQxoLilY8xVhTFFdZETSuTcpn0LVLLYOFLPhr8jQNQlFIks5byaN2jorgBFHfdaITfYO6RB1FZbWw4aue5ol+kjY8NZ6TLe6cqr7Shlrr8YyVMV/G0qDSZ++8ygvTTihSQvuaXQAAAABJRU5ErkJggg==",
  "pptx": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADoAAABGCAMAAACqhXegAAAAWlBMVEUAAADfYDDdXjDxt6LfXzHeXzHeXzHbXDDeXzHcXTDfYDDeXjDdXjDfYDDeXzHaXjDfXjHfXjLcYDDbYDDtpYzcYDDPYDDeXzHwt6LurJTokXDicknvspvroYYvBnudAAAAF3RSTlMAIICfv9+QQO9gMKBwEM8wsH9gQJ9QEEr+jEsAAAEESURBVFjD7dXJrsIwDIXhhJuEdGS64DK9/2tCjpCQ2gLOgQ2o/86LT17Uasy77WoX5Vn/j+TCy4v2fw9klJe0G7XWi4KO2lo0dNS2Sjq0jSjp0FolhSUpLElhSQpLUliSwpIUlqSwLIXV00PXs3p67AYWlFgLq6RyPJ96FpTrR2iw5lZTbqscuuz9DCo9nZleM5LCchQ5Lb0viq4A3eRStIJ1DJVNGgNFQxoLilY8xVhTFFdZETSuTcpn0LVLLYOFLPhr8jQNQlFIks5byaN2jorgBFHfdaITfYO6RB1FZbWw4aue5ol+kjY8NZ6TLe6cqr7Shlrr8YyVMV/G0qDSZ++8ygvTTihSQvuaXQAAAABJRU5ErkJggg==",
  "doc": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADkAAABHCAMAAACK7h8GAAAAUVBMVEUAAABOhuVPh+ZNheRMg+NOhuarx/NMhOROhuZOhudNheRNhuZNh+dOheZNheRQgN9AgN9Ph+dPh+ZOhuZOh+dOhuapxvOFru5wnuujwvJ8puzGavmoAAAAFXRSTlMAoJ9gQO9/IN+/kHCvgDAQEJ9Qz9/RbuuFAAABr0lEQVRYw+3W2XKFIAwAUCpwBRTX0u3/P7TXECe0Q2PK051p8yIuxwRUUGFMmotN/RBLtImNt1sd9o5TIF9uDORlla5JIGu0TxJZo4NI1uiTTAJtkUhbJNJWSVQuibZKonJJtFUSlUuiLRKpWKb3CpXJDxREpTK9VihIif0WIJvir8owzzHVYpyVtozs1D18BdpeKRUZGb/MhtFod6Y8TpirnBPuDMXF+jihGWnVEdif/mjvRXtkZJroI/BwlwBth3dk5ABVURPL7bDJSEg0Y3pKtMHIsdIuZ+dcOdvAUc/KZM7OdSg1VcLLcPbIoJzpIC93uNri88EisRBOUpdGWmDxLvuV1HB13ix5Ufd5w0p6dPDSrJDsmR4yJ/Fp5GJt7uAEd7mUOdsMmfOgAlTuWg70v4MFAE+8pDcd0/Q0wtfSUpqiAC+QyVAaKiBJZCzS2CW3jUj68rduUxBBJNNyfiT0yewyuSmadSzml8mAkwGN1yaUDseExmu9lPS9LL6Y3Xv5ijSuliq47zzoKvgvH0OGZmmapXJN0KmGpDSfxwaoFcTmfluqAQc2dPII4D4BjNsDzzvczhYAAAAASUVORK5CYII=",
  "docx": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADkAAABHCAMAAACK7h8GAAAAUVBMVEUAAABOhuVPh+ZNheRMg+NOhuarx/NMhOROhuZOhudNheRNhuZNh+dOheZNheRQgN9AgN9Ph+dPh+ZOhuZOh+dOhuapxvOFru5wnuujwvJ8puzGavmoAAAAFXRSTlMAoJ9gQO9/IN+/kHCvgDAQEJ9Qz9/RbuuFAAABr0lEQVRYw+3W2XKFIAwAUCpwBRTX0u3/P7TXECe0Q2PK051p8yIuxwRUUGFMmotN/RBLtImNt1sd9o5TIF9uDORlla5JIGu0TxJZo4NI1uiTTAJtkUhbJNJWSVQuibZKonJJtFUSlUuiLRKpWKb3CpXJDxREpTK9VihIif0WIJvir8owzzHVYpyVtozs1D18BdpeKRUZGb/MhtFod6Y8TpirnBPuDMXF+jihGWnVEdif/mjvRXtkZJroI/BwlwBth3dk5ABVURPL7bDJSEg0Y3pKtMHIsdIuZ+dcOdvAUc/KZM7OdSg1VcLLcPbIoJzpIC93uNri88EisRBOUpdGWmDxLvuV1HB13ix5Ufd5w0p6dPDSrJDsmR4yJ/Fp5GJt7uAEd7mUOdsMmfOgAlTuWg70v4MFAE+8pDcd0/Q0wtfSUpqiAC+QyVAaKiBJZCzS2CW3jUj68rduUxBBJNNyfiT0yewyuSmadSzml8mAkwGN1yaUDseExmu9lPS9LL6Y3Xv5ijSuliq47zzoKvgvH0OGZmmapXJN0KmGpDSfxwaoFcTmfluqAQc2dPII4D4BjNsDzzvczhYAAAAASUVORK5CYII=",
  "pdf": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADoAAABGCAMAAACqhXegAAAAWlBMVEUAAADeYDDeXzHxt6LbYDDeXzHfXzDeXjDfXzHbXjDdXzDeXzHdXjDcXTDeXzHcYDDcXzDaYDDfXjLfYDDfXzHfXzHtpYzeXzHwt6LurZXicknroYbplnfni2kovmegAAAAF3RSTlMAIN+fEO+gcL9AgM+QYLBgUDB/MI/PnwtaBZ0AAAGDSURBVFjD7dbZboMwEIXhY9c7mCVJO+n2/q/ZAUUilZNgT29aqb/EDdKnAYMw+GnGB0uPerknZ007vT7dkZZ26fmmVZoq6E3rqYbetF0lLa2hSlpaVU3ZiilbMWUrpmzFlK2YshVTtmLKVkzZNtCPc2Fr6du5sLWUPgtbS3nue3G/TIX9PmqDFlIPwIuow5KT0AnoM6KERsA5KCkdAOkFDzAS6gHfYxaucBwxSWgHGIMkoWTABRGN4LSITuCShGosGSugI3Bg69upXiYmtn0zPQGZaGabWqkCNM9WbEeX/DimvqujDohEq9062hqqLm+DHQy2/A7dhto+Ys04TdRlqAqqgOfgrwdOLqQKaj1g5lXEQ7BO4VLeo8lgnWeiu6xLyKue9WPaKWDiI57st9ODCzsPxxngMO5vCiUdwNIBCK1UK5baAJ5aaQ/khStqphnoF6nb6QBgk7u02N6yle2vXbB/9ofgn15RI6fQMtkxTTLqmRrRWK0uH/Xm7BFYrW6eyfIL5G0tj9NKLzsAAAAASUVORK5CYII=",
  "xls": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADkAAABHCAMAAACK7h8GAAAAXVBMVEUAAABIol5Iol1In2BIo1+t17dIo19Iol5An2BHo19KpGJHol9Io19Iol1Jo19Io19Ho19Jo15Ho15Jol5FpWBHo2BHomBJo1xFn1pIo1+s1bdttoCHwpZ+vo5RqGcUvkUFAAAAGXRSTlMAoGAg33+AQBCvUO+/cO/P75CQkDCQcFAwUSvXtgAAAVVJREFUWMPt1dlug0AMBVCHcWZhCWmSLm6b/P9ntgyImwqa2vPUSLkvLOJoZDAemvJW3UpNvyT6vdzMx3YdOhb5Q75vjRBylW5EIdeoE51c0k4pl3SjkqBWCWqVoFYJapWgZglqlqBmCWqWoGYJqpYXIFCVlPOCQpqpVsrl/PkzkNY8ZJF8js4L0p9i1ahkou+k+ZIdEXmV9IOMLFMGSEG9Jp71+apSSalpyFM+b7H3KOTO0ZB2LHJILxqJhRqRKp95/ffsMqjFo2SllJDJC4pUS47XE9kiJc3QW/u2w85skfg0kc1SiqWnMcEq29I3xI7mtGqJpn+dutAgp66bjrVe8tx1AT+cRjZjkWnuwtgr5fWvlVCqdpo4dGHhBNudjBMMtznqJ5ineBQkOQpcuDvs//OO9JB3LI/FMhRL4iLIVLqow6SwpaKcmo2OQ3bZHjb6HLL7Ap4mQ8r/wUVpAAAAAElFTkSuQmCC",
  "xlsx": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADkAAABHCAMAAACK7h8GAAAAXVBMVEUAAABIol5Iol1In2BIo1+t17dIo19Iol5An2BHo19KpGJHol9Io19Iol1Jo19Io19Ho19Jo15Ho15Jol5FpWBHo2BHomBJo1xFn1pIo1+s1bdttoCHwpZ+vo5RqGcUvkUFAAAAGXRSTlMAoGAg33+AQBCvUO+/cO/P75CQkDCQcFAwUSvXtgAAAVVJREFUWMPt1dlug0AMBVCHcWZhCWmSLm6b/P9ntgyImwqa2vPUSLkvLOJoZDAemvJW3UpNvyT6vdzMx3YdOhb5Q75vjRBylW5EIdeoE51c0k4pl3SjkqBWCWqVoFYJapWgZglqlqBmCWqWoGYJqpYXIFCVlPOCQpqpVsrl/PkzkNY8ZJF8js4L0p9i1ahkou+k+ZIdEXmV9IOMLFMGSEG9Jp71+apSSalpyFM+b7H3KOTO0ZB2LHJILxqJhRqRKp95/ffsMqjFo2SllJDJC4pUS47XE9kiJc3QW/u2w85skfg0kc1SiqWnMcEq29I3xI7mtGqJpn+dutAgp66bjrVe8tx1AT+cRjZjkWnuwtgr5fWvlVCqdpo4dGHhBNudjBMMtznqJ5ineBQkOQpcuDvs//OO9JB3LI/FMhRL4iLIVLqow6SwpaKcmo2OQ3bZHjb6HLL7Ap4mQ8r/wUVpAAAAAElFTkSuQmCC",
  "other": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADoAAABGCAMAAACqhXegAAAAUVBMVEUAAABPh+eqxfROh+ZNheROhuZOhudMg+NQgN9OheVNhuZOhuZOhuVOh+ZNheRAgN9PiOaYuPFMheROh+dOhuapxvOevvKBqu2Stu9llulfkuiwbEQLAAAAFHRSTlMAIJ/fMO+/QBCAcM+gn2AQr5+Qfy57yBcAAAEMSURBVFjD7dfZbsQgDIVhU4clZOtCur3/g3Z6U1clObGpNJqR8t9/QkgIA/23JQdfUE97suNy0OvDjvTlkK6b1nFR0E2bi4Zu2kFJa5uKktbW6ahYMxVrpmLNVKyZirVTsXYq1k7F2qlYA31bK6ulH2tlhaqXFSsU9/le7RdR3EnvlXIIg2+gISb6Lo5GypF+cqOFDol+1+spixSro47+FpS0p6qopI7qgob6DB4NkPaJtpv5gM60m2NIewJ1iDLBRkAnTCOgGVMH6NxOnzHtAA2YZkBLRNIxouwAfSyAIrtMh8d/igvVdS/+9u/hk570ejS1U+I2OcjYtZYvNHGLZAe+vjDfyey0xRf5BceC9BjIhDOVAAAAAElFTkSuQmCC"
}

const playerInitQueue = [];
const playerList = [];
let queueLength = 0;
let i = 0;
const startInitAllPlayer = () => {
  if (i > queueLength) return;

  const currentPlayer = playerInitQueue[i]();

  playerList.push(currentPlayer);

  currentPlayer.on("ready", () => {
    i++;
    startInitAllPlayer();
  });

  currentPlayer.on("playing", () => {
    playerList.forEach(player => {
      if (player !== currentPlayer) {
        player.pause();
      }
    });
  });
};

const disableVideoIcon = `data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADgAAAA4CAMAAACfWMssAAA
AOVBMVEUAAADPz8/MzMzMzMzIyMjMzMzLy8vLy8vNzc3MzMzMzMzKysrNzc3MzMzLy8vNzc3MzMzMzMzMzMwPcwYrAAAAEnR
STlMAEM/fIO9wQKC/YDB/kICPUG9Rq5a2AAAB1ElEQVRIx82WW7aDIAxFDe+3tvMf7G1RbogBbf+aL2Gxk0NIwOVXDDazupe
txsLnlF3lszOx2o9iJfFkJsMt99CE+BSNGI2j6iJcW6S92RS8dEdb/D+aJhi4Y4E3QOZD07GOOXFgdpBneSQYBuDOaTOWY/S
MXG8yoPag7jyfmENG7pLMaZZx0yREMilR5wUpq/d+KtS8MI6p1WexEicuzdQAQANKcnBhHL6WUaEB7SnHCQagxV3iqDM9bQjf
x8jvAVlFGoLvMvfFBgRs5s4oaEwH1NJeKNhMp5FWwC0WBmLvc60bfloGovle74ZHnrECGci3qjA7DlVzkOUXqiMElwuQ9uGXY
Mbi+Fwq7RzzHmFy1CWYe78qADsZBvJ7j53MFJRD7L7kCjCAtKOeFDnDestM63xzvK09a2QdujfzCP0QpHDFucwN3Zxrd256J6
tTX1hIG4A4rmTlyJGDvr4eSwXE8bzw+1JdXf7NdORvnIQbknNL3MXcksih2EpO1abpk+yw02ccJ/lTzrk5CWLaDWH3uS/wg5pt
TgMMxOh4uFacLNi7xUakfeWaKJj9WqDJdByWfMrY/qHKMrRA0dg8Avm4R7flG7NZ4AX+pYE1xbmslh+yP6jDPH3+K7RyAAAAAElFTkSuQmCC`

export default {
  name: 'itemBankResourcePreview',
  props: {
    attachment: {
      type: Object,
      require: true
    },
    canLoadPlayer: Boolean
  },
  data() {
    return {
      isLoaded: false,
      isShow: false,
      disableVideoIcon,
      resourceStatus: {
        'waiting': this.$t('attachment.waiting'),
        'doing': this.$t('attachment.doing'),
        'error': this.$t('attachment.error'),
      },
      randomId: Math.floor(Math.random() * 100)
    }
  },
  computed: {
    ...mapState({
      storageSetting: state => state.storageSetting
    }),
    isMedia() {
      return ['video', 'audio'].indexOf(this.attachment.file_type) > -1
    },
    isImmediatePreview() {
      return !this.disableVideo && this.isMedia && this.attachment.convert_status === 'success';
    },
    iconSrc() {
      const { ext, file_type } = this.attachment

      if (file_type === 'other') return icons.other

      if (file_type === 'document') return icons[ext]

      return icons[file_type]
    },
    disableVideo() {
      const { isEncryptionPlus, securityVideoPlayer } = this.storageSetting

      if (!isEncryptionPlus) return false

      if (securityVideoPlayer && !this.isWechat()) return true

      if (!securityVideoPlayer) return true

      return false
    }
  },
  watch: {
    canLoadPlayer(val, oldVal) {
      if (this.isLoaded) return

      if (!oldVal && val && this.isImmediatePreview) {
        this.loadPlayer()
      }
    }
  },
  mounted() {
    if (this.canLoadPlayer && this.isImmediatePreview) {
      this.loadPlayer()
    }
  },
  methods: {
    isWechat() {
      const ua = navigator.userAgent.toLowerCase();
      if (ua.match(/MicroMessenger/i) == 'micromessenger') {
        return true;
      } else {
        return false;
      }
    },
    loadPlayer() {
      if (this.isLoaded) return
      
      queueLength++

      const initPlayer = (data) => {
        this.isLoaded = true

        return new window.QiQiuYun.Player(
          Object.assign(data)
        );
      }

      Api.getItemDetail({ 
        params: { globalId: this.attachment.global_id } 
      }).then(res => {
        res.data.id = 'player' + this.attachment.id + '-' + this.randomId
        playerInitQueue.push(initPlayer.bind(null, res.data))

        if (playerInitQueue.length === queueLength) {
          startInitAllPlayer();
        }
      })
    },
    previewDoc() {
      this.isShow = true
      this.loadPlayer()
    }
  }
}
</script>

<style lang="scss" scoped>
  .attachment-preview {
    display: flex;
    align-items: center;
    height: 42px;
    padding: 0 12px 0 8px;
    font-size: 14px;
    color: #333;
    background-color: #F7F7F7;
    border-radius: 4px;

    &__icon {
      width: 24px;
      margin-right: 8px;
    }

    &__status {
      margin-left: 8px;
      font-size: 12px;
      color: #999;
    }
  }
</style>
