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
      :blockNode="true"
      @dragenter="onDragEnter"
      @drop="onDrop"
    >
      <a-icon slot="switcherIcon" type="down" />

      <template v-for="firstLesson in lessonDirectory">
        <a-tree-node :class="`tree-node-${firstLesson.type}`" :key="firstLesson.id">
          <template slot="title">
            <lesson-directory-item :lesson="firstLesson" class-name='first' />
          </template>

          <template v-if="firstLesson.children">
            <template v-for="secondLesson in firstLesson.children">
              <a-tree-node :class="`tree-node-${secondLesson.type}`" :key="secondLesson.id">
                <template slot="title">
                  <lesson-directory-item :lesson="secondLesson" class-name='second' />
                </template>

                <template v-if="secondLesson.children">
                  <template v-for="thirdLesson in secondLesson.children">
                    <a-tree-node :class="`tree-node-${thirdLesson.type}`" :key="thirdLesson.id">
                      <template slot="title">
                        <lesson-directory-item :lesson="thirdLesson" class-name='third' />
                      </template>

                      <template v-if="thirdLesson.tasks">
                        <template v-for="fourLesson in thirdLesson.tasks">
                          <a-tree-node class="tree-node-task" :key="fourLesson.id">
                            <template slot="title">
                              <lesson-directory-item :lesson="fourLesson" class-name='four' />
                            </template>
                          </a-tree-node>
                        </template>
                      </template>
                    </a-tree-node>
                  </template>
                </template>

                <template v-if="secondLesson.tasks">
                  <template v-for="thirdLesson in secondLesson.tasks">
                    <a-tree-node class="tree-node-task" :key="thirdLesson.id">
                      <template slot="title">
                        <lesson-directory-item :lesson="thirdLesson" class-name='third' />
                      </template>
                    </a-tree-node>
                  </template>
                </template>
              </a-tree-node>
            </template>
          </template>

          <template v-if="firstLesson.tasks">
            <template v-for="secondLesson in firstLesson.tasks">
              <a-tree-node class="tree-node-task" :key="secondLesson.id">
                <template slot="title">
                  <lesson-directory-item :lesson="secondLesson" class-name='second' />
                </template>
              </a-tree-node>
            </template>
          </template>

        </a-tree-node>
      </template>

    </a-tree>
  </div>
</template>

<script>
import _ from '@codeages/utils';
import LessonDirectoryItem from './LessonDirectoryItem.vue';

export default {
  name: 'LessonDirectory',

  components: {
    LessonDirectoryItem
  },

  props: {
    lessonDirectory: {
      type: Array,
      required: true,
      default() {
        return []
      }
    }
  },

  methods: {
    supportDrag(type) {
      return _.includes(['chapter', 'unit', 'lesson'], type);
    },

    onDragEnter(info) {
      // console.log(info);
      // expandedKeys 需要受控时设置
      // this.expandedKeys = info.expandedKeys
    },

    onDrop(info) {
      const dropKey = info.node.eventKey;
      const dragKey = info.dragNode.eventKey;
      const dropPos = info.node.pos.split('-');
      const dropPosition = info.dropPosition - Number(dropPos[dropPos.length - 1]);

      const loop = (data, key, callback) => {
        data.forEach((item, index, arr) => {
          if (item.id === key) {
            return callback(item, index, arr);
          }
          if (item.children) {
            return loop(item.children, key, callback);
          }
          if (item.tasks) {
            return loop(item.tasks, key, callback);
          }
        });
      };
      const data = [...this.lessonDirectory];

      let dragObj;
      loop(data, dragKey, (item, index, arr) => {
        if (this.supportDrag(item.type)) {
          arr.splice(index, 1);
        }

        dragObj = item;
      });

      if (!this.supportDrag(dragObj.type)) return;

      if (!info.dropToGap) {
        // Drop on the content
        loop(data, dropKey, item => {

          item.children = item.children || [];
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
      this.lessonDirectory = data;
    },
  }
}
</script>

<style lang="less">
.lesson-directory {
  min-height: 600px;
  margin-top: 16px;
  background-color: #fff;
  border: 1px solid #ebebeb;

  &__header {
    padding: 0 5px;
    background: #f5f5f5;

    .title {
      width: 388px;
      padding-left: 24px;
    }

    .start-time {
      width: 170px;
    }

    .duration {
      width: 80px;
    }
  }

  li span.ant-tree-switcher {
    line-height: 34px;
    height: 34px;
  }

  li .ant-tree-node-content-wrapper {
    line-height: 30px;
    height: 34px;
  }

  .tree-node-chapter {
    .title,
    .start-time,
    .duration {
      font-weight: 600;
      color: #333;
    }
  }

  .tree-node-unit,
  .tree-node-lesson,
  .tree-node-task {
    .title,
    .start-time,
    .duration {
      font-weight: 400;
      color: #666;
    }
  }
}
</style>
