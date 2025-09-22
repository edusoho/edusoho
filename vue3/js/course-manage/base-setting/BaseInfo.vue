<script setup>
import {onMounted, reactive, ref} from 'vue';
import Api from '../../../api';
import {PlusOutlined,} from '@ant-design/icons-vue';
import {message} from 'ant-design-vue';
import VueCropper from '../../components/VueCropper.vue';
import {removeHtml} from '../../common';
import {t} from './vue-lang';

const props = defineProps({
  manage: {type: Object, default: {}}
});

const categoryTree = ref();
const tabOptions = ref();
const getCategory = async () => {
  const category = await Api.category.getCategory('course');
  categoryTree.value = transformCategoryData(category);
  categoryTree.value.unshift({ value: '0', label: t('label.nothing') });
};

const getTabs = async () => {
  const tabs = await Api.tag.getTags('');
  tabOptions.value = tabs.map(item => ({
    label: item.name,
    value: item.name
  }));
};

const formRef = ref(null);
const formState = reactive({
  title: props.manage.course.title,
  subtitle: props.manage.course.subtitle,
});

const courseTitleValidator = (rule, value) => {
  return new Promise((resolve, reject) => {
    if (!/^[^<>]*$/.test(value)) {
      reject(new Error(t('validate.cannotContainAngleBrackets')));
    }
    resolve();
  });
};

const isPositiveInteger = (value) => {
  return Number.isInteger(value) && value > 0;
};

const interByteValidator = (rule, value) => {
  return new Promise((resolve, reject) => {
    if (!value || (!rule.maxSize && !rule.minSize)) {
      return resolve();
    }
    let byteLength = 0;
    for (let i = 0; i < value.length; i++) {
      let c = value.charAt(i);
      if (/^[\u0000-\u00ff]$/.test(c)) {
        byteLength++;
      } else {
        byteLength += 2;
      }
    }
    if (rule.maxSize && isPositiveInteger(rule.maxSize) && byteLength > rule.maxSize) {
      reject(new Error(t('validate.maxByteLimit', {maxByte: rule.maxSize})));
    } else if (rule.minSize && isPositiveInteger(rule.minSize) && byteLength < rule.minSize) {
      reject(new Error(t('validate.minByteLimit', {minByte: rule.minSize})));
    } else {
      resolve();
    }
  });
};

const calculateByteLength = (str) => {
  let byteLength = 0;
  for (let i = 0; i < str.length; i++) {
    const charCode = str.charCodeAt(i);
    if (charCode >= 0x0001 && charCode <= 0x007F) {
      byteLength += 1;
    } else if (charCode > 0x07FF) {
      byteLength += 3;
    } else {
      byteLength += 2;
    }
  }
  return byteLength;
};

const courseTitleLengthValidator = async (rule, value) => {
  return new Promise((resolve, reject) => {
    const byteLength = calculateByteLength(value);
    if (byteLength <= 200) {
      resolve();
    } else {
      reject(new Error(t('validate.courseTitleLengthLimit')));
    }
  });
};

function transformCategoryData(data) {
  return data.map(item => {
    const transformedItem = {
      value: item.id,
      label: item.name,
    };
    if (item.children && item.children.length > 0) {
      transformedItem.children = transformCategoryData(item.children);
    }
    return transformedItem;
  });
}

const orgTree = ref();
const getOrgCodes = async () => {
  const orgCodes = await Api.org.search();
  orgTree.value = buildOrgTree(orgCodes);
};

function buildOrgTree(data) {
  const map = {};
  const tree = [];
  data.forEach((item) => {
    map[item.id] = {
      label: item.name,
      value: item.orgCode,
      children: []
    };
  });
  data.forEach((item) => {
    const node = map[item.id];
    if (item.parentId === "0") {
      tree.push(node);
    } else if (map[item.parentId]) {
      map[item.parentId].children.push(node);
    }
  });
  return tree;
}

const cropperModalVisible = ref(false);
const cropperInstance = ref();
const coverUrl = ref('');
const fileData = ref();
function uploadCover(info) {
  const isPngOrGifOrJpg = info.file.type === 'image/png' || info.file.type === 'image/gif' || info.file.type === 'image/jpg' || info.file.type === 'image/jpeg';
  if (!isPngOrGifOrJpg) {
    message.error(t('validate.imgTypeLimit'));
  }
  const isLt2M = info.file.size / 1024 / 1024 < 2;
  if (!isLt2M) {
    message.error(t('validate.imgSizeLimit'));
  }
  if (isPngOrGifOrJpg && isLt2M) {
    const reader = new FileReader();
    reader.onload = async (event) => {
      coverUrl.value = event.target.result;
      cropperModalVisible.value = true;
      const response = await fetch(event.target.result);
      fileData.value = {
        blob: await response.blob(),
        name: info.file.originFileObj.name,
      }
    };
    reader.readAsDataURL(info.file.originFileObj);
  }
}
const upload = ref();
const reSelectCover = () => {
  const inputElement = upload.value.$el.querySelector('input[type="file"]');
  if (inputElement) {
    inputElement.click();
  }
  cropperModalVisible.value = false;
};
const hideCropperModal = () => {
  cropperModalVisible.value = false;
};

const cropUrl = ref();
const saveCropperCover = async () => {
  const formData = new FormData();
  formData.append('file', fileData.value.blob, fileData.value.name);
  formData.append('group', 'course');
  const file = await Api.file.upload(formData);

  const cropper = cropperInstance.value.cropper;
  const imageData = cropper.getImageData();
  const cropperData = cropper.getData();
  const canvas = cropper.getCroppedCanvas();
  cropUrl.value = canvas.toDataURL('image/png');
  cropperModalVisible.value = false;

  const params = new URLSearchParams();
  params.append('imgs[large][]', 480);
  params.append('imgs[large][]', 270);
  params.append('imgs[middle][]', 304);
  params.append('imgs[middle][]', 171);
  params.append('imgs[small][]', 96);
  params.append('imgs[small][]', 54);
  params.append('x', Math.round(cropperData.x));
  params.append('y', Math.round(cropperData.y));
  params.append('x2', Math.round(cropperData.x) + Math.round(cropperData.width));
  params.append('y2', Math.round(cropperData.y) + Math.round(cropperData.height));
  params.append('w', Math.round(cropperData.width));
  params.append('h', Math.round(cropperData.height));
  params.append('width', Math.round(imageData.naturalWidth));
  params.append('height', Math.round(imageData.naturalHeight));
  params.append('group', 'course');
  params.append('post', false);
  params.append('fileId', file.id);
  formState.covers = await Api.crop.crop(params);
};

const serializeOption = [
  {label: t('label.NonSerialCourse'), value: 'none'},
  {label: t('label.updating'), value: 'serialized'},
  {label: t('label.completed'), value: 'finished'},
];

let editor = null;
const initEditor = () => {
  editor = CKEDITOR.replace('course-introduction', {
    toolbar: [
      {items: ['FontSize', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']},
      {items: ['Bold', 'Italic', 'Underline', 'TextColor', '-', 'RemoveFormat', 'PasteText', '-', 'NumberedList', 'BulletedList', 'Indent', 'Outdent', '-', 'Link', 'Unlink', 'uploadpictures', 'CodeSnippet', 'Iframe', '-', 'Source', 'kityformula', '-', 'Maximize']}
    ],
    extraPlugins: 'questionblank,smiley,table,font,kityformula,codesnippet,uploadpictures,shortUrl,image2,colorbutton,colordialog,justify,find,filebrowser,pasteimage,katex,iframe',
    filebrowserImageUploadUrl: props.manage.imageUploadUrl,
  });
  editor.setData(formState.summary);
};

const validateForm = () => {
  if (props.manage.isUnMultiCourseSet) {
    formState.summary = editor.getData();
  }
  return formRef.value.validate()
    .then(() => {
      return formState;
    })
    .catch((error) => {
    });
};
defineExpose({
  validateForm,
});

onMounted(() => {
  if (props.manage.isUnMultiCourseSet) {
    Object.assign(formState, {
      title: removeHtml(props.manage.courseSet.title),
      subtitle: props.manage.courseSet.subtitle ? removeHtml(props.manage.courseSet.subtitle) : '',
      tags: props.manage.tags,
      categoryId: props.manage.course.categoryId,
      orgCode: props.manage.courseSet.orgCode,
      serializeMode: props.manage.course.serializeMode,
      covers: '',
      summary: props.manage.courseSet.summary,
    });
    cropUrl.value = props.manage.imageSrc;
    initEditor();
    getCategory();
    getOrgCodes();
    getTabs();
  }
});
</script>

<template>
  <div class="flex flex-col w-full">
    <div class="flex flex-col relative" v-if="!props.manage.isUnMultiCourseSet">
      <div class="absolute -left-32 w-full px-32 font-medium py-10 text-14 text-stone-900 bg-[#f5f5f5]"
           style="width: calc(100% + 64px);">{{ t('title.basicInformation') }}
      </div>
      <a-form
        ref="formRef"
        class="mt-66"
        :model="formState"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 16 }"
      >
        <a-form-item
          :label="t('label.planName')"
          name="title"
          :validateTrigger="['blur']"
          :rules="[
          { required: true, message: t('validate.inputPlanName') },
          { validator: interByteValidator, maxSize: 100, minSize: 2 },
          { validator: courseTitleValidator },
          ]"
        >
          <a-input v-model:value.trim="formState.title"/>
        </a-form-item>
        <a-form-item
          :label="t('label.subheadingOfThePlan')"
          name="subtitle"
          :validateTrigger="['blur']"
          :rules="[
          { validator: interByteValidator, maxSize: 100 }
          ]"
        >
          <a-textarea v-model:value.trim="formState.subtitle" :rows="3"/>
        </a-form-item>
      </a-form>
    </div>

    <div class="relative" v-else>
      <div class="absolute -left-32 w-full px-32 font-medium py-10 text-14 text-stone-900 bg-[#f5f5f5]"
           style="width: calc(100% + 64px);">{{ t('title.basicInformation') }}
      </div>
      <a-form
        ref="formRef"
        class="mt-66"
        :model="formState"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 16 }"
      >
        <a-form-item
          :label="t('label.courseTitle')"
          name="title"
          :validateTrigger="['blur']"
          :rules="[
          { required: true, message: t('validate.inputCourseTitle') },
          { validator: courseTitleLengthValidator },
          { validator: courseTitleValidator },
          ]"
        >
          <a-input v-model:value.trim="formState.title"/>
        </a-form-item>
        <a-form-item
          :label="t('label.courseSubtitle')"
          name="subtitle"
          :validateTrigger="['blur']"
          :rules="[
          { max: 50, message: t('validate.courseSubtitleLimit') },
          ]"
        >
          <a-textarea v-model:value="formState.subtitle" :rows="3"/>
        </a-form-item>
        <a-form-item
          :label="t('label.tag')"
        >
          <a-select
            v-model:value="formState.tags"
            mode="multiple"
            :placeholder="t('placeholder.pleaseSelect')"
            :options="tabOptions"
            allow-clear
          ></a-select>
          <div class="text-[#adadad] text-14 mt-8 ">{{ t('tip.tag') }}</div>
        </a-form-item>
        <a-form-item
          :label="t('label.category')"
        >
          <a-tree-select
            v-model:value="formState.categoryId"
            :tree-data="categoryTree"
            allow-clear
            tree-default-expand-all
            :show-search="true"
            :treeNodeFilterProp="'label'"
            style="width: 250px"
          ></a-tree-select>
        </a-form-item>
        <a-form-item
          v-if="props.manage.enableOrg === 1"
          :label="t('label.organization')"
        >
          <a-tree-select
            v-model:value="formState.orgCode"
            :tree-data="orgTree"
            allow-clear
            tree-default-expand-all
            :show-search="true"
            :treeNodeFilterProp="'label'"
            style="width: 250px"
          ></a-tree-select>
        </a-form-item>
        <a-form-item
          :label="t('label.serializeMode')"
        >
          <a-radio-group v-model:value="formState.serializeMode"
                         :options="serializeOption"/>
        </a-form-item>
        <a-form-item
          :label="t('label.coverPicture')"
        >
          <a-upload
            ref="upload"
            accept="image/png, image/gif, image/jpg, image/jpeg"
            :file-list="[]"
            :maxCount="1"
            :customRequest="() => {}"
            list-type="picture-card"
            @change="uploadCover"
          >
            <img v-if="cropUrl" :src="cropUrl" style="width: 100%;" alt=""/>
            <div v-else class="flex flex-col items-center relative">
              <div class="flex flex-col items-center">
                <PlusOutlined/>
                <div class="mt-8">{{ t('tip.uploadPictures') }}</div>
              </div>
            </div>
          </a-upload>
          <div class="text-[#a1a1a1]">{{ t('tip.coverPicture') }}</div>
        </a-form-item>
        <a-form-item
          :label="t('label.courseIntroduction')"
        >
          <textarea id="course-introduction"></textarea>
          <div class="text-[#a1a1a1] font-normal text-14 leading-28">
            {{ t('tip.courseIntroduction') }}
          </div>
        </a-form-item>
      </a-form>
    </div>
    <a-modal
      :mask-closable="false"
      :width="'auto'"
      :zIndex="1050"
      :centered="true"
      v-model:open="cropperModalVisible"
      @cancel="cropperModalVisible = false"
    >
      <vue-cropper ref="cropperInstance" :src="coverUrl" :aspectRatio="16/9"></vue-cropper>
      <template #title>{{ t('title.cropThePicture') }}</template>
      <template #footer>
        <div class="flex justify-between">
          <a-button @click="reSelectCover">{{ t('btn.reselect') }}</a-button>
          <div>
            <a-button @click="hideCropperModal">{{ t('btn.cancel') }}</a-button>
            <a-button type="primary" @click="saveCropperCover">{{ t('btn.saveTheImage') }}</a-button>
          </div>
        </div>
      </template>
    </a-modal>
  </div>
</template>
