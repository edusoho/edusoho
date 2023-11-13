module.exports = {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}'
  ],
  theme: {
    spacing: () => {
      const obj = {};
      const baseSpacing = 4;
      for (let i = 0; i <= 50; i++) {
        const key = i * baseSpacing;
        const value = key + 'px';
        obj[key] = value;
      }
      return obj;
    },
    fontSize: {
      12: ['12px', { lineHeight: '16px' }],
      14: ['14px', { lineHeight: '20px' }],
      16: ['16px', { lineHeight: '24px' }],
      18: ['18px', { lineHeight: '28px' }],
      20: ['20px', { lineHeight: '30px' }],
      24: ['24px', { lineHeight: '32px' }],
      28: ['28px', { lineHeight: '36px' }],
      32: ['32px', { lineHeight: '40px' }],
      36: ['32px', { lineHeight: '44px' }],
      40: ['40px', { lineHeight: '48px' }],
      48: ['48px', { lineHeight: '60px' }]
    },
    extend: {
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
        'line-4': '#86909c'
      }
    }
  },
  plugins: [
    require('@tailwindcss/line-clamp')
  ]
}
