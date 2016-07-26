/*global module:false*/
module.exports = function(grunt) {

    'use strict';

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        meta : {
            banner : '/*!\n' +
            ' * GMaps.js\n' +
            ' * <%= pkg.homepage %>\n' +
            ' *\n' +
            ' * Copyright <%= grunt.template.today("yyyy") %>, <%= pkg.author %>\n' +
            ' * Released under the <%= pkg.license %> License.\n' +
            ' */\n\n'
        },

        concat: {
            options: {
                banner: '<%= meta.banner %>'
            },
            dist: {
                src: [
                    'js/src/console.js',
                    'js/src/underscore.js',
                    'js/lib/gmaps.core.js',
                    'js/lib/gmaps.controls.js',
                    'js/lib/gmaps.markers.js',
                    'js/lib/gmaps.overlays.js',
                    'js/lib/gmaps.geometry.js',
                    'js/lib/gmaps.layers.js',
                    'js/lib/gmaps.routes.js',
                    'js/lib/gmaps.geofences.js',
                    'js/lib/gmaps.static.js',
                    'js/lib/gmaps.map_types.js',
                    'js/lib/gmaps.styles.js',
                    'js/lib/gmaps.streetview.js',
                    'js/lib/gmaps.events.js',
                    'js/lib/gmaps.utils.js',
                    'js/lib/gmaps.native_extensions.js',
                    'js/src/markerCluster.js',
                    'js/src/infobox.js',
                    'js/src/underscore.js',
                    'js/src/ee_gmaps.js',
                    'js/src/plugins.js',
                ],
                dest: 'js/gmaps.js'
            }
        },

        uglify: {
            gmaps: {
                files: {
                    'js/gmaps.min.js': ['js/gmaps.js']
                },
                options: {
                    sourceMap: true,
                    sourceMapName: 'js/gmaps.min.map'
                }
            },
            options: {
                banner: "<%= meta.banner %>"
            }
        },

        watch : {
            files : '<%= concat.dist.src %>',
            tasks : 'compile'
        }
    });

    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('compile', ['concat', 'uglify']);
    grunt.registerTask('dev', ['watch']);
};