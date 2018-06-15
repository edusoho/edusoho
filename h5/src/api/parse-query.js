const parseQuery = (url, query) => {
  url = decodeURIComponent(url);

  const aftpart = url.split('?')[1];
  let newUrl = '';

  if (aftpart === '') return;

  const params = aftpart.split('&');

  params.forEach(item => {
    if (item) {
      item = item.replace('{', '').replace('}', '');
      const p = item.split('=');

      newUrl = `${newUrl + p[0]}=${query[p[0]]}&`;
      return item;
    }
  });

  return `${url.split('?')[0]}?${newUrl.substring(0, newUrl.length - 1)}`;
};

export default parseQuery;
