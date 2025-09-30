<script setup>
import { Modal } from 'ant-design-vue';
import AntConfigProvider from '../components/AntConfigProvider.vue';

const props = defineProps({
  displayBindPopUp: {required: true},
})

const showRemoveModal = () => {
  Modal.warning({
    title: '很抱歉，你无法使用该题库',
    content: `你已不是${props.displayBindPopUp.displayContent}学习，该题库你暂无使用权限，需要重新加入${props.displayBindPopUp.displayContent}或单独购买该题库`,
    okText: '我知道了',
    centered: true,
    onOk() {

    },
  });
};

if (props.displayBindPopUp.display === true) {
  showRemoveModal()
}
</script>

<template>
  <AntConfigProvider>
  </AntConfigProvider>
</template>
