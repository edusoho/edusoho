<script setup>
import {onMounted, reactive, ref} from 'vue';
import Api from '../../../api';

const props = defineProps({
  manage: {type: Object, default: {}}
});

const removeHtml = (input) => {
  return input && input.replace(/<(?:.|\n)*?>/gm, '')
    .replace(/(&rdquo;)/g, '\"')
    .replace(/&ldquo;/g, '\"')
    .replace(/&mdash;/g, '-')
    .replace(/&nbsp;/g, '')
    .replace(/&amp;/g, '&')
    .replace(/&gt;/g, '>')
    .replace(/&lt;/g, '<')
    .replace(/<[\w\s"':=\/]*/, '');
};

const cover = ref();
const categoryOptions = ref();
const tabOptions = ref();

const getCover = async () => {
  cover.value = await Api.file.getCourseCover({
    saveUrl: props.manage.imageSaveUrl,
    targetImg: 'course-cover',
    uploadToken: 'tmp',
    imageText: '修改封面图片',
    imageSrc: props.manage.imageSrc,
    imageClass: 'course-manage-cover',
  });
};

const getCategory = async () => {
  const category = await Api.category.getCategory();
  categoryOptions.value = transformCategoryData(category.data);
  categoryOptions.value.unshift({ value: '0', label: '无' });
};

const getTabs = async () => {
  const tabs = await Api.tag.getTags('');
  tabOptions.value = tabs.data.map(item => ({
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
      reject(new Error(`标题不能包含尖括号`));
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
      reject(new Error(`输入内容的长度不能超过 ${rule.maxSize} 字节`));
    } else if (rule.minSize && isPositiveInteger(rule.minSize) && byteLength < rule.minSize) {
      reject(new Error(`输入内容的长度不能少于 ${rule.minSize} 字节`));
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
      reject(new Error('字符长度必须小于等于200，一个中文字算2个字符'));
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

const getOrgCodes = async () => {
  const OrgCodes = await Api.organization.getOrgCodes({withoutFormGroup: true, orgCode: formState.orgCode});
};
getOrgCodes();

const serializeOption = [
  {label: '非连载课程', value: 'none'},
  {label: '更新中', value: 'serialized'},
  {label: '已完结', value: 'finished'},
];

const initEditor = () => {
  const editor = CKEDITOR.replace('course-introduction', {
    toolbar: [
      {items: ['FontSize', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']},
      {items: ['Bold', 'Italic', 'Underline', 'TextColor', '-', 'RemoveFormat', 'PasteText', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', 'uploadpictures', 'CodeSnippet', 'Iframe', '-', 'Source', 'kityformula', '-', 'Maximize']}
    ],
    extraPlugins: 'questionblank,smiley,table,font,kityformula,codesnippet,uploadpictures,shortUrl,image2,colorbutton,colordialog,justify,find,filebrowser,pasteimage,katex,iframe',
    filebrowserImageUploadUrl: props.manage.imageUploadUrl,
  });

  editor.setData(formState.summary);

  editor.on('change', () => {
    formState.summary = editor.getData();
  });
};

const validateForm = () => {
  return formRef.value.validate()
    .then(() => {
      return formState;
    })
    .catch((error) => {
    });
};

onMounted(() => {
  if (props.manage.isUnMultiCourseSet) {
    initEditor();
  }
  Object.assign(formState, {
    title: removeHtml(props.manage.courseSet.title),
    subtitle: removeHtml(props.manage.courseSet.subtitle),
    tags: props.manage.tags,
    categoryId: props.manage.course.categoryId,
    orgCode: props.manage.course.orgCode,
    serializeMode: props.manage.course.serializeMode,
    summary: props.manage.courseSet.summary,
  });
  getCover();
  getCategory();
  getTabs();
});

defineExpose({
  validateForm,
});
</script>

<template>
  <div class="flex flex-col w-full">
    <div class="flex flex-col relative" v-if="!props.manage.isUnMultiCourseSet">
      <div class="absolute -left-32 w-full px-32 font-medium py-10 text-14 text-stone-900 bg-[#f5f5f5]"
           style="width: calc(100% + 64px);">基础信息
      </div>
      <a-form
        ref="formRef"
        class="mt-66"
        :model="formState"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 16 }"
      >
        <a-form-item
          label="计划名字"
          name="title"
          :validateTrigger="['blur']"
          :rules="[
          { required: true, message: '请输入计划名称' },
          { validator: interByteValidator, maxSize: 100, minSize: 2 },
          { validator: courseTitleValidator },
          ]"
        >
          <a-input v-model:value.trim="formState.title"/>
        </a-form-item>

        <a-form-item
          label="计划副标题"
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
           style="width: calc(100% + 64px);">基础信息
      </div>
      <a-form
        ref="formRef"
        class="mt-66"
        :model="formState"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 16 }"
      >
        <a-form-item
          label="课程标题"
          name="title"
          :validateTrigger="['blur']"
          :rules="[
          { required: true, message: '请输入课程标题' },
          { validator: courseTitleLengthValidator },
          { validator: courseTitleValidator },
          ]"
        >
          <a-input v-model:value.trim="formState.title"/>
        </a-form-item>

        <a-form-item
          label="课程副标题"
          name="subtitle"
          :validateTrigger="['blur']"
          :rules="[
          { max: 50, message: '最多支持50个字符' },
          ]"
        >
          <a-textarea v-model:value="formState.subtitle" :rows="3"/>
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
            style="width: 250px"
          ></a-select>
          <div class="text-[#adadad] text-12 mt-8 ">用于按标签搜索课程、相关课程的提取等，由网校管理员后台统一管理</div>
        </a-form-item>

        <a-form-item
          label="分类"
        >
          <a-tree-select
            v-model:value="formState.categoryId"
            :tree-data="categoryOptions"
            allow-clear
            tree-default-expand-all
            :show-search="true"
            :treeNodeFilterProp="'label'"
            style="width: 250px"
          ></a-tree-select>
        </a-form-item>

        <a-form-item
          v-if="props.manage.enableOrg"
          label="组织机构"
        >

        </a-form-item>

        <a-form-item
          label="连载状态"
        >
          <a-radio-group class="base-info-radio" v-model:value="formState.serializeMode"
                         :options="serializeOption"/>
        </a-form-item>

        <a-form-item
          label="封面图片"
        >

        </a-form-item>

        <a-form-item
          label="课程简介"
          name="categoryId"
        >
          <textarea id="course-introduction"></textarea>
          <div class="text-[#a1a1a1] font-normal text-14 leading-28">
            为正常使用IFrame，请在【管理后台】-【系统】-【站点设置】-【安全】-【IFrame白名单】中进行设置
          </div>
        </a-form-item>

      </a-form>
    </div>
  </div>
</template>

<style lang="less">
.base-info-radio {
  .ant-radio-wrapper {
    font-weight: 400 !important;
  }
}
</style>
