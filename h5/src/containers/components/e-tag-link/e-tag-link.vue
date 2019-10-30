<template>
  <div v-if="tagData.isShow" class="e-tag-link" :class="tagData.className">
    <a :href="tagData.link">
      <span>赚{{ tagData.earnings }}元</span>
    </a>
  </div>
</template>

<script>
export default {
  name: "e-tag-link",
  props: {
    tagData: {
      type: Object,
      default: {
        earnings: 0,
        isShow: false,
        link: '',
        className: '',
        minDirectRewardRatio: 0,
      }
    },
    price: {
      type: String,
      default: 0,
    }
  },
};
</script>

