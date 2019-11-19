<template>
  <div v-if="tagData.isShow" :class="tagData.className" class="e-tag-link">
    <a :href="tagData.link">
      <span>赚{{ tagData.earnings }}元</span>
    </a>
  </div>
</template>

<script>
export default {
  name: 'ETagLink',
  props: {
    tagData: {
      type: Object,
      default: {
        earnings: 0,
        isShow: false,
        link: '',
        className: '',
        minDirectRewardRatio: 0
      }
    }
  }
}
</script>

