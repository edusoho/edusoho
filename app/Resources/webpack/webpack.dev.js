import fs from 'fs';

fs.exists('web/static-dist', (exists) => {
  if(!exists) {
    fs.mkdir('web/static-dist','0777');
  }

  fs.writeFile('web/static-dist/dev.lock', '');
})

import esWebpackEngine from 'es-webpack-engine';

import config from './webpack.config';
import settings from './settings';

const options = Object.assign({}, config, settings);

esWebpackEngine(options);