<template>
  <a-form layout="inline" :form="form" @submit="handleSubmit">
    <a-form-item>
      <a-select
        style="width: 120px;"
        v-decorator="['plan']"
      >
        <a-select-option value="male">
          male
        </a-select-option>
        <a-select-option value="female">
          female
        </a-select-option>
      </a-select>
    </a-form-item>

    <a-form-item>
      <a-select
        style="width: 120px;"
        v-decorator="['source']"
      >
        <a-select-option value="male">
          male
        </a-select-option>
        <a-select-option value="female">
          female
        </a-select-option>
      </a-select>
    </a-form-item>

    <a-form-item>
      <a-select
        style="width: 120px;"
        v-decorator="['name']"
      >
        <a-select-option value="male">
          male
        </a-select-option>
        <a-select-option value="female">
          female
        </a-select-option>
      </a-select>
    </a-form-item>

    <a-form-item>
      <a-select
        style="width: 120px;"
        v-decorator="['frequency']"
      >
        <a-select-option value="male">
          male
        </a-select-option>
        <a-select-option value="female">
          female
        </a-select-option>
      </a-select>
    </a-form-item>

    <a-form-item>
      <a-button type="primary" html-type="submit">
        搜索
      </a-button>
    </a-form-item>
  </a-form>
</template>

<script>
export default {
  data() {
    return {
      form: this.$form.createForm(this, { name: 'wrong_question_search' })
    }
  },

  methods: {
    handleSubmit(e) {
      e.preventDefault();
      this.form.validateFields((err, values) => {
        if (!err) {
          this.$emit('on-search', values);
        }
      });
    }
  }
}
</script>
