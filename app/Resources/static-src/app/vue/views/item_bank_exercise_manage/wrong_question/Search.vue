<template>
  <a-form-model
    ref="form"
    :model="form"
    layout="inline"
  >
    <a-form-model-item>
      <a-select
        v-model="form.source"
        style="width: 120px;"
        placeholder="题目来源"
      >
        <a-select-option value="zuoye">
          作业任务
        </a-select-option>
        <a-select-option value="zuoye">
          作业任务
        </a-select-option>
        <a-select-option value="kaoshi">
          考试任务
        </a-select-option>
        <a-select-option value="lianxi">
          练习任务
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-select
        v-model="form.taskName"
        style="width: 120px;"
        placeholder="任务名称"
      >
        <a-select-option value="zuoye">
          作业任务
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-select
        v-model="form.sort"
        style="width: 120px;"
        placeholder="答错人次"
      >
        <a-select-option value="DES">
          由高至低
        </a-select-option>
        <a-select-option value="ASC">
          由低至高
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-button type="primary" @click="onSearch">搜索</a-button>
    </a-form-model-item>
  </a-form-model>
</template>

<script>
export default {
  data() {
    return {
      form: {
        source: undefined,
        taskName: undefined,
        sort: undefined
      }
    }
  },

  methods: {
    onSearch() {
      this.$refs.form.validate(valid => {
        if (valid) {
          // do
        }
      })
    }
  }
}
</script>
