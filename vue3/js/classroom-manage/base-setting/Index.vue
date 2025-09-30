<script setup>
import BaseInfo from './BaseInfo.vue';
import MarketSetting from './MarketSetting.vue';
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import {ref} from 'vue';
import Api from '../../../api';
import {message} from 'ant-design-vue';

const manageProps = defineProps({
  classroom: Object,
  tags: Array,
  enableOrg: Number,
  cover: String,
  imageUploadUrl: String,
  courseNum: {type: Number, default: 0},
  coursePrice: {type: Number, default: 0},
  coinSetting: {type: Object, default: {}},
  classroomExpiryRuleUrl: String,
  vipInstalled: {type: Boolean, default: false},
  vipEnabled: {type: Number, default: 0},
  vipLevels: {type: Array, default: []},
  serviceTags: {type: Array, default: []},
  infoSaveUrl: String,
});

const baseInfoRef = ref(null);
const marketSettingRef = ref(null);

const submitForm = async () => {
  const baseInfo = await baseInfoRef.value.validateForm();
  const marketSetting = await marketSettingRef.value.validateForm();
  if (baseInfo && marketSetting) {
    const coversResult = {};
    if (baseInfo.covers) {
      baseInfo.covers.forEach((item, index) => {
        coversResult[`covers[${index}][type]`] = item.type;
        coversResult[`covers[${index}][id]`] = item.id;
        coversResult[`covers[${index}][url]`] = item.url;
        coversResult[`covers[${index}][uri]`] = item.uri;
      });
    }
    const serviceResult = {};
    if (marketSetting.service.length > 0) {
      marketSetting.service.forEach((item, index) => {
        serviceResult[`service[${index}]`] = item;
      });
    }
    const tagsResult = {};
    if (baseInfo.tags.length > 0) {
      baseInfo.tags.forEach((item, index) => {
        tagsResult[`tags[${index}]`] = item;
      });
    }
    let params = {
      _csrf_token: $('meta[name=csrf-token]').attr('content'),
      ...coversResult,
      ...serviceResult,
      ...tagsResult,
      ...baseInfo,
      ...marketSetting,
      expiryValue: marketSetting.expiryMode === 'date' ? new Date(marketSetting.expiryValue).getTime() : marketSetting.expiryValue,
    };
    delete params.covers;
    delete params.freeTaskIds;
    delete params.service;
    delete params.tags;
    await Api.classroom.updateClassroom(manageProps.classroom.id, params);
    message.success('保存成功');
    setTimeout(() => location.reload(), 1000);
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
    >
    </base-info>
    <market-setting
      ref="marketSettingRef"
      :manage="manageProps"
    >
    </market-setting>
    <a-button type="primary" @click="submitForm" class="ml-200 mb-24">保存</a-button>
  </ant-config-provider>
</template>

<style lang="less">

</style>
