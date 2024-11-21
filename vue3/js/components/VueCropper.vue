<template>
  <div class="cropper-container" style="width: 400px; height: 400px;">
    <img class="mt-20 w-400 h-400" id="cropper-image" :src="src"/>
  </div>
</template>

<script setup>
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'
import {ref, onMounted, watch} from 'vue';

const props = defineProps({
  src: {
    type: String,
    required: true
  },
  aspectRatio: {
    type: Number,
    required: true
  }
})

const cropper = ref()
onMounted(() => {
  const image = document.getElementById('cropper-image')

  cropper.value = new Cropper(image, {
    aspectRatio: props.aspectRatio,
    autoCropArea: 1,
    crop(event) {
    }
  })
})

watch(() => props.src, (value) => {
  cropper.value.replace(value);
})

defineExpose({
  cropper
})
</script>
<style lang="less">

</style>
