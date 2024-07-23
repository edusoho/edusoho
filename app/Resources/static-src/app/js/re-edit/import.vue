<template>
  <div id="app" class="ibs-vue question-import">
    <item-import
      :isDownload="false"
      :subject="subject"
      :showCKEditorData="showCKEditorData"
      :bank_id="bank_id"
      :category="category"
      :importType="importType"
      :showAttachment="showAttachment"
      :cdnHost="cdnHost"
      :repeatList="repeatList"
      :loading="loading"
      :isVisiblePopconfim="isVisiblePopconfim"
      :uploadSDKInitData="uploadSDKInitData"
      :aiAnalysisEnable="aiAnalysisEnable"
      :deleteAttachmentCallback="deleteAttachmentCallback"
      :previewAttachmentCallback="previewAttachmentCallback"
      :downloadAttachmentCallback="downloadAttachmentCallback"
      @deleteAttachment="deleteAttachment"
      @previewAttachment="previewAttachment"
      @downloadAttachment="downloadAttachment"
      @getRepeatQuestion="getRepeatQuestion"
      @getImportData="getImportData"
      @renderFormula="renderFormula"
      @editQuestion="editQuestion"
      @changeEditor="changeEditor"
      @getInitRepeatQuestion="getInitRepeatQuestion"
      @getEditRepeatQuestion="getEditRepeatQuestion"
      @getAiAnalysis="getAiAnalysis"
    ></item-import>
  </div>
</template>

<script>
import axios from "axios";
import { renderKatex } from "app/common/katex-render";

export default {
  data() {
    return {
      subject: {
        fileName: $("[name=filename]").val(),
        items: JSON.parse($("[name=items]").val()),
      },
      showCKEditorData: {
        publicPath: $("[name=ckeditor_path]").val(),
        filebrowserImageUploadUrl: $("[name=ckeditor_image_upload_url]").val(),
        filebrowserImageDownloadUrl: $(
          "[name=ckeditor_image_download_url]"
        ).val(),
        language:
          document.documentElement.lang === "zh_CN"
            ? "zh-cn"
            : document.documentElement.lang,
        jqueryPath: $("[name=jquery_path]").val(),
      },
      bank_id: $("[name=bankId]").val(),
      category: JSON.parse($("[name=categoryTree]").val()),
      importType: $("[name=type]").val(),
      showAttachment: $("[name=show_attachment]").val(),
      cdnHost: $("[name=cdn_host]").val(),
      uploadSDKInitData: {
        sdkBaseUri: app.cloudSdkBaseUri,
        disableDataUpload: app.cloudDisableLogReport,
        disableSentry: app.cloudDisableLogReport,
        initUrl: $("[name=upload_init_url]").val(),
        finishUrl: $("[name=upload_finish_url]").val(),
        accept: JSON.parse($("[name=upload_accept]").val()),
        fileSingleSizeLimit: $("[name=upload_size_limit]").val(),
        ui: "batch",
        multiple: true,
        multitaskNum: 3,
        fileNumLimit: 3,
        locale: document.documentElement.lang
      },
      fileId: 0,
      redirect: true,
      token: $('[name="import_token"]').val(),
      repeatList: [],
      loading: false,
      isVisiblePopconfim: false,
      isWrong: false,
      duplicatedIds: [],
      ids: null,
      stopAnswer: {},
      aiAnalysisEnable: $("[name=ai_analysis_enable]").val() == 1 ? true : false,
    }
  },
  created() {
    let self = this;
    $(window).on("beforeunload", function () {
      if (self.redirect) {
        return Translator.trans("admin.block.not_saved_data_hint");
      }
    });
  },
  provide() {
    return {
      modeOrigin: "create",
      self: this
    }
  },
  methods: {
    async getRepeatQuestion(subject, test = 0) {
      const that = this

      if (!test) {
        that.loading = true
      }

      await $.ajax({
        url: $("[name=check_duplicated_questions_url]").val(),
        contentType: "application/json;charset=utf-8",
        type: "post",
        data: JSON.stringify(subject),
        beforeSend(request) {
          request.setRequestHeader(
            "X-CSRF-Token",
            $("meta[name=csrf-token]").attr("content")
          );
        }
      }).done(function (res) {
        that.duplicatedIds = res.duplicatedIds
        that.repeatList = []

        for (const key in res.duplicatedIds) {
          that.repeatList.push(Number(key));
        }

        if (test) {
          return
        }

        if (that.repeatList.length > 0) {
          that.$confirm({
            title: Translator.trans("created.question.confirm.import.title"),
            okText: Translator.trans("created.question.confirm.ok.btn"),
            cancelText: Translator.trans(
              "created.question.confirm.import.close.btn"
            ),
            icon: "exclamation-circle",
            onOk() {
              that.loading = false;
              that.forceRemoveModalDom()
            },
            onCancel() {
              that.getImportData(subject)
              that.forceRemoveModalDom()
            },
          });
        } else {
          that.getImportData(subject)
        }
      })
    },
    forceRemoveModalDom() {
      const modal = document.querySelector(".ant-modal-root");

      if (modal) {
        modal.remove();
      }

      document.body.style = "";
    },
    editQuestion(data, items) {
      this.ids = data.ids

      items = items.filter((item) => {
        return item.ids !== data.ids
      })

      const material = data.type === 'material' ? data.material : data.questions[0].stem
      return new Promise(resolve => {
        $.ajax({
          url: $('[name=check_question_duplicative_url]').val(),
          contentType: 'application/json;charset=utf-8',
          type: 'post',
          data: JSON.stringify({material: material, items: items}),
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          }
        }).done((res) => {
          this.isWrong = res;
          resolve(res);
        })
      });
    },
    getImportData(subject) {
      this.redirect = false;
      $.ajax({
        url: $("[name=saveUrl]").val(),
        contentType: "application/json;charset=utf-8",
        type: "post",
        data: JSON.stringify(subject),
        beforeSend(request) {
          request.setRequestHeader(
            "X-CSRF-Token",
            $("meta[name=csrf-token]").attr("content")
          );
        }
      }).done(function (resp) {
        if (resp.goto) {
          window.location.href = resp.goto;
        }
      })
    },
    deleteAttachmentCallback() {
      return new Promise((resolve) => {
        $.ajax({
          url: $("[name=delete-attachment-url]").val(),
          type: "post",
          data: { id: this.fileId },
          beforeSend(request) {
            request.setRequestHeader(
              "X-CSRF-Token",
              $("meta[name=csrf-token]").attr("content")
            );
          }
        }).done(function (resp) {
          resolve(resp);
        })
      });
    },
    deleteAttachment(fileId) {
      this.fileId = fileId;
    },
    previewAttachment(fileId) {
      this.fileId = fileId;
    },
    downloadAttachment(fileId) {
      this.fileId = fileId;
    },
    previewAttachmentCallback() {
      let self = this;
      return new Promise((resolve) => {
        $.ajax({
          url: $("[name=preview-attachment-url]").val(),
          type: "post",
          data: { id: this.fileId },
          beforeSend(request) {
            request.setRequestHeader(
              "X-CSRF-Token",
              $("meta[name=csrf-token]").attr("content")
            );
          }
        }).done(function (resp) {
          console.log(app);
          console.log(resp);
          resp.data["sdkBaseUri"] = app.cloudSdkBaseUri;
          resp.data["disableDataUpload"] = app.cloudDisableLogReport;
          resp.data["disableSentry"] = app.cloudDisableLogReport;
          resolve(resp);
          self.fileId = 0;
        })
      });
    },
    downloadAttachmentCallback() {
      let self = this;
      return new Promise((resolve) => {
        $.ajax({
          url: $("[name=download-attachment-url]").val(),
          type: "post",
          data: { id: this.fileId },
          beforeSend(request) {
            request.setRequestHeader(
              "X-CSRF-Token",
              $("meta[name=csrf-token]").attr("content")
            );
          }
        }).done(function (resp) {
          resolve(resp);
          self.fileId = 0;
        })
      });
    },
    changeEditor(material, items) {
      const that = this;

      items = items.filter((item) => {
        return item.ids !== that.ids;
      })
      return new Promise((resolve) => {
        $.ajax({
          url: $("[name=check_question_duplicative_url]").val(),
          contentType: "application/json;charset=utf-8",
          type: "post",
          data: JSON.stringify({ material: material, items: items }),
          beforeSend(request) {
            request.setRequestHeader(
              "X-CSRF-Token",
              $("meta[name=csrf-token]").attr("content")
            );
          }
        }).done(function (res) {
          if (!res) {
            that.isWrong = false;
            resolve(res);
          } else {
            that.isWrong = true;
          }
        })
      });
    },
    getInitRepeatQuestion(subject) {
      axios({
        url: $("[name=check_duplicated_questions_url]").val(),
        method: "POST",
        data: subject,
        headers: {
          Accept: "application/vnd.edusoho.v2+json",
          "X-CSRF-Token": $("meta[name=csrf-token]").attr("content"),
          "X-Requested-With": "XMLHttpRequest",
        }
      }).then((res) => {
        this.repeatList = []
        this.duplicatedIds = res.data.duplicatedIds

        for (const key in res.data.duplicatedIds) {
          this.repeatList.push(Number(key));
        }
      });
    },
    getEditRepeatQuestion(subject) {
      this.getInitRepeatQuestion(subject)
    },
    renderFormula() {
      this.$nextTick(() => {
        renderKatex()
      })
    },
    async getAiAnalysis(data, disable, enable, complete, finish) {
      if (/<img .*>/.test(JSON.stringify(data))) {
        disable();
        return;
      }
      enable();
      data.role = "teacher";
      const response = await fetch("/api/ai/question_analysis/generate", {
        method: "POST",
        headers: {
          "Content-Type": "application/json;charset=utf-8",
          Accept: "application/vnd.edusoho.v2+json",
        },
        body: JSON.stringify(data),
      });
      const reader = response.body.getReader();
      const decoder = new TextDecoder();
      let lastMessgae = "";
      while (true) {
        const { done, value } = await reader.read();
        const messages = (lastMessgae + decoder.decode(value)).split("\n\n");
        let key = 1;
        for (let message of messages) {
          if (key == messages.length) {
            lastMessgae = message;
          } else {
            const parseMessage = JSON.parse(message.slice(5));
            if (parseMessage.event === "message") {
              complete(parseMessage.answer);
            }
            key++;
          }
        }
        if (done) {
          finish();
          break;
        }
      }
    },
  },
};
</script>
