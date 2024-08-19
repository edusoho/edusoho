<template>
  <img class="mt-20 w-400" id="cropper-image" :src="src" />
</template>

<script setup>
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'
import {ref, onMounted, watch} from 'vue';

const props = defineProps({
  src: {
    type: String,
    required: true
  }
})

const cropper = ref()
onMounted(() => {
  const image = document.getElementById('cropper-image')

  cropper.value = new Cropper(image, {
    aspectRatio: 1/1,
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
