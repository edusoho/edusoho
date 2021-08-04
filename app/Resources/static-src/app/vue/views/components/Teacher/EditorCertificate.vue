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
        <a-upload
          ref="upload"
          accept="image/*"
          :file-list="[]"
          list-type="picture-card"
          :customRequest="() => {}"
          @change="uploadTeacherPicture"
        >
          <img v-if="teacherPictureUrl" :src="teacherPictureUrl" />
          <div v-else>
            <a-icon :type="loading ? 'loading' : 'plus'" />
            <div class="ant-upload-text">
              上传照片
            </div>
          </div>
        </a-upload>
      </a-form-model-item>
    </a-form-model>

    <cropper-modal
      :visible="cropperModalVisible"
      :img-url="imgUrl"
      :img-name="teacherPictureName"
      :aspect-ratio="1 / 1"
      @cancal="handleCancel"
      @reselect="handleReselect"
      @save="handlesave"
    />
  </div>
</template>

<script>
import CropperModal from 'app/vue/views/components/CropperModal.vue';

export default {
  name: 'EditorTeacherCertificate',

  components: {
    CropperModal
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

      },
      loading: false,
      teacherPictureName: '',
      teacherPictureUrl: '',
      imgUrl: '',
      cropperModalVisible: false
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

    uploadTeacherPicture(info) {
      const reader = new FileReader();

      reader.onload = (event) => {
        this.imgUrl = event.target.result;
        this.cropperModalVisible = true;
      };

      this.teacherPictureName = info.file.originFileObj.name;
      reader.readAsDataURL(info.file.originFileObj);
    },

    handleCancel() {
      this.cropperModalVisible = false;
    },

    handleReselect() {
      const $inputs = this.$refs.upload.$el.getElementsByTagName('input');

      this.cropperModalVisible = false;

      if ($inputs.length > 0) {
        $inputs[0].click()
      }
    },

    handlesave(obj) {
      console.log(obj);
    }
  }
}
</script>

<style lang="less" scoped>
@import "~common/variable.less";

.ant-upload-select-picture-card {
  i {
    font-size: 32px;
    color: @gray;
  }
}

.ant-upload-select-picture-card .ant-upload-text {
  margin-top: 8px;
  color: @gray-dark;
}
</style>
