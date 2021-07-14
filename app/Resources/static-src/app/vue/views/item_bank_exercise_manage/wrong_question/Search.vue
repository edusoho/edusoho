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
        v-model="form.exerciseMediaType"
        @change="(value) => handleChange(value, 'exerciseMediaType')"
      >
        <a-select-option
          v-for="source in ['chapter', 'testpaper']"
          :value="source"
          :key="source"
        >
          {{ source | sourceTitle }}
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item v-if="form.exerciseMediaType === 'testpaper'">
      <a-select
        show-search
        option-filter-prop="children"
        :filter-option="filterOption"
        notFoundContent="暂无数据"
        style="width: 120px;"
        v-model="form.testpaperId"
      >
        <a-select-option value="default">全部试卷</a-select-option>

        <a-select-option
          v-for="testpaper in conditions.testpapers"
          :value="testpaper.assessmentId"
          :key="testpaper.assessmentId"
        >
          {{ testpaper.assessmentName }}
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item v-if="form.exerciseMediaType === 'chapter'">
      <a-tree-select
        notFoundContent="暂无数据"
        v-model="form.chapterId"
        style="min-width: 120px; max-width: 120px;"
        :dropdown-style="{ maxHeight: '400px',  overflow: 'auto' }"
        :tree-data="conditions.chapter"
        :replace-fields="{title:'name', key:'id', value: 'id', children: 'children' }"
        tree-default-expand-all
      >
      </a-tree-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-select
        style="width: 120px;"
        v-model="form.wrongTimesSort"
      >
        <a-select-option value="default">
          做错频次
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
      <a-button type="primary" @click="onSearch">搜索</a-button>
    </a-form-model-item>
  </a-form-model>
</template>

<script>
import _ from "lodash";
import { WrongBookSourceManageCondition } from 'common/vue/service';

const sources = {
  chapter: '章节练习',
  testpaper: '试卷练习',
}
export default {
  data() {
    return {
      form: {
        exerciseMediaType: 'chapter',
        wrongTimesSort: 'default',
        chapterId: 'default',
        testpaperId: 'default',
      },
      conditions: {}
    }
  },
  props: {
    targetId: 0,
  },
  created() {
    this.initSearchParams();
    this.fetchWrongBookCondition();
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
    getParams(type) {
      const { exerciseMediaType, testpaperId, chapterId } = this.form;
      const apiParams = {
        query: {
          targetType: 'exercise',
          targetId: this.targetId
        },
        params: {}
      };

      const params = apiParams.params;


      if (type == undefined) {
        _.assign(params, this.form);
      }

      if (type === 'exerciseMediaType') {
        if (exerciseMediaType === 'chapter') {
          _.assign(this.form, {
            chapterId: 'default',
          });
        }else if (exerciseMediaType === 'testpaper') {
          _.assign(this.form, {
            testpaperId: 'default',
          });
        }

        exerciseMediaType !== 'default' && (params.exerciseMediaType = exerciseMediaType)
      }

      return apiParams;
    },
    initSearchParams() {
      const params = this.$route.query;

      _.forEach(params, (value, key) => {
        this.form[key] = value;
      });
    },
    async fetchWrongBookCondition(type) {
      const params = this.getParams(type);

      const result = await WrongBookSourceManageCondition.get(params);

      result.chapter = [{"id": "default", "name": "全部章节"}].concat(result.chapter);
      result.testpapers = result.testpaper;

      this.conditions = result;
    },
    handleChange(value, type) {
      this.fetchWrongBookCondition(type);
    },
    filterOption(input, option) {
      return (
        option.componentOptions.children[0].text.toLowerCase().indexOf(input.toLowerCase()) >= 0
      );
    },
  }
}
</script>
