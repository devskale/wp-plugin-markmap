<?php
/*
Plugin Name: Skalemap
Description: A very simple shortcode plugin that displays a message with the current date and includes Markmap integration.
Version: 1.0
Author: Your Name
*/

// Shortcode to display current date with a message
function skaledate_shortcode() {
    return "<p>I am a helpful helloworld plugin, " . date("Y-m-d") . "</p>";
}
add_shortcode('skale_date', 'skaledate_shortcode');

// Shortcode to display a simple mindmap
function skalemap_shortcode() {
    $output = "<p>I am a helpful map plugin, " . date("Y-m-d") . "</p>";
    $output .= "
        <div class='simple-mindmap'>
            <div class='mindmap-center'>center</div>
            <ul class='mindmap-nodes'>
                <li class='mindmap-node'>node1</li>
                <li class='mindmap-node'>node2</li>
                <li class='mindmap-node'>node3</li>
            </ul>
        </div>";
    return $output;
}
add_shortcode('skale_map', 'skalemap_shortcode');

// Shortcode to display static Markmap
function skale_markmap_static_shortcode() {
    $output = '<div class="markmap"><script type="text/template">
        ---
        markmap:
          maxWidth: 400
          colorFreezeLevel: 2
        ---

        # markmap

        ## Links

        - <https://markmap.js.org/>
        - [GitHub](https://github.com/markmap/markmap)

        ## Related

        - [coc-markmap](https://github.com/markmap/coc-markmap)
        - [gatsby-remark-markmap](https://github.com/markmap/gatsby-remark-markmap)

        ## Features

        - links
        - **inline** ~~text~~ *styles*
        - multiline text
        - `inline code`
        - Katex - $x = {-b \pm \sqrt{b^2-4ac} \over 2a}$
        - This is a very very very very very very very very very very very very very very very long line.
    </script></div>';
    return $output;
}
add_shortcode('skale_markmap_static', 'skale_markmap_static_shortcode');

// Enqueue scripts
function skale_markmap_enqueue_scripts() {
    global $post;
    if (is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'skale_markmap_static') || has_shortcode($post->post_content, 'skale_markmap_fromurl'))) {
        wp_enqueue_script('markmap-autoloader', 'https://cdn.jsdelivr.net/npm/markmap-autoloader@0.16', array(), null, true);
    }
}
add_action('wp_enqueue_scripts', 'skale_markmap_enqueue_scripts');

// Enqueue styles
function skale_markmap_enqueue_styles() {
    $custom_css = ".markmap { width: 100%; height: 60vh; }";
    wp_add_inline_style('wp-block-library', $custom_css);
}
add_action('wp_enqueue_scripts', 'skale_markmap_enqueue_styles');



function simple_display_url_shortcode($atts) {
    ob_start(); // Start output buffering
    var_dump($atts); // Dump the attributes to see what is received
    $output = ob_get_clean(); // Get the output and clean buffer
    return "<pre>dat is: $output</pre>";
}
add_shortcode('geturl', 'simple_display_url_shortcode');


function skale_display_map_shortcode($atts) {
    // Define default attributes for the shortcode with the new default URL
    $atts = shortcode_atts(array(
        'url' => 'https://raw.githubusercontent.com/devskale/markmaps/main/default.md' // Default URL
    ), $atts);

    // Properly escape the URL to ensure it's safe to insert into HTML/JavaScript
    $encoded_url = esc_js($atts['url']);

    // Generate the content with an updated script that includes a DOMContentLoaded listener
    $content = <<<HTML
    <div class="markmap" id="markmap"></div>
    <script src="https://cdn.jsdelivr.net/npm/markmap-autoloader@0.16"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function loadMarkmapText(fileUrl) {
                fetch(fileUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.statusText);
                        }
                        return response.text();
                    })
                    .then(text => {
                        const element = document.createElement('script');
                        element.type = 'text/template';
                        element.textContent = text;
                        document.getElementById('markmap').appendChild(element);
                        window.markmap.Markmap.autoload();
                    })
                    .catch(error => {
                        console.error('Error loading markmap text from URL:', fileUrl, 'Error:', error.message);
                    });
            }
            loadMarkmapText("$encoded_url");
        });
    </script>
HTML;
    return $content;
}
add_shortcode('skale_display_map', 'skale_display_map_shortcode');
