<template>
  <aside-layout :breadcrumbs="[{ name: breadcrumbName }]" style="padding-bottom: 88px;">
    <!-- Tip: Form表单使用组件FormModel更合适，请大家使用FormModel来做表单开发 -->
    <a-form
      :form="form"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 20 }"
      autoComplete="off"
      style="max-width: 1000px;"
    >
      <a-form-item label="班课名称">
        <a-input
          v-decorator="['title', {
            trigger: 'blur',
            rules: [
              { required: true, message: '请填写班课名称' },
              { validator: validatorTitle }
            ]
          }]"
          placeholder="请输入班课名称"
        />
      </a-form-item>

      <a-form-item label="选择课程">
        <a-row :gutter="16">
          <a-col :span="mode === 'editor' ? 24 : 20">
            <a-select
              show-search
              v-decorator="['courseId', {
                initialValue: course.initialValue,
                rules: [
                  { required: true, message: '请选择课程' }
                ]
              }]"
              :filter-option="false"
              placeholder="请选择课程"
              :disabled="mode === 'editor'"
              @popupScroll="courseScroll"
              @search="handleSearchCourse"
              @change="handleChangeCourse"
            >
              <a-select-option v-for="item in course.list" :key="item.id">
                {{ item.courseSetTitle }}
              </a-select-option>
            </a-select>
          </a-col>
          <a-col :span="4" v-if="mode !== 'editor'">
            <a-button type="primary" :block="true" @click="$router.push({ name: 'MultiClassCreateCourse', query: { type: 'group' } })">
              <a-icon type="plus" />
              创建新课程
            </a-button>
          </a-col>
        </a-row>
      </a-form-item>

      <a-form-item label="所属产品">
        <a-select
          show-search
          :filter-option="false"
          v-decorator="['productId', {
            initialValue: product.initialValue,
            rules: [
              { required: true, message: '请选择归属产品' }
            ]
          }]"
          placeholder="请选择归属产品"
          @popupScroll="productScroll"
          @search="handleSearchProduct"
        >
          <a-select-option v-for="item in product.list" :key="item.id">
            {{ item.title }}
          </a-select-option>
        </a-select>
      </a-form-item>

      <a-form-item label="授课老师">
        <a-select
          show-search
          :filter-option="false"
          v-decorator="['teacherId', {
            initialValue: teacher.initialValue,
            rules: [
              { required: true, message: '请选择授课老师' }
            ]
          }]"
          placeholder="请选择授课教师"
          @popupScroll="teacherScroll"
          @change="(value) => handleChange(value, 'teacher')"
          @search="handleSearchTeacher"
        >
          <a-select-option v-for="item in teacher.list" :key="item.id" :disabled="item.disabled">
            {{ item.nickname }}
          </a-select-option>
        </a-select>
      </a-form-item>

      <a-form-item label="助教">
        <a-select
          show-search
          :filter-option="false"
          v-decorator="['assistantIds', {
            initialValue: assistant.initialValue,
            rules: [
              { required: true, message: '至少选择一位助教' },
              { validator: validatorAssistant }
            ]
          }]"
          mode="multiple"
          placeholder="请选择助教"
          @popupScroll="assistantScroll"
          @search="handleSearchAssistant"
          @blur="() => handleSearchAssistant('')"
          @change="(value) => handleChange(value, 'assistant')"
          option-label-prop="label"
        >
          <a-select-option v-for="item in assistant.list" :key="item.id" :label="item.nickname" :disabled="item.disabled">
            {{ item.nickname }} <span v-if="Number(item.isScrmBind) === 0" class="assistant-tip">提示：该助教未绑定销客助手，可能会影响学习服务</span>
          </a-select-option>
        </a-select>
        <div class="pull-left color-gray" >
          <a-icon type="exclamation-circle" style="color: #bebebe;" />
          用户中心设置助教
          <a href="/admin/v2/user" target="_blank">去设置</a>
        </div>
      </a-form-item>
      <a-form-item class="assistant-max-number" label="分组容纳学员上限" :wrapper-col="{ span: 3 }">
        <a-input v-decorator="['group_limit_num', {
              rules: [
                { required: true, message: '请输入分组容纳学员人数' },
                { validator: validateGroupNum }
               ]
             }]">
          <span slot="suffix">人</span>
        </a-input>
        <a-popover placement="right">
          <template slot="content">
            第1组学员达到上限后，将自动生成第2组添加学员，依次类推
          </template>
          <svg-icon class="icon-tip" icon="icon-tip" />
        </a-popover>
        <span class="tip-color setup-tip">可去【参数设置】中设置默认值</span>
      </a-form-item>
      <a-form-item label="排课">
        <Schedule
          :course-id="selectedCourseId"
          :course-set-id="selectedCourseSetId"
        />
      </a-form-item>

      <a-form-item label="限购人数">
        <a-input v-decorator="['maxStudentNum', {
          rules: [
            { required: true, message: '请输入限购人数' },
            { validator: validateStudentNum }
            ]
          }]"
          :disabled="!form.getFieldValue('courseId')"
        >
          <span slot="suffix">人</span>
        </a-input>
      </a-form-item>

      <a-form-item label="直播回放观看">
        <a-radio-group
          :options="[
            { label: '开启', value: '1' },
            { label: '关闭', value: '0' },
          ]"
          v-decorator="['isReplayShow', { initialValue: '1'}]"
        >
        </a-radio-group>
      </a-form-item>
      <a-form-item label="通知设置" v-if="notificationShow !== ''">
        <a-form-item style="position: relative;left: -7.5%;margin-top: 50px;">
          <div class="pull-left mr12">开课提醒</div>
          <div v-if="notificationShow">
            <div class="pull-left">开课</div>
            <a-select class="pull-left ml8" style="width: 200px; " v-decorator="['liveRemindTime', { initialValue: 5 }]">
              <a-select-option v-for="time in [0, 5, 15, 30, 60, 1440]" :value="time" :key="time">
                <template v-if="time === 0">不通知</template>
                <template v-else-if="time === 1440">1天前</template>
                <template v-else>{{ time }}分钟</template>
              </a-select-option>
            </a-select>
            <div class="pull-left ml8">自动发送提醒</div>
          </div>
          <div v-if="!notificationShow">
            <a-icon type="info-circle" style="color: #bebebe;" />
            尚未在系统后台配置微信通知，开启配置，才可使用该功能
            <a href="/admin/v2/wechat/notification/manage" target="_blank">去设置</a>
          </div>
        </a-form-item>
      </a-form-item>
    </a-form>

    <div class="create-multi-class-btn-group">
      <a-space size="large">
        <a-button type="primary" @click="handleSubmit" :loading="ajaxLoading">
          {{ mode === 'editor' ? '确定' : '立即创建' }}
        </a-button>
        <a-button @click="cancelCreate">
          取消
        </a-button>
      </a-space>
    </div>
  </aside-layout>
</template>

<script>
import _ from 'lodash';
import { ValidationTitle, Assistant, MultiClassProduct, MultiClass, MultiClassSetting, Teacher, Me, Course, Setting } from 'common/vue/service';
import AsideLayout from 'app/vue/views/layouts/aside.vue';
import Schedule from '../create/Schedule.vue';

export default {
  name: 'MultiClassCreate',

  components: {
    AsideLayout,
    Schedule
  },

  data() {
    return {
      ajaxLoading: false,
      form: this.$form.createForm(this, { name: 'multi_class_create' }),
      selectedCourseId: 0,
      selectedCourseSetId: 0,
      multiClassId: 0,
      mode: 'create', // create, editor, copy
      notificationShow: '',
      maxStudentNum: 100000,
      course: {
        list: [],
        title: '',
        flag: true,
        initialValue: undefined,
        paging: {
          pageSize: 10,
          current: 0
        }
      },
      product: {
        list: [],
        flag: true,
        initialValue: undefined,
        paging: {
          pageSize: 10,
          current: 0
        }
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
      }
    }
  },

  computed: {
    breadcrumbName() {
      const names = {
        create: '新建分组大班课',
        editor: '编辑分组大班课'
      }
      return names[this.mode];
    }
  },

  created() {
    // 编辑班课
    this.fetchNotificationSetting();
    this.isEdit();
    this.getMultiClassSetting();
  },

  methods: {
    initFetch() {
      this.fetchCourse();
      this.fetchAssistants();
      this.fetchProducts();
      this.fetchTeacher();
    },

    isEdit() {
      const id = this.$route.query.id;
      if (id) {
        this.multiClassId = id;
        this.mode = 'editor';
        this.fetchEditorMultiClass();
        return;
      }
      this.afterCreateCourse();
    },

    afterCreateCourse() {
      let course = this.$route.query.course
      if (course) {
        course = JSON.parse(course)

        this.selectedCourseId = course.id;
        this.selectedCourseSetId = course.courseSetId;
        this.maxStudentNum = course.maxStudentNum > 0 ? course.maxStudentNum : 100000;
        this.course.list.push(course)
        this.$set(this.course, 'initialValue', course.id)
        this.fetchCourse();
        this.fetchProducts();
        this.fetchCourseInfo(course.id);
        return;
      }
      this.initFetch();
    },

    // 编辑模式下, 下拉选择数据去除默认值
    duplicateRemoval(data, id) {
      _.forEach(data, (item, index) => {
        if (item.id == id) {
          data.splice(index, 1);
          return false;
        }
      });
    },

    disabledTeacher(value) {
      const assistantIds = value || this.form.getFieldValue('assistantIds') || this.assistant.initialValue;
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
      const teacherId = value || this.form.getFieldValue('teacherId') || this.teacher.initialValue;
      _.forEach(this.assistant.list, item => {
        if (item.id == teacherId) {
          item.disabled = true;
        } else {
          item.disabled = false;
        }
      });
    },

    fetchNotificationSetting() {
      Setting.get('wechat_message_subscribe').then(res => {
        this.notificationShow = res.enable;
      });
    },

    fetchCourseInfo(courseId) {
      this.form.resetFields(['teacherId', 'assistantIds']);
      Course.getSingleCourse(courseId).then(res => {
        const { teachers, assistants, maxStudentNum } = res;
        const defaultTeacher = teachers[0];
        const defaultAssistant = assistants;

        this.teacher = {
          list: [defaultTeacher],
          title: '',
          flag: true,
          initialValue: defaultTeacher.id,
          paging: {
            pageSize: 10,
            current: 0
          }
        };

        const assistantIds = [];
        _.forEach(defaultAssistant, item => {
          assistantIds.push(item.id);
        });
        this.assistant = {
          list: defaultAssistant,
          title: '',
          flag: true,
          initialValue: assistantIds,
          paging: {
            pageSize: 10,
            current: 0
          }
        };
        this.form.setFieldsValue({
          'teacherId': defaultTeacher.id,
          'assistantIds': assistantIds,
          'maxStudentNum': maxStudentNum
        });
        this.fetchAssistants();
        this.fetchTeacher();
      });
    },

    fetchEditorMultiClass() {
      MultiClass.get(this.multiClassId).then(res => {
        const { title, course, courseId, product, productId, teachers, teacherIds, assistants, assistantIds, maxStudentNum, service_setting_type, service_group_num, group_limit_num, isReplayShow, liveRemindTime } = res;
        this.form.setFieldsValue({ 'title': title, 'maxStudentNum': maxStudentNum, 'service_setting_type':service_setting_type , 'service_group_num':service_group_num, 'group_limit_num':group_limit_num, 'isReplayShow': isReplayShow, 'liveRemindTime': Number(liveRemindTime) });
        this.selectedCourseId = courseId;
        this.selectedCourseSetId = course.courseSetId;
        this.maxStudentNum = course.maxStudentNum > 0 ? course.maxStudentNum : 100000;
        this.course.list = [course];
        this.course.initialValue = courseId;
        this.product.list = [product];
        this.product.initialValue = productId;
        this.teacher.list = teachers;
        this.teacher.initialValue = teacherIds[0];
        this.assistant.list = assistants;
        this.assistant.initialValue = assistantIds;
        this.initFetch();
      });
    },

    fetchCourse() {
      const { title, paging: { pageSize, current } } = this.course;

      const params = {
        isDefault: 1,
        limit: pageSize,
        offset: pageSize * current,
        type: 'live',
        excludeMultiClassCourses: true,
        sort: '-createdTime'
      };

      if (title) {
        params.courseSetTitleLike = title;
      }

      Course.searchCourses(params).then(res => {
        this.course.paging.current++;

        if (this.course.initialValue) {
          this.duplicateRemoval(res.data, this.course.initialValue);
        }

        this.course.list = _.concat(this.course.list, res.data);
        if (_.size(this.course.list) >= res.paging.total) {
          this.course.flag = false;
        }
      });
    },

    handleSearchCourse: _.debounce(function(input) {
      this.course = {
        list: [],
        title: input,
        flag: true,
        paging: {
          pageSize: 10,
          current: 0
        }
      };
      this.fetchCourse();
    }, 300),

    courseScroll: _.debounce(function (e) {
      const { scrollHeight, offsetHeight, scrollTop } = e.target;
      const maxScrollTop = scrollHeight - offsetHeight - 20;
      if ((maxScrollTop < scrollTop) && this.course.flag) {
        this.fetchCourse();
      }
    }, 300),

    fetchProducts() {
      const { title, paging: { pageSize, current } } = this.product;

      const params = {
        limit: pageSize,
        offset: pageSize * current
      };

      if (title) {
        params.keywords = title;
      }

      MultiClassProduct.search(params).then(res => {
        this.product.paging.current++;

        if (this.product.initialValue) {
          this.duplicateRemoval(res.data, this.product.initialValue);
        }

        this.product.list = _.concat(this.product.list, res.data);
        if (_.size(this.product.list) >= res.paging.total) {
          this.product.flag = false;
        }
      });
    },

    productScroll: _.debounce(function (e) {
      const { scrollHeight, offsetHeight, scrollTop } = e.target;
      const maxScrollTop = scrollHeight - offsetHeight - 20;
      if (maxScrollTop < scrollTop && this.product.flag) {
        this.fetchProducts();
      }
    }, 300),

    handleSearchProduct: _.debounce(function(input) {
      this.product = {
        list: [],
        title: input,
        flag: true,
        paging: {
          pageSize: 10,
          current: 0
        }
      };
      this.fetchProducts();
    }, 300),

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
        if (this.teacher.initialValue) {
          this.duplicateRemoval(res.data, this.teacher.initialValue);
        }
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
        _.forEach(this.assistant.initialValue, item => {
          this.duplicateRemoval(res.data, item);
        });
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

    handleChangeCourse(value) {
      this.selectedCourseId = value;
      _.forEach(this.course.list, item => {
        if (item.id == value) {
          this.selectedCourseSetId = item.courseSet.id;
          this.maxStudentNum = item.maxStudentNum > 0 ? item.maxStudentNum : 100000;
          return false;
        }
      });
      this.fetchCourseInfo(value);
    },

    handleChange(value, type) {
      if (type === 'teacher') {
        this.disabledAssistant(value);
        return;
      }
      if (type === 'assistant') {
        this.disabledTeacher(value);
      }
    },

    async validatorTitle(rule, value, callback) {
      const { result } = await ValidationTitle.search({
        type: 'multiClass',
        title: value,
        exceptId: this.multiClassId
      });
      if (!result) {
        callback('班课名称不能与已创建的相同')
      }
      if (value && ((value.replace(/[\u0391-\uFFE5]/g, 'aa').length / 2) > 40)) {
        callback('班课名称不能超过40个字符')
      }
      callback();
    },

    validatorAssistant: (rule, value, callback) => {
      value.length > 20 ? callback('最多选择20个助教') : callback();
    },

    validateStudentNum(rule, value, callback) {
      if (value && /^\+?[1-9][0-9]*$/.test(value) === false) {
        callback('请输入正整数')
      }

      if (value > Number(this.maxStudentNum)) {
        callback(`人数范围在0-${this.maxStudentNum}人`)
      }

      callback()
    },
     validateAssistantNum(rule, value, callback) {
      if (value && /^\+?[0-9][0-9]*$/.test(value) === false) {
        callback('请输入整数')
      }
      callback()
    },
    validateGroupNum(rule, value, callback) {
      if (value && /^\+?[0-9][0-9]*$/.test(value) === false) {
        callback('请输入整数')
      }
      callback()
    },
    handleSubmit(e) {
      e.preventDefault();
      this.form.validateFields((err, values) => {
        if (err) return
        values.type = 'group';
        if (this.mode === 'create') {
          this.createMultiClass(values);
          return;
        }

        if (this.mode === 'editor') {
          this.editorMultiClass(values);
        }
      });
    },

    createMultiClass(values) {
      this.ajaxLoading = true
      MultiClass.add(values).then(() => {
        this.cancelCreate();
      }).finally(() => {
        this.ajaxLoading = false
      })
    },

    editorMultiClass(values) {
      this.ajaxLoading = true
      MultiClass.editorMultiClass(this.multiClassId, values).then(() => {
        this.cancelCreate();
      }).finally(() => {
        this.ajaxLoading = false
      })
    },

    cancelCreate() {
      this.$router.push({
        name: 'MultiClass',
        params: this.$route.params.paging || {}
      });
    },

    async getMultiClassSetting() {
      if (this.mode == 'editor') return;
      const { group_number_limit, assistant_group_limit } = await MultiClassSetting.search();
      this.form.setFieldsValue({ 'group_limit_num': group_number_limit, 'service_group_num': assistant_group_limit });
    },
  }
}
</script>

<style lang="less">
.create-multi-class-btn-group {
  position: fixed;
  bottom: 0;
  right: 64px;
  left: 200px;
  padding: 12px 0 12px 164px;
  margin: 0;
  border-top: solid 1px #ebebeb;
  background-color: #ffffff;
}

.es-transition(@property:all,@time:.3s) {
  -webkit-transition: @property @time ease;
     -moz-transition: @property @time ease;
       -o-transition: @property @time ease;
          transition: @property @time ease;
}

.es-transition {
  .es-transition()
}

.border-radius(@radius) {
  border-radius: @radius;
}

.form-split-item {
  margin-bottom: 12px;
  padding: 4px 0 4px 24px;
  font-size: 16px;
  font-weight: 500;
  color: #333;
  text-align: right;
  background-color: #f5f5f5;
}
.assistant-max-number{
  .ant-form-explain{
    width: 250px
  }
}
.assistant-tip{
  margin-left: 48px;
  color: @brand-danger
}
.tip-color{
  color: @cdv2-dark-assist;
}
.icon-tip{
  position: absolute;
  top: 0;
  left: 140px;
  width:16px;
  height:16px;
  color: #31A1FF;
}
.setup-tip{
  position: absolute;
  left: 170px;
  width: 200px;
}
.total-number-tip{
  color: @cdv2-dark-assist;
  margin-left: 18px;
}

@import "~app/less/admin-v2/variables.less";
@import "~app/less/page/course-manage/task/create.less";
@import "~app/less/component/es-step.less";
@import "~common/variable.less";
</style>
