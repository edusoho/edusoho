import EsMessenger from 'app/common/messenger';

let messenger = new EsMessenger({
  name: 'parent',
  project: 'PlayerProject',
  children: [document.getElementById('viewerIframe')],
  type: 'parent'
});

export default messenger;
