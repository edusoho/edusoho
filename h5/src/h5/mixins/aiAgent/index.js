export default {
  methods: {
    initAIAgentSdk(token, chatMetaData, bottom, right, preventDefault = false, draggable = false) {
      const sdk = new window.AgentSDK({
        token: token,
        // uiIframeSrc: `${window.location.origin}/static-dist/libs/agent-web-sdk/ui/index.html`,
        uiIframeSrc: `http://edusoho.me/static-dist/libs/agent-web-sdk/ui/index.html`,
        bottom: bottom,
        right: right,
        preventDefault: preventDefault,
        draggable: draggable,
      });
      // chatMetaData.workerUrl = `${window.location.origin}/agent_worker`;
      chatMetaData.workerUrl = `http://edusoho.me/agent_worker`;
      sdk.setChatMetadata(chatMetaData);
      sdk.on('clickLink', (data) => {
        const regex = /\/course\/(\d+)\/task\/(\d+)/;
        const matches = data.match(regex);
        if (matches){
          const courseId = matches[1];
          const taskId = matches[2];
          this.$router.push({
            name: 'course',
            params: {
              id: courseId
            }
          })
          this.$nextTick(() => {
            const taskElement = document.getElementById(taskId)
            taskElement.click();
          })
          sdk.hideIframe();
        } else {
          window.open(data, '_blank');
        }
      });
      window.aiAgentSdk = sdk;
      return sdk;
    },
  },
};
