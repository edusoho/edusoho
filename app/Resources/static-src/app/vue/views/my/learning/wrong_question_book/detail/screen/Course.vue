<template>
  <a-form-model :model="form" layout="inline">
    <a-form-model-item>
      <a-select
        show-search
        style="width: 120px;"
        v-model="form.plan"
        @change="(value) => handleChange(value, 'plan')"
      >
        <a-select-option
          v-for="plan in conditions.plans"
          :value="plan.id"
          :key="plan.id"
        >
          {{ plan.title }}
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-select
        style="width: 120px;"
        v-model="form.source"
        @change="(value) => handleChange(value, 'source')"
      >
        <a-select-option
          v-for="item in conditions.source"
          :value="item"
          :key="item"
        >
          {{ item | sourceTitle }}
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-select
        style="width: 120px;"
        v-model="form.taskName"
      >
        <a-select-option
          v-for="task in conditions.tasks"
          :value="task.id"
          :key="task.id"
        >
          {{ task.title }}
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-select
        style="width: 120px;"
        v-model="form.frequency"
      >
        <a-select-option value="0">
          做错频次
        </a-select-option>
        <a-select-option value="1">
          做错频次1
        </a-select-option>
        <a-select-option value="2">
          做错频次2
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-button type="primary" @click="handleSubmit">搜索</a-button>
    </a-form-model-item>
  </a-form-model>
</template>

<script>
import _ from 'lodash';
import { WrongBookCondition } from 'common/vue/service';

const sources = {
  all: '题目来源',
  testpaper: '考试任务',
  homeword: '作业任务',
  exercise: '练习任务'
}

export default {
  filters: {
    sourceTitle(value) {
      return sources[value];
    }
  },

  props: {
    id: {
      type: String,
      required: true
    }
  },

  data() {
    return {
      form: {
        plan: 'all',
        source: 'all',
        taskName: 'all',
        frequency: 'all'
      },
      conditions: {}
    }
  },

  created() {
    this.fetchWrongBookCondition();
  },

  methods: {
    async fetchWrongBookCondition(type) {
      const { plan, source } = this.form;
      const params = {
        id: this.id
      };

      if (type === 'plan' && plan !== 'all') {
        params.courseId = plan;
      }

      if (type === 'source' && source !== 'all') {
        params.courseMediaType = source;

        plan !== 'all' && (params.courseId = plan);
      }

      const result = await WrongBookCondition.get(params);

      _.forEach(result.plans, (plan, index) => {
        if (!plan.title) {
          result.plans.splice(index, 1);
        }
      });

      result.plans.unshift({
        id: 'all',
        title: '全部计划'
      });

      result.source.unshift('all');

      result.tasks.unshift({
        id: 'all',
        title: '任务名称'
      });

      this.conditions = result;

      console.log(result);
    },

    handleChange(value, type) {
      this.fetchWrongBookCondition(type);
    },

    handleSubmit() {
      console.log('submit!', this.form);
    }
  }
}
</script>
