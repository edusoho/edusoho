import Viewer from 'viewerjs';

const onImgViewer = (container) => {
  const viewer = new Viewer(container, {
    navbar: false,
    title: false,
    toolbar: {
      zoomIn: true,
      zoomOut: true,
      oneToOne: true,
      reset: true,
      prev: false,
      play: false,
      next: false,
      rotateLeft: true,
      rotateRight: true,
      flipHorizontal: true,
      flipVertical: true,
    }
  });
}

export {
  onImgViewer,
};