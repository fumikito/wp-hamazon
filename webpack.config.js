module.exports = {
	mode  : 'production',
	module: {
		rules: [
			{
				test   : /\.jsx?$/,
				exclude: /(node_modules)/,
				use    : {
					loader : 'babel-loader',
					options: {
						presets: [ '@babel/preset-env' ],
						plugins: [ '@babel/plugin-transform-react-jsx' ]
					}
				}
			}
		]
	},
	devtool: 'source-map'
};
