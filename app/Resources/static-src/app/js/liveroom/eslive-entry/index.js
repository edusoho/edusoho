import ESLiveWebSDK from '@edusoho-live/eslive-web-sdk';

const $params = $('#eslive-params');

const sdk = new ESLiveWebSDK();

(async () => {
  await sdk.connect({
    container: 'eslive-container', // 即页面中 ID 为 eslive-container 的 div 容器
    url: $params.data('url'),
    watermark: $params.data('watermark'),
  });
})();
