<template>
<div>
  <div class="py-12 text-center text-37393D text-16 font-medium">{{ $t('contract.handwritten') }}</div>

  <div class="py-16 text-center text-37393D text-14">{{ $t('contract.acrossScreen') }}</div>

  <div class="box-container" :style="customBoxContainerStyle">
    <canvas id="canvas"></canvas>

    <div class="tip" :style="customTipStyle">{{ $t('contract.signTips') }}</div>
    <div class="bg-text" :style="bgTextStyle">{{ $t('contract.signScope') }}</div>
  </div>

  <div class="fixed left-0 right-0 bottom-0 flex items-center justify-center h-100">
    <div class="btn-list">
      <van-button type="primary" class="mb-16 rounded-md" @click="getPreviewImg">{{ $t('contract.submit') }}</van-button>
      <van-button class="rounded-md" @click="signature.clear()">{{ $t('contract.clear') }}</van-button>
    </div>
  </div>

  <div v-if="!isVertical" class="across-tips">
    {{ $t('contract.acrossTips') }}
  </div>
</div>
</template>

<script>
import SmoothSignature from "smooth-signature"
import { Toast } from 'vant'

export default {
  name: 'WriteSignature',
  data() {
    return {
      signature: null,
      customTipStyle: null,
      customBoxContainerStyle: null,
      bgTextStyle: null,
      isVertical: [0, 180].includes(window.orientation)
    }
  },
  created() {
    window.addEventListener('orientationchange', () => {
      this.isVertical = [0, 180].includes(window.orientation)
    })
  },
  mounted() {
    this.init()
  },
  methods: {
    init() {
      const canvas = document.getElementById("canvas")
      const topDistance = 102
      const bottomDistance = 100
      const canvasWidth = window.innerWidth - 104
      const canvasHeight = window.innerHeight - topDistance - bottomDistance

      this.signature = new SmoothSignature(canvas, {
        width: canvasWidth,
        height: canvasHeight,
        minWidth: 4,
        maxWidth: 10
      })

      this.customTipStyle = {
        right: -1 * ((window.innerWidth - 16 - 22 / 2) - (window.innerWidth / 2)) * 2 + 'px'
      }

      this.customBoxContainerStyle = {
        top: topDistance + 'px',
        bottom: bottomDistance + 'px'
      }

      this.bgTextStyle = {
        width: canvasHeight + 'px',
        height: canvasWidth + 'px',
        left: window.innerWidth / 2 - canvasHeight / 2 + 'px',
        top: canvasHeight / 2 - canvasWidth / 2 + 'px',
      }
    },
    getPreviewImg() {
      if (this.signature.isEmpty()) {
        Toast.fail(this.$t('contract.signEmpty'))

        return
      }

      const imageBase64 = this.signature.getRotateCanvas(-90).toDataURL()

      this.signature.clear()
      this.$emit('submit', imageBase64)
    }
  }
}
</script>

<style lang="scss" scoped>
.text-37393D {
  color: #37393D;
}

.across-tips {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  background-color: #fff;
}

.box-container {
  position: fixed;
  left: 0;
  right: 0;
  text-align: center;

  #canvas {
    margin: 0 auto;
    border: 1px dashed #86909C;
    border-radius: 8px;
  }

  .tip {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    top: 0;
    z-index: -1;
    font-size: 14px;
    color: #37393D;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: rotate(90deg);
  }

  .bg-text {
    position: absolute;
    right: 0;
    bottom: 0;
    z-index: -1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 80px;
    font-weight: 500;
    color: #F2F3F5;
    transform: rotate(90deg);
  }
}

.btn-list {
  display: inline-flex;
  flex-direction: column;
  transform: rotate(90deg)
}
</style>
