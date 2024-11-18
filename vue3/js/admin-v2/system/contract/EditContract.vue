<script setup>
import {useRoute} from 'vue-router';
import {createVNode, onMounted, reactive, ref} from 'vue';
import {CloudUploadOutlined, ExclamationCircleOutlined} from '@ant-design/icons-vue';
import {message, Modal} from 'ant-design-vue';
import { t } from './vue-lang';
import router from './router';
import Api from '../../../../api';
import VueCropper from 'vue3/js/components/VueCropper.vue';

message.config({
  top: `90px`,
});

const route = useRoute();
const contractId = route.query.contractId;
const editType = route.query.editType;

const formRef = ref();
const formState = reactive({
  name: '',
  content: '',
  seal: '',
  sign: {
    IDNumber: false,
    phoneNumber: false,
    handSignature: false,
  },
});

const sealName = ref('');
const sealUrl = ref('');
const imgUrl = ref('');
const cropperModalVisible = ref(false);

const CKEditorConfig = {
  filebrowserImageUploadUrl: document.getElementById('ckeditor_image_upload_url').value,
  filebrowserImageDownloadUrl: document.getElementById('ckeditor_image_download_url').value,
};

const initEditor = () => {
  const editor = CKEDITOR.replace('contract-content', {
    extraPlugins: 'questionblank,smiley,table,font,kityformula,codesnippet,shortUrl,colorbutton,colordialog,justify,find,pasteimage,katex',
    allowedContent: true,
    toolbar: [
      { items: ['Bold', 'Italic', 'Underline', 'TextColor'] },
    ],
    fileSingleSizeLimit: app.fileSingleSizeLimit,
  });

  editor.setData(formState.content);

  editor.on('change', () => {
    formState.content = editor.getData();
  });
};

onMounted(async () => {
  if (editType === 'update') {
    const formData = await Api.contract.get(contractId);
    formState.name = formData.name;
    formState.content = formData.content;
    formState.seal = formData.sealFile.id;
    formState.sign.IDNumber = formData.sign.IDNumber === 1;
    formState.sign.handSignature = formData.sign.handSignature === 1;
    formState.sign.phoneNumber = formData.sign.phoneNumber === 1;
    sealUrl.value = formData.seal;
  }
  initEditor();
})

const uploadCourseCover = (info) => {
  const isPng = info.file.type === 'image/png';
  if (!isPng) {
    message.error(`${ t('message.imagesInPng') }`);
  }
  const isLt2M = info.file.size / 1024 / 1024 < 2;
  if (!isLt2M) {
    message.error(`${ t('message.fileSizeLimit') }`);
  }
  if (isPng && isLt2M) {
    sealName.value = info.file.originFileObj.name;
    const reader = new FileReader();
    reader.onload = (event) => {
      imgUrl.value = event.target.result;
      cropperModalVisible.value = true;
    };
    reader.readAsDataURL(info.file.originFileObj);
  }
};

const cropperInstance = ref();
const fileData = ref();
const upload = ref();

const reSelectSeal = () => {
  const inputElement = upload.value.$el.querySelector('input[type="file"]');
  if (inputElement) {
    inputElement.click();
  }
  cropperModalVisible.value = false;
  formState.seal = '';
  sealUrl.value = '';
  sealName.value = '';
  imgUrl.value = '';
};

const hideCropperModal = () => {
  cropperModalVisible.value = false;
};

const saveCropperImage = async () => {
  const cropper = cropperInstance.value.cropper;
  const canvas = cropper.getCroppedCanvas();

  canvas.toBlob(async (blob) => {
    const formData = new FormData();
    formData.append('file', blob, sealName.value);
    formData.append('group', 'system');
    fileData.value = await Api.file.upload(formData);
    formState.seal = fileData.value.id;
    sealUrl.value = canvas.toDataURL('image/png');
    cropperModalVisible.value = false;
    formRef.value.validateFields(['seal'], (errors) => {});
  });
};

const validateContent = async (_rule, value) => {
  if (!value) {
    return Promise.reject(`${ t('message.enterContractContent') }`);
  }
  value = value.trim();
  if (!value) {
    return Promise.reject(`${ t('message.enterContractContent') }`);
  }
  return Promise.resolve();
}

const showCancelModal = () => {
  Modal.confirm({
    title: `${ t('modal.title.leaveTheCurrentPage') }`,
    icon: createVNode(ExclamationCircleOutlined),
    centered: true,
    okText: `${ t('btn.leave') }`,
    cancelText: `${ t('btn.cancel') }`,
    content: createVNode('div', {style: 'color:#626973; font-size:14px; font-weight:400'}, `${ t('modal.dataWillDisappear') }`),
    onOk() {
      router.push({name: 'Index'});
    },
    onCancel() {
    },
    class: 'test',
  });
};

const submitBtnDisabled = ref(false);
const onFinish = async () => {
  submitBtnDisabled.value = true;
  if (editType === 'create') {
    await Api.contract.create(formState);
    message.success(`${ t('message.createdSuccessfully') }`);
  } else if (editType === 'update') {
    await Api.contract.update(contractId, formState);
    message.success(`${ t('message.editSuccessfully') }`);
  }
  await router.push({name: 'Index'});
};
</script>

<template>
  <div class="flex h-[calc(100vh-172px)] overflow-y-auto">
    <div class="create-contract p-24 w-full">
      <a-form
        ref="formRef"
        :model="formState"
        @finish="onFinish"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 12 }"
      >
        <a-form-item>
          <div class="flex items-center" id="basicInformation">
            <div class="text-16 font-medium text-[#1E2226]">{{ t('contract') }}</div>
          </div>
        </a-form-item>
        <a-form-item
          name="name"
          :label="t('label.contractName')"
          :rules="[{ required: true, message: `${t('message.enterContractName')}` }]"
        >
          <a-input v-model:value.trim="formState.name" :maxlength="80" show-count :placeholder="t('placeholder.enterName')"/>
        </a-form-item>
        <a-form-item
          name="content"
          :label="t('label.contractContent')"
          :rules="[{ required: true, message: `${t('message.enterContractContent')}`, validator: validateContent }]"
        >
          <div class="flex flex-col space-y-4">
            <textarea id="contract-content"></textarea>
            <span class="text-[#8A9099] text-12 font-normal">{{ t('tip.contractContent') }}</span>
          </div>
        </a-form-item>
        <a-form-item
          name="sign"
          :label="t('label.contentSignedByPartyB')"
          :rules="[{ required: true }]"
        >
          <a-checkbox
            :checked="true"
            disabled
          >
            {{ t('checkbox.name') }}
          </a-checkbox>
          <a-checkbox
            v-model:checked="formState.sign.IDNumber"
          >
            {{ t('checkbox.iDNumber') }}
          </a-checkbox>
          <a-checkbox
            v-model:checked="formState.sign.phoneNumber"
          >
            {{ t('checkbox.contactInformation') }}
          </a-checkbox>
          <a-checkbox
            v-model:checked="formState.sign.handSignature"
          >
            {{ t('checkbox.handSignature') }}
          </a-checkbox>
        </a-form-item>
        <a-form-item
          name="seal"
          :label="t('label.partyASeal')"
          :validate-trigger="['']"
          :rules="[{ required: true, message: `${t('message.uploadStamp')}` }]"
        >
          <div>
            <a-upload
              ref="upload"
              class="seal-uploader"
              accept="image/png"
              :file-list="[]"
              :maxCount="1"
              :customRequest="() => {}"
              list-type="picture-card"
              @change="uploadCourseCover"
            >
              <img v-if="sealUrl" :src="sealUrl" style="width: 100%;" alt=""/>
              <div v-else class="flex flex-col items-center">
                <div class="p-18 bg-[#006AFF]/5 w-fit" style="border-radius: 9999px">
                  <cloud-upload-outlined :style="{fontSize: '32px'}" class="text-[#006AFF]"/>
                </div>
                <div class="mt-8">{{ t('label.uploadStamp') }}</div>
              </div>
            </a-upload>
            <div class="w-240 text-[#8A9099] text-12 font-normal">{{ t('tip.seal') }}</div>
          </div>
          <a-modal
            :mask-closable="false"
            class="flex justify-center"
            zIndex="1050"
            v-model:open="cropperModalVisible"
            @cancel="cropperModalVisible = false; sealUrl = ''; formState.seal = ''">

            <vue-cropper ref="cropperInstance" :src="imgUrl"></vue-cropper>
            <template #title>{{ t('modal.title.cropPicture') }}</template>
            <template #footer>
              <div class="flex justify-between">
                <a-button @click="reSelectSeal">{{ t('btn.reselect') }}</a-button>
                <div>
                  <a-button @click="hideCropperModal">{{ t('btn.cancel') }}</a-button>
                  <a-button type="primary" @click="saveCropperImage">{{ t('btn.saveImage') }}</a-button>
                </div>
              </div>
            </template>
          </a-modal>
        </a-form-item>
        <a-form-item>
          <div
            class="flex justify-center fixed bottom-20 w-[calc(100%-216px)] border-t border-x-0 border-b-0 border-solid border-[#F0F2F5] p-20 left-200 bg-white">
            <a-button class="mr-16" @click="showCancelModal">{{ t('btn.cancel') }}</a-button>
            <a-button type="primary" html-type="submit" :disabled="submitBtnDisabled">{{ t('btn.save') }}</a-button>
          </div>
        </a-form-item>
      </a-form>
    </div>
  </div>
</template>

<style lang="less" scoped>
.create-contract {
  /deep/ .ant-form-item-label >label {
    color: #626973;
    font-weight: 400;
    font-size: 14px;
  }
  /deep/ label.ant-checkbox-wrapper{
    color: #1E2226 !important;
    font-weight: 400 !important;
    font-size: 14px !important;
  }
}
.seal-uploader {
  /deep/ .ant-upload.ant-upload-select {
    width: 240px !important;
    height: 240px !important;
  }
}
</style>

<style lang="less">
.cke_chrome {
  border-color: #d9d9d9 !important;
  border-radius: 4px;
}
.cke_inner {
  border-color: #d9d9d9 !important;
  border-radius: 4px !important;
}
.cke_top {
  border-color: #d9d9d9 !important;
  border-top-left-radius: 4px !important;
  border-top-right-radius: 4px !important;
}
.cke_contents {
  border-color: #d9d9d9 !important;
  border-bottom-left-radius: 4px !important;
  border-bottom-right-radius: 4px !important;
}
</style>
