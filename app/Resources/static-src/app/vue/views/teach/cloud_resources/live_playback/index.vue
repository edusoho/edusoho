<template>
  <div>
    <a-form-model class="mt16" layout="inline" :model="searchForm">
      <a-form-model-item label="时间">
        <a-range-picker v-model="searchForm.time" />
      </a-form-model-item>

      <a-form-model-item>
        <a-select v-model="searchForm.courseCategoryId" placeholder="课程分类" style="width: 200px;">
          <a-select-option v-for="category in categoryData" :key="category.id" :value="category.id">
            {{ category.name }}
          </a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item>
        <a-select v-model="searchForm.replayTagId" placeholder="回放标签" style="width: 200px;">
          <a-select-option v-for="tag in tagData" :key="tag.id" :value="tag.id">
            {{ tag.name }}
          </a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item>
        <a-input v-model="searchForm.keyword" placeholder="请输入关键字" style="width: 200px;" />
      </a-form-model-item>

      <a-form-model-item>
        <a-button type="primary" @click="handleClickSearch">
          搜索
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
      <template slot="customTitle">直播名称</template>
      <template slot="anchorTitle">主讲人</template>
      <template slot="liveTimeTitle">回放时长</template>
      <template slot="liveStartTimeTitle">直播时间</template>
      <template slot="actionsTitle">操作</template>

      <template slot="actions" slot-scope="record">
        <a-button-group>
          <a-button type="primary" style="padding: 0 8px;" @click="handleClickEdit(record.id)">
            编辑
          </a-button>
          <a-dropdown placement="bottomRight">
            <a-menu slot="overlay">
              <a-menu-item @click="handleClickView(record.url)">
                查看回放
              </a-menu-item>
               <a-menu-item @click="handleClickRemove(record.id)">
                移除回放
              </a-menu-item>
            </a-menu>
            <a-button type="primary" style="padding: 0 8px;">
              <a-icon type="down" />
            </a-button>
          </a-dropdown>
        </a-button-group>
      </template>
    </a-table>

    <remove-modal ref="removeModal" />

    <edit-modal ref="editModal" :tags="tagData" />
  </div>
</template>

<script>
import _ from 'lodash';
import { LiveReplay, CourseCategory, CourseTag } from 'common/vue/service';
import EditModal from './components/EditModal.vue';
import RemoveModal from './components/RemoveModal.vue';

const columns = [
  {
    dataIndex: 'title',
    slots: { title: 'customTitle' }
  },
  {
    dataIndex: 'anchor',
    slots: { title: 'anchorTitle' }
  },
  {
    dataIndex: 'liveTime',
    slots: { title: 'liveTimeTitle' }
  },
  {
    dataIndex: 'liveStartTime',
    slots: { title: 'liveStartTimeTitle' }
  },
  {
    slots: { title: 'actionsTitle' },
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
        keyword: ''
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
      const query = _.pickBy(this.searchForm, _.identity);

      if (!_.size(query)) return;

      const { time } = query;
      if (time) {
        query.startTime = moment(time[0]).valueOf();
        query.endTime = moment(time[1]).valueOf();
        delete query.time;
      }

      this.pagination.current = 1;
      this.fetchLiveReplay(query);
    },

    handleTableChange(pagination) {
      this.pagination.current = pagination.current;
      this.fetchLiveReplay();
    },

    async fetchLiveReplay(query = {}) {
      this.loading = true;
      const { current, pageSize } = this.pagination;
      const params = {
        query,
        params: {
          offset: (current - 1) * pageSize,
          limit: pageSize
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

    handleClickEdit() {
      this.$refs.editModal.showModal();
    },

    handleClickRemove() {
      this.$refs.removeModal.showModal();
    }
  }
}
</script>
