var path = require('path');

module.exports = {
	entry: {
		'admin-main': './assets/js/_src/admin-main.js'
	},
	output: {
		filename: './assets/js/[name].js'
	},
	module: {
		loaders: [
			{
				test: /.jsx?$/,
				loader: 'babel-loader',
				exclude: /node_modules/,
				query: {
					presets: ['es2015', 'react']
				}
			}
		]
	},
	externals: {
		'jQuery': 'jQuery',
		'wp': 'wp'
	},
	resolve: {
		root: [
			path.resolve('./assets/js/_src/')
		],
		extensions: ['', '.js', '.jsx']
	}
}