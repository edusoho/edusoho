<template>
  <a-form-model
    ref="form"
    :model="form"
    layout="inline"
  >
    <a-form-model-item>
      <a-select
        show-search
        option-filter-prop="children"
        :filter-option="filterOption"
        notFoundContent="暂无数据"
        style="width: 120px;"
        v-model="form.courseMediaType"
        @change="(value) => handleChange(value, 'source')"
      >
        <a-select-option value="default">题目来源</a-select-option>

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
        show-search
        option-filter-prop="children"
        :filter-option="filterOption"
        notFoundContent="暂无数据"
        style="width: 120px;"
        v-model="form.courseTaskId"
      >
        <a-select-option value="default">任务名称</a-select-option>

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
        v-model="form.wrongTimesSort"
      >
        <a-select-option value="default">
          答错人次
        </a-select-option>
        <a-select-option value="DESC">
          由高至低
        </a-select-option>
        <a-select-option value="ASC">
          由低至高
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-select
        style="width: 120px;"
        v-model="form.itemType"
      >
        <a-select-option value="">题型分类</a-select-option>

        <a-select-option
          v-for="(itemType, index) in itemTypes"
          :value="itemType.value"
          :key="index"
        >
          {{ itemType.name }}
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-input v-model="form.itemTitle" placeholder="请输入题目关键字" allowClear>
      </a-input>
    </a-form-model-item>

    <a-form-model-item>
      <a-button type="primary" @click="onSearch">搜索</a-button>
    </a-form-model-item>
  </a-form-model>
</template>

<script>
import _ from "lodash";
import { WrongBookSourceManageCondition } from 'common/vue/service';
const sources = {
  testpaper: '考试任务',
  homework: '作业任务',
  exercise: '练习任务'
}
export default {
  props: {
    targetId: 0,
    courseId: 0
  },
  created() {
    this.fetchWrongBookCondition();
  },
  data() {
    return {
      form: {
        courseMediaType: 'default',
        courseTaskId: 'default',
        wrongTimesSort: 'default',
        itemType: '',
        itemTitle: '',
      },
      conditions: {},
      itemTypes: [
        {
          name: '单选题',
          value: 'single_choice',
        },
        {
          name: '多选题',
          value: 'choice',
        },
        {
          name: '不定项选择题',
          value: 'uncertain_choice',
        },
        {
          name: '判断题',
          value: 'determine',
        },
        {
          name: '填空题',
          value: 'fill',
        },
        {
          name: '材料题',
          value: 'material',
        }
      ],
    }
  },
  filters: {
    sourceTitle(value) {
      return sources[value];
    }
  },
  methods: {
    onSearch() {
      this.$refs.form.validate(valid => {
        if (valid) {
          const params = {};
          _.forEach(_.keys(this.form), item => {
            const value = this.form[item];
            if (value !== 'default') {
              params[item] = value;
            }
          });
          this.$emit('on-search', params);
        }
      })
    },
    filterOption(input, option) {
      return (
        option.componentOptions.children[0].text.toLowerCase().indexOf(input.toLowerCase()) >= 0
      );
    },
    handleChange(value, type) {
      this.fetchWrongBookCondition(type);
    },
    async fetchWrongBookCondition(type) {
      const params = this.getParams(type);

      const result = await WrongBookSourceManageCondition.get(params);

      _.forEach(result.plans, (plan, index) => {
        if (!plan.title) {
          result.plans.splice(index, 1);
        }
      });

      this.conditions = result;
    },
    getParams(type) {
      const { courseId, courseMediaType } = this.form;
      const apiParams = {
        query: {
          targetType: 'course',
          targetId: this.targetId,
        },
        params: {
          courseId: this.courseId,
        }
      };

      const params = apiParams.params;

      if (type === 'plan') {
        _.assign(this.form, {
          courseMediaType: 'default',
          courseTaskId: 'default'
        });

        courseId !== 'default' && (params.courseId = courseId);
      }

      if (type === 'source') {
        _.assign(this.form, {
          courseTaskId: 'default'
        });

        courseMediaType !== 'default' && (params.courseMediaType = courseMediaType);
        courseId !== 'default' && (params.courseId = courseId);
      }

      return apiParams;
    },
  }
}
</script>
