/**
 * External dependencies
 */
const path = require( 'path' );
const webpack = require( 'webpack' );
const { CleanWebpackPlugin } = require( 'clean-webpack-plugin' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const MiniCssExtractPlugin = require( "mini-css-extract-plugin" );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const WebpackRTLPlugin = require( 'webpack-rtl-plugin' );
const inProduction = ('production' === process.env.NODE_ENV);
// const ExtractTextPlugin = require( 'extract-text-webpack-plugin' );
// const UglifyJsPlugin = require( "uglifyjs-webpack-plugin" );
// const wpPot = require( 'wp-pot' );

/**
 * WordPress dependencies
 */
// const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

const config = {
	// ...defaultConfig,
	mode:      process.env.NODE_ENV === 'production' ? 'production' : 'development',
	externals: {
		// react:       'React',
		// 'react-dom': 'ReactDOM',
		// tinymce:     'tinymce',
		// moment:      'moment',
		// jquery:      'jQuery',
		// $:           'jQuery',
		// lodash:      'lodash',
		// 'lodash-es': 'lodash',
		//https://www.cssigniter.com/importing-gutenberg-core-wordpress-libraries-es-modules-blocks/
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

		new DependencyExtractionWebpackPlugin( {
			combineAssets: true,
			combinedOutputFile: 'require/dependencies.php',
			injectPolyfill: false,
			// outputFilename: 'require/[name].php', // Seems to require package version >3.2.1
			// Example showing how to have an external library queued as a dependency.
			// @link https://github.com/Automattic/woocommerce-payments/blob/develop/CONTRIBUTING.md
			// requestToExternal( request ) {
			// 	if ( request === 'js-cookie' ) { // The import library name.
			// 		return 'Cookies'; //
			// 	}
			// },
			// requestToHandle: function( request ) {
			// 	if ( request === 'js-cookie' ) { // The import library name.
			// 		return 'js-cookie'; // The library handle registered with wp_register_script()
			// 	}
			// },
		} ),

		// Removes the "dist" folder before building.
		new CleanWebpackPlugin( {
			verbose: true
		} ),

		// new ExtractTextPlugin( 'css/[name].css' ),
		new MiniCssExtractPlugin( {
			filename: `[name].css`
		} ),

		new RemoveEmptyScriptsPlugin(),

		// Copy vendor files to ensure 3rd party plugins relying on a script handle to exist continue to be enqueued.
		new CopyWebpackPlugin(
			{
				patterns: [
					{
						context: './node_modules/chosen-js/',
						from: '*',
						to: path.resolve( __dirname, './assets/vendor/chosen/' ),
						globOptions: {
							ignore: [
								'**/chosen.proto*.js'
							]
						}
					},
					{
						context: './node_modules/@fortawesome/fontawesome-free/css/',
						from: 'all*.css',
						to: path.resolve( __dirname, './assets/vendor/fontawesome/css/' ),
					},
					{
						context: './node_modules/@fortawesome/fontawesome-free/webfonts/',
						from: '*',
						to: path.resolve( __dirname, './assets/vendor/fontawesome/webfonts/' ),
					},
					{
						context: './node_modules/@fonticonpicker/fonticonpicker/dist/',
						from: '**',
						to: path.resolve( __dirname, './assets/vendor/fonticonpicker/' ),
					},
					{
						context: './node_modules/picturefill/dist/',
						from: '**',
						to: path.resolve( __dirname, './assets/vendor/picturefill/' ),
						globOptions: {
							ignore: [
								'**/plugins/**/*'
							]
						}
					},
					{
						context: './node_modules/js-cookie/src/',
						from: '**',
						to: path.resolve( __dirname, './assets/vendor/js-cookie/' ),
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
		],
		removeEmptyChunks: true,
		// splitChunks: {
		// 	cacheGroups: {
		// 		stylesEditor: {
		// 			type: 'css/mini-extract',
		// 			name: 'block-styles-editor',
		// 			chunks: ( chunk )=>{
		// 				return chunk.name === 'block-styles-editor';
		// 			},
		// 			enforce: true,
		// 		},
		// 		stylesShared: {
		// 			type: 'css/mini-extract',
		// 			name: 'block-styles-shared',
		// 			chunks: ( chunk )=>{
		// 				return chunk.name === 'block-styles-shared';
		// 			},
		// 			enforce: true,
		// 		},
		// 	},
		// },
	},
	resolve: {
		// Alias @Connections-Directory to the blocks folder so components can be imported like:
		// import { PageSelect } from '@Connections-Directory/components';
		alias: {
			'@Connections-Directory': path.resolve( __dirname, './includes/blocks/' )
		}
	},
	stats: {
		children: false
	},
};

module.exports = [
	Object.assign( {
			entry: {
				'admin/icon-picker/script': './assets/src/sortable-iconpicker',
				'admin/style': './assets/src/admin.scss',
				'frontend/style': './assets/src/frontend.scss',
				'block/editor/style': './includes/blocks/components/style.scss',
				'block/editor/script': './includes/blocks/index.js',
				'block/carousel/script': './includes/blocks/carousel/public',
				'block/carousel/style': './includes/blocks/carousel/style.scss',
				'block/team/style': './includes/blocks/team/style.scss',
				'content-block/recently-viewed/script': './assets/src/content-blocks/recently-viewed',
			},

			// Tell webpack where to output.
			output: {
				path: path.resolve( __dirname, './assets/dist/' ),
				filename: '[name].js',
				// library: ['wp', '[name]'],
				// libraryTarget: 'window',
			},
		},
		config
	)
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
