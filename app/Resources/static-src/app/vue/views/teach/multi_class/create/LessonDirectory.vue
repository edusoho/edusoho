<template>
  <div class="lesson-directory">
    <div class="clearfix lesson-directory__header">
      <div class="pull-left title">课时</div>
      <div class="pull-left start-time">开始时间</div>
      <div class="pull-left duration">持续时间</div>
    </div>

    <a-tree
      class="lesson-directory-tree"
      draggable
      :blockNode="true"
      @drop="onDrop"
    >
      <a-icon slot="switcherIcon" type="down" />

      <template v-for="firstLesson in lessonDirectory">
        <a-tree-node :class="`tree-node-${firstLesson.type}`" :key="firstLesson.id">
          <template slot="title">
            <lesson-directory-item
              :courseId="courseId"
              :lesson="firstLesson"
              class-name="first"
              @event-communication="eventCommunication"
            />
          </template>

          <template v-if="firstLesson.children">
            <template v-for="secondLesson in firstLesson.children">
              <a-tree-node :class="`tree-node-${secondLesson.type}`" :key="secondLesson.id">
                <template slot="title">
                  <lesson-directory-item
                    :courseId="courseId"
                    :lesson="secondLesson"
                    class-name="second"
                    @event-communication="eventCommunication"
                  />
                </template>

                <template v-if="secondLesson.children">
                  <template v-for="thirdLesson in secondLesson.children">
                    <a-tree-node :class="`tree-node-${thirdLesson.type}`" :key="thirdLesson.id">
                      <template slot="title">
                        <lesson-directory-item
                          :courseId="courseId"
                          :lesson="thirdLesson"
                          class-name="third"
                          @event-communication="eventCommunication"
                        />
                      </template>

                      <template v-if="thirdLesson.tasks">
                        <template v-for="fourLesson in thirdLesson.tasks">
                          <a-tree-node class="tree-node-task" :key="fourLesson.id">
                            <template slot="title">
                              <lesson-directory-item
                                :courseId="courseId"
                                :lesson="fourLesson"
                                class-name="four"
                                @event-communication="eventCommunication"
                              />
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
                        <lesson-directory-item
                          :courseId="courseId"
                          :lesson="thirdLesson"
                          class-name="third"
                          @event-communication="eventCommunication"
                        />
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
                  <lesson-directory-item
                    :courseId="courseId"
                    :lesson="secondLesson"
                    class-name="second"
                    @event-communication="eventCommunication"
                  />
                </template>
              </a-tree-node>
            </template>
          </template>

        </a-tree-node>
      </template>

    </a-tree>
     <a-empty style="margin-top: 200px;" v-if="!lessonDirectory.length" />
  </div>
</template>

<script>
import { Course } from 'common/vue/service';
import _ from '@codeages/utils';
import LessonDirectoryItem from './LessonDirectoryItem.vue';

export default {
  name: 'LessonDirectory',

  components: {
    LessonDirectoryItem
  },

  props: {
    courseId: {
      type: [Number, String],
      required: true
    },

    lessonDirectory: {
      type: Array,
      required: true
    }
  },

  methods: {
    allowDrag(dragType, dragEnterType, dropPosition) {
      if (!['chapter', 'unit', 'lesson'].includes(dragType)) return false;

      if (dragType === 'chapter' && dragEnterType === 'chapter' && dropPosition != 0) return true;

      if (dragType === 'unit') {
        if (dragEnterType === 'chapter') return true;
        if (dragEnterType === 'unit' && dropPosition != 0) return true;
      }

      if (dragType === 'lesson') {
        if (['chapter', 'unit'].includes(dragEnterType)) return true;
        if (dragEnterType === 'lesson' && dropPosition != 0) return true;
      }

      return false;
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

      let dragObj, dragIndex, dragArr, dragEnterObj, dragEnterIndex, dragEnterArr;

      loop(data, dragKey, (item, index, arr) => {
        dragObj = item;
        dragIndex = index;
        dragArr = arr;
      });

      loop(data, dropKey, (item, index, arr) => {
        dragEnterObj = item;
        dragEnterIndex = index;
        dragEnterArr = arr;
      });

      if (!this.allowDrag(dragObj.type, dragEnterObj.type, dropPosition)) return;

      dragArr.splice(dragIndex, 1);

      if (!info.dropToGap) {
        dragEnterObj.children = dragEnterObj.children || [];
        dragEnterObj.children.push(dragObj);
      } else if (
        (info.node.children || []).length > 0 &&
        info.node.expanded &&
        dropPosition === 1
      ) {
          dragEnterObj.children = dragEnterObj.children || [];
          dragEnterObj.children.unshift(dragObj);
      } else {
        if (dropPosition === -1) {
          dragEnterArr.splice(dragEnterIndex, 0, dragObj);
        } else {
          dragEnterArr.splice(dragEnterIndex + 1, 0, dragObj);
        }
      }
      this.$emit('change-lesson-directory', { data });
    },

    /**
     * params = { eventType, id }
     */
    eventCommunication(params = {}) {
      const { eventType, id } = params;

      // 章节重命名
      if (eventType === 'renameChapterUnit') {
        this.renameChapterUnit(params);
        return;
      }

      // 删除章节
      if (eventType === 'deleteChapterUnit') {
        this.deleteChapter(id);
        return;
      }

      // 删除任务
      if (eventType === 'deleteTask') {
        this.deleteTask(id);
      }
    },

    renameChapterUnit(params) {
      this.$emit('change-lesson-directory', params);
    },

    async deleteChapter(id) {
      const { success } = await Course.deleteChapter(this.courseId, id);
      if (success) {
        this.$emit('change-lesson-directory', { eventType: 'update' });
        this.$message.success('删除成功');
      }
    },

    async deleteTask(id) {
      const success = await Course.deleteTask(this.courseId, id);
      if (success) {
        this.$emit('change-lesson-directory', { eventType: 'update' });
        this.$message.success('删除成功');
      }
    }
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
    color: #666;

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
