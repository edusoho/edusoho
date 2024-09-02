<script setup>
import { ref } from 'vue';
import router from './router';
import ContractList from './ContractList.vue';
import SignatureList from './SignatureList.vue';
import { t } from './vue-lang';

const activeTab = ref('contractList');

function createContract() {
  router.push({name: 'EditContract', query: { editType: 'create' }});
}
</script>

<template>
  <div class="flex flex-col p-24">
    <div class="flex justify-between">
      <div class="text-18 font-medium mb-24">{{ t('contract') }}</div>
      <a-button type="primary" @click="createContract">{{ t('createContract') }}</a-button>
    </div>
    <a-tabs v-model:activeKey="activeTab">
      <a-tab-pane key="contractList" :tab="t('contractManagement')">
        <contract-list />
      </a-tab-pane>
      <a-tab-pane key="signatureRecord" :tab="t('signatureRecord')">
        <signature-list />
      </a-tab-pane>
    </a-tabs>
  </div>
</template>
