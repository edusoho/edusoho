<script setup>
import {onMounted, reactive, ref} from 'vue';
import {removeHtml} from '../../common';
import Api from '../../../api';
import {message} from 'ant-design-vue';
import VueCropper from '../../components/VueCropper.vue';
import {PlusOutlined} from '@ant-design/icons-vue';

const props = defineProps({
  manage: {type: Object, default: {}}
});

const formRef = ref(null);
const formState = reactive({
  title: removeHtml(props.manage.classroom.title),
  subtitle: props.manage.classroom.subtitle ? props.manage.classroom.subtitle : '',
  tags: props.manage.tags ? props.manage.tags : [],
  categoryId: props.manage.classroom.categoryId,
  orgCode: props.manage.classroom.orgCode,
  about: props.manage.classroom.about,
});

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
      reject(new Error(`输入内容的长度不能超过 ${rule.maxSize} 字节`));
    } else if (rule.minSize && isPositiveInteger(rule.minSize) && byteLength < rule.minSize) {
      reject(new Error(`输入内容的长度不能少于 ${rule.minSize} 字节`));
    } else {
      resolve();
    }
  });
};
const isPositiveInteger = (value) => {
  return Number.isInteger(value) && value > 0;
};

const classroomTitleValidator = (rule, value) => {
  return new Promise((resolve, reject) => {
    if (!/^[^<>]*$/.test(value)) {
      reject(new Error(`标题不能包含尖括号`));
    }
    resolve();
  });
};

const tabOptions = ref();
const getTabs = async () => {
  const tabs = await Api.tag.getTags('');
  tabOptions.value = tabs.map(item => ({
    label: item.name,
    value: item.name
  }));
};

const categoryTree = ref();
const getCategory = async () => {
  const category = await Api.category.getCategory('classroom');
  categoryTree.value = transformCategoryData(category);
  categoryTree.value.unshift({value: '0', label: '分类'});
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
    if (item.parentId === '0') {
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
    message.error('请上传jpg,gif,png格式的图片');
  }
  const isLt2M = info.file.size / 1024 / 1024 < 2;
  if (!isLt2M) {
    message.error('图片大小不能超过2MB');
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
      };
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
  params.append('imgs[small][]', 219);
  params.append('imgs[small][]', 124);
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

let editor = null;
const initEditor = () => {
  editor = CKEDITOR.replace('classroom-introduction', {
    toolbar: [
      {items: ['FontSize', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']},
      {items: ['Bold', 'Italic', 'Underline', 'TextColor', '-', 'RemoveFormat', 'PasteText', '-', 'NumberedList', 'BulletedList', 'Indent', 'Outdent', '-', 'Link', 'Unlink', 'uploadpictures', 'CodeSnippet', 'Iframe', '-', 'Source', 'kityformula', '-', 'Maximize']}
    ],
    extraPlugins: 'questionblank,smiley,table,font,kityformula,codesnippet,uploadpictures,shortUrl,image2,colorbutton,colordialog,justify,find,filebrowser,pasteimage,katex,iframe',
    filebrowserImageUploadUrl: props.manage.imageUploadUrl,
  });
  editor.setData(formState.about);
};

const validateForm = () => {
  formState.about = editor.getData();
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
  getTabs();
  getCategory();
  getOrgCodes();
  cropUrl.value = props.manage.cover;
  initEditor();
});
</script>

<template>
  <div class="flex flex-col w-full relative">
    <div class="absolute w-full px-32 font-medium py-10 text-14 text-stone-900 bg-[#f5f5f5]">基础信息</div>
    <a-form
      ref="formRef"
      class="mt-66"
      :model="formState"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 16 }"
    >
      <a-form-item
        label="班级标题"
        name="title"
        :validateTrigger="['blur']"
        :rules="[
          { required: true, message: '请输入班级标题' },
          { validator: interByteValidator, maxSize: 100, minSize: 2 },
          { validator: classroomTitleValidator },
          ]"
      >
        <a-input v-model:value="formState.title"/>
      </a-form-item>
      <a-form-item
        label="班级副标题"
        name="subtitle"
        :validateTrigger="['blur']"
        :rules="[
          { validator: interByteValidator, maxSize: 100 }
          ]"
      >
        <a-textarea v-model:value.trim="formState.subtitle" :rows="3"/>
      </a-form-item>
      <a-form-item
        label="标签"
      >
        <a-select
          v-model:value="formState.tags"
          mode="multiple"
          placeholder="请选择"
          :options="tabOptions"
          allow-clear
        ></a-select>
        <div class="text-[#adadad] text-14 mt-8 ">用于按标签检索班级等，由网校管理员后台统一管理</div>
      </a-form-item>
      <a-form-item
        label="分类"
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
        label="组织机构"
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
        label="封面图片"
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
              <div class="mt-8">上传图片</div>
            </div>
          </div>
        </a-upload>
        <div class="text-[#a1a1a1]">请上传jpg, gif, png格式的图片, 建议图片尺寸为 480×270px。建议图片大小不超过2MB。</div>
      </a-form-item>
      <a-form-item
        label="班级简介"
      >
        <textarea id="classroom-introduction"></textarea>
        <div class="text-[#a1a1a1] font-normal text-14 leading-28">
          为正常使用IFrame，请在【管理后台】-【系统】-【站点设置】-【安全】-【IFrame白名单】中进行设置
        </div>
      </a-form-item>
    </a-form>
    <a-modal
      :mask-closable="false"
      :width="'auto'"
      :zIndex="1050"
      :centered="true"
      v-model:open="cropperModalVisible"
      @cancel="cropperModalVisible = false"
    >
      <vue-cropper ref="cropperInstance" :src="coverUrl" :aspectRatio="16/9"></vue-cropper>
      <template #title>裁剪图片</template>
      <template #footer>
        <div class="flex justify-between">
          <a-button @click="reSelectCover">重新选择</a-button>
          <div>
            <a-button @click="hideCropperModal">取消</a-button>
            <a-button type="primary" @click="saveCropperCover">保存图片</a-button>
          </div>
        </div>
      </template>
    </a-modal>
  </div>
</template>
