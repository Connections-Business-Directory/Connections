// process.traceDeprecation = true; // Enable to see deprecation trace.
const webpack = require( 'webpack' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const path = require( 'path' );
// const ExtractTextPlugin = require( 'extract-text-webpack-plugin' );
const MiniCssExtractPlugin = require( "mini-css-extract-plugin" );
const inProduction = ( 'production' === process.env.NODE_ENV );
// const BrowserSyncPlugin = require( 'browser-sync-webpack-plugin' );
// const ImageminPlugin = require( 'imagemin-webpack-plugin' ).default;
const { CleanWebpackPlugin } = require( 'clean-webpack-plugin' );
// const UglifyJsPlugin = require( "uglifyjs-webpack-plugin" );
const TerserPlugin = require('terser-webpack-plugin');
const WebpackRTLPlugin = require( 'webpack-rtl-plugin' );
// const wpPot = require( 'wp-pot' );

const config = {
	mode:      process.env.NODE_ENV === 'production' ? 'production' : 'development',
	// Ensure modules like magnific know jQuery is external (loaded via WP).
	externals: {
		react:       'React',
		'react-dom': 'ReactDOM',
		tinymce:     'tinymce',
		moment:      'moment',
		jquery:      'jQuery',
		$:           'jQuery',
		lodash:      'lodash',
		'lodash-es': 'lodash',
		//https://www.cssigniter.com/importing-gutenberg-core-wordpress-libraries-es-modules-blocks/
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
	devtool:   'source-map',
	module:    {
		rules: [

			{
				test: /\.(css)$/,
				use:  [ 'style-loader', 'css-loader' ]
			},

			// {
			// 	test: /\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
			// 	use: [
			// 		{
			// 			loader: 'file-loader',
			// 			options: {
			// 				name: '[name].[ext]',
			// 				outputPath: 'fonts/'
			// 			}
			// 		}
			// 	]
			// },

			{
				test: /\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
				use:  [
					{
						loader:  'url-loader',
						options: {
							name:       '[name].[ext]',
							limit:      100000,
							outputPath: 'fonts/'
						}
					}
				]
			},

			// Use Babel to compile JS.
			{
				test:    /\.js$/,
				exclude: /node_modules/,
				use:     {
					loader:  'babel-loader',
					options: {
						// plugins: ['lodash'],
						presets: [ '@wordpress/default' ]
					}
				},
				// loaders: [
				// 	'babel-loader'
				// ]
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
				use:  [
					MiniCssExtractPlugin.loader,
					{
						loader:  'css-loader',
						options: {
							sourceMap: true
						}
					}, {
						loader: 'postcss-loader',
						// options: {
						// 	options:   {},
						// 	sourceMap: true
						// }
					}, {
						loader:  'sass-loader',
						options: {
							sourceMap:   true,
							// outputStyle: ( inProduction ? 'compressed' : 'nested' )
						},
					}
				]
			},

			// Image files.
			{
				test: /\.(png|jpe?g|gif|svg)$/,
				use:  [
					{
						loader:  'file-loader',
						options: {
							name:       'images/[name].[ext]',
							publicPath: '../'
						}
					}
				]
			}
		]
	},

	// Plugins. Gotta have em'.
	plugins:      [

		// Removes the "dist" folder before building.
		new CleanWebpackPlugin( {
			verbose: true
		} ),

		// new ExtractTextPlugin( 'css/[name].css' ),
		new MiniCssExtractPlugin( {
			filename: `css/[name].css`
		} ),

		// Copy vendor files to ensure 3rd party plugins relying on a script handle to exist continue to be enqueued.
		new CopyWebpackPlugin(
			{
				patterns: [
					{
						context:     './node_modules/chosen-js/',
						from:        '*',
						to:          path.resolve( __dirname, './assets/vendor/chosen/' ),
						globOptions: {
							ignore: [
								'**/chosen.proto*.js'
							]
						}
					},
					{
						context: './node_modules/@fortawesome/fontawesome-free/css/',
						from:    'all*.css',
						to:      path.resolve( __dirname, './assets/vendor/fontawesome/css/' ),
					},
					{
						context: './node_modules/@fortawesome/fontawesome-free/webfonts/',
						from:    '*',
						to:      path.resolve( __dirname, './assets/vendor/fontawesome/webfonts/' ),
					},
					{
						context: './node_modules/@fonticonpicker/fonticonpicker/dist/',
						from:    '**',
						to:      path.resolve( __dirname, './assets/vendor/fonticonpicker/' ),
					},
					{
						context:     './node_modules/picturefill/dist/',
						from:        '**',
						to:          path.resolve( __dirname, './assets/vendor/picturefill/' ),
						globOptions: {
							ignore: [
								'**/plugins/**/*'
							]
						}
					},
					{
						context:     './node_modules/js-cookie/src/',
						from:        '**',
						to:          path.resolve( __dirname, './assets/vendor/js-cookie/' ),
						globOptions: {
							ignore: [
								'**/plugins/**/*'
							]
						}
					},
					// {
					// 	context: './node_modules/leaflet/dist/',
					// 	from:    'leaflet.*',
					// 	to:      path.resolve( __dirname, './assets/vendor/leaflet/' ),
					// },
				]
			}
		),

		// Create RTL CSS.
		new WebpackRTLPlugin()
	],
	optimization: {
		minimizer: [
			// new UglifyJsPlugin( {
			// 	sourceMap: true
			// } )
			new TerserPlugin( {
				// sourceMap: true,
				test: /\.js(\?.*)?$/i,
			} )
		]
	},
	resolve:      {
		// Alias @Connections-Directory to the blocks folder so components can be imported like:
		// import { PageSelect } from '@Connections-Directory/components';
		alias: {
			'@Connections-Directory': path.resolve( __dirname, './includes/blocks/' )
		}
	},
	stats:        {
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
			'blocks-editor':  './includes/blocks/blocks.js',
			'blocks-public':  './includes/blocks/public.js',
			'icon-picker':    './assets/src',
			'bundle':        './assets/js/index.js',
			'admin':          './assets/css/cn-admin.scss',
			'frontend':       './assets/css/cn-user.scss',
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
