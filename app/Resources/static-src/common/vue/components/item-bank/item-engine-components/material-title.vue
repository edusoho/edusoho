<template>
  <div>
    <a-row>
      <a-col class="ibs-pr16 ibs-pl16">
        <div>
          <!-- word导入解析错误 -->
          <div
            v-if="item.errors && item.errors.stem"
            class="ibs-engine-material ibs-danger-color"
          >
            {{ t("itemEngine.stemErrorMessage") }}
          </div>

          <div class="ibs-engine-material" v-else>
            <div class=" ibs-editor-text" v-html="material"></div>
            <div
              class="ibs-mt16 ibs-engine-material__attachment"
              style="padding: 8px;background-color: #f5f5f5;border-radius: 6px;"
              v-if="getAttachmentTypeData('material').length > 0"
            >
              <attachment-preview
                v-for="fileData in getAttachmentTypeData('material')"
                :key="fileData.id"
                :cdnHost="cdnHost"
                :fileData="fileData"
              ></attachment-preview>
            </div>
          </div>
          <div
            v-if="item.errors && item.errors.subQuestions"
            class="ibs-engine-material ibs-danger-color"
          >
            {{ t("itemEngine.subQuestions") }}
          </div>
        </div>
      </a-col>
    </a-row>
  </div>
</template>

<script>
import Locale from "common/vue/mixins/locale";
import attachmentPreview from "../attachment-preview";
export default {
  name: "material-title",
  mixins: [Locale],
  inject: ["cdnHost"],
  components: {
    attachmentPreview
  },
  props: {
    needScore: {
      type: Number,
      default() {
        return 1;
      }
    },
    material: {
      type: String,
      default: ""
    },
    item: {
      type: Object,
      default() {
        return {};
      }
    },
    attachments: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  methods: {
    getAttachmentTypeData(type) {
      const result = this.attachments.filter(item => {
        return item.module == type;
      });

      return result;
    }
  }
};
</script>
