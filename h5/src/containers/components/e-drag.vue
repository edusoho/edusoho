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
          <img src="static/images/drag.png" alt="">
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
      },
      dragToEnd: false,
    }
  },
  created() {
    this.initDragCaptcha();
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
          maskWidth: 0,
        })
        this.dragToEnd = false;
      })
    },
    // sendSmsCenter() {
    //   Api.getSmsCenter({
    //     data: {
    //       type: 'register',
    //       mobile: this.info.mobile,
    //       dragCaptchaToken: this.getToken()
    //     }
    //   }).then(res => {
    //     Toast.success('验证码发送成功');
    //     this.$emit('success', res);
    //     this.dragState = {};
    //   }).catch(err => {
    //     this.$toast(err);
    //     this.initDragCaptcha();
    //   })
    // },
    handletTouchEnd() {
      if (this.dragToEnd) {
        return;
      }
      if (this.dragState.currentLeft) {
        this.dragToEnd = true;
        Api.dragValidate({
          query: {
            token: this.getToken()
          }
        }).then(res => {
          // this.sendSmsCenter();
          Toast.success('验证成功');
          this.$emit('success', this.getToken());
        }).catch(err => {
          Toast.fail(err.message);
          this.initDragCaptcha();
        })
      }
    },
    handleTouchMove(e) {
      if (this.dragToEnd) {
        return;
      }
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

