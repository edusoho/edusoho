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
              v-decorator="['courseId', {
                initialValue: course.initialValue,
                rules: [
                  { required: true, message: '请选择课程' }
                ]
              }]"
              :filter-option="false"
              placeholder="请选择课程"
              @popupScroll="courseScroll"
              @search="handleSearchCourse"
              @change="handleChangeCourse"
            >
              <a-select-option v-for="item in course.list" :key="item.id">
                {{ item.courseSetTitle }}
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
          v-decorator="['productId', {
            initialValue: product.initialValue,
            rules: [
              { required: true, message: '请选择归属产品' }
            ]
          }]"
          placeholder="请选择归属产品"
          @popupScroll="productScroll"
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
          @search="handleSearchTeacher"
        >
          <a-select-option v-for="item in teacher.list" :key="item.id">
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
              { required: true, message: '至少选择一位助教' }
            ]
          }]"
          mode="multiple"
          placeholder="请选择助教"
          @popupScroll="assistantScroll"
          @search="handleSearchAssistant"
        >
          <a-select-option v-for="item in assistant.list" :key="item.id">
            {{ item.nickname }}
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
import { ValidationTitle, Assistant, MultiClassProduct, MultiClass, Teacher, Me } from 'common/vue/service';
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
      mode: 'create', // create, editor, copy
      course: {
        list: [],
        title: '',
        flag: true,
        initialValue: '',
        paging: {
          pageSize: 10,
          current: 0
        }
      },
      product: {
        list: [],
        flag: true,
        initialValue: '',
        paging: {
          pageSize: 10,
          current: 0
        }
      },
      teacher: {
        list: [],
        title: '',
        flag: true,
        initialValue: '',
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

  created() {
    const id = this.$route.query.id;
    if (id) {
      this.selectedCourseId = id;
      this.mode = 'editor';
      this.fetchEditorCourse();
    } else {
      this.fetchCourse();
      this.fetchAssistants();
      this.fetchProducts();
      this.fetchTeacher();
    }
  },

  methods: {
    fetchEditorCourse() {
      MultiClass.get(this.selectedCourseId).then(res => {
        const { title, course, courseId, product, productId, teachers, teacherIds, assistants, assistantIds } = res;
        this.form.setFieldsValue({ 'title': title });
        this.course.list = [course];
        this.course.initialValue = courseId;
        this.product.list = [product];
        this.product.initialValue = productId;
        this.teacher.list = teachers;
        this.teacher.initialValue = teacherIds[0];
        this.assistant.list = assistants;
        this.assistant.initialValue = assistantIds;
      });
    },

    fetchCourse() {
      const { title, paging: { pageSize, current } } = this.course;

      const params = {
        isDefault: 1,
        limit: pageSize,
        offset: pageSize * current
      };

      if (title) {
        params.titleLike = title;
      }

      Me.get('teach_courses', { params }).then(res => {
        this.course.paging.current++;
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
      const { paging: { pageSize, current } } = this.product;

      const params = {
        limit: pageSize,
        offset: pageSize * current
      };

      MultiClassProduct.search(params).then(res => {
        this.product.paging.current++;
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
    },

    validatorＴitle: _.debounce(async (rule, value, callback) => {
      const { result } = await ValidationTitle.search({
        type: 'multiClass',
        title: value
      });

      result ? callback() : callback('产品名称不能与已创建的相同');
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
