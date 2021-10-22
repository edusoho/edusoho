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
          <a-button
            type="primary"
            style="padding: 0 8px;"
            data-target="#modal"
            data-toggle="modal"
            :data-url="`/admin/v2/cloud_file/${record.id}/preview`"
          >
            查看回放
          </a-button>
          <a-dropdown placement="bottomRight">
            <a-menu slot="overlay">
              <a-menu-item @click="showModal(record.id)">
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

    <a-modal
      title="移除回放"
      :visible="visible"
      @cancel="hiddenModal"
    >
      直播回放将从该课程中移除关联
      <a-checkbox class="mt8" :checked="checked" @change="handleChange">
        同时在我的教学资料中删除相关直播回放
      </a-checkbox>
      <template slot="footer">
        <div class="clearfix">
          <span class="pull-left" style="color: #fe4040; margin-top: 7px;">直播回放若被引用，移除会引起引用任务无法正常使用</span>
          <a-button type="danger" :loading="btnLoading" @click="handleClickRemoveLivePlayback">
            确认
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
      currentId: 0,
      checked: false
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
      this.data = await LiveReplay.get();
    },

    handleTableChange() {

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
    },

    showModal(id) {
      this.currentId = id;
      this.visible = true;
    },

    hiddenModal() {
      this.visible = false;
    },

    handleClickRemoveLivePlayback() {
      console.log(this.currentId);
      this.btnLoading = true;


      setInterval( () => {
        this.visible = false;
        this.btnLoading = false;
      }, 3000)
    },

    handleChange(e) {
      this.checked = e.target.checked;
    }
  }
};
</script>
