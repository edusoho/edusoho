import { Dialog } from "vant";

export default {
  methods: {
    copyPcUrl(courseUrl) {
      const message = `移动端暂不支持此类课程学习。请移步至电脑「${courseUrl}」完成课程。`;
      Dialog.alert({
        title: "暂不支持",
        message,
        messageAlign: "left",
        confirmButtonText: "复制链接"
      }).then(() => {
        this.$copyText(courseUrl).then(e => {
          console.log(e);
        });
      });
    }
  }
};
