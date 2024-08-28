<template>
  <a-form
    ref="formRef"
    :model="formState"
    :rules="rules"
  >
    <div class="form-group">
      <div class="col-md-3 control-label">
        <label>任务详情页水印</label>
      </div>
      <div class="controls col-md-8 radios">
        <label v-for="radio in radios">
          <input type="radio" name="watermarkEnable" :value="radio.value" v-model="watermarkEnable"/>
          {{ radio.label }}
        </label>
        <div v-show="watermarkEnable === 1">
          <div class="help-block">针对网站内容截图泄露，可进行威慑和溯源。</div>
          <div class="help-block">设置水印信息：</div>
          <div class="watermark-fields">
            <label v-for="watermarkField in watermarkFields" class="watermark-field">
              <input type="checkbox" name="fields" :value="watermarkField.key" v-model="formState.fields"/>
              {{ watermarkField.label }}
              <input v-if="watermarkField.key === 'custom'" class="form-control" type="text"
                     v-model="formState.custom_text" placeholder="输入自定义文案"/>
            </label>
            <div class="watermark-field">
              颜色
              <div class="watermark-color-picker" id="color-picker" :style="{backgroundColor: formState.color}"></div>
            </div>
            <div class="watermark-field">
              透明度
              <a-form-item name="opacity">
                <a-input class="form-control watermark-opacity" type="number" v-model:value="formState.opacity"/>
                1 ~ 100
              </a-form-item>
            </div>
          </div>
        </div>
      </div>
    </div>
  </a-form>
</template>

<script setup>
import {onMounted, reactive, ref} from 'vue';
import Picker from 'vanilla-picker';
import {SettingApi} from 'vue3/api/Setting';

const watermarkEnable = ref(0);
const radios = [
  {
    label: '开启',
    value: 1,
  },
  {
    label: '关闭',
    value: 0,
  },
];
const watermarkFields = [
  {
    key: 'truename',
    label: '姓名',
  },
  {
    key: 'nickname',
    label: '用户名',
  },
  {
    key: 'mobile',
    label: '手机号码',
  },
  {
    key: 'custom',
    label: '',
  },
];
const formState = reactive({
  fields: [],
  custom_text: '',
  color: '#d0d0d2',
  opacity: 20,
});
const opacityValidator = async (rule, value) => {
  if (!value) {
    return Promise.reject('请输入透明度');
  }
  value = Number(value);
  if (!Number.isInteger(value) || value < 1) {
    return Promise.reject('请输入正整数');
  }
  if (value > 100) {
    return Promise.reject('请输入不大于100的数值');
  } else {
    return Promise.resolve();
  }
};
const rules = {
  opacity: [
    {
      validator: opacityValidator,
      trigger: 'change',
    },
  ],
};
const formRef = ref();

document.getElementById('submit').addEventListener('click', event => {
  formRef.value.validate()
    .then(() => {
      document.getElementById('task_page_watermark_enable').value = watermarkEnable.value;
      document.getElementById('task_page_watermark_setting').value = JSON.stringify(formState);
    })
    .catch(error => {
      event.preventDefault();
    });
});

const fetchWatermarkSetting = async () => {
  const setting = await SettingApi.get('course');
  const watermark = setting.task_page_watermark;
  watermarkEnable.value = watermark.enable;
  formState.fields = watermark.setting.fields || formState.fields;
  formState.custom_text = watermark.setting.custom_text || formState.custom_text;
  formState.color = watermark.setting.color || formState.color;
  formState.opacity = watermark.setting.opacity || formState.opacity;
};
fetchWatermarkSetting();

onMounted(() => {
  const picker = new Picker({
    parent: document.getElementById('color-picker'),
    color: formState.color,
  });
  picker.onChange = color => {
    formState.color = color.rgbaString;
  };
});

</script>

<style lang="less" scoped>

.watermark-fields {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 10px;

  .watermark-field {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 0;

    input[type="checkbox"] {
      margin: 0;
      flex-shrink: 0;
    }
  }
}

.watermark-color-picker {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  cursor: pointer;
}

.watermark-opacity {
  display: inline;
  width: 100px;
  margin-right: 10px;
}

input::-webkit-outer-spin-button, input::-webkit-inner-spin-button {
  -webkit-appearance: none;
}

</style>
