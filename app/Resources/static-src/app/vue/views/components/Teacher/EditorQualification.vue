<template>
  <a-form-model
    ref="ruleForm"
    :model="form"
    :rules="rules"
    :label-col="labelCol"
    :wrapper-col="wrapperCol"
  >
    <a-form-model-item ref="name" label="姓名" prop="name">
      <a-input v-model="form.name" @blur="() => {$refs.name.onFieldBlur();}" />
    </a-form-model-item>

    <a-form-model-item label="照片" prop="picture">
      <upload-picture
        :aspect-ratio="1 / 1"
        tip="请上传jpg, gif, png格式的图片，建议图片尺寸为 270×270px，建议图片大小不超过2MB"
        @success="uploadedSuccessfully"
      />
    </a-form-model-item>

    <a-form-model-item ref="number" label="教师资格证书编号" prop="number">
      <a-input v-model="form.number" @blur="() => {$refs.number.onFieldBlur();}" />
    </a-form-model-item>

    <a-form-model-item :wrapper-col="{ span: 14, offset: 4 }">
      <a-button type="primary" @click="onSubmit">
        保存
      </a-button>
    </a-form-model-item>
  </a-form-model>
</template>

<script>
import UploadPicture from 'app/vue/components/UploadPicture.vue';

export default {
  name: 'EditorTeacherQualification',

  components: {
    UploadPicture
  },

  data() {
    return {
      labelCol: { span: 4 },
      wrapperCol: { span: 16 },
      form: {
        name: '',
        imgUrl: '',
        number: '',
      },
      rules: {
        name: [
          { required: true, message: '请输入姓名', trigger: 'blur' },
          { min: 3, max: 5, message: 'Length should be 3 to 5', trigger: 'blur' }
        ],
        picture: [
          { required: true, message: 'Please select activity resource', trigger: 'change' },
        ],
        number: [
          { required: true, message: '请输入编号', trigger: 'blur' },
          { min: 3, max: 5, message: 'Length should be 3 to 5', trigger: 'blur' }
        ]
      }
    }
  },

  methods: {
    onSubmit() {
      this.$refs.ruleForm.validate(valid => {
        if (valid) {
          alert('submit!');
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },

    uploadedSuccessfully(img) {
      console.log(img);
    }
  }
}
</script>
