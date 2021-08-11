<template>
  <a-form-model
    ref="ruleForm"
    :key="userId"
    :model="form"
    :rules="rules"
    :label-col="labelCol"
    :wrapper-col="wrapperCol"
  >
    <a-form-model-item ref="truename" label="姓名" prop="truename">
      <a-input v-model="form.truename" @blur="() => { $refs.truename.onFieldBlur(); }" />
    </a-form-model-item>

    <a-form-model-item label="照片" prop="avatarFileId">
      <upload-picture
        :file="file"
        :aspect-ratio="1 / 1"
        tip="请上传jpg, gif, png格式的图片，建议图片尺寸为 270×270px，建议图片大小不超过2MB"
        @success="uploadedSuccessfully"
      />
    </a-form-model-item>

    <a-form-model-item ref="code" label="教师资格证书编号" prop="code">
      <a-input v-model="form.code" @blur="() => { $refs.code.onFieldBlur();}" />
    </a-form-model-item>

    <a-form-model-item :wrapper-col="{ span: 14, offset: 4 }">
      <a-button type="primary" @click="onSubmit">
        保存
      </a-button>
    </a-form-model-item>
  </a-form-model>
</template>

<script>
import _ from 'lodash';
import { TeacherQualification } from 'common/vue/service/index.js';
import UploadPicture from 'app/vue/components/UploadPicture.vue';

export default {
  name: 'EditorTeacherQualification',

  components: {
    UploadPicture
  },

  props: {
    userId: {
      type: String,
      required: true
    },

    editInfo: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      labelCol: { span: 4 },
      wrapperCol: { span: 16 },
      form: {
        truename: '',
        avatarFileId: '',
        code: '',
      },
      rules: {
        truename: [
          { required: true, message: '请输入姓名', trigger: 'blur' },
          { min: 2, message: '最少需要输入 2 个字符', trigger: 'blur' },
          { max: 36, message: '最多只能输入 36 个字符', trigger: 'blur' },
          { pattern: /^[\u4E00-\u9FA5A-Za-z0-9_]+$/, message: '只支持中文字、英文字母、数字及_', trigger: 'blur' }
        ],
        avatarFileId: [{ required: true, message: '请上传图片' }],
        code: [
          { required: true, message: '请输入教师资格证书编号', trigger: 'blur' },
          { len: 17, message: '请输入 17 位字符', trigger: 'blur' },
          { pattern: /^[0-9]*$/, message: '请输入整数', trigger: 'blur' }
        ]
      },
      file: ''
    }
  },

  watch: {
    editInfo() {
      this.setFormValue();
    }
  },

  created() {
    this.setFormValue();
  },

  methods: {
    setFormValue() {
      const { truename, avatarFileId, code, url } = this.editInfo;
      _.assign(this.form, {
        truename,
        avatarFileId,
        code
      });
      this.file = url;
    },

    onSubmit() {
      this.$refs.ruleForm.validate(async valid => {
        if (!valid) return;

        const result = await TeacherQualification.add({ ...this.form, userId: this.userId });
        this.$message.success('保存成功');
        this.$emit('handle-cancel-modal', result);
      });
    },

    uploadedSuccessfully(id) {
      this.form.avatarFileId = id;
    }
  }
}
</script>
