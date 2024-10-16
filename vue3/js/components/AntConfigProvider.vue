<template>
  <config-provider :theme="{ token }" :locale="locale">
    <slot></slot>
  </config-provider>
</template>

<script setup>
import { ConfigProvider } from 'ant-design-vue';
import { ref } from 'vue'
import { getCurrentPrimaryColor } from 'vue3/js/common';
import zh_CN from 'ant-design-vue/es/locale/zh_CN';
import en_US from 'ant-design-vue/es/locale/en_US';
import dayjs from 'dayjs';
import 'dayjs/locale/zh-cn';

dayjs.locale(window.app.lang);
const locale = window.app.lang === 'zh_CN' ? zh_CN : en_US

const token = ref({
  "colorPrimary": getCurrentPrimaryColor(),
  "colorSuccess": "#00b42a",
  "colorWarning": "#ff7d00",
  "colorError": "#f53f3f",
  "colorInfo": "#165dff",
  "colorText": "#37393d",
  "colorTextSecondary": "#5E6166",
  "colorTextTertiary": "#919399",
  "colorTextQuaternary": "#C0C0C2",
  "colorFill": "#D4D4D4",
  "colorFillSecondary": "#EBEBEB",
  "colorFillTertiary": "#F5F5F5",
  "colorFillQuaternary": "#FAFAFA",
  "colorBgSpotlight": "#37393d",
  "borderRadius": 4
})

</script>
