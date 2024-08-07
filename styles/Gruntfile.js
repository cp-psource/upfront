/*global module, require */
module.exports = function(grunt) {
	require('load-grunt-tasks')(grunt);
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks( 'grunt-wp-i18n' );

	grunt.initConfig({
		sass: {
			options: {
				sourceMap: true,
				outputStyle: 'nested',
				sourceComments: false
			},
			dist: {
				files: {
					'styles/editor-interface.css': 'styles/editor-interface.scss'
				}
			}
		},
		watch: {
			sass: {
				files: ['**/*.scss'],
				tasks: ['sass'],
				options: {
					spawn: false
				}
			}
		},
		makepot: {
			target: {
				options: {
					domainPath: 'languages/',
					type: 'wp-theme'
				}
			}
		}
	});

	grunt.registerTask('default', ['sass']);
};