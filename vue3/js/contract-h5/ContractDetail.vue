<script setup>
import { watch, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElectronicContract } from '../api'
import { t } from './vue-lang'

let id = ''
let goodsKey = ''

const route = useRoute()
const router = useRouter()

document.title = t('contractDetail')

watch(route, () => {
  id = route.params.id
  goodsKey = route.params.goodsKey
}, { immediate: true })

const contractContent = ref('')
const getContractDetail = async () => {
  const res = await ElectronicContract.getSignContractTemplate({ id, goodsKey })

  contractContent.value = res.content
}
getContractDetail()

const routerBack = () => {
  router.go(-1)
}
</script>

<template>
  <div class="sticky top-0 w-full text-center h-48 leading-48 font-medium text-16 text-[#272E3B]" @click="routerBack">
    <svg class="absolute top-12 left-12" width="24" height="24" viewBox="0 0 24 24" fill="none">
      <path d="M15.5 18L9.5 12L15.5 6" stroke="#272E3B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    {{ t('title') }}
  </div>

  <div class="py-16 px-32" v-html="contractContent"></div>
</template>
