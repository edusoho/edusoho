<template>
  <a-modal
    title="助教权限管理"
    :visible="visible"
    okText="保存"
    cancelText="取消"
    @ok="handleOk"
    class="permission-modal"
    @cancel="handleCancel"
    :destroyOnClose="true"
  >
    <a-tree
      checkable
      :default-expand-all="true"
      :checkStrictly="true"
      :tree-data="treeData"
      :default-checked-keys="permissions"
      :replace-fields="replaceFields"
      @check="onCheck"
    />
  </a-modal>
</template>

<script>
import { AssistantPermission } from 'common/vue/service';
import _ from '@codeages/utils';

export default {
  name: 'PermissionModal',

  props: {
    visible: {
      type: Boolean,
      required: true
    }
  },

  data() {
    return {
      replaceFields: {
        title: 'title',
        key: 'code'
      },
      checkedKeys: [],
      treeData: [],
      permissions: [],
    }
  },

  created() {
    this.getAssistantPermission();
  },

  methods: {
    getAssistantPermission() {
      AssistantPermission.search().then(res => {
        const { menu, permissions } = res;

        const loop = (treeData) => {
          _.forEach(treeData, item => {
            item.disabled = !!item.disabled;

            if (item.children) {
              loop(item.children);
            }
          });
        };

        loop(menu);

        this.treeData = menu;
        this.permissions = permissions;
        this.checkedKeys = permissions;
      });
    },

    handleOk() {
      AssistantPermission.add({
        permissions: this.checkedKeys
      }).then(res => {
        this.$message.success('更新成功');
        this.handleCancel();
      });
    },

    handleCancel() {
      this.getAssistantPermission();
      this.$emit('cancel-permission-modal');
    },

    onCheck(checkedKeys) {
      this.checkedKeys = checkedKeys.checked;
    },
  }
}
</script>


<style scoped>
.permission-modal >>> .ant-modal-body {
  max-height: 410px;
  overflow: auto;
}
</style>