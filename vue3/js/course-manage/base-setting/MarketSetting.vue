<script setup>
import {reactive, ref} from 'vue';

const props = defineProps({
  params: {type: Object, default: {}}
});

const formRef = ref(null);
const formState = reactive({

})
</script>

<template>
  <div class="flex flex-col w-full relative">
    <div class="absolute -left-32 w-full px-32 font-medium py-10 text-14 text-stone-900 bg-[#f5f5f5]"
         style="width: calc(100% + 64px);">营销设置
    </div>
    <a-form
      ref="formRef"
      class="mt-66"
      :model="formState"
      name="baseInfo"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 16 }"
      autocomplete="off"
    >
      <div>
        <a-form-item
          label="价格"

        >

        </a-form-item>

        <a-form-item>

        </a-form-item>
      </div>


    </a-form>
  </div>
</template>

<style scoped lang="less">

</style>
