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
        v-decorator="['name', { rules: [
          { required: true, message: '请填写班课名称' }
        ]}]"
        placeholder="请输入班课名称"
      />
    </a-form-item>

    <a-form-item label="选择课程">
      <a-row :gutter="16">
        <a-col :span="19">
          <a-select
            show-search
            v-decorator="['course_name', { rules: [
              { required: true, message: '请选择课程' }
            ]}]"
            placeholder="请选择课程"
            option-filter-prop="children"
            :filter-option="filterOption"
            @change="changeCourse"
          >
            <a-select-option value="jack">
              Jack
            </a-select-option>
            <a-select-option value="lucy">
              Lucy
            </a-select-option>
            <a-select-option value="tom">
              Tom
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
        v-decorator="['product', { rules: [
          { required: true, message: '请选择归属产品' }
        ]}]"
        mode="multiple"
        placeholder="请选择归属产品"
        @change="changeProduct"
      >
        <a-select-option v-for="i in 25" :key="(i + 9).toString(36) + i">
          {{ (i + 9).toString(36) + i }}
        </a-select-option>
      </a-select>
    </a-form-item>

    <a-form-item label="授课老师">
      <a-select
        v-decorator="['teacher', { rules: [
          { required: true, message: '请选择授课老师' }
        ]}]"
        placeholder="请选择授课教师"
        @change="changeTeacher"
      >
        <a-select-option value="male">
          male
        </a-select-option>
        <a-select-option value="female">
          female
        </a-select-option>
      </a-select>
    </a-form-item>

    <a-form-item label="助教">
      <a-select
        v-decorator="['assistant', { rules: [
          { required: true, message: '至少选择一位助教' }
        ]}]"
        mode="multiple"
        placeholder="请选择助教"
        @change="changeAssistant"
      >
        <a-select-option v-for="i in 25" :key="(i + 9).toString(36) + i">
          {{ (i + 9).toString(36) + i }}
        </a-select-option>
      </a-select>
    </a-form-item>

    <a-form-item label="排课">
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

export default {
  name: 'MultiClassCreate',

  data() {
    return {
      form: this.$form.createForm(this, { name: 'multi_class_create' })
    }
  },

  methods: {
    handleSubmit(e) {
      e.preventDefault();
      this.form.validateFieldsAndScroll((err, values) => {
        if (!err) {
          console.log('Received values of form: ', values);
        }
      });
    },

    changeCourse(value) {
      console.log(`changeCourse ${value}`);
    },

    filterOption(input, option) {
      console.log(input, option);
      return (
        option.componentOptions.children[0].text.toLowerCase().indexOf(input.toLowerCase()) >= 0
      );
    },

    changeTeacher() {

    },

    changeAssistant() {

    },

    clickCancelCreate() {
      this.$router.push({
        path: '/'
      });
    }
  }
}
</script>
