export default {
  methods: {
    initAIAgentSdk(token, chatMetaData, bottom, right, preventDefault = false, draggable = false) {
      const sdk = new window.AgentSDK({
        token: token,
        // uiIframeSrc: 'http://edusoho.me/static-dist/libs/agent-web-sdk/ui/index.html',
        uiIframeSrc: `${window.location.origin}/static-dist/libs/agent-web-sdk/ui/index.html`,
        signalServerUrl: 'wss://test-ai-signal.edusoho.cn/',
        bottom: bottom,
        right: right,
        preventDefault: preventDefault,
        draggable: draggable,
      });
      sdk.addShortcut("plan.create", {
        name: "制定学习计划",
        icon: "<svg width=\"16\" height=\"16\" viewBox=\"0 0 16 16\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n" +
          "<path d=\"M1.66665 6.33301H14.3333V13.6663C14.3333 14.0345 14.0348 14.333 13.6666 14.333H2.33332C1.96513 14.333 1.66665 14.0345 1.66665 13.6663V6.33301Z\" stroke=\"#333333\" stroke-linejoin=\"round\"/>\n" +
          "<path d=\"M1.66665 3.33366C1.66665 2.96547 1.96513 2.66699 2.33332 2.66699H13.6666C14.0348 2.66699 14.3333 2.96547 14.3333 3.33366V6.33366H1.66665V3.33366Z\" stroke=\"#333333\" stroke-linejoin=\"round\"/>\n" +
          "<path d=\"M5.33335 10.333L7.33335 12.333L11.3334 8.33301\" stroke=\"#333333\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n" +
          "<path d=\"M5.33335 1.66699V4.33366\" stroke=\"#333333\" stroke-linecap=\"round\"/>\n" +
          "<path d=\"M10.6666 1.66699V4.33366\" stroke=\"#333333\" stroke-linecap=\"round\"/>\n" +
          "</svg>",
        type: "Send",
        data: {
          content: "制定学习计划"
        }
      });
      // chatMetaData.workerUrl = 'http://edusoho.me/agent_worker';
      chatMetaData.workerUrl = `${window.location.origin}/agent_worker`;
      sdk.setChatMetadata(chatMetaData);
      sdk.boot();
      window.aiAgentSdk = sdk;
      return sdk;
    },
  },
}
