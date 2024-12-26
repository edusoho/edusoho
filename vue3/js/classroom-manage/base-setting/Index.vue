<script setup>
import BaseInfo from './BaseInfo.vue';
import MarketSetting from './MarketSetting.vue';

const manageProps = defineProps({
  classroom: Object,
});
</script>

<template>
  <base-info
    :manage="manageProps"
  >
  </base-info>
  <market-setting
    :manage="manageProps"
  >
  </market-setting>
</template>

<style lang="less">

</style>
