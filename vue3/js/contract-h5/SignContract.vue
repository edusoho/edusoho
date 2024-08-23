<template>
<div class="sticky top-0 w-full text-center h-48 leading-48 font-medium text-16 text-[#272E3B]" @click="routerBack">
  <svg class="absolute top-12 left-12" width="24" height="24" viewBox="0 0 24 24" fill="none">
    <path d="M15.5 18L9.5 12L15.5 6" stroke="#272E3B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
  {{ t('title') }}
</div>

<div class="px-16 py-16 pb-80">
  <div class="relative h-280 px-32 mb-24 rounded-8 border border-solid border-[#DFE2E6]">
    <div class="py-20" v-html="contractDetail?.content"></div>
    <div class="check-contract-detail" >
      <div class="inline-flex items-center" @click="checkContractDetail">
        <svg width="17" height="16" viewBox="0 0 17 16" fill="none">
          <path d="M3.84205 13.6576V6.32422H1.84204V13.6576H3.84205Z" :stroke="primaryColor" stroke-linejoin="round"/>
          <path fill-rule="evenodd" clip-rule="evenodd" d="M3.84204 6.32606C6.21634 4.15306 7.59231 2.90619 7.96997 2.58544C8.53644 2.10431 9.17671 2.30634 9.17671 3.5104C9.17671 4.71446 7.41487 5.40886 7.41487 6.32606C7.41374 6.33153 9.66681 6.3319 14.174 6.32716C14.7265 6.32656 15.1748 6.77393 15.1754 7.3264V7.32743C15.1754 7.8805 14.727 8.32883 14.174 8.32883H11.5042C11.1012 10.9864 10.8789 12.4313 10.8372 12.6634C10.7747 13.0116 10.4424 13.6594 9.48601 13.6594C8.84844 13.6594 7.18934 13.6594 3.84204 13.6594V6.32606Z" :stroke="primaryColor" stroke-linejoin="round"/>
        </svg>
        <span class="v3-text-primary ml-10">{{ t('contract_detail') }}</span>
      </div>
    </div>
  </div>

  <a-form :model="formItems" ref="formRef" :rules="rules">
    <a-form-item v-for="(a, key) in rules" :name="key" class="mb-16">
      <a-input v-if="key !== 'handSignature'" v-model:value="formItems[key]" :placeholder="t(`placeholder.${key}`)" size="large"></a-input>

      <template v-else>
        <div v-if="!formItems.handSignature" @click="writeSignatureVisible = true"
          class="flex items-center justify-center py-8 leading-24 border border-dashed border-[#B6BABF] rounded-6">
          <svg width="17" height="16" viewBox="0 0 17 16" fill="none">
            <path d="M2.83398 14H14.834" stroke="#5E6166" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M4.16602 8.90663V11.3333H6.60508L13.4993 4.43603L11.0644 2L4.16602 8.90663Z" stroke="#5E6166" stroke-width="1.5" stroke-linejoin="round"/>
          </svg>
          <span class="ml-8 text-16 font-medium text-[#37393D]">{{ t('handwritten') }}</span>
        </div>

        <div v-if="formItems.handSignature" class="flex">
          <div class="h-42 flex-1 text-center border border-dashed border-[#B6BABF] rounded-6">
            <img class="h-full" :src="formItems.handSignature" />
          </div>
          <div class="flex items-center ml-16 py-8 px-16 border border-solid border-[#fff]" @click="writeSignatureVisible = true">
            <svg class="mr-10" width="16" height="16" viewBox="0 0 16 16" fill="none">
              <path d="M2.33398 14H14.334" :stroke="primaryColor" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M3.66602 8.90663V11.3333H6.10508L12.9993 4.43603L10.5644 2L3.66602 8.90663Z" :stroke="primaryColor" stroke-linejoin="round"/>
            </svg>
            <span class="v3-text-primary">{{ t('reSign') }}</span>
          </div>
        </div>
      </template>
    </a-form-item>
  </a-form>
</div>

<a-drawer :open="writeSignatureVisible" :title="t('handwritten')" placement="bottom" rootClassName="text-drawer-container"
  @close="writeSignatureVisible = false;">
  <WriteSignature @submit="getWriteSignature" />
</a-drawer>

<div class="fixed bottom-0 w-full py-8 px-16 box-border flex">
  <van-button :disabled="!canSubmit" class="flex-1"
    type="primary" @click="submitForm"
    :loading="submitLoading" :loading-text="t('signing')">{{ t('confirmSign') }}</van-button>
</div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElectronicContract } from '../api'
import { getCurrentPrimaryColor } from '../common'
import { truenameValidator, phoneNumberValidator, IDNumberValidator, handSignatureValidator } from './validator.js'
import { t } from './vue-lang.js'
import { showSuccessToast } from 'vant'
import 'vant/lib/toast/style/index.js'
import WriteSignature from './WriteSignature.vue';

const baseRules = {
  truename: [{ trigger: 'change', validator: truenameValidator }],
  IDNumber: [{ trigger: 'change', validator: IDNumberValidator }],
  phoneNumber: [{ trigger: 'change', validator: phoneNumberValidator }],
  handSignature: [{ validator: handSignatureValidator }],
}
let id = ''
let goodsKey = ''

const route = useRoute()
const router = useRouter()
const primaryColor = getCurrentPrimaryColor()
const rules = ref({})
window.route = route
document.title = t('title')

watch(route, () => {
  id = route.params.id
  goodsKey = route.params.goodsKey
}, { immediate: true })

const formItems = ref({})
const contractDetail = ref({})
const initFormItems = async () => {
  const res = await ElectronicContract.getSignContractTemplate({ id, goodsKey })

  contractDetail.value = res
  res.signFields.forEach(item => {
    formItems.value[item.field] = ''
    rules.value[item.field] = baseRules[item.field]
  })
}
initFormItems()

const formRef = ref()
const writeSignatureVisible = ref(false)

const getWriteSignature = (url) => {
  formItems.value.handSignature = url
  formRef.value.validate(['handSignature'])
  writeSignatureVisible.value = false
}

const checkContractDetail = () => {
  router.push(`/contract_detail/${id}/${goodsKey}`)
}

const canSubmit = computed(() => {
  for (const key in formItems.value) {
    if (!formItems.value[key] || !formItems.value[key].trim()) return false
  }

  return true
})

const submitLoading = ref(false)
const submitForm = async () => {
  await formRef.value.validate()

  try {
    submitLoading.value = true

    await ElectronicContract.signContract({
      id,
      contractCode: contractDetail.value.code,
      goodsKey,
      ...formItems.value,
    })

    showSuccessToast(t('signSuccess'))
    routerBack()
  } finally {
    submitLoading.value = false
  }
}

const routerBack = () => {
  window.location.replace(`/h5/index.html${route.query.backUrl}`)
}
</script>

<style lang="less">
.text-drawer-container {
  .ant-drawer-body {
    padding: 0;
  }

  .ant-drawer-content-wrapper {
    width: 100% !important;
    height: 100% !important;
  }

  .ant-drawer-title {
    position: absolute;
    left: 60px;
    right: 60px;
    text-align: center;
  }
}
</style>

<style lang="less" scoped>
.check-contract-detail {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 62px;
  box-shadow: 0 -1px 0 0 #F0F0F0;
  cursor: pointer;
}
</style>
