import fs from 'fs';
fs.writeFile('web/static-dist/dev.lock', '');
fs.exists('add-new-file.log', function(exists) {
  if (!exists) {
    fs.writeFile('add-new-file.log','');
  }
});

import esWebpackEngine from 'es-webpack-engine';

import config from './webpack.config';
import settings from './settings';

const options = Object.assign({}, config, settings);

esWebpackEngine(options);