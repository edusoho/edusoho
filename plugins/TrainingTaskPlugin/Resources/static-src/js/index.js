import Split from 'split.js'
var split = Split(['#one', '#two'], {
    sizes: [25, 75],
    // elementStyle: (dimension, size, gutterSize) => ({
    //     'flex-basis': `calc(${size}% - ${gutterSize}px)`,
    // }),
    gutterStyle: (dimension, gutterSize) => ({
        'flex-basis':  `${gutterSize}px`,
    }),
})
console.log(split);