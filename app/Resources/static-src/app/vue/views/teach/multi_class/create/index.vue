<template>
  <a-form
    :form="form"
    :label-col="{ span: 4 }"
    :wrapper-col="{ span: 20 }"
    @submit="handleSubmit"
    style="max-width: 860px;"
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
            :filter-option="filterOption"
            @change="handleChangeCourse"
          >
            <a-select-option v-for="course in courses" :key="course.id">
              {{ course.title }}
            </a-select-option>
          </a-select>
        </a-col>
        <a-col :span="5">
          <a-button type="primary" :block="true">
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

    <a-form-item :wrapper-col="{ span: 20, offset: 4 }">
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
</template>

<script>
import _ from '@codeages/utils';
import { ValidationTitle, Create } from 'common/vue/service';


export default {
  name: 'MultiClassCreate',

  data() {
    return {
      form: this.$form.createForm(this, { name: 'multi_class_create' }),
      courses: [],
      teachers: [],
      assistants: [],
      products: []
    }
  },

  created() {
    this.fetchCourse();
    this.fetchAssistants();
    this.fetchProducts();
  },

  methods: {
    fetchCourse() {
      Create.teachCourses().then(res => {
        this.courses = res.data;
      });
    },

    fetchTeacher(id) {
      Create.teacher(id, { role: 'teacher' }).then(res => {
        this.teachers = res.data;
      });
    },

    fetchAssistants() {
      Create.assistants().then(res => {
        this.assistants = res.data;
      });
    },

    fetchProducts() {
      Create.products().then(res => {
        this.products = res.data;
      })
    },

    handleChangeCourse(value) {
      this.fetchTeacher(value);
    },

    validatorＴitle: _.debounce(async (rule, value, callback) => {
      const { result } = await ValidationTitle.search({
        type: 'multiClass',
        title: value
      });

      result ? callback() : callback('产品名称不能与已创建的相同');
    }, 300),

    filterOption(input, option) {
      console.log(input, option);
      return (
        option.componentOptions.children[0].text.toLowerCase().indexOf(input.toLowerCase()) >= 0
      );
    },

    handleSubmit(e) {
      e.preventDefault();
      this.form.validateFieldsAndScroll((err, values) => {
        if (!err) {
          Create.createMultiClass(values).then(res => {
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
