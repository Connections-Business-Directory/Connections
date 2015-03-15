module.exports = function(grunt) {

	// Load multiple grunt tasks using globbing patterns
	require('load-grunt-tasks')(grunt);

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		makepot: {
			target: {
				options: {
					domainPath: '/languages/', // Where to save the POT file.
					exclude: ['build/.*'],
					mainFile: 'connections.php', // Main project file.
					potFilename: 'connections.pot', // Name of the POT file.
					potHeaders: {
						poedit: true, // Includes common Poedit headers.
						'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
					},
					type: 'wp-plugin', // Type of project (wp-plugin or wp-theme).
					updateTimestamp: true, // Whether the POT-Creation-Date should be updated without other changes.
					updatePoFiles: true, // Whether to update PO files in the same directory as the POT file.
					processPot: function(pot, options) {
						pot.headers['report-msgid-bugs-to'] = 'http://connections-pro.com/support/forum/translations/';
						pot.headers['last-translator'] = 'WP-Translations (http://wp-translations.org/)\n';
						pot.headers['language-team'] = 'WP-Translations <wpt@wp-translations.org>\n';
						pot.headers['language'] = 'en_US';
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
									console.log('Excluded meta: ' + pot.translations[''][translation].comments.extracted);
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
				text_domain: 'connections',
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

		dirs: {
			lang: 'languages' // It should be languages or lang
		},

		potomo: {
			dist: {
				options: {
					poDel: true // Set to true if you want to erase the .po
				},
				files: [{
					expand: true,
					cwd: '<%= dirs.lang %>',
					src: ['*.po'],
					dest: '<%= dirs.lang %>',
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
			files: {
				expand: true,           // Seems to be required will get "(Error code: EISDIR)" error without it.
				flatten: true,          // remove all unnecessary nesting (what dos this mean ???)
				src: 'assets/js/*.js',  // source files mask
				dest: 'assets/js/',
				ext: '.min.js'          // replace .js to .min.js
			}
		},

		autoprefixer: {
			options: {
				// Same as WordPress core.
				browsers: ['Android >= 2.1', 'Chrome >= 21', 'Explorer >= 7', 'Firefox >= 17', 'Opera >= 12.1', 'Safari >= 6.0'],
				cascade: false
			},

			assets: {
				expand: true,
				flatten: true,
				src: ['assets/css/*.css', '!assets/css/*.min.css'],
				dest: 'assets/css/prefixed/',
				extDot: 'first'
			}
		}
	});

	// Default task. - grunt makepot
	grunt.registerTask('default', 'makepot');

	//  Checktextdomain and makepot task(s)
	grunt.registerTask('go-pot', ['checktextdomain', 'makepot', 'potomo']);

	// Makepot and push it on Transifex task(s).
	grunt.registerTask('tx-push', ['makepot', 'exec:txpush_s']);

	// Pull from Transifex and create .mo task(s).
	grunt.registerTask('tx-pull', ['exec:txpull', 'potomo']);

	// Minify JavaScript
	grunt.registerTask('minify-js', 'uglify');

	// Minify JavaScript
	grunt.registerTask('prefix-css', 'autoprefixer');

	// Build task(s).
	grunt.registerTask('build', ['clean', 'copy', 'compress']);
};
