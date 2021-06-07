<template>
  <aside-layout :breadcrumbs="[{ name: breadcrumbName }]" style="padding-bottom: 88px;">
    <a-form
      :form="form"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 20 }"
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
          <a-col :span="mode === 'editor' ? 24 : 19">
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
          <a-col :span="5" v-if="mode !== 'editor'">
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
              { required: true, message: '至少选择一位助教' },
              { validator: validatorAssistant }
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
        <Schedule
          :course-id="selectedCourseId"
          :course-set-id="selectedCourseSetId"
        />
      </a-form-item>
    </a-form>

    <div class="create-multi-class-btn-group">
      <a-space size="large">
        <a-button type="primary" @click="handleSubmit">
          {{ mode === 'editor' ? '确定' : '立即创建' }}
        </a-button>
        <a-button @click="clickCancelCreate">
          取消
        </a-button>
      </a-space>
    </div>
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
      selectedCourseSetId: 0,
      multiClassId: 0,
      mode: 'create', // create, editor, copy
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
        create: '新建班课',
        editor: '编辑班课'
      }
      return names[this.mode];
    }
  },

  created() {
    const id = this.$route.query.id;
    if (id) {
      this.multiClassId = id;
      this.mode = 'editor';
      this.fetchEditorMultiClass();
    } else {
      this.initFetch();
    }
  },

  methods: {
    initFetch() {
      this.fetchCourse();
      this.fetchAssistants();
      this.fetchProducts();
      this.fetchTeacher();
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

    fetchEditorMultiClass() {
      MultiClass.get(this.multiClassId).then(res => {
        const { title, course, courseId, product, productId, teachers, teacherIds, assistants, assistantIds } = res;
        this.form.setFieldsValue({ 'title': title });
        this.selectedCourseId = courseId;
        this.selectedCourseSetId = course.courseSetId;
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
        offset: pageSize * current
      };

      if (title) {
        params.titleLike = title;
      }

      Me.get('teach_courses', { params }).then(res => {
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
      const { paging: { pageSize, current } } = this.product;

      const params = {
        limit: pageSize,
        offset: pageSize * current
      };

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
          this.selectedCourseSetId = item.courseSetId;
          return false;
        }
      });
    },

    validatorＴitle: _.debounce(async function(rule, value, callback) {
      const { result } = await ValidationTitle.search({
        type: 'multiClass',
        title: value,
        exceptId: this.multiClassId
      });

      result ? callback() : callback('班课名称不能与已创建的相同');
    }, 300),

    validatorAssistant: (rule, value, callback) => {
      value.length > 20 ? callback('最多选择20个助教') : callback();
    },

    handleSubmit(e) {
      e.preventDefault();
      this.form.validateFields((err, values) => {
        if (!err) {
          if (this.mode === 'create') {
            this.createMultiClass(values);
            return;
          }
          if (this.mode === 'editor') {
            this.editorMultiClass(values);
          }
        }
      });
    },

    createMultiClass(values) {
      MultiClass.add(values).then(res => {
        this.clickCancelCreate();
      });
    },

    editorMultiClass(values) {
      MultiClass.editorMultiClass(this.multiClassId, values).then(res => {
        this.clickCancelCreate();
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
  padding: 24px 0 24px 164px;
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

@import "~app/less/admin-v2/variables.less";
@import "~app/less/page/course-manage/task/create.less";
@import "~app/less/component/es-step.less";
</style>
