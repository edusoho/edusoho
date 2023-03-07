<template>
  <div :id="'player' + attachment.id" style="margin-top: 8px;">
    <div v-if="!isImmediatePreview && isShow" class="attachment-preview">
      <img :src="iconSrc" class="attachment-preview__icon" />
      <div class="text-overflow" style="flex: 1;">{{ this.attachment.file_name }}</div>
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
  </div>
</template>

<script>
import Api from '@/api';

const icons = {
  "audio": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAkCAMAAACpD3pbAAAAM1BMVEUAAABguV5gt1pgt1xgul5gul1gul5gul5gul1guVxgul5guF1gtVtgtVpguV5gu11gul49TWCCAAAAEHRSTlMAgCBA32Cf779Qz3AQMN+vikAzXAAAAHlJREFUOMvV0TsShDAMBFHZZiX/WOb+p8U4AgpNSBWdvrBllMK1KKd6xq3l7AowB6iDO7iDO7iDO7iDOGfc2bIxbiKxqMs/Oao+l1VkcTkBMFDG19hq3qLL/7nX5SaUw2T1GG2dszyevcvGWX3Vwb16mrsclfBYGrQDaJI09WzYegAAAAAASUVORK5CYII=",
  "video": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAkCAMAAACpD3pbAAAASFBMVEUAAACJatOJadKLa9THt+fMvOyLatSMatSJaNOIZdKAcM+LatSnjd6LatWKatSLadSKatSMadKPcM+LatTLvOurkt/Dseibfto71v3aAAAAE3RSTlMAgB+/IN/PoEAwEO/f35BwYFAQo4R7rgAAAIhJREFUOMvN0ksOwjAMRVEnASctf9zC/nfKA4rkOsSTqlLv7OkMbUIlzNuRqk9i2mvPYhu0S81wl+Euw12Guwxv8EN7zeOgXOqe47ejYdsmOMepjrG4GO7oVxA5ExmOii/kclzExedTrPg6e81gWdIUfxZv6CQr8cHn3NYMvnNLU0/vbuFvBfQCW9c6ghxd79sAAAAASUVORK5CYII=",
  "ppt": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAkCAMAAACpD3pbAAAAS1BMVEUAAADdXjDbXjDvt5/wt6LZYDDcXzDcWzDeXzHfXzHeXjDeXzHeXzHeXjHdXjDfXjLmhWHdXzLcYDDeXzHwt6Lni2nurJTmhWLjdU1ExSZpAAAAE3RSTlMAgEAg3xBgIN+/oO/PsJB/349QqmsWAwAAAJVJREFUOMvNzzkWwjAMRdFvgzxlAhyG/a8U22lEHKnk5BVqbqOPkjW/XcCimHdduae8b+Weey6ucXWFmyvcXOHmAr+49/xemee+z3PrVlmJc5hNKQWBLbaMzpgFtuWOHjTInCcgKhyBSWEHhGMm55wFnPq5DwKT994uQxaHtc7Hd1pkbv2bB52TrKkwjZJGQu1hDrOFvrTiPJ9UkjnhAAAAAElFTkSuQmCC",
  "pptx": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAkCAMAAACpD3pbAAAAS1BMVEUAAADdXjDbXjDvt5/wt6LZYDDcXzDcWzDeXzHfXzHeXjDeXzHeXzHeXjHdXjDfXjLmhWHdXzLcYDDeXzHwt6Lni2nurJTmhWLjdU1ExSZpAAAAE3RSTlMAgEAg3xBgIN+/oO/PsJB/349QqmsWAwAAAJVJREFUOMvNzzkWwjAMRdFvgzxlAhyG/a8U22lEHKnk5BVqbqOPkjW/XcCimHdduae8b+Weey6ucXWFmyvcXOHmAr+49/xemee+z3PrVlmJc5hNKQWBLbaMzpgFtuWOHjTInCcgKhyBSWEHhGMm55wFnPq5DwKT994uQxaHtc7Hd1pkbv2bB52TrKkwjZJGQu1hDrOFvrTiPJ9UkjnhAAAAAElFTkSuQmCC",
  "doc": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB0AAAAkCAMAAABCOMFYAAAAV1BMVEUAAABNheRNhuZPh+dOhuZMg+Onv++pxvROh+ZQgN9MhORMhuVMheRPhudOh+ZNhuZAgN9OhuZOheROheVOh+dunulqmuxNhOROhuapxvNklulxnut8pux5by1pAAAAGHRSTlMAYL+f70Ag398QUJAwH7BwEM+QgN+wn3DMoQoDAAAA3UlEQVQ4y73R2W7EIAyF4Z8EWkL2SRd3ef/nbOKqQmyjXs25sCJ/EQYZmMc+ydsTMcFKms/nyIOXXD8iOyk1sqmoclOV26rc0siliqJyTb8jq+Znf/3mVbWV7n96cItdN++pBuaz+uldmyypjvAismo9wKfaQS8ycQ1wTPmttvPL684srLkaBukB7AJdriN0jg16AzZXDweYc8CEk1xlZoDOXPVW6gp/o/dSFyCI1X9KteiTHYRSte/17n1NR5wesfmaym71afb+fh+i0x3VbTUTYPYNsytXnKklDPADXSRH2qZR5uAAAAAASUVORK5CYII=",
  "docx": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB0AAAAkCAMAAABCOMFYAAAAV1BMVEUAAABNheRNhuZPh+dOhuZMg+Onv++pxvROh+ZQgN9MhORMhuVMheRPhudOh+ZNhuZAgN9OhuZOheROheVOh+dunulqmuxNhOROhuapxvNklulxnut8pux5by1pAAAAGHRSTlMAYL+f70Ag398QUJAwH7BwEM+QgN+wn3DMoQoDAAAA3UlEQVQ4y73R2W7EIAyF4Z8EWkL2SRd3ef/nbOKqQmyjXs25sCJ/EQYZmMc+ydsTMcFKms/nyIOXXD8iOyk1sqmoclOV26rc0siliqJyTb8jq+Znf/3mVbWV7n96cItdN++pBuaz+uldmyypjvAismo9wKfaQS8ycQ1wTPmttvPL684srLkaBukB7AJdriN0jg16AzZXDweYc8CEk1xlZoDOXPVW6gp/o/dSFyCI1X9KteiTHYRSte/17n1NR5wesfmaym71afb+fh+i0x3VbTUTYPYNsytXnKklDPADXSRH2qZR5uAAAAAASUVORK5CYII=",
  "pdf": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAkCAMAAACpD3pbAAAAV1BMVEUAAADdXjDfXzHvt5/wt6LeXzHfXzDcXjDdYDDZYDDdXzDeXzHeXzHdXzDeXzHcYDDdYDDfXjPmhWHdXzLcXDDfYDDaYDDeXzHwt6Lni2nurJTmhWLjdU2d6ElZAAAAF3RSTlMAgL8g39+fQCAQYM+PcO9QgG/fn1AwMC6LBJIAAAC3SURBVDjLtdK3EgIxDEXRtwJnewNJS/j/7wQPFMZei4rbHs2okPDKDN/tUOSIq/alH7luLZ1bzi5wdomzS5xd4uwdvpXe8n0tnNse13eHzEIVx6QEJlyMwBoEkaMTWOFsBGaHJLGFFXcvmPtsPXvMgShssQYxG+TSBltDyQHwpNGwmmBhYlDeAkvN0Xmcxs+k4oo1krWqezEzeQTucoQj6d5q/PEO/+BRZt1XDcCFnpJDbho2MwCe+IVKcmKHq8sAAAAASUVORK5CYII=",
  "xls": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB0AAAAkCAMAAABCOMFYAAAAY1BMVEUAAABIol5Io19Io1+nz7et1bdIn2BAn2BHo19Iol9Iol9IpF9Iol9Ho19Ho19Fn2BJo15HpF9JomBIo11Jo2BHo15rtX5Io19nsnpJpGBFpWBIo1+s1bdttYBhr3V6vItbrHD5OzAeAAAAG3RSTlMAYL+fIN8gEO/fQN+Az68wkI9wb1BQsLCfcDAIVUyaAAAAuklEQVQ4y8XSNxLCMBBA0W8blJzIaUn3PyVgih3DykPHb7Z4o2IlAW5ZjTrM0LZBxl3nyr6WTz0rJ/lW5cJQZUuVLVXO6cCq2lnZ0Juyqna/vNupGpU/a1UNIyyioSV0r7mhNzR4/PNUA4Wh0kGSCC5YKmtYOajF1HACaHIb1R6S5DROqgNYZLSBtcO3pnbgpMxsFN3wP5ewMrSF4aIT/cQr1E1rqfZ/7Sd0D2VeN+BixsKRV6mw2np4AJ/xVV2LG928AAAAAElFTkSuQmCC",
  "xlsx": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB0AAAAkCAMAAABCOMFYAAAAY1BMVEUAAABIol5Io19Io1+nz7et1bdIn2BAn2BHo19Iol9Iol9IpF9Iol9Ho19Ho19Fn2BJo15HpF9JomBIo11Jo2BHo15rtX5Io19nsnpJpGBFpWBIo1+s1bdttYBhr3V6vItbrHD5OzAeAAAAG3RSTlMAYL+fIN8gEO/fQN+Az68wkI9wb1BQsLCfcDAIVUyaAAAAuklEQVQ4y8XSNxLCMBBA0W8blJzIaUn3PyVgih3DykPHb7Z4o2IlAW5ZjTrM0LZBxl3nyr6WTz0rJ/lW5cJQZUuVLVXO6cCq2lnZ0Juyqna/vNupGpU/a1UNIyyioSV0r7mhNzR4/PNUA4Wh0kGSCC5YKmtYOajF1HACaHIb1R6S5DROqgNYZLSBtcO3pnbgpMxsFN3wP5ewMrSF4aIT/cQr1E1rqfZ/7Sd0D2VeN+BixsKRV6mw2np4AJ/xVV2LG928AAAAAElFTkSuQmCC",
  "other": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAkCAMAAACpD3pbAAAAQlBMVEUAAABOheWnv++pxvROgN9Oh+ZMg+NOhuZOh+ZNheROhuhNhudMh+d1oexOhuZMg+VQheROhuapxvN6puyevvJllunsIICqAAAAEXRSTlMAgCDfEN9A759gr78g389QMEmY2GQAAACTSURBVDjL1dHJDsIwDIRhG+IsbVnSwvu/KmUTE9tBQuLS//qdRkNrkdt2BIWxqvbouepm9GoZ3GNwj8E9BvcY3fIF3fIyg1fbdXl2UKz7L4v0OXEgCkV8Tmd6NInLhV5Fj+Xz8WCZCSpJcaYmVhxbDr8xt1wUp4g6iRk2ML9Tw0zb4PSdc1/zyuHY0zHQvRO7xZVuGDMy6XnEIfAAAAAASUVORK5CYII="
}

const playerInitQueue = []
let queueLength = 0

let i = 0;
const startInitAllPlayer = () => {
  if (i > queueLength) return;

  const player = playerInitQueue[i]();

  player.on("ready", () => {
    i++;
    startInitAllPlayer();
  });
};

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
      isShow: true,
      resourceStatus: {
        'none': '转码中...',
        'fail': '转码失败'
      }
    }
  },
  computed: {
    isImmediatePreview() {
      return ['video', 'audio'].indexOf(this.attachment.file_type) > -1
    },
    iconSrc() {
      const { ext, file_type } = this.attachment

      if (file_type === 'other') return icons.other

      if (file_type === 'document') return icons[ext]

      return icons[file_type]
    },
  },
  watch: {
    canLoadPlayer(val, oldVal) {
      if (this.isLoaded) return

      if (!oldVal && val) {
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
    loadPlayer() {
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
        res.data.id = 'player' + this.attachment.id
        playerInitQueue.push(initPlayer.bind(null, res.data))

        if (playerInitQueue.length === queueLength) {
          startInitAllPlayer();
        }
      })
    },
    previewDoc() {
      this.isShow = false
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
      width: 32px;
      height: 32px;
      margin-right: 8px;
    }

    &__status {
      margin-left: 8px;
      font-size: 12px;
      color: #999;
    }
  }
</style>
