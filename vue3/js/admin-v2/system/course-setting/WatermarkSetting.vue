<template>
  <a-form
    ref="formRef"
    :model="formState"
    :rules="rules"
  >
    <div class="form-group">
      <div class="col-md-3 control-label">
        <label>{{ t('label.taskWatermark') }}</label>
      </div>
      <div class="controls col-md-8 radios">
        <label v-for="radio in radios">
          <input type="radio" name="watermarkEnable" :value="radio.value" v-model="watermarkEnable"/>
          {{ radio.label }}
        </label>
        <div v-show="watermarkEnable === 1">
          <div class="help-block">{{ t('tip.purpose') }}</div>
          <div class="help-block">{{ t('tip.setting') }}</div>
          <div class="watermark-fields">
            <label v-for="watermarkField in watermarkFields" class="watermark-field">
              <input type="checkbox" name="fields" :value="watermarkField.key" v-model="formState.fields"/>
              {{ watermarkField.label }}
              <input v-if="watermarkField.key === 'custom'" class="form-control" type="text"
                     v-model="formState.custom_text" :placeholder="t('placeholder.customText')"/>
            </label>
            <div class="watermark-field">
              {{ t('label.color') }}
              <div class="watermark-color-picker" id="color-picker" :style="{backgroundColor: formState.color}"></div>
            </div>
            <div class="watermark-field">
              {{ t('label.alpha') }}
              <a-form-item name="alpha" class="mb-0">
                <a-input class="form-control watermark-alpha" type="number" v-model:value="formState.alpha"/>
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
import Api from 'vue3/api';
import {t} from './vue-lang';

const watermarkEnable = ref(0);
const radios = [
  {
    label: t('radio.open'),
    value: 1,
  },
  {
    label: t('radio.close'),
    value: 0,
  },
];
const watermarkFields = [
  {
    key: 'truename',
    label: t('checkbox.name'),
  },
  {
    key: 'nickname',
    label: t('checkbox.username'),
  },
  {
    key: 'mobile',
    label: t('checkbox.mobile'),
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
  alpha: 20,
});
const alphaValidator = async (rule, value) => {
  if (!value) {
    return Promise.reject(t('validate.alpha.required'));
  }
  value = Number(value);
  if (!Number.isInteger(value) || value < 1) {
    return Promise.reject(t('validate.alpha.positiveInteger'));
  }
  if (value > 100) {
    return Promise.reject(t('validate.alpha.max'));
  } else {
    return Promise.resolve();
  }
};
const rules = {
  alpha: [
    {
      validator: alphaValidator,
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

onMounted(async () => {
  const setting = await Api.setting.get('course');
  const watermark = setting.task_page_watermark;
  watermarkEnable.value = watermark.enable;
  formState.fields = watermark.setting.fields || formState.fields;
  formState.custom_text = watermark.setting.custom_text || formState.custom_text;
  formState.color = watermark.setting.color || formState.color;
  formState.alpha = watermark.setting.alpha || formState.alpha;
  const picker = new Picker({
    parent: document.getElementById('color-picker'),
    color: formState.color,
  });
  picker.onChange = color => {
    formState.color = color.hex.slice(0, 7);
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

.watermark-alpha {
  display: inline;
  width: 100px;
  margin-right: 10px;
}

input::-webkit-outer-spin-button, input::-webkit-inner-spin-button {
  -webkit-appearance: none;
}

</style>
