/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./js/**/*.{vue,js}', '../plugins/*Plugin/vue3/js/**/*.{vue,js}'],
  theme: {
    padding: Array.from({ length: 1000 }).reduce((map, _, index) => {
      map[index] = `${index}px`;
      return map;
    }, {}),
    spacing: Array.from({ length: 1000 }).reduce((map, _, index) => {
      map[index] = `${index}px`;
      return map;
    }, {}),
    borderRadius: Array.from({ length: 100 }).reduce((map, _, index) => {
      map[index] = `${index}px`;
      return map;
    }, {}),
    extend: {
      width: Array.from({ length: 1600 }).reduce((map, _, index) => {
        map[index] = `${index}px`;
        return map;
      }, {}),
      height: Array.from({ length: 1600 }).reduce((map, _, index) => {
        map[index] = `${index}px`;
        return map;
      }, {}),
      fontSize: Array.from({ length: 100 }).reduce((map, _, index) => {
        map[index] = `${index}px`;
        return map;
      }, {}),
      lineHeight: Array.from({ length: 100 }).reduce((map, _, index) => {
        map[index] = `${index}px`;
        return map;
      }, {}),
      gap: Array.from({ length: 100 }).reduce((map, _, index) => {
        map[index] = `${index}px`;
        return map;
      }, {}),
    },
  },
  plugins: [
    function ({ addUtilities }) {
      addUtilities({
        //为了规避bootstrap的hidden类
        '.tw-hidden': {
          display: 'none',
        },
      });
    },
  ],
};
