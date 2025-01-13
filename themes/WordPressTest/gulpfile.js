const gulp = require("gulp");
const sass = require("gulp-sass")(require("sass"));
const cleanCSS = require("gulp-clean-css");
const sourcemaps = require("gulp-sourcemaps");
const plumber = require("gulp-plumber");
const rename = require("gulp-rename");

const paths = {
  scss: {
    src: "src/**/*.scss",
    dest: "dist/",
  },
  js: {
    src: "src/**/*.js",
    dest: "dist/",
  },
};

function compileSCSS() {
  return gulp
     .src(paths.scss.src)
     .pipe(plumber())
     .pipe(sourcemaps.init())
     .pipe(sass().on("error", sass.logError))
     .pipe(cleanCSS())
     .pipe(rename({ dirname: "" }))
     .pipe(sourcemaps.write("./"))
     .pipe(gulp.dest(paths.scss.dest));
}

function copyJS() {
  return gulp
     .src(paths.js.src)
     .pipe(gulp.dest(paths.js.dest));
}

function watchFiles() {
  gulp.watch(paths.scss.src, compileSCSS);
  gulp.watch(paths.js.src, copyJS);
}

const build = gulp.series(gulp.parallel(compileSCSS, copyJS));
const watch = gulp.parallel(watchFiles);

exports.compile = compileSCSS;
exports.copyJS = copyJS;
exports.watch = watch;
exports.default = build;