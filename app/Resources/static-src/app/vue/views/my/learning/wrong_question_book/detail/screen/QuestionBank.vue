<template>
  <a-form-model :model="form" layout="inline">
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
        @change="(value) => handleChange(value, 'testpaperId')"
      >
        <a-select-option value="default">全部试卷</a-select-option>

        <a-select-option
          v-for="testpaper in conditions.testpapers"
          :value="testpaper.id"
          :key="testpaper.id"
        >
          {{ testpaper.title }}
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item v-if="form.exerciseMediaType === 'chapter'">
      <a-tree-select
        notFoundContent="暂无数据"
        v-model="form.chapterId"
        style="min-width: 120px"
        :dropdown-style="{ maxHeight: '400px',  overflow: 'auto' }"
        :tree-data="conditions.chapter"
        placeholder="全部章节"
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
      <a-button type="primary" @click="handleSubmit">搜索</a-button>
    </a-form-model-item>
  </a-form-model>
</template>

<script>
import _ from 'lodash';
import { WrongBookCondition } from 'common/vue/service';

const sources = {
  chapter: '章节练习',
  testpaper: '试卷练习',
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
        chapterId: 'default',
        exerciseMediaType: 'chapter',
        testpaperId: 'default',
        wrongTimesSort: 'default'
      },
      conditions: {
      },
    }
  },
  created() {
    this.initSearchParams();
    this.fetchWrongBookCondition();
  },

  methods: {
    initSearchParams() {
      const params = this.$route.query;

      _.forEach(params, (value, key) => {
        this.form[key] = value;
      });
    },

    getParams(type) {
      const { exerciseMediaType, testpaperId, chapterId } = this.form;
      const apiParams = {
        query: {
          poolId: this.id
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
        }else if (exerciseMediaType === 'chapter') {
          _.assign(this.form, {
            testpaperId: 'default',
          });
        }

        exerciseMediaType !== 'default' && (params.exerciseMediaType = exerciseMediaType)
      }

      // if (type === 'testpaperId') {
      //   _.assign(this.form, {
      //     testpaperId: 'default'
      //   });
      //
      //   testpaperId !== 'default' && (params.testpaperId = testpaperId);
      // }
      //
      // if (type === 'chapterId') {
      //   console.log(chapterId)
      //   _.assign(this.form, {
      //     chapterId: chapterId,
      //   });
      //
      //   chapterId !== 'default' && (params.chapterId = chapterId);
      // }

      return apiParams;
    },

    async fetchWrongBookCondition(type) {
      const params = this.getParams(type);

      const result = await WrongBookCondition.get(params);

      result.chapter = [{"id": "default", "name": "全部章节"}].concat(result.chapter);

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

    handleSubmit() {
      const params = {};
      _.forEach(_.keys(this.form), item => {
        const value = this.form[item];
        if (value != 'default') {
          params[item] = value;
        }
      });
      this.$emit('on-search', params);
    }
  }
}
</script>
