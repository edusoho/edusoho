<template>
  <div class="vip-sec__title">
    <div class="vip-sec__style">
      <span class="style style--first"></span>
      <span class="style style--second"></span>
      <span class="style style--third"></span>
    </div>
    <span class="vip-sec__text">{{ title }}</span>
    <div class="vip-sec__style">
      <span class="style style--first"></span>
      <span class="style style--second"></span>
      <span class="style style--third"></span>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    title: String,
  },
};
</script>
