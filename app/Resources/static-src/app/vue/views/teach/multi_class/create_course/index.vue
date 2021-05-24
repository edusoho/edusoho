<template>
  <div class="create-course">
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
        <a-select
          show-search
          :default-active-first-option="false"
          :show-arrow="false"
          :filter-option="false"
          :not-found-content="null"
          @search="searchTeachers"
          v-decorator="['teachers', { rules: [{ required: true, message: '请选择授课老师' }] }]"
        >
          <a-select-option v-for="teacher in teachersList" :key="teacher.id">
            {{ teacher.nickname }}
          </a-select-option>
        </a-select>
      </a-form-item>
      <a-form-item label="助教">
        <a-select
          mode="multiple"
          :default-active-first-option="false"
          :show-arrow="false"
          :filter-option="false"
          :not-found-content="null"
          @search="searchAssistants"
          v-decorator="['assistants', { rules: [{ required: true, message: '至少选择一位助教'}]}]"
        >
          <a-select-option v-for="assistant in assistantsList" :key="assistant.id">
            {{ assistant.nickname }}
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
        <a-switch v-model="formInfo.buyable"  />
      </a-form-item>
      <a-form-item label="加入截止日期">
        <div style="overflow: hidden">
          <a-radio-group class="pull-left mt3"
            :options="[{ label: '不限制', value: '1' }, { label: '自定义', value: '0' }]"
            v-decorator="['enableBuyExpiryTime', {
              initialValue: '1'
            }]"
          />
          <a-form-item class="pull-left" v-if="form.getFieldValue('enableBuyExpiryTime') === '0'">
            <a-date-picker placeholder=""
              v-decorator="['buyExpiryTime', {
                rules: [{ required: true, message: '请输入加入截止日期' }]
              }]"
            />
          </a-form-item>
        </div>
      </a-form-item>
      <a-form-item label="学习有效期" >
        <a-radio-group
          :options="[
            { label: '随到随学', value: 'days' },
            { label: '固定周期', value: 'date' },
            { label: '长期有效', value: 'forever' },
          ]"
          v-decorator="['expiryMode', { initialValue: 'forever' }]"
        />
      </a-form-item>
      <a-form-item v-if="form.getFieldValue('expiryMode') === 'days'"
        style="position: relative;left: 12.5%;"
      >
        <a-radio-group
          :options="[
            { label: '按截止日期', value: 'end_date' },
            { label: '按有效天数', value: 'days' },
          ]"
          v-decorator="['deadlineType', { initialValue: 'days' }]"
        />
        <a-form-item v-if="form.getFieldValue('deadlineType') === 'end_date'">
          <a-date-picker
            v-decorator="['deadline', {
              rules: [{ validator: requiredValidator, message: '请输入截止日期' }]
            }]" />
          在此日期前，学员可进行学习。
        </a-form-item>
        <a-form-item v-if="form.getFieldValue('deadlineType') !== 'end_date'">
          <a-input
            style="width: 200px;"
            v-decorator="['expiryDays', {
              rules: [{ required: true, message: '请输入有效期天数' }]
            }]" />
          从加入当天起，在几天内可进行学习。
        </a-form-item>
      </a-form-item>
      <a-form-item v-if="form.getFieldValue('expiryMode') === 'date'"
        style="position: relative;left: 12.5%;overflow: hidden"
      >
      <a-form-item class="pull-left">
        开始日期
        <a-date-picker v-decorator="['expiryStartDate', {
          rules: [{ required: true, message: '请输入开始日期' }]
        }]" />
      </a-form-item>
      <a-form-item class="pull-left ml2">
        结束日期
        <a-date-picker v-decorator="['expiryEndDate', {
          rules: [{ required: true, message: '请输入结束日期' }]
        }]" />
      </a-form-item>
      </a-form-item>
    </a-form>

    <div class="create-course-btn-group">
      <a-button class="save-course-btn" type="primary" @click="saveCourseSet" :loading="ajaxLoading">创建课程</a-button>
      <a-button class="ml2" @click="saveCourseSet">取消</a-button>
    </div>

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
  import { Teachers, Assistants, CourseSet, UploadToken } from 'common/vue/service/index.js';

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
    name: 'CreateCourse',
    props: {},
    components: { VueCropper },
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
        editor: {},
        ajaxLoading: false,
      };
    },
    mounted() {
      this.editor = CKEDITOR.replace('summary', {
        allowedContent: true,
        toolbar: 'Detail',
        fileSingleSizeLimit: app.fileSingleSizeLimit,
        filebrowserImageUploadUrl: this.uploadUrl // TODO {{ path('editor_upload', {token:upload_token('course')}) }}
      });
    },
    methods: {
      saveCourseSet() {
        this.form.validateFields(async (err, values) => {
          if (err) return;

          this.ajaxLoading = true
          values.summary = this.editor.getData()
          values.teachers = [values.teachers]

          try {
            const { error } = await CourseSet.add(values);

            if (!error) {
              this.$message.success('创建成功')
              // TODO 页面跳转
            }
          } finally {
            this.ajaxLoading = false;
          }
        })
      },
      searchTeachers: _.debounce(async function(nickname) {
        const { data } = await Teachers.search({ nickname })

        this.teachersList = data
      }, 300),
      searchAssistants: _.debounce(async function(nickname) {
        const { data } = await Assistants.search({ nickname })

        this.assistantsList = data
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

          // TODO 上传图片接口；现在差token
        })
      },
      requiredValidator(rule, value, callback) {
        if (!value) {
          callback(rule.message)
        }
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

  .create-course {
    padding-bottom: 64px;

    .save-course-btn {
      margin-left: 12.5%;
    }
  }

  .create-course-btn-group {
    position: fixed;
    bottom: 0;
    right: 64px;
    left: 200px;
    padding: @spacing-6x 0;
    border-top: solid 1px @border;
    background-color: @bg;
  }
</style>
