<template>
  <div v-if="tagData.isShow" class="e-tag-link" :class="tagData.className">
    <a :href="tagData.link">
      <span>赚{{tagData.money}}元</span>
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
        isShow: false,
        money: '',
        link: '',
        className: '',
      }
    }
  }
};
</script>

