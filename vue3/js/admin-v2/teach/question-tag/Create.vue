<script setup>
import {reactive, ref} from 'vue';
import Api from '../../../../api';

const popoverVisible = ref(false);

const emit = defineEmits(['create']);

const onOpenChange = (visible) => {
  if (!visible) {
    formState.name = null;
  }
};

const formRef = ref(null);
const formState = reactive({
  name: null
})

const rules = {
  name: [
    {
      validator: async (_, value) => {
        if (!value) return Promise.resolve();

        const exists = await Api.questionTag.isTagExists(value);
        if (exists) {
          return Promise.reject("标签名称不得重复");
        }
        return Promise.resolve();
      },
      trigger: "blur"
    }
  ],
};

function onConfirm() {
  formRef.value
    .validate()
    .then(() => {
      emit('create', formState)
      popoverVisible.value = false;
    })
    .catch((err) => {

    });
}
</script>

<template>
  <a-popover
    v-model:open="popoverVisible"
    title="添加类型"
    trigger="click"
    placement="rightTop"
    @onOpenChange="onOpenChange"
  >
    <template #content>
      <a-form
        :model="formState"
        :rules="rules"
        ref="formRef"
      >
        <a-form-item name="name" class="mb-0">
          <a-input
            v-model:value="formState.name"
            placeholder="请输入标签名称"
            show-count
            :maxlength="50"
            style="width: 264px"
          />
        </a-form-item>
        <a-form-item class="mb-0">
          <div class="flex flex-row-reverse mt-8">
            <a-button type="primary" size="small" :disabled="!formState.name" @click="onConfirm">确定</a-button>
          </div>
        </a-form-item>
      </a-form>
    </template>
    <a-button type="primary" class="w-fit">添加</a-button>
  </a-popover>
</template>
