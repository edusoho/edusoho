module.exports = {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}'
  ],
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
      zIndex: Object.fromEntries(Array.from({length: 100}, (_, index) => [(index + 1).toString(), (index + 1).toString()])),
      colors: {
        'primary': '#165dff',
        'primary-disabled': '#94bfff',
        'text-1': '#ffffff',
        'text-2': '#c9cdd4',
        'text-3': '#86909c',
        'text-4': '#4e5969',
        'text-5': '#1d2129',
        'text-6': '#919399',
        'text-7': '#37393D',
        'fill-1': '#ffffff',
        'fill-2': '#f7f8fa',
        'fill-3': '#f2f3f5',
        'fill-4': '#e5e6eb',
        'fill-5': '#c9cdd4',
        'fill-6': '#4e5969',
        'fill-7': '#F7F7F7',
        'line-1': '#f2f3f5',
        'line-2': '#e5e6eb',
        'line-3': '#c9cdd4',
        'line-4': '#86909c',
        '[#492F0B]': '#492F0B',
        '[#F7D27B]': '#F7D27B',
        '[#FCEABE]': '#FCEABE',
        '[#60F1A3]': '#60F1A3',
      },
      lineHeight: Array.from({ length: 100 }).reduce((map, _, index) => {
        map[index] = `${index}px`;
        return map;
      }, {}),
      gap: Array.from({ length: 100 }).reduce((map, _, index) => {
        map[index] = `${index}px`;
        return map;
      }, {}),
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
    }
  },
  plugins: [
    require('@tailwindcss/line-clamp')
  ]
}
