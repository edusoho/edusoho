<script setup>
import BaseInfo from './BaseInfo.vue';
import MarketSetting from './MarketSetting.vue';
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import {ref} from 'vue';

const manageProps = defineProps({
  classroom: Object,
  tags: Array,
  enableOrg: Number,
  cover: String,
  imageUploadUrl: String,
});

const baseInfoRef = ref(null);
</script>

<template>
  <ant-config-provider>
    <base-info
      ref="baseInfoRef"
      :manage="manageProps"
    >
    </base-info>
    <market-setting
      :manage="manageProps"
    >
    </market-setting>
  </ant-config-provider>
</template>

<style lang="less">

</style>
