<template>
  <div class="lesson-directory">
    <div class="clearfix lesson-directory__header">
      <div class="pull-left title">课时（8）</div>
      <div class="pull-left start-time">开始时间</div>
      <div class="pull-left duration">持续时间</div>
    </div>

    <a-tree
      class="lesson-directory-tree"
      draggable
      :tree-data="gData"
      :blockNode="true"
      @dragenter="onDragEnter"
      @drop="onDrop"
    >
      <a-icon slot="switcherIcon" type="down" />
    </a-tree>
  </div>
</template>

<script>
const x = 3;
const y = 2;
const z = 1;
const gData = [];

const generateData = (_level, _preKey, _tns) => {
  const preKey = _preKey || '0';
  const tns = _tns || gData;

  const children = [];
  for (let i = 0; i < x; i++) {
    const key = `${preKey}-${i}`;
    tns.push({ title: key, key });
    if (i < y) {
      children.push(key);
    }
  }
  if (_level < 0) {
    return tns;
  }
  const level = _level - 1;
  children.forEach((key, index) => {
    tns[index].children = [];
    return generateData(level, key, tns[index].children);
  });
};
generateData(z);

export default {
  name: 'LessonDirectory',

  data() {
    return {
      gData
    }
  },

  methods: {
    onDragEnter(info) {
      console.log(info);
      // expandedKeys 需要受控时设置
      // this.expandedKeys = info.expandedKeys
    },
    onDrop(info) {
      console.log(info);
      const dropKey = info.node.eventKey;
      const dragKey = info.dragNode.eventKey;
      const dropPos = info.node.pos.split('-');
      const dropPosition = info.dropPosition - Number(dropPos[dropPos.length - 1]);
      const loop = (data, key, callback) => {
        data.forEach((item, index, arr) => {
          if (item.key === key) {
            return callback(item, index, arr);
          }
          if (item.children) {
            return loop(item.children, key, callback);
          }
        });
      };
      const data = [...this.gData];

      // Find dragObject
      let dragObj;
      loop(data, dragKey, (item, index, arr) => {
        arr.splice(index, 1);
        dragObj = item;
      });
      if (!info.dropToGap) {
        // Drop on the content
        loop(data, dropKey, item => {
          item.children = item.children || [];
          // where to insert 示例添加到尾部，可以是随意位置
          item.children.push(dragObj);
        });
      } else if (
        (info.node.children || []).length > 0 && // Has children
        info.node.expanded && // Is expanded
        dropPosition === 1 // On the bottom gap
      ) {
        loop(data, dropKey, item => {
          item.children = item.children || [];
          // where to insert 示例添加到尾部，可以是随意位置
          item.children.unshift(dragObj);
        });
      } else {
        let ar;
        let i;
        loop(data, dropKey, (item, index, arr) => {
          ar = arr;
          i = index;
        });
        if (dropPosition === -1) {
          ar.splice(i, 0, dragObj);
        } else {
          ar.splice(i + 1, 0, dragObj);
        }
      }
      this.gData = data;
    },
  }
}
</script>

<style lang="less">
.lesson-directory {
  background-color: #fff;
  border: 1px solid #ebebeb;

  &__header {
    background: #f5f5f5;

    .title {
      width: 400px;
      padding-left: 40px;
    }

    .start-time {
      width: 170px;
    }

    .duration {
      width: 80px;
    }
  }
}


</style>
