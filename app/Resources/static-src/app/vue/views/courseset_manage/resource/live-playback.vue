<template>
  <div>
    <div class="mb16">
      <a-button
        type="danger"
        :disabled="!hasSelected"
        @click="handleClickRemove"
      >
        {{ 'site.btn.remove_playback' | trans }}
      </a-button>
    </div>
    <a-table
      :row-selection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }"
      :columns="columns"
      :row-key="record => record.id"
      :data-source="data"
      :pagination="pagination"
      :loading="loading"
      @change="handleTableChange"
    >
      <template slot="actions" slot-scope="record">
        <a-button-group>
          <a-button type="primary" style="padding: 0 8px;" @click="handleClickViewLivePlayback(record.url)">
            {{ 'site.btn.view_playback' | trans }}
          </a-button>
          <a-dropdown placement="bottomRight">
            <a-menu slot="overlay">
              <a-menu-item @click="showModal(record.id)">
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

    <a-modal
      :title="'site.btn.remove_playback' | trans"
      :visible="visible"
      @cancel="hiddenModal"
    >
      {{ 'live.playback.tip.remove_association' | trans }}
      <a-checkbox class="mt8" :checked="checked" @change="handleChange">
        {{ 'live.playback.tip.delete_related_live_playback' | trans }}
      </a-checkbox>
      <template slot="footer">
        <div class="clearfix">
          <span class="pull-left" style="color: #fe4040; margin-top: 7px;">{{ 'live.playback.tip.cannot_be_used_normally' | trans }}</span>
          <a-button type="danger" :loading="btnLoading" @click="handleClickRemoveLivePlayback">
            {{ 'site.btn.confirm' | trans }}
          </a-button>
        </div>
      </template>
    </a-modal>
  </div>
</template>
<script>
import { LiveReplay } from 'common/vue/service';

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
  name: 'CoursesetManageLivePlayback',

  data() {
    return {
      data: [],
      columns,
      pagination: {
        hideOnSinglePage: true,
        current: 1,
        pageSize: 10,
        total: 0
      },
      selectedRowKeys: [],
      loading: false,
      visible: false,
      btnLoading: false,
      currentId: undefined,
      checked: false,
      courseId: $('.js-course-id').val()
    }
  },

  computed: {
    hasSelected() {
      return this.selectedRowKeys.length > 0;
    }
  },

  mounted() {
    this.fetchLiveReplay();
  },

  methods: {
    async fetchLiveReplay() {
      this.loading = true;
      const { current, pageSize } = this.pagination;
      const params = {
        params: {
          offset: (current - 1) * pageSize,
          limit: pageSize,
          courseId: this.courseId

        }
      }
      const { data, paging } = await LiveReplay.get(params);
      this.loading = false;
      this.pagination.total = paging.total;
      this.data = data;
    },

    handleTableChange(pagination) {
      this.pagination.current = pagination.current;
      this.fetchLiveReplay();
    },

    onSelectChange(selectedRowKeys) {
      this.selectedRowKeys = selectedRowKeys;
    },

    showModal(id) {
      this.currentId = [id];
      this.visible = true;
    },

    hiddenModal() {
      this.visible = false;
    },

    handleClickRemove() {
      this.currentId = this.selectedRowKeys;
      this.visible = true;
    },

    async handleClickRemoveLivePlayback() {
      this.btnLoading = true;

      const params = {
        data: {
          ids: this.currentId,
          realDelete: this.checked
        }
      }

      const { success } = await LiveReplay.delete(params);

      if (success) {
        this.$message.success(Translator.trans('message.removal_succeeded'));
        this.btnLoading = false;
        this.visible = false;
        this.pagination.current = 1;
        this.fetchLiveReplay();
      }
    },

    handleChange(e) {
      this.checked = e.target.checked;
    },

    handleClickViewLivePlayback(url) {
      window.open(url);
    }
  }
};
</script>
