const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
    ...defaultConfig,
    entry: {
        ...defaultConfig.entry,
        'index': path.resolve( __dirname, 'src/index.js' ),
        'reblock-single': path.resolve( __dirname, 'src/reblock-single.js' )
    },
    output: {
        ...defaultConfig.output,
        path: path.resolve( __dirname, 'build' ),
        filename: '[name].js'
    }
};
