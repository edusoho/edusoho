<script setup>
import { ref } from 'vue';
import router from './router';
import ContractList from './ContractList.vue';
import SignatureList from './SignatureList.vue';

const activeTab = ref('contractList');

function createContract() {
  router.push({name: 'CreateContract'});
}
</script>

<template>
  <div class="flex flex-col p-24">
    <div class="flex justify-between">
      <div class="text-18 font-medium mb-24">电子合同</div>
      <a-button type="primary" @click="createContract">创建电子合同</a-button>
    </div>
    <a-tabs v-model:activeKey="activeTab">
      <a-tab-pane key="contractList" tab="电子合同管理">
        <contract-list />
      </a-tab-pane>
      <a-tab-pane key="signatureRecord" tab="签署记录">
        <signature-list />
      </a-tab-pane>
    </a-tabs>
  </div>
</template>
