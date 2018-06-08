<template>
  <div class="e-drag">
    <div class="e-drag-section">
      <div class="e-drag-img">
        <img :src="imgInfo.url" alt="" ref="dragImgBg">
        <img :src="imgInfo.jigsaw" alt="" 
          class="e-drag-img__dragable" 
          :style="{ left: `${dragState.currentLeft}px` }">
      </div>

      <div class="e-drag-bar" ref="bar">
        <span>{{tips}}</span>
        <div class="e-drag-bar__mask" 
          :style="{ width: `${dragState.maskWidth}px` }"></div>
        <div class="e-drag-btn" 
          ref="dragBtn"
          @touchend="handletTouchEnd"
          @touchmove="handleTouchMove" 
          :style="{ left: `${dragState.currentLeft}px`}">
          <img src="/static/images/drag.png" alt="">
        </div>
      </div>
      
    </div>
  </div>
</template>
<script>
import Api from '@/api';
import { Toast, Loading } from 'vant';

export default {
  props: {
    tips: {
      type: String,
      default: '拖动左边滑块完成上方拼图'
    }
  },
  data() {
    return {
      imgInfo: {
        url: '',
        jigsaw: '',
        token: ''
      },
      dragState: {
        left: 0,
        width: 0,
        currentX: 0,
        currentLeft: 0,
        btnWidth: 0,
        maskWidth: 0
      }
    }
  },
  created() {
    this.initDragCaptcha();
    this.$toast('提示文案');
  },
  mounted() {
    const bar = this.$refs.bar;
    const dragBtn = this.$refs.dragBtn;
    const barRect = bar.getBoundingClientRect();

    Object.assign(this.dragState, {
      left: barRect.left.toFixed(2),
      width: bar.clientWidth,
      btnWidth: dragBtn.offsetWidth / 2
    })
  },
  methods: {
    initDragCaptcha() {
      Api.dragCaptcha().then(res => {
        this.imgInfo = { ...res };

        Object.assign(this.dragState, {
          currentLeft: 0,
          maskWidth: 0
        })
      })
    },
    handleTouchMove(e) {
      e.preventDefault();

      const dragBtn = this.$refs.dragBtn;
      const bg = this.$refs.dragImgBg;
      const dragState = this.dragState;

      let pageX = e.clientX ?
        e.clientX.toFixed(2) :
        e.targetTouches[0].pageX.toFixed(2);

      let currentX = (pageX - dragState.left - dragState.btnWidth).toFixed(2);

      if(currentX < 0) currentX = 0;

      if(pageX > dragState.width)
        currentX = (dragState.width - dragState.left - dragState.btnWidth).toFixed(2);

      Object.assign(this.dragState, {
        currentLeft: currentX,
        maskWidth: (Number(currentX) + dragBtn.offsetWidth / 2).toFixed(2)
      })
    },
    handletTouchEnd() {
      if(this.dragState.currentLeft) {
        Api.dragValidate({
          query: {
            token: this.getToken()
          }
        }).then(res => {
          Toast.success('成功');
          this.dragState = {};
        }).catch(err => {
          Toast.fail(err.message);
          this.initDragCaptcha();
        })
      }
    },
    calPositionX() {
      const bg = this.$refs.dragImgBg;
      const rate = (bg.naturalWidth / bg.width).toFixed(2);
      const positionLeft = Number(this.dragState.currentLeft).toFixed(2);

      return (positionLeft * rate).toFixed(2);
    },
    getToken() {
      const dragToken = {
        token: this.imgInfo.token,
        captcha: this.calPositionX()
      };

      return [...btoa(JSON.stringify(dragToken))].reverse().join('');
    }
  }
}
</script>

