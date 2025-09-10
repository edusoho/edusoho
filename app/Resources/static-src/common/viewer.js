import Viewer from 'viewerjs';

const onImgViewer = (container) => {
  const viewer = new Viewer(container, {
    navbar: false,
    title: false,
  });
}

export {
  onImgViewer,
};