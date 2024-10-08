<script setup>
import BaseInfo from './BaseInfo.vue';
import {ref} from 'vue';
import BaseRule from './BaseRule.vue';
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import MarketSetting from './MarketSetting.vue';

const manageProps = defineProps({
  isUnMultiCourseSet: Number,
  course: Object,
  tags: Object,
  imageSrc: String,
  imageSaveUrl: String,
  imageUploadUrl: String,
  enableOrg: Number,
  courseSet: Object,
  lessonWatchLimit: Number,
  uploadMode: String,
  audioServiceStatus: String,
  videoConvertCompletion: Number,
  courseSetManageFilesUrl: String,
  freeTasks: Object,
  canFreeTasks: Object,
  taskName: String,
  activityMetas: Object,
  canFreeActivityTypes: String,
  freeTaskChangelog: String,
  canModifyCoursePrice: Number,
  liveCapacityUrl: String,
  serviceTags: Object,
  vipInstalled: String,
  vipEnabled: String,
  vipLevels: Object,
});

const baseInfoRef = ref(null);
const baseRuleRef = ref(null);
const marketSettingRef = ref(null);

const submitForm = async () => {
  if (baseInfoRef.value && baseRuleRef.value ) {
    const baseInfo = await baseInfoRef.value.validateForm();
    const baseRule = await baseRuleRef.value.validateForm()
    console.log(baseInfo);
    console.log(baseRule);
  }

};


</script>

<template>
  <ant-config-provider>
    <base-info
      ref="baseInfoRef"
      :manage="manageProps"
    />
<!--    <base-rule-->
<!--      ref="baseRuleRef"-->
<!--      :params="baseRuleProps"-->
<!--    />-->
<!--    <market-setting-->
<!--      ref="marketSettingRef"-->
<!--      :params="marketSettingProps"-->
<!--    />-->
    <a-button @click="submitForm">获取表单值</a-button>
  </ant-config-provider>
</template>

<style scoped lang="less">

</style>
