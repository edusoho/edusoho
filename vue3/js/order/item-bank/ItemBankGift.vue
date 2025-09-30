<script setup>
import {CloseOutlined, RightOutlined} from '@ant-design/icons-vue';
import ItemBankListItem from './ItemBankListItem.vue';
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import {ref} from 'vue';

const props = defineProps({
  exerciseBind: Array,
})

const itemBankModalVisible = ref(false);
function showItemBankModal() {
  itemBankModalVisible.value = true;
}

function closeItemBankModal() {
  itemBankModalVisible.value = false;
}
</script>

<template>
  <AntConfigProvider>
    <div v-if="props.exerciseBind.length > 0" class="flex flex-col py-16 space-y-12">
      <div class="flex justify-between items-center">
        <div class="text-[#000000] text-14 font-semibold">赠送题库</div>
        <div v-if="props.exerciseBind.length > 2" class="flex items-center text-14 text-[#919399] font-normal cursor-pointer" @click="showItemBankModal">
          <div>{{ `查看全部（${props.exerciseBind.length}）` }}</div>
          <RightOutlined />
        </div>
      </div>
      <div class="flex flex-col space-y-12">
        <ItemBankListItem :item="props.exerciseBind[0]"/>
        <ItemBankListItem v-if="props.exerciseBind.length > 1" :item="props.exerciseBind[1]"/>
      </div>
    </div>
    <a-modal :width="900"
             v-model:open="itemBankModalVisible"
             :closable=false
             :zIndex="1050"
             :centered="true"
             :bodyStyle="{ 'height': '432px', 'overflow': 'auto'}"
             wrapClassName="item-bank-list-modal"
    >
      <template #title>
        <div class="flex justify-between items-center px-24 py-16 border border-solid border-[#F0F0F0] border-t-0 border-x-0">
          <div class="text-16 text-[#1E2226] font-medium">赠送题库</div>
          <CloseOutlined class="h-16 w-16" @click="closeItemBankModal"/>
        </div>
      </template>
      <div class="pt-24 px-24 pb-12">
        <div v-for="item in props.exerciseBind" class="flex flex-col">
          <ItemBankListItem :item="item" class="mb-12"/>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-center">
          <a-button @click="closeItemBankModal">关闭</a-button>
        </div>
      </template>
    </a-modal>
  </AntConfigProvider>
</template>

<style lang="less">
.item-bank-list-modal {
  .ant-modal {
    padding: 0 !important;
    .ant-modal-content {
      padding: 0 !important;
      .ant-modal-footer {
        border-top: 1px solid #ebebeb;
        padding: 20px 24px;
        margin-top: 0;
      }
      .ant-modal-header {
        padding: 0;
        margin-bottom: 0;
        border: none;
      }
    }
  }
}
</style>
