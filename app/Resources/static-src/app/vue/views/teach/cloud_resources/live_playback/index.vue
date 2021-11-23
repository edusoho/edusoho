<template>
  <div>
    <a-form-model class="mt16" layout="inline" :model="searchForm">
      <a-form-model-item>
        <a-range-picker v-model="searchForm.time" />
      </a-form-model-item>

      <a-form-model-item>
        <a-select
          v-model="searchForm.courseCategoryId"
          :allowClear="true"
          :placeholder="'placeholder.course_category' | trans"
          style="width: 160px;"
        >
          <a-select-option
            v-for="category in categoryData"
            :key="category.id"
            :value="category.id"
          >
            {{ category.name }}
          </a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item>
        <a-select
          v-model="searchForm.replayTagId"
          :allowClear="true"
          :placeholder="'placeholder.playback_label' | trans"
          style="width: 160px;"
        >
          <a-select-option
            v-for="tag in tagData"
            :key="tag.id"
            :value="tag.id"
          >
            {{ tag.name }}
          </a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item>
        <a-input-group compact>
          <a-select style="width: 100px;" v-model="searchForm.keywordType" default-value="activityTitle">
            <a-select-option value="activityTitle">
              {{ 'live_name' | trans }}
            </a-select-option>
            <a-select-option value="anchor">
              {{ 'live_statistics.presenter' | trans }}
            </a-select-option>
            <a-select-option value="courseTitle">
              {{ 'course.name' | trans }}
            </a-select-option>
          </a-select>
          <a-input
            v-model="searchForm.keyword"
            :placeholder="'placeholder.enter_keyword' | trans"
            style="width: 200px;"
          />
        </a-input-group>
      </a-form-model-item>

      <a-form-model-item>
        <a-button type="primary" @click="handleClickSearch">
          {{ 'site.search_hint' | trans }}
        </a-button>
      </a-form-model-item>
    </a-form-model>

    <a-table
      class="mt16"
      :columns="columns"
      :row-key="record => record.id"
      :data-source="data"
      :pagination="pagination"
      :loading="loading"
      @change="handleTableChange"
    >
      <template slot="actions" slot-scope="record">
        <a-button-group>
          <a-button type="primary" style="padding: 0 8px;" @click="handleClickEdit(record)">
            {{ 'modal.title.edit' | trans }}
          </a-button>
          <a-dropdown placement="bottomRight">
            <a-menu slot="overlay">
              <a-menu-item @click="handleClickView(record.url)">
                {{ 'site.btn.view_playback' | trans }}
              </a-menu-item>
               <a-menu-item @click="handleClickRemove(record.id)">
                {{ 'site.btn.remove_playback' | trans }}
              </a-menu-item>
            </a-menu>
            <a-button type="primary" style="padding: 0 8px;">
              <a-icon type="down" />
            </a-button>
          </a-dropdown>
        </a-button-group>
      </template>
    </a-table>

    <remove-modal ref="removeModal" @success="removeSuccess" />

    <edit-modal ref="editModal" :tags="tagData" @success="editSuccess" />
  </div>
</template>

<script>
import _ from 'lodash';
import { LiveReplay, CourseCategory, CourseTag } from 'common/vue/service';
import EditModal from './components/EditModal.vue';
import RemoveModal from './components/RemoveModal.vue';

const columns = [
  {
    title: Translator.trans('live_name'),
    dataIndex: 'title'
  },
  {
    title: Translator.trans('live_statistics.presenter'),
    dataIndex: 'anchor'
  },
  {
    title: Translator.trans('live_playback_duration'),
    dataIndex: 'liveTime'
  },
  {
    title: Translator.trans('live_statistics.live_time'),
    dataIndex: 'liveStartTime'
  },
  {
    title: Translator.trans('live_statistics.operation'),
    scopedSlots: { customRender: 'actions' }
  }
];

export default {
  name: 'LivePlayback',

  components: {
    EditModal,
    RemoveModal
  },

  data() {
    return {
      searchForm: {
        time: undefined,
        replayTagId: undefined,
        courseCategoryId: undefined,
        keyword: '',
        keywordType: 'activityTitle'
      },
      data: [],
      columns,
      pagination: {
        hideOnSinglePage: true,
        current: 1,
        pageSize: 10,
        total: 0
      },
      loading: false,
      categoryData: [],
      tagData: []
    }
  },

  computed: {
    searchQuery() {
      const query = _.pickBy(this.searchForm, _.identity);

      if (!_.size(query)) return {};

      const { time } = query;
      if (time) {
        query.startTime = moment(time[0]).unix();
        query.endTime = moment(time[1]).unix();
        delete query.time;
      }

      return query;
    }
  },

  mounted() {
    this.fetchLiveReplay();
    this.fetchCourseCategory();
    this.fetchCourseTag();
  },

  methods: {
    async fetchCourseCategory() {
      const { data } = await CourseCategory.get();
      this.categoryData = data;
    },

    async fetchCourseTag() {
      const { data } = await CourseTag.get();
      this.tagData = data;
    },

    handleClickSearch() {
      this.pagination.current = 1;
      this.fetchLiveReplay();
    },

    handleTableChange(pagination) {
      this.pagination.current = pagination.current;
      this.fetchLiveReplay();
    },

    async fetchLiveReplay() {
      this.loading = true;
      const { current, pageSize } = this.pagination;
      const params = {
        params: {
          offset: (current - 1) * pageSize,
          limit: pageSize,
          ...this.searchQuery
        }
      }
      const { data, paging } = await LiveReplay.get(params);
      this.loading = false;
      this.pagination.total = paging.total;
      this.data = data;
    },

    handleClickView(url) {
      window.open(url);
    },

    handleClickEdit(params) {
      this.$refs.editModal.showModal(params);
    },

    handleClickRemove(id) {
      this.$refs.removeModal.showModal(id);
    },

    removeSuccess(id) {
      _.forEach(this.data, (item, index) => {
        if (item.id === id) {
          this.data.splice(index, 1);
          return false;
        }
      });
    },

    editSuccess(params) {
      const { id, replayPublic } = params;
      _.forEach(this.data, (item, index) => {
        if (item.id === id) {
          item.replayPublic = replayPublic;
          return false;
        }
      });
    }
  }
}
</script>
