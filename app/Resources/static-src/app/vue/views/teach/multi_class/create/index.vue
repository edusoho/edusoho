<template>
  <aside-layout :breadcrumbs="[{ name: '新建班课' }]" style="padding-bottom: 88px;">
    <a-form
      :form="form"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 20 }"
      @submit="handleSubmit"
      style="max-width: 900px;"
    >
      <a-form-item label="班课名称">
        <a-input
          v-decorator="['title', { rules: [
            { required: true, message: '请填写班课名称' },
            { max: 40, message: '班课名称不能超过40个字' },
            { validator: validatorＴitle }
          ]}]"
          placeholder="请输入班课名称"
        />
      </a-form-item>

      <a-form-item label="选择课程">
        <a-row :gutter="16">
          <a-col :span="19">
            <a-select
              show-search
              v-decorator="['courseId', { rules: [
                { required: true, message: '请选择课程' }
              ]}]"
              placeholder="请选择课程"
              option-filter-prop="children"
              :filter-option="false"
              @search="handleSearchCourse"
              @change="handleChangeCourse"
            >
              <a-select-option v-for="course in courses" :key="course.id">
                {{ course.courseSetTitle }}
              </a-select-option>
            </a-select>
          </a-col>
          <a-col :span="5">
            <a-button type="primary" :block="true" @click="$router.push({ name: 'MultiClassCreateCourse' })">
              <a-icon type="plus" />
              创建新课程
            </a-button>
          </a-col>
        </a-row>
      </a-form-item>

      <a-form-item label="所属产品">
        <a-select
          v-decorator="['productId', { rules: [
            { required: true, message: '请选择归属产品' }
          ]}]"
          placeholder="请选择归属产品"
          @popupScroll="productScroll"
        >
          <a-select-option v-for="product in products" :key="product.id">
            {{ product.title }}
          </a-select-option>
        </a-select>
      </a-form-item>

      <a-form-item label="授课老师">
        <a-select
          v-decorator="['teacherId', { rules: [
            { required: true, message: '请选择授课老师' }
          ]}]"
          placeholder="请选择授课教师"
        >
          <a-select-option v-for="teacher in teachers" :key="teacher.user.id">
            {{ teacher.user.nickname }}
          </a-select-option>
        </a-select>
      </a-form-item>

      <a-form-item label="助教">
        <a-select
          v-decorator="['assistantIds', { rules: [
            { required: true, message: '至少选择一位助教' }
          ]}]"
          mode="multiple"
          placeholder="请选择助教"
        >
          <a-select-option v-for="assistant in assistants" :key="assistant.id">
            {{ assistant.nickname }}
          </a-select-option>
        </a-select>
      </a-form-item>

      <a-form-item label="排课">
        <Schedule :course-id="selectedCourseId" />
      </a-form-item>

      <a-form-item :wrapper-col="{ span: 20, offset: 4 }" class="create-multi-class-btn-group">
        <a-space size="large">
          <a-button type="primary" html-type="submit">
            立即创建
          </a-button>
          <a-button @click="clickCancelCreate">
            取消
          </a-button>
        </a-space>
      </a-form-item>
    </a-form>
  </aside-layout>
</template>

<script>
import _ from '@codeages/utils';
import { ValidationTitle, Assistant, MultiClassProduct, MultiClass, Course, Me } from 'common/vue/service';
import AsideLayout from 'app/vue/views/layouts/aside.vue';
import Schedule from './Schedule.vue';

export default {
  name: 'MultiClassCreate',

  components: {
    AsideLayout,
    Schedule
  },

  data() {
    return {
      form: this.$form.createForm(this, { name: 'multi_class_create' }),
      selectedCourseId: 0,
      courses: [],
      teachers: [],
      assistants: [],
      products: [],
      productPaging: {
        pageSize: 10,
        current: 0,
        flag: true
      }
    }
  },

  created() {
    this.fetchCourse();
    this.fetchAssistants();
    this.fetchProducts();
  },

  methods: {
    fetchCourse(params = {}) {
      _.assign(params, {
        isDefault: 1
      });
      Me.get('teach_courses', { params }).then(res => {
        this.courses = res.data;
      });
    },

    fetchTeacher(id) {
      Course.getTeacher(id, { role: 'teacher' }).then(res => {
        this.teachers = res.data;
      });
    },

    fetchAssistants() {
      Assistant.search().then(res => {
        this.assistants = res.data;
      });
    },

    fetchProducts() {
      const { pageSize, current } = this.productPaging;
      MultiClassProduct.search({
        limit: pageSize,
        offset: pageSize * current
      }).then(res => {
        this.productPaging.current++;
        this.products = _.concat(this.products, res.data);
        if (_.size(this.products) >= res.paging.total) {
          this.productPaging.flag = false;
        }
      });
    },

    productScroll: _.debounce(function (e) {
      const { scrollHeight, offsetHeight, scrollTop } = e.target;
      const maxScrollTop = scrollHeight - offsetHeight - 20;
      if (maxScrollTop < scrollTop && this.productPaging.flag) {
        this.fetchProducts();
      }
    }, 300),

    handleChangeCourse(value) {
      this.selectedCourseId = value;
      this.fetchTeacher(value);
    },

    validatorＴitle: _.debounce(async (rule, value, callback) => {
      const { result } = await ValidationTitle.search({
        type: 'multiClass',
        title: value
      });

      result ? callback() : callback('产品名称不能与已创建的相同');
    }, 300),

    handleSearchCourse: _.debounce(function(input) {
      this.fetchCourse({
        titleLike: input
      });
    }, 300),

    handleSubmit(e) {
      e.preventDefault();
      this.form.validateFieldsAndScroll((err, values) => {
        if (!err) {
          MultiClass.add(values).then(res => {
            this.clickCancelCreate();
          });
        }
      });
    },

    clickCancelCreate() {
      this.$router.push({
        path: '/'
      });
    }
  }
}
</script>

<style lang="less">
.create-multi-class-btn-group {
  position: fixed;
  bottom: 0;
  right: 64px;
  left: 200px;
  padding: 24px 0;
  margin: 0;
  border-top: solid 1px #ebebeb;
  background-color: #ffffff;
}
</style>
