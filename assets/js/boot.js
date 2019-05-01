window.Popper = require('popper.js').default;
try {
    window.$ = window.jQuery = require('jquery');
    require('bootstrap');
    $(document).ready(function() {
        $('[data-toggle="popover"]').popover();
    })
    // require('summernote/dist/summernote-bs4');
    // require('bootstrap-datepicker');
    // require('slick-carousel');
} catch (e) {}
try {
    // for Node.js
    //var ab = window.ab = require('autobahn');
} catch (e) {
    // for browsers (where AutobahnJS is available globally)
}
