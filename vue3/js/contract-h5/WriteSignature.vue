<template>
  <div class="header-box">这个是导航</div>

  <div class="py-16 text-center text-[#37393D] text-14">请横着屏幕手写</div>

  <div class="box-container" :style="customBoxContainerStyle">
    <canvas id="canvas"></canvas>

    <div class="tip" :style="customTipStyle">请确保“字迹清晰”并尽量把“签字范围”撑满</div>
    <div class="bg-text">签字范围</div>
  </div>

  <div class="fixed left-0 right-0 bottom-0 flex items-center justify-center h-100">
    <div class="btn-list">
      <a-button type="primary" class="mb-16" @click="getPreviewImg">提交</a-button>
      <a-button @click="signature.clear()">清空</a-button>
    </div>
  </div>

  <a-modal v-model:open="previewModalVisible">
    <img :src="previewImgUrl" style="max-width: 100%;">
  </a-modal>
</template>

<script setup>
import { ref, onMounted } from "vue";
import SmoothSignature from "smooth-signature";

onMounted(() => {
  init()
})

const previewModalVisible = ref(false)
const previewImgUrl = ref('')

const signature = ref()
const customTipStyle = ref()
const customBoxContainerStyle = ref()
const init = () => {
  const canvas = document.getElementById("canvas")
  const topDistance = 102
  const bottomDistance = 100
  const canvasWidth = window.innerWidth - 104
  const canvasHeight = window.innerHeight - topDistance - bottomDistance

  signature.value = new SmoothSignature(canvas, {
    width: canvasWidth,
    height: canvasHeight,
    minWidth: 2,
    maxWidth: 6
  })

  customTipStyle.value = {
    right: -1 * ((window.innerWidth - 16 - 22 / 2) - (window.innerWidth / 2)) * 2 + 'px'
  }

  customBoxContainerStyle.value = {
    top: topDistance + 'px',
    bottom: bottomDistance + 'px'
  }
}

const getPreviewImg = () => {
  previewImgUrl.value = signature.value.getRotateCanvas(-90).toDataURL()
  previewModalVisible.value = true
}
</script>

<style lang="less" scoped>
.header-box {
  height: 48px;
  line-height: 48px;
  text-align: center;
  border-bottom: 1px solid #F2F3F5;
}

.box-container {
  position: fixed;
  left: 0;
  right: 0;
  text-align: center;

  #canvas {
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
    left: 0;
    right: 0;
    bottom: 0;
    top: 0;
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
