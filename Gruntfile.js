module.exports = function( grunt ) {
	// Project configuration.
	grunt.initConfig( {
		// Package
		pkg: grunt.file.readJSON( 'package.json' ),
		
		// MakePOT
		makepot: {
			target: {
				options: {
					cwd: '',
					domainPath: 'languages',
					type: 'wp-plugin'
				}
			}
		}
	} );

	grunt.loadNpmTasks( 'grunt-wp-i18n' );

	// Tasks.
	grunt.registerTask( 'pot', [ 'makepot' ] );
};
