<script setup>
import {inject, onMounted, reactive, ref, watch} from 'vue';
import Api from '../../../api';

const props = defineProps({
  params: {type: Object, default: {}}
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
}

const formRef = ref(null);
const baseFormState = reactive({
  title: props.params.course.title,
  subtitle: props.params.course.subtitle,
});

if (props.params.isUnMultiCourseSet) {
  Object.assign(baseFormState, {
    tags: props.params.tags,
    categoryId: props.params.course.categoryId,
    orgCode: props.params.course.orgCode,
    serializeMode: props.params.course.serializeMode,
    summary: props.params.courseSet.summary,
    title: removeHtml(props.params.courseSet.title),
    subtitle: removeHtml(props.params.courseSet.subtitle),
  });
}

const parentMessage = inject('needValidatorForm', ref(false));
watch(parentMessage, (newValue) => {
  if (newValue === true) {
    formRef.value.validate()
      .then(() => {
        // 表单验证成功
        console.log('验证通过:', baseFormState);
      })
      .catch((error) => {
        // 表单验证失败
        console.log('验证失败:', error);
      });
  }
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

const tabOptions = ref();
const getTabs = async () => {
  const tabs = await Api.tag.getTags('');
  tabOptions.value = tabs.data.map(item => ({
    label: item.name,
    value: item.name
  }));
};
getTabs();

const categoryOptions = ref();

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

const getCategory = async () => {
  const Category = await Api.category.getCategory();
  categoryOptions.value = transformCategoryData(Category.data);
};
getCategory();

const getOrgCodes = async () => {
  const OrgCodes = await Api.organization.getOrgCodes({withoutFormGroup: true, orgCode: baseFormState.orgCode});
  console.log(OrgCodes.data);
};
getOrgCodes();

const serializeOption = [
  {label: '非连载课程', value: 'none'},
  {label: '更新中', value: 'serialized'},
  {label: '已完结', value: 'finished'},
];

const cover = ref();
const getCover = async () => {
  cover.value = await Api.file.getCourseCover({
    saveUrl: props.params.imageSaveUrl,
    targetImg: 'course-cover',
    uploadToken: 'tmp',
    imageText: '修改封面图片',
    imageSrc: props.params.imageSrc,
    imageClass: 'course-manage-cover',
  });
};
getCover();

const initEditor = () => {
  const editor = CKEDITOR.replace('course-introduction', {
    toolbar: 'Detail',
    filebrowserImageUploadUrl: props.params.imageUploadUrl,
  });

  editor.setData(baseFormState.summary);

  editor.on('change', () => {
    baseFormState.summary = editor.getData();
  });
};

onMounted( () => {
  initEditor();
} )
</script>

<template>
  <div class="flex flex-col w-full">
    <div class="flex flex-col relative" v-if="!props.params.isUnMultiCourseSet">
      <div class="absolute -left-32 w-full px-32 font-medium py-10 text-14 text-stone-900 bg-[#f5f5f5]"
           style="width: calc(100% + 64px);">基础信息
      </div>
      <a-form
        ref="formRef"
        class="mt-66"
        :model="baseFormState"
        name="baseInfo"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 16 }"
        autocomplete="off"
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
          <a-input v-model:value.trim="baseFormState.title"/>
        </a-form-item>

        <a-form-item
          label="计划副标题"
          name="subtitle"
          :validateTrigger="['blur']"
          :rules="[
          { validator: interByteValidator, maxSize: 100 }
          ]"
        >
          <a-textarea v-model:value.trim="baseFormState.subtitle" :rows="3"/>
        </a-form-item>
      </a-form>
    </div>


    <div class="relative" v-if="props.params.isUnMultiCourseSet">
      <div class="absolute -left-32 w-full px-32 font-medium py-10 text-14 text-stone-900 bg-[#f5f5f5]"
           style="width: calc(100% + 64px);">基础信息
      </div>
      <a-form
        ref="formRef"
        class="mt-66"
        :model="baseFormState"
        name="baseInfo"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 16 }"
        autocomplete="off"
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
          <a-input v-model:value.trim="baseFormState.title"/>
        </a-form-item>

        <a-form-item
          label="课程副标题"
          name="subtitle"
          :validateTrigger="['blur']"
          :rules="[
          { max: 50, message: '最多支持50个字符' },
          ]"
        >
          <a-textarea v-model:value="baseFormState.subtitle" :rows="3"/>
        </a-form-item>

        <a-form-item
          label="标签"
          name="tabs"
        >
          <a-select
            v-model:value="baseFormState.tags"
            mode="multiple"
            placeholder="请选择"
            :options="tabOptions"
            allow-clear
          ></a-select>
        </a-form-item>

        <a-form-item
          label="分类"
          name="categoryId"
        >
          <a-tree-select
            v-model:value="baseFormState.categoryId"
            :tree-data="categoryOptions"
            allow-clear
            tree-default-expand-all
            show-search
            :treeNodeFilterProp="'label'"
          ></a-tree-select>
        </a-form-item>

        <!--        <a-form-item v-if="props.params.enableOrg">-->
        <!--          <div class="bg-[#f5f5f5]">组织机构占位</div>-->
        <!--        </a-form-item>-->

        <a-form-item
          label="连载状态"
          name="serializeMode"
        >
          <a-radio-group class="base-info-serialize-radio" v-model:value="baseFormState.serializeMode"
                         :options="serializeOption"/>
        </a-form-item>

        <!--        <a-form-item>-->
        <!--          <div class="bg-[#f5f5f5]">封面图片</div>-->
        <!--        </a-form-item>-->

        <a-form-item
          label="课程简介"
          name="categoryId"
        >
          <textarea id="course-introduction"></textarea>
        </a-form-item>

      </a-form>
    </div>
  </div>
</template>

<style lang="less">
.base-info-serialize-radio {
  .ant-radio-wrapper {
    font-weight: 400 !important;
  }
}
</style>
