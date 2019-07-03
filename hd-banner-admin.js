/**
 * HD Banner admin js.
 * Injects a customisable banner.
 */
jQuery(document).ready(function ($) {

    // Build styles.
    var style = '<style>';
    style += '.hd_banner_container {';
    style += 'padding: 0px; width: 100%; position: relative; z-index: 10000; border: none; display: block;';
    style += 'min-height: 30px';
    style += '}';
    style += '.hd_banner_wrap {';
    style += 'padding: 0px; width: 100%; position: relative; z-index: 1000; border: none; display: block;';
    if (hd_banner_vars.background_colour) {
        style += 'background-color: ' + hd_banner_vars.background_colour + ';';
    }
    style += 'text-align: center; font-size: 1em;';
    style += '}';
    style += '.hd_banner {';
    style += 'padding: 5px 20px; ';
    if (hd_banner_vars.text_colour) {
        style += 'color: ' + hd_banner_vars.text_colour + ';';
    }
    style += '}';
    style += '.hd_banner a {';
    if (hd_banner_vars.link_colour) {
        style += 'color: ' + hd_banner_vars.link_colour + ';';
    }
    style += '}';
    style += '</style>';

    // Build html.
    var banner = '<div class="hd_banner_container"><div class="hd_banner_wrap"><div class="hd_banner">' + hd_banner_vars.banner_message + '</div></div></div>';

    // Output.
    $('body').prepend(style + banner);

});
