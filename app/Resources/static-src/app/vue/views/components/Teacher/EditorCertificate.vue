<template>
  <div>
    <a-form-model
      ref="ruleForm"
      :model="form"
      :rules="rules"
      :label-col="labelCol"
      :wrapper-col="wrapperCol"
    >
      <a-form-model-item label="照片" prop="region">
        <upload-picture
          :aspect-ratio="1 / 1"
          tip="请上传jpg, gif, png格式的图片，建议图片尺寸为 270×270px，建议图片大小不超过2MB"
          @success="uploadedSuccessfully"
        />
      </a-form-model-item>
    </a-form-model>
  </div>
</template>

<script>
import UploadPicture from 'app/vue/components/UploadPicture.vue';

export default {
  name: 'EditorTeacherCertificate',

  components: {
    UploadPicture
  },

  data() {
    return {
      labelCol: { span: 4 },
      wrapperCol: { span: 16 },
      form: {
        name: '',
        region: undefined,
        date1: undefined,
        delivery: false,
        type: [],
        resource: '',
        desc: '',
      },
      rules: {

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

    resetForm() {
      this.$refs.ruleForm.resetFields();
    },

    uploadedSuccessfully(img) {
      console.log(img);
    }
  }
}
</script>
