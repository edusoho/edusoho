import Viewer from 'viewerjs';

const handleClickImage = (container) => {
  if (!container) return;
  const viewer = new Viewer(container, {
    minZoomRatio: 1,
    navbar: false,
    title: false,
    toolbar: {
      zoomIn: {
        show: true,
        size: 'large',
      },
      zoomOut: {
        show: true,
        size: 'large',
      },
      oneToOne: {
        show: true,
        size: 'large',
      },
      reset: {
        show: true,
        size: 'large',
      },
      prev: false,
      play: false,
      next: false,
      rotateLeft: {
        show: true,
        size: 'large',
      },
      rotateRight: {
        show: true,
        size: 'large',
      },
      flipHorizontal: {
        show: true,
        size: 'large',
      },
      flipVertical: {
        show: true,
        size: 'large',
      },
    }
  });
}

export {
  handleClickImage,
};