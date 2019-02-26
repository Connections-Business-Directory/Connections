// process.traceDeprecation = true; // Enable to see deprecation trace.
const webpack = require( 'webpack' );
// const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const path = require( 'path' );
// const ExtractTextPlugin = require( 'extract-text-webpack-plugin' );
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const inProduction = ('production' === process.env.NODE_ENV);
// const BrowserSyncPlugin = require( 'browser-sync-webpack-plugin' );
// const ImageminPlugin = require( 'imagemin-webpack-plugin' ).default;
const CleanWebpackPlugin = require( 'clean-webpack-plugin' );
const UglifyJsPlugin = require("uglifyjs-webpack-plugin");
const WebpackRTLPlugin = require( 'webpack-rtl-plugin' );
// const wpPot = require( 'wp-pot' );

const config = {
	mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
	// Ensure modules like magnific know jQuery is external (loaded via WP).
	externals: {
		react: 'React',
		'react-dom': 'ReactDOM',
		tinymce: 'tinymce',
		moment: 'moment',
		jquery: 'jQuery',
		$: 'jQuery',
		lodash: 'lodash',
		'lodash-es': 'lodash',
		// 'wp.i18n': '@wordpress/i18n',
		// 'wp.blocks': {
		// 	window: [ 'wp', 'blocks' ],
		// },
		// 'wp.compose': '@wordpress/compose',
		// 'wp.data': '@wordpress/data',
		// 'wp.date': '@wordpress/date',
		// 'wp.editor': '@wordpress/editor',
		// 'wp.element': '@wordpress/element',
		// 'wp.utils': '@wordpress/utils',
	},
	devtool: 'source-map',
	module: {
		rules: [

			// Use Babel to compile JS.
			{
				test: /\.js$/,
				exclude: /node_modules/,
				loaders: [
					'babel-loader'
				]
			},

			// Create RTL styles.
			// {
			// 	test: /\.css$/,
			// 	loader: ExtractTextPlugin.extract( 'style-loader' )
			// },

			// SASS to CSS.
			{
				test: /\.scss$/,
				// use: ExtractTextPlugin.extract( {
				// 	use: [ {
				// 		loader: 'css-loader',
				// 		options: {
				// 			sourceMap: true
				// 		}
				// 	}, {
				// 		loader: 'postcss-loader',
				// 		options: {
				// 			options: {},
				// 			sourceMap: true
				// 		}
				// 	}, {
				// 		loader: 'sass-loader',
				// 		options: {
				// 			sourceMap: true,
				// 			outputStyle: (inProduction ? 'compressed' : 'nested')
				// 		}
				// 	} ]
				// } )
				use: [
					MiniCssExtractPlugin.loader,
					{
						loader:  'css-loader',
						options: {
							sourceMap: true
						}
					}, {
						loader:  'postcss-loader',
						options: {
							options:   {},
							sourceMap: true
						}
					}, {
						loader:  'sass-loader',
						options: {
							sourceMap:   true,
							outputStyle: (inProduction ? 'compressed' : 'nested')
						},
					}
				]
			},

			// Image files.
			{
				test: /\.(png|jpe?g|gif|svg)$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							name: 'images/[name].[ext]',
							publicPath: '../'
						}
					}
				]
			}
		]
	},

	// Plugins. Gotta have em'.
	plugins: [

		// Removes the "dist" folder before building.
		new CleanWebpackPlugin( [ 'assets/dist' ] ),

		// new ExtractTextPlugin( 'css/[name].css' ),
		new MiniCssExtractPlugin( {
			filename: `css/[name].css`
		}),

		// Create RTL CSS.
		new WebpackRTLPlugin()
	],
	optimization: {
		minimizer: [
			new UglifyJsPlugin({
				sourceMap: true
			})
		]
	},
	resolve: {
		// Alias @Connections-Directory to the blocks folder so components can be imported like:
		// import PageSelect from '@Connections-Directory/components';
		// However I could not get this to work. I think it has something to do with named exports and how they are imported.
		alias: {
			'@Connections-Directory': path.resolve( __dirname, './includes/blocks/' )
		}
	},
	stats: {
		children: false
	},
};

module.exports = [
	// Object.assign({
	// 	entry: {
	// 		'frontend': ['./assets/src/css/public.scss', './assets/src/js/public.js'],
	// 		'backend': ['./assets/src/css/admin.scss', './assets/src/js/admin.js'],
	// 	},
	// 	output: {
	// 		path: path.join( __dirname, './assets/dist/' ),
	// 		filename: 'js/[name].js',
	// 	},
	// }, config),
	Object.assign( {
		entry: {
			'babel-polyfill': '@babel/polyfill',
			'blocks':         './includes/blocks/blocks.js'
		},

		// Tell webpack where to output.
		output: {
			path:     path.resolve( __dirname, './assets/dist/' ),
			filename: 'js/[name].js',
			// library: ['wp', '[name]'],
			// libraryTarget: 'window',
		},
	}, config )
];

// inProd?
if ( inProduction ) {

	// // POT file.
	// wpPot( {
	// 	package: 'ConvertKit',
	// 	domain: 'convertkit',
	// 	destFile: 'languages/convertkit.pot',
	// 	relativeTo: './',
	// });

	// Uglify JS.
	// config.plugins.push( new webpack.optimize.UglifyJsPlugin( { sourceMap: true } ) );

	// Minify CSS.
	config.plugins.push( new webpack.LoaderOptionsPlugin( { minimize: true } ) );
}
