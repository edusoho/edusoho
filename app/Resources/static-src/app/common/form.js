const enterSubmit = ($formDom, $btnDom) => {
  $formDom.keypress((e) => {
    if (e.which == 13) {
      $btnDom.trigger('click');
      e.preventDefault();
    }
  });
};

export {
  enterSubmit
};