export default (text, callback) => {
  const textArea = document.createElement('textArea');
  const ElementId = 'for_clipboard';

  textArea.id = ElementId;
  textArea.value = text;
  textArea.style.position = 'fixed';
  textArea.style.top = 0;
  textArea.style.left = 0;
  textArea.style.border = 'none';
  textArea.style.outline = 'none';
  textArea.style.resize = 'none';
  textArea.style.background = 'transparent';
  textArea.style.color = 'transparent';

  document.body.appendChild(textArea);
  textArea.select();

  document.execCommand('copy');
  document.body.removeChild(textArea);
  callback();
};
