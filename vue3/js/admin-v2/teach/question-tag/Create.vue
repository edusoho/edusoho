<script setup>
import {reactive, ref} from 'vue';
import Api from '../../../../api';

const popoverVisible = ref(false);

const emit = defineEmits(['create']);
const props = defineProps({
  isGroup: Boolean,
  groupId: String,
  tagNum: Number,
})

const TAG_NUM_LIMIT = 200;

const onOpenChange = (visible) => {
  if (props.tagNum >= TAG_NUM_LIMIT) {
    popoverVisible.value = false;
  }
};

const formState = reactive({
  name: null
})

const rules = {
  name: [
    {
      validator: async (_, value) => {
        if (!value) return Promise.resolve();

        let res;
        if (props.isGroup) {
          const params = {
            name: value,
          }
          res = await Api.questionTag.isGroupNameExists(params);
        } else {
          const params = {
            groupId: props.groupId,
            name: value,
          }
          res = await Api.questionTag.isNameExists(params);
        }

        if (!res.ok && props.isGroup) {
          return Promise.reject("标签类型名称不得重复");
        } else if(!res.ok && !props.isGroup) {
          return Promise.reject("标签名称不得重复");
        }

        return Promise.resolve();
      },
      trigger: "blur",
    }
  ],
};

function openPopover() {
  popoverVisible.value = true;
}

function closePopover() {
  popoverVisible.value = false;
}

async function onConfirm(values) {
  emit('create', values)
  formState.name = null;
  closePopover();
}
</script>

<template>
  <a-popover
    v-model:open="popoverVisible"
    title="添加类型"
    placement="rightTop"
    trigger="click"
    @openChange="onOpenChange"
  >
    <template #content>
      <a-form
        :model="formState"
        :rules="rules"
        @finish="onConfirm"
      >
        <a-form-item name="name" class="mb-0">
          <a-input
            v-model:value.trim="formState.name"
            :placeholder="isGroup ? '请输入标签类型名称' : '请输入标签名称'"
            show-count
            :maxlength="50"
            style="width: 264px"
          />
        </a-form-item>
        <a-form-item class="mb-0">
          <div class="flex flex-row-reverse mt-8">
            <a-button type="primary" size="small" :disabled="!formState.name" html-type="submit">确定</a-button>
          </div>
        </a-form-item>
      </a-form>
    </template>
    <a-button type="primary" class="w-fit" @click="openPopover" :disabled="tagNum >= TAG_NUM_LIMIT">添加</a-button>
  </a-popover>
</template>
