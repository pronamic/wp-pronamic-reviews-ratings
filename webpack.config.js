const defaultConfig = require( "@wordpress/scripts/config/webpack.config" );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		...defaultConfig.entry,
		index: path.resolve( process.cwd(), 'js/src', 'index.js' )
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( process.cwd(), 'js/dist' )
	}
};