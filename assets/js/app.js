/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.Twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.scss';

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';

import mermaid from 'mermaid';
mermaid.initialize({startOnLoad:true});

require('@fortawesome/fontawesome-free/js/all.min');

require('bootstrap');
// Toggle the side navigation
$("#sidebarToggle").on("click", function(e) {
    e.preventDefault();
    $("body").toggleClass("sb-sidenav-toggled");
});
$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});