<template>
  <div>
    <div class="mb16">
      <a-button
        type="danger"
        :disabled="!hasSelected"
        :loading="loading"
        @click="handleClickRemove"
      >
        移除回放
      </a-button>
    </div>
    <a-table
      :row-selection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }"
      :columns="columns"
      :data-source="data"
    >
      <template slot="customTitle">直播名称</template>
      <template slot="anchorTitle">主讲人</template>
      <template slot="liveTimeTitle">回放时长</template>
      <template slot="liveStartTimeTitle">直播时间</template>
      <template slot="actionsTitle">操作</template>

      <template slot="actions" slot-scope="record">
        <a-button-group>
          <a-button type="primary" style="padding: 0 8px;">
            查看回放
          </a-button>
          <a-dropdown placement="bottomRight">
            <a-menu slot="overlay">
              <a-menu-item key="1">
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
  </div>
</template>
<script>
import { LiveReplay } from 'common/vue/service';

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

const data = [];
for (let i = 0; i < 46; i++) {
  data.push({
    key: i,
    title: `直播名称 ${i}`,
    anchor: 'anchor',
    liveTime: i,
    liveStartTime: '2021-09-09 22.33.22'
  });
}

export default {
  name: 'CoursesetManageLivePlayback',

  data() {
    return {
      data,
      columns,
      selectedRowKeys: [],
      loading: false
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
      const result = await LiveReplay.get();
      console.log(result);
    },

    handleClickRemove() {
      this.loading = true;
      setTimeout(() => {
        this.loading = false;
        this.selectedRowKeys = [];
      }, 1000);
    },

    onSelectChange(selectedRowKeys) {
      this.selectedRowKeys = selectedRowKeys;
    }
  }
};
</script>
