<template>
  <div>
    <a-form :form="form" :label-col="{ span: 3 }" :wrapper-col="{ span: 21 }">
      <a-form-item label="课程类型">
        <a-radio-group 
          :options="[{ label: '普通课程', value: 'normal' }, { label: '直播课程', value: 'live' }]"
          v-decorator="['type', {
            initialValue: 'normal'
          }]"
        />
      </a-form-item>
      <a-form-item label="课程标题">
        <a-input v-decorator="['title', { rules: [
            { required: true, message: '请填写课程名称' },
          ]} ]"
        />
      </a-form-item>
      <a-form-item label="课程副标题" >
        <a-textarea auto-size v-decorator="['subTitle']" />
      </a-form-item>
      <a-form-item label="封面图片">
        <a-upload
          ref="upload"
          accept="image/*"
          :file-list="[]"
          list-type="picture-card"
          @change="uploadCourseCover"
        >
        <img style="width: 100%;" v-if="courseCoverUrl" :src="courseCoverUrl" />
        <div v-else>
          <a-icon :type="loading ? 'loading' : 'plus'" />
          <div class="ant-upload-text">
            上传照片
          </div>
        </div>
      </a-upload>
      </a-form-item>
      <a-form-item label="课程简介">
        <div id="summary"></div>
      </a-form-item>
      <a-form-item label="授课教师">
        <a-auto-complete
          v-decorator="['teachers', { rules: [{ required: true, message: '请选择授课老师' }] }]"
          :data-source="teachersList"
          @search="searchTeachers"
        />
      </a-form-item>
      <a-form-item label="助教" :assistants="[1, 2]">
        <a-select mode="tags" @change="searchAssistants" 
          v-decorator="['assistants', { rules: [{ required: true, message: '至少选择一位助教'}]}]"
        >
          <a-select-option v-for="i in 25" :key="(i + 9).toString(36) + i">
            {{ (i + 9).toString(36) + i }}
          </a-select-option>
        </a-select>
      </a-form-item>
      <a-form-item label="价格">
        <a-input suffix="元" v-decorator="['originPrice', {}]" />
      </a-form-item>
      <a-form-item label="学习模式">
        <a-radio-group 
          :options="[{ label: '自由式', value: 'freeMode' }, { label: '解锁式', value: 'lockMode' }]"
          v-decorator="['learnMode', {
            initialValue: 'lockMode'
          }]"
        />
        <div class="color-gray cd-mt8">
          <template>自由式：学习过程自由安排</template>
          <template>解锁式：根据既定顺序逐个解锁学习</template>
        </div>
      </a-form-item>
      <a-form-item label="任务完成规则">
        <a-radio-group 
          :options="[{ label: '无限制', value: '1' }, { label: '任务完成', value: '2' }]"
          v-decorator="['enableFinish', {
            initialValue: '2'
          }]"
        />
        <div class="color-gray cd-mt8">
          <template>必须达到完成条件，任务才算完成</template>
        </div>
      </a-form-item>
      <a-form-item label="是否可加入">
        <!-- @change="switchBuyAble" -->
        <a-switch v-model="formInfo.buyable"  />
      </a-form-item>
      <a-form-item label="加入截止日期" buyExpiryTime="">
        <a-radio-group 
          :options="[{ label: '不限制', value: '1' }, { label: '自定义', value: '0' }]"
          v-decorator="['enableBuyExpiryTime', {
            initialValue: '1'
          }]"
        />
        <template v-if="form.getFieldValue('enableBuyExpiryTime') === '0'">
          日期选择器
        </template>
      </a-form-item>
      <a-form-item label="学习有效期" expiryMode>
        <a-radio-group 
          :options="[
            { label: '随到随学', value: 'days' }, 
            { label: '固定周期', value: 'date' },
            { label: '长期有效', value: 'forever' },
          ]"
          default-value="forever" 
        />
      </a-form-item>
      <a-form-item>
        
      </a-form-item>
      <a-form-item>
        <a-button @click="handleSubmit">确定</a-button>
      </a-form-item>
    </a-form>

    <a-modal 
      :visible="cropModalVisible" 
      @cancel="cropModalVisible = false">
      <vue-cropper
        ref="cropper"
        :aspect-ratio="16 / 9"
        :src="courseCoverUrl"
      >
      </vue-cropper>
      <template slot="footer">
        <a-button>重新选择</a-button>
        <a-button type="primary" @click="saveCourseCover">保存图片</a-button>
      </template>
    </a-modal>
  </div>
</template>

<script>
  import _ from 'lodash';
  import VueCropper from 'vue-cropperjs';
  import 'cropperjs/dist/cropper.css';

  const images = {
    large: [480, 270],
    middle: [304, 171],
    small: [96,]
  }

  function getBase64(img, callback) {
    const reader = new FileReader();
    reader.addEventListener('load', () => callback(reader.result));
    reader.readAsDataURL(img);
  }

  export default {
    name: '',
    props: {},
    components: { VueCropper},
    data () {
      return {
        form: this.$form.createForm(this),
        formInfo: {
          buyable: false,
          deadline: '',
          deadlineType: '',
          expiryDays: '',
          expiryStartDate: '',
          expiryEndDate: '',
        },
        teachersList: [],
        assistantsList: [],
        courseCoverUrl: '',
        cropModalVisible: false,
        loading: false,
      };
    },
    mounted() {
      CKEDITOR.replace('summary', {
        allowedContent: true,
        toolbar: 'Detail',
        fileSingleSizeLimit: app.fileSingleSizeLimit,
        filebrowserImageUploadUrl: this.uploadUrl // {{ path('editor_upload', {token:upload_token('course')}) }}
      });
    },
    methods: {
      handleSubmit() {
        this.form.validateFields();
      },
      searchTeachers: _.debounce(function() {
      }, 300),
      searchAssistants: _.debounce(function() {
      }, 300),
      switchBuyAble(checked) {
        this.$set(this.formInfo, 'buyable', checked)
      },
      uploadCourseCover(info) {
        this.loading = true
        const reader = new FileReader();

        reader.onload = (event) => {
          this.courseCoverUrl = event.target.result;
          this.cropModalVisible = true
          this.loading = false;
        };

        reader.readAsDataURL(info.file.originFileObj);
      },
      saveCourseCover() {
        this.$refs.cropper.getCroppedCanvas().toBlob(blob => {
          const { x, y, width, height } = this.$refs.cropper.getData();
          const imageData = this.$refs.cropper.getImageData();
          const cropperData = {
            x: _.ceil(_.max([0, x])),
            y: _.ceil(_.max([0, y])),
            width: _.ceil(width),
            height: _.ceil(height)
          }
          const cropResult = {
            x: cropperData.x,
            y: cropperData.y,
            x2: _.add(cropperData.x, cropperData.width),
            y2: _.add(cropperData.y, cropperData.height),
            w: cropperData.width, // 裁剪后宽度
            h: cropperData.height, // 裁剪后高度
            imgs: {
              large: [480, 270],
              middle: [304, 171],
              small: [96,]
            },
            post: false,
            width: imageData.naturalWidth, // 原图片宽度
            height: imageData.naturalHeight, // 原图片高度
            group: 'course',
          }
          const formData = new FormData();

          formData.append(file, blob);
        })
      }
    }
  }
</script>

<style lang="less">
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