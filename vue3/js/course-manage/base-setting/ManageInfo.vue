<script setup>
import BaseInfo from './BaseInfo.vue';
import {ref} from 'vue';
import BaseRule from './BaseRule.vue';
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import MarketSetting from './MarketSetting.vue';
import Api from '../../../api';
import {message} from 'ant-design-vue';

const manageProps = defineProps({
  isUnMultiCourseSet: {type: [String, Number]},
  course: Object,
  courseManageUrl: String,
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
  const baseInfo = await baseInfoRef.value.validateForm();
  const baseRule = await baseRuleRef.value.validateForm();
  const marketSetting = await marketSettingRef.value.validateForm();
  if (baseInfo && baseRule && marketSetting) {
    const coversResult = {};
    if (baseInfo.covers) {
      baseInfo.covers.forEach((item, index) => {
        coversResult[`covers[${index}][type]`] = item.type;
        coversResult[`covers[${index}][id]`] = item.id;
        coversResult[`covers[${index}][url]`] = item.url;
        coversResult[`covers[${index}][uri]`] = item.uri;
      });
    }
    const servicesResult = {};
    if (marketSetting.services.length > 0) {
      marketSetting.services.forEach((item, index) => {
        servicesResult[`services[${index}]`] = item;
      });
    }
    const freeTaskIdsResult = {};
    if (baseRule.freeTaskIds) {
      baseRule.freeTaskIds.forEach((item, index) => {
        freeTaskIdsResult[`freeTaskIds[${index}]`] = item;
      });
    }
    let params = {
      _csrf_token: $('meta[name=csrf-token]').attr('content'),
      ...coversResult,
      ...freeTaskIdsResult,
      ...servicesResult,
      ...baseInfo,
      ...baseRule,
      ...marketSetting,
      buyExpiryTime: new Date(marketSetting.buyExpiryTime).getTime(),
    };
    delete params.covers;
    delete params.freeTaskIds;
    delete params.services;
    await Api.courseSets.updateCourseSet(manageProps.courseSet.id, manageProps.course.id, params);
    message.success('保存成功');
    location.reload();
  } else {
    return 0;
  }
};
</script>

<template>
  <ant-config-provider>
    <base-info
      ref="baseInfoRef"
      :manage="manageProps"
    />
    <base-rule
      ref="baseRuleRef"
      :manage="manageProps"
    />
    <market-setting
      ref="marketSettingRef"
      :manage="manageProps"
    />
    <a-button type="primary" @click="submitForm" class="ml-200">保存</a-button>
  </ant-config-provider>
</template>

<style scoped lang="less">

</style>
