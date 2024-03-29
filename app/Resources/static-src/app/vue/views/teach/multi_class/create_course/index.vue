<template>
  <aside-layout :breadcrumbs="[{ name: '新建课程' }]" class="create-course">
    <!-- Tip: Form表单使用组件FormModel更合适，请大家使用FormModel来做表单开发 -->
    <a-form :form="form" :label-col="{ span: 3 }" :wrapper-col="{ span: 21 }" style="max-width: 860px;">
      <a-form-item label="课程类型">
        <a-radio-group
          :options="[{ label: '直播大班课', value: 'live' }]"
          v-decorator="['type', {
            initialValue: 'live'
          }]"
        />
      </a-form-item>
      <a-form-item label="课程标题">
        <a-input v-decorator="['title', { rules: [
            { required: true, message: '请填写课程名称' },
          ]} ]"
          placeholder="请输入课程标题"
        />
      </a-form-item>
      <a-form-item label="课程副标题" >
        <a-textarea auto-size v-decorator="['subtitle']" placeholder="请输入课程副标题" />
      </a-form-item>
      <a-form-item label="封面图片">
        <a-upload
          ref="upload"
          accept="image/*"
          :file-list="[]"
          :customRequest="() => {}"
          list-type="picture-card"
          @change="uploadCourseCover"
        >
        <img v-if="courseCoverUrl" :src="courseCoverUrl" style="width: 100%;" />
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
          @popupScroll="teacherScroll"
          @search="handleSearchTeacher"
          @change="(value) => handleChange(value, 'teacher')"
          v-decorator="['teachers', { rules: [{ required: true, message: '请选择授课老师' }] }]"
          placeholder="请选择授课教师"
        >
          <a-select-option v-for="item in teacher.list" :key="item.id" :disabled="item.disabled">
            {{ item.nickname }}
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
          @popupScroll="assistantScroll"
          @search="handleSearchAssistant"
          @change="(value) => handleChange(value, 'assistant')"
          v-decorator="['assistants', { rules: [{ required: true, message: '至少选择一位助教'}]}]"
          placeholder="请选择一个或多个助教"
        >
          <a-select-option v-for="item in assistant.list" :key="item.id" :disabled="item.disabled">
            {{ item.nickname }}
          </a-select-option>
        </a-select>
      </a-form-item>
      <a-form-item label="价格" style="position: relative;">
        <a-input
          v-decorator="['originPrice', { initialValue: 0, rules: [
            { validator: validatePrice, message: '请输入大于0的有效价格，最多两位小数，整数位不超过8位！' }
          ] }]"
          placeholder="请输入产品价格"
        >
            <span slot="suffix">元</span>
          </a-input>
      </a-form-item>
      <a-form-item label="学习模式">
        <a-radio-group
          :options="[{ label: '自由式', value: 'freeMode' }, { label: '解锁式', value: 'lockMode' }]"
          v-decorator="['learnMode', {
            initialValue: 'freeMode'
          }]"
        />
        <div class="color-gray cd-mt8">
          <template>自由式：学习过程自由安排;</template>
          <template>解锁式：根据既定顺序逐个解锁学习</template>
        </div>
      </a-form-item>
      <a-form-item label="任务完成规则">
        <a-radio-group
          :options="[{ label: '无限制', value: '1' }, { label: '由任务完成条件决定', value: '0' }]"
          v-decorator="['enableFinish', {
            initialValue: '1'
          }]"
        />
        <div class="color-gray cd-mt8">
          <template>必须达到完成条件，任务才算完成</template>
        </div>
      </a-form-item>
      <a-form-item label="课程人数" v-if="form.getFieldValue('type') === 'live'">
        <a-input
          style="width: 100%;"
          v-decorator="['maxStudentNum', { rules: [
            { required: true, message: '请输入课程人数' },
            { validator: validateRange },
          ]}]"
          placeholder="请输入课程人数"
        >
            <span slot="suffix">人</span>
          </a-input>
      </a-form-item>
      <a-form-item label="是否可加入">
        <a-switch v-model="formInfo.buyable"  />
        <div class="color-gray cd-mt8">
          <template>关闭后，前台显示为“限制课程”，学员自己无法加入，需要由老师手动添加学员。常用于封闭型教学。</template>
        </div>
      </a-form-item>
      <a-form-item label="加入截止日期">
        <div style="overflow: hidden">
          <a-radio-group class="pull-left mt12"
            :options="[{ label: '不限制', value: '0' }, { label: '自定义', value: '1' }]"
            v-decorator="['enableBuyExpiryTime', {
              initialValue: '0',
              rules: [{ required: true, message: '请输入加入截止日期' }]
            }]"
          />
          <a-form-item class="pull-left" style="margin: 4px 0 0;" v-if="form.getFieldValue('enableBuyExpiryTime') === '1'">
            <a-date-picker placeholder=""
              v-decorator="['buyExpiryTime', {
                rules: [{ required: true, message: '请输入加入截止日期' }]
              }]"
            />
          </a-form-item>
        </div>
      </a-form-item>
      <a-form-item label="学习有效期" style="margin-bottom: 0;">
        <a-radio-group
          :options="[
            { label: '随到随学', value: 'days' },
            { label: '固定周期', value: 'date' },
            { label: '长期有效', value: 'forever' },
          ]"
          v-decorator="['expiryMode', { initialValue: 'forever' }]"
        />
        <a-tooltip overlayClassName='expand-tooltip'>
          <template slot="title">
            随到随学：有效期从学员加入的当天开始算起，截至到期当天晚上的23:59<br>
            固定周期：有固定的学习开始日期和结束日期<br>
            过期后无法继续学习，系统会在到期前10天提醒学员。
          </template>
          <a>
            <a-icon type="question-circle" style="margin-left: -6px;"/>
          </a>
        </a-tooltip>
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
          <span class="ml8">在此日期前，学员可进行学习。</span>
        </a-form-item>
        <a-form-item v-else>
          <a-input
            style="width: 200px;"
            v-decorator="['expiryDays', {
              rules: [{ required: true, message: '请输入有效期天数' }]
            }]" />
          <span class="ml8">从加入当天起，在几天内可进行学习。</span>
        </a-form-item>
      </a-form-item>
      <a-form-item v-if="form.getFieldValue('expiryMode') === 'date'"
        style="position: relative;left: 12.5%;overflow: hidden"
      >
        <a-form-item class="pull-left">
          <span class="mr8">开始日期</span>
          <a-date-picker v-decorator="['expiryStartDate', {
            rules: [{ required: true, message: '请输入开始日期' }]
          }]" />
        </a-form-item>
        <a-form-item class="pull-left ml12">
          <span class="mr8">结束日期</span>
          <a-date-picker v-decorator="['expiryEndDate', {
            rules: [{ required: true, message: '请输入结束日期' }]
          }]" />
        </a-form-item>
      </a-form-item>
    </a-form>

    <div class="create-course-btn-group">
      <a-button class="save-course-btn" type="primary" @click="saveCourseSet" :loading="ajaxLoading">创建课程</a-button>
      <a-button @click="goToMultiClassCreatePage()">取消</a-button>
    </div>

    <a-modal
      :key='cropModal'
      :visible="cropModalVisible"
      @cancel="cropModalVisible = false;courseCoverUrl = ''">
      <vue-cropper
        ref="cropper"
        :aspect-ratio="16 / 9"
        :src="imgUrl"
      >
      </vue-cropper>
      <template slot="footer">
        <a-button @click="reSelectCourseCover">重新选择</a-button>
        <a-button type="primary" @click="saveCourseCover" :loading="uploading">保存图片</a-button>
      </template>
    </a-modal>
  </aside-layout>
</template>

<script>
  import AsideLayout from 'app/vue/views/layouts/aside.vue';
  import _ from 'lodash';
  import VueCropper from 'vue-cropperjs';
  import 'cropperjs/dist/cropper.css';
  import { Teacher, Assistant, CourseSet, UploadToken, File, LiveCapacity } from 'common/vue/service/index.js';

  export default {
    name: 'CreateCourse',
    props: {},

    components: {
      VueCropper,
      AsideLayout
    },

    data () {
      return {
        cropModal: 0,
        form: this.$form.createForm(this),
        formInfo: {
          buyable: true,
        },
        teacher: {
          list: [],
          title: '',
          flag: true,
          initialValue: undefined,
          paging: {
            pageSize: 10,
            current: 0
          }
        },
        assistant: {
          list: [],
          title: '',
          flag: true,
          initialValue: [],
          paging: {
            pageSize: 10,
            current: 0
          }
        },
        courseCoverUrl: '',
        imgUrl: '',
        cropModalVisible: false,
        loading: false,
        editor: {},
        ajaxLoading: false,
        uploadToken: {},
        courseCoverName: '',
        uploading: false,
        imgs: null,
        imageUploadUrl: '/editor/upload?token=',
        flashUploadUrl: '/editor/upload?token=',
        liveCapacity: 0,
      };
    },
    created() {
      this.fetchAssistants();
      this.fetchTeacher();
      this.getLiveCapacity();
    },
    async mounted() {
      await this.getEditorUploadToken()
      this.editor = CKEDITOR.replace('summary', {
        allowedContent: true,
        toolbar: 'Detail',
        fileSingleSizeLimit: app.fileSingleSizeLimit,
        filebrowserImageUploadUrl: this.imageUploadUrl,
        filebrowserFlashUploadUrl: this.flashUploadUrl
      });
    },
    methods: {
      async getUploadToken() {
        this.uploadToken = await UploadToken.get('default')

        return Promise.resolve(1);
      },

      async getEditorUploadToken() {
        const { token } = await UploadToken.get('course')

        this.imageUploadUrl += token
        this.flashUploadUrl += token

        return Promise.resolve(1);
      },

      async getLiveCapacity() {
        const { capacity } = await LiveCapacity.search()

        this.liveCapacity = Number(capacity)
      },

      saveCourseSet() {
        this.form.validateFields(async (err, values) => {
          if (err) return;

          this.ajaxLoading = true
          values = this.formatValues(values)

          try {
            const { error, defaultCourseId: id, title: courseSetTitle, id: courseSetId, title, course } = await CourseSet.add(values);

            if (!error) {
              this.$message.success('创建成功');
              let maxStudentNum = course.maxStudentNum;
              this.goToMultiClassCreatePage({ id, title, courseSetId, courseSetTitle, maxStudentNum })
            }
          } finally {
            this.ajaxLoading = false;
          }
        })
      },

      disabledTeacher(value) {
        const assistantIds = value || this.form.getFieldValue('assistants') || this.assistant.initialValue;
        _.forEach(this.teacher.list, item => {
          if (!_.includes(assistantIds, item.id)) {
            item.disabled = false;
          }
          _.forEach(assistantIds, id => {
            if (item.id == id) {
              item.disabled = true;
            }
          });
        });
      },

      disabledAssistant(value) {
        const teacherId = value || this.form.getFieldValue('teachers') || this.teacher.initialValue;
        _.forEach(this.assistant.list, item => {
          if (item.id == teacherId) {
            item.disabled = true;
          } else {
            item.disabled = false;
          }
        });
      },

      formatValues(values = {}) {
        values.summary = this.editor.getData()
        values.teachers = [values.teachers]
        values = _.assignIn(values, {
          buyable: Number(this.formInfo.buyable)
        })

        if (this.imgs) {
          values.images = this.imgs;
        }

        for (const key in values) {
          if (['buyExpiryTime', 'expiryStartDate', 'expiryEndDate', 'deadline'].includes(key)) {
            values[key] = (new Date(values[key])).getTime()
            values[key] = _.floor(values[key] / 1000)
          }
        }

        return values
      },

      fetchTeacher() {
        const { title, paging: { pageSize, current } } = this.teacher;
        const params = {
          limit: pageSize,
          offset: pageSize * current
        };
        if (title) {
          params.nickname = title;
        }
        Teacher.search(params).then(res => {
          this.teacher.paging.current++;
          this.teacher.list = _.concat(this.teacher.list, res.data);
          if (_.size(this.teacher.list) >= res.paging.total) {
            this.teacher.flag = false;
          }
          this.disabledTeacher();
        });
      },

      handleSearchTeacher: _.debounce(function(input) {
        this.teacher = {
          list: [],
          title: input,
          flag: true,
          paging: {
            pageSize: 10,
            current: 0
          }
        };
        this.fetchTeacher();
      }, 300),

      teacherScroll: _.debounce(function (e) {
        const { scrollHeight, offsetHeight, scrollTop } = e.target;
        const maxScrollTop = scrollHeight - offsetHeight - 20;
        if (maxScrollTop < scrollTop && this.teacher.flag) {
          this.fetchTeacher();
        }
      }, 300),

      fetchAssistants() {
        const { title, paging: { pageSize, current } } = this.assistant;
        const params = {
          limit: pageSize,
          offset: pageSize * current
        };

        if (title) {
          params.nickname = title;
        }

        Assistant.search(params).then(res => {
          this.assistant.paging.current++;
          this.assistant.list = _.concat(this.assistant.list, res.data);
          if (_.size(this.assistant.list) >= res.paging.total) {
            this.assistant.flag = false;
          }
          this.disabledAssistant();
        });
      },

      handleSearchAssistant: _.debounce(function(input) {
        this.assistant = {
          list: [],
          title: input,
          flag: true,
          paging: {
            pageSize: 10,
            current: 0
          }
        };
        this.fetchAssistants();
      }, 300),

      assistantScroll: _.debounce(function (e) {
        const { scrollHeight, offsetHeight, scrollTop } = e.target;
        const maxScrollTop = scrollHeight - offsetHeight - 20;
        if (maxScrollTop < scrollTop && this.assistant.flag) {
          this.fetchAssistants();
        }
      }, 300),

      handleChange(value, type) {
        console.log(value);
        if (type === 'teacher') {
          this.disabledAssistant(value);
          return;
        }
        if (type === 'assistant') {
          this.disabledTeacher(value);
        }
      },

      switchBuyAble(checked) {
        this.$set(this.formInfo, 'buyable', checked)
      },

      uploadCourseCover(info) {
        this.cropModal += 1;
        const reader = new FileReader();

        reader.onload = (event) => {
          this.imgUrl = event.target.result;
          this.cropModalVisible = true;
        };

        this.courseCoverName = info.file.originFileObj.name
        reader.readAsDataURL(info.file.originFileObj);
      },
      reSelectCourseCover () {
        const $inputs = this.$refs.upload.$el.getElementsByTagName('input');

        this.cropModalVisible = false;

        if ($inputs.length > 0) {
          $inputs[0].click()
        }
      },
      async saveCourseCover() {
        this.loading = true;

        if (!this.uploadToken.expiry || (new Date() >= new Date(this.uploadToken.expiry))) {
          await this.getUploadToken()
        }

        this.$refs.cropper.getCroppedCanvas().toBlob(async blob => {
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
            'imgs[large][0]': 480,
            'imgs[large][1]': 270,
            'imgs[middle][0]': 304,
            'imgs[middle][1]': 171,
            'imgs[small][0]': 96,
            'imgs[small][1]': 54,
            post: false,
            width: imageData.naturalWidth, // 原图片宽度
            height: imageData.naturalHeight, // 原图片高度
            group: 'course',
            post: false,
          }
          const formData = new FormData();

          formData.append('file', blob, this.courseCoverName);
          formData.append('token', this.uploadToken.token);

          this.uploading = true;
          try {
            const { url } = await File.uploadFile(formData)

            this.imgUrl = url;
            this.courseCoverUrl = url;

            const formData1 = new FormData();
            for(const key in cropResult) {
              formData1.append(key, cropResult[key])
            }

            this.imgs = await File.imgCrop(formData1);
          } finally {
            this.uploading = false;
            this.cropModalVisible = false;
            this.loading = false;
          }
        })
      },
      requiredValidator(rule, value, callback) {
        if (!value) {
          callback(rule.message)
        }

        callback()
      },
      goToMultiClassCreatePage(course) {
        if (!course) {
          this.$router.go(-1)
          return
        }

        if (!_.isObject(course)) {
          return
        }
        if (this.$route.query.type === 'group') {
            this.$router.replace({
            name: 'MultiClassCreateGroup',
            query: {
              course: JSON.stringify(course)
            }
          })
          return
        }
          this.$router.replace({
            name: 'MultiClassCreate',
            query: {
              course: JSON.stringify(course)
            }
          })
      },
      validatePrice(rule, value, callback) {
        if (/^[0-9]{0,8}(\.\d{0,2})?$/.test(value) === false) {
          callback(rule.message)
        }

        callback()
      },
      validateRange(rule, value, callback) {
        value = Number(value)

        if (!_.isInteger(value) || value < 0) {
          callback(`请输入正整数`)
        } else if (value > this.liveCapacity) {
          callback(`网校可支持最多${this.liveCapacity}人同时参加直播。`)
        }

        callback()
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
    padding: @spacing-3x 0;
    border-top: solid 1px @border;
    background-color: @bg;
  }

  .expand-tooltip .ant-tooltip-content{
    width: 500px !important;
  }
</style>
