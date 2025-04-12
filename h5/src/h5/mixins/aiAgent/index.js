export default {
  methods: {
    initAIAgentSdk(token, chatMetaData, bottom, right, preventDefault = false, draggable = false) {
      const sdk = new window.AgentSDK({
        token: token,
        uiIframeSrc: `${window.location.origin}/static-dist/libs/agent-web-sdk/ui/index.html`,
        // uiIframeSrc: `http://edusoho.me/static-dist/libs/agent-web-sdk/ui/index.html`,
        signalServerUrl: 'wss://test-ai-signal.edusoho.cn/',
        bottom: bottom,
        right: right,
        preventDefault: preventDefault,
        draggable: draggable,
      });
      sdk.addShortcut('plan.create', {
        name: '制定学习计划',
        icon: '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">\n' +
          '<path d="M1.66665 6.33301H14.3333V13.6663C14.3333 14.0345 14.0348 14.333 13.6666 14.333H2.33332C1.96513 14.333 1.66665 14.0345 1.66665 13.6663V6.33301Z" stroke="#333333" stroke-linejoin="round"/>\n' +
          '<path d="M1.66665 3.33366C1.66665 2.96547 1.96513 2.66699 2.33332 2.66699H13.6666C14.0348 2.66699 14.3333 2.96547 14.3333 3.33366V6.33366H1.66665V3.33366Z" stroke="#333333" stroke-linejoin="round"/>\n' +
          '<path d="M5.33335 10.333L7.33335 12.333L11.3334 8.33301" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>\n' +
          '<path d="M5.33335 1.66699V4.33366" stroke="#333333" stroke-linecap="round"/>\n' +
          '<path d="M10.6666 1.66699V4.33366" stroke="#333333" stroke-linecap="round"/>\n' +
          '</svg>',
        type: 'Send',
        data: {
          content: '制定学习计划'
        }
      });
      chatMetaData.workerUrl = `${window.location.origin}/agent_worker`;
      // chatMetaData.workerUrl = `http://edusoho.me/agent_worker`;
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
      sdk.on('generateStudyPlan', () => {
        sdk.removeShortcut('plan.create');
        sdk.addShortcut('plan.check', {
          name: '查看学习计划',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">\n' +
            '<path d="M13 2H3C2.44772 2 2 2.44772 2 3V13C2 13.5523 2.44772 14 3 14H13C13.5523 14 14 13.5523 14 13V3C14 2.44772 13.5523 2 13 2Z" stroke="#333333" stroke-linejoin="round"/>\n' +
            '<path d="M7.00016 4.33301H4.3335V6.99967H7.00016V4.33301Z" stroke="#333333" stroke-linejoin="round"/>\n' +
            '<path d="M7.00016 9H4.3335V11.6667H7.00016V9Z" stroke="#333333" stroke-linejoin="round"/>\n' +
            '<path d="M9 9.33301H11.6667" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>\n' +
            '<path d="M9 11.667H11.6667" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>\n' +
            '<path d="M9 4.33301H11.6667" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>\n' +
            '<path d="M9 6.66699H11.6667" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>\n' +
            '</svg>',
          type: 'Send',
          data: {
            content: '查看学习计划'
          }
        });
        sdk.addShortcut('plan.recreate', {
          name: '重新制定学习计划',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">\n' +
            '<path d="M12.2426 12.2426C11.1569 13.3284 9.65687 14 8 14C4.6863 14 2 11.3137 2 8C2 4.6863 4.6863 2 8 2C9.65687 2 11.1569 2.67157 12.2426 3.75737C12.7953 4.31003 14 5.66667 14 5.66667" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>\n' +
            '<path d="M14 2.66699V5.66699H11" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>\n',
          type: 'Send',
          data: {
            content: '重新制定学习计划'
          }
        });
      });
      sdk.boot();
      window.aiAgentSdk = sdk;
      return sdk;
    },
  },
};
