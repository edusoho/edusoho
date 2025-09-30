<script setup>
import {message} from 'ant-design-vue';

const modalVisible = defineModel();
const emit = defineEmits(['ok', 'cancel'])
const props = defineProps({
  shareUrl: {
    type: String,
    default: null,
  },
});

const fallbackCopyTextToClipboard = (text) => {
  const textArea = document.createElement('textarea');
  textArea.value = text;
  textArea.style.position = 'fixed';
  textArea.style.left = '-9999px';
  document.body.appendChild(textArea);
  textArea.select();
  try {
    const successful = document.execCommand('copy');
    if (successful) {
      message.success('复制成功');
    } else {
      message.success('复制失败');
    }
  } catch (err) {
    console.error('execCommand 方法复制失败：', err);
  }
  document.body.removeChild(textArea);
};

const copyToClipboard = async () => {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    try {
      await navigator.clipboard.writeText(props.shareUrl);
      message.success('复制成功');
    } catch (err) {
      console.error('Clipboard API 复制失败：', err);
    }
  } else {
    console.warn('Clipboard API 不支持，使用回退方法');
    fallbackCopyTextToClipboard(props.shareUrl);
  }
};

async function copyShareUrl() {
  await copyToClipboard();
  modalVisible.value = false;
  emit('ok')
}

function closeShareModal() {
  modalVisible.value = false;
  emit('cancel')
}
</script>

<template>
  <a-modal v-model:open="modalVisible" :centered="true" title="分享链接" ok-text="复制链接" cancel-text="取消" @cancel="closeShareModal" @ok="copyShareUrl">
    <div class="px-16 py-20 rounded-6 bg-[#FAFAFA]" v-text="shareUrl"></div>
  </a-modal>
</template>

