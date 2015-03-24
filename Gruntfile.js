module.exports = function(grunt) {

	// Load multiple grunt tasks using globbing patterns
	require('load-grunt-tasks')(grunt);

	var pck    = require('./package' );
	var config = pck.config;

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		makepot: {
			target: {
				options: {
					domainPath: config.makepot.dest, // Where to save the POT file.
					exclude: ['build/.*'],
					mainFile: config.makepot.src, // Main project file.
					potFilename: config.makepot.domain + '.pot', // Name of the POT file.
					potHeaders: {
						poedit: true, // Includes common Poedit headers.
						'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
					},
					type: config.makepot.type, // Type of project (wp-plugin or wp-theme).
					updateTimestamp: true, // Whether the POT-Creation-Date should be updated without other changes.
					updatePoFiles: true, // Whether to update PO files in the same directory as the POT file.
					processPot: function(pot, options) {
						pot.headers['report-msgid-bugs-to'] = config.makepot.header.bugs;
						pot.headers['last-translator'] = config.makepot.header.last_translator;
						pot.headers['language-team'] = config.makepot.header.team;
						pot.headers['language'] = config.makepot.header.language;
						var translation, // Exclude meta data from pot.
							excluded_meta = [
								'Plugin Name of the plugin/theme',
								'Plugin URI of the plugin/theme',
								'Author of the plugin/theme',
								'Author URI of the plugin/theme'
							];
						for (translation in pot.translations['']) {
							if ('undefined' !== typeof pot.translations[''][translation].comments.extracted) {
								if (excluded_meta.indexOf(pot.translations[''][translation].comments.extracted) >= 0) {
									grunt.log.writeln('Excluded meta: ' + pot.translations[''][translation].comments.extracted);
									delete pot.translations[''][translation];
								}
							}
						}
						return pot;
					}
				}
			}
		},

		checktextdomain: {
			options: {
				text_domain: config.makepot.domain,
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,3d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d',
					' __ngettext:1,2,3d',
					'__ngettext_noop:1,2,3d',
					'_c:1,2d',
					'_nc:1,2,4c,5d'
				]
			},
			files: {
				src: [
					'**/*.php', // Include all files
					'!node_modules/**', // Exclude node_modules/
					'!build/.*' // Exclude build/
				],
				expand: true
			}
		},

		exec: {
			npmUpdate: {
				command: 'npm update'
			},
			txpull: { // Pull Transifex translation - grunt exec:txpull
				cmd: 'tx pull -a --minimum-perc=1' // Change the percentage with --minimum-perc=yourvalue
			},
			txpush_s: { // Push pot to Transifex - grunt exec:txpush_s
				cmd: 'tx push -s'
			}
		},

		potomo: {
			dist: {
				options: {
					poDel: true // Set to true if you want to erase the .po
				},
				files: [{
					expand: true,
					flatten : true,
					cwd: config.makepot.dest,
					src: ['*.po'],
					dest: config.makepot.dest,
					ext: '.mo',
					nonull: true
				}]
			}
		},

		// Clean up build directory
		clean: {
			main: ['build/<%= pkg.name %>']
		},

		// Copy the theme into the build directory
		copy: {
			main: {
				src: [
					'**',
					'!node_modules/**',
					'!build/**',
					'!.git/**',
					'!Gruntfile.js',
					'!package.json',
					'!.gitignore',
					'!.gitmodules',
					'!.tx/**',
					'!**/Gruntfile.js',
					'!**/package.json',
					'!**/README.md',
					'!**/*~'
				],
				dest: 'build/<%= pkg.name %>/'
			}
		},

		// Compress build directory into <name>.zip and <name>-<version>.zip
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './build/<%= pkg.name %>.zip'
				},
				expand: true,
				cwd: 'build/<%= pkg.name %>/',
				src: ['**/*'],
				dest: '<%= pkg.name %>/'
			}
		},

		uglify: {

			core: {
				files : [{
					expand : true,
					flatten : true,
					cwd: config.uglify.core.src,
					src: [
						'*.js',
						'!*.min.js'
					],
					dest : config.uglify.core.dest,
					ext : '.min.js'
				}]
			}
		},

		autoprefixer: {
			options: {
				// Same as WordPress core.
				browsers: ['Android >= 2.1', 'Chrome >= 21', 'Explorer >= 7', 'Firefox >= 17', 'Opera >= 12.1', 'Safari >= 6.0'],
				cascade: false
			},

			core: {
				files: [{
					expand : true,
					flatten : true,
					cwd: 'assets/css/',
					src : ['*.css', '!*.min.css', '!jquery-ui-*'],
					dest : 'assets/css/'
				}]
			},
			jqueryui: {
				files: [{
					expand : true,
					flatten : true,
					cwd: 'assets/css/',
					src : ['jquery-ui-*.css', '!*.min.css'],
					dest : 'assets/css/'
				}]
			}
		},

		cssmin: {
			core: {
				files: [{
					expand: true,
					flatten: true,
					src: ['assets/css/*.css', '!assets/css/*.min.css'],
					dest: 'assets/css/minified/',
					ext: '.min.css'
				}]
			}
		},

		csslint: {
			strict: {
				options: {
					csslintrc: '.csslintrc-strict'
				},
				files: [{
					expand: true,
					flatten: true,
					cwd: 'assets/css/',
					src: [ '*.css', '!*.min.css', '!jquery-ui-*' ]
				}]
			},
			lax: {
				options: {
					csslintrc: '.csslintrc'
				},
				files: [{
					expand: true,
					flatten: true,
					cwd: 'assets/css/',
					src: ['*.css', '!*.min.css', '!jquery-ui-*']
				}]
			}
		},

		jshint: {
			options: grunt.file.readJSON('.jshintrc'),
			grunt: {
				options: {
					node: true
				},
				src: ['Gruntfile.js']
			},
			core: {
				expand: true,
				cwd: config.uglify.core.src,
				src: [ '*.js', '!*.min.js' ]
			}
		}/*,

		log: {
			lint_css: {
				options: {
					keepColors: false,
					clearLogFile: false,
					filePath: './logs/lint-css.log'
				}
			}
		}*/
	});

	//require('logfile-grunt')(grunt);

	// Default task.
	grunt.registerTask('default', function() {
		console.log( 'No default task created.' );
	});

	// Checktextdomain and makepot task(s)
	grunt.registerTask('make-pot', ['checktextdomain', 'makepot']);

	// Makepot and push it on Transifex task(s).
	grunt.registerTask('tx-push', ['makepot', 'exec:txpush_s']);

	// Pull from Transifex and create .mo task(s).
	grunt.registerTask('tx-pull', ['exec:txpull', 'potomo']);

	// Minify CSS
	grunt.registerTask('minify-css', 'cssmin');

	// Minify JavaScript
	grunt.registerTask('minify-js', 'uglify');

	// Autoprefix CSS
	grunt.registerTask( 'prefix-css', [ 'autoprefixer:core', 'autoprefixer:jqueryui' ] );
	grunt.registerTask( 'prefix-css:core', ['autoprefixer:core'] );
	grunt.registerTask( 'prefix-css:jqueryui', ['autoprefixer:jqueryui'] );

	// CSS Lint
	grunt.registerTask( 'lint-css', [ 'log-lint:css', 'csslint:lax' ] );
	grunt.registerTask( 'lint-css:strict', [ 'log-lint:css', 'csslint:strict' ] );

	// JS Lint
	grunt.registerTask( 'lint-js', ['jshint:core'] );
	grunt.registerTask( 'lint-js:grunt', ['jshint:grunt'] );

	// Add task specific logging.
	// @link https://github.com/brutaldev/logfile-grunt
	grunt.task.registerTask( 'log-lint:css', 'Log the CSSLint report.', function() {
		require('logfile-grunt')(grunt, { filePath: './logs/lint-css.log', clearLogFile: true });
	});

	// Build task(s).
	grunt.registerTask('build', ['clean', 'copy', 'compress']);
};
