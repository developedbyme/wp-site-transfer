var webpack = require("webpack");
var path = require("path");
var ExtractTextPlugin = require("extract-text-webpack-plugin");

var configuration = {
	mode: "development",
	context: __dirname + "/private/jsapp",
	entry: {
		"admin": "./admin.js",
		"admin-style": "../css/admin.css",
	},

	output: {
		path: __dirname + "/assets/js",
		filename: "[name].js"
	},

	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				loader: 'babel-loader',
				options: {
					presets: ['es2015', 'react']
				}
			},
			{
				test: /\.json$/,
				exclude: /node_modules/,
				loader: 'json-loader'
			},
			{
				test: /\.css$/,
				exclude: /node_modules/,
				loader: ExtractTextPlugin.extract({"use": ["css-loader"]})
			},
			{
				test: /\.(jpe?g|gif|png|svg)$/,
				loader: 'file-loader?emitFile=true&name=../css/[path][name].[ext]'
			}
		]
	},
	
	resolve: {
		modules: [path.resolve(__dirname, "private/jsapp"), path.resolve(__dirname, "private/css"), "node_modules"]
	}
};

configuration.plugins = new Array();



configuration.plugins.push(new ExtractTextPlugin({
	"filename": "../css/[name].css",
	"allChunks": true
}));

//MENOTE: production plugins
configuration.plugins.push(new webpack.DefinePlugin({
	"process.env": {
		"NODE_ENV": JSON.stringify("development")
	}
}));
/*
configuration.plugins.push(new webpack.optimize.UglifyJsPlugin({
	"compress": {
		
		"warnings": false,
		"drop_console": true,
		"drop_debugger": true
		
	}
}));
*/


module.exports = configuration;