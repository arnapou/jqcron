<?php
header('Content-Type: text/html; charset=utf-8');
$elements = explode('==========', substr(file_get_contents(__FILE__), __COMPILER_HALT_OFFSET__));
echo strtr(file_get_contents('demo.tpl'),
    array(
    '__DEMO__' => basename(__FILE__, '.php'),
    '__JS__' => $elements[1],
    '__JSPRE__' => htmlspecialchars($elements[1]),
    '__HTML__' => $elements[2],
    '__HTMLPRE__' => htmlspecialchars($elements[2]),
    )
);

__halt_compiler();
==========
$(function(){
    $('.example7').jqCron({
        enabled_minute: true,
        multiple_dom: true,
        multiple_month: true,
        multiple_mins: true,
        multiple_dow: true,
        multiple_time_hours: true,
        multiple_time_minutes: true,
        default_period: 'week',
        default_value: '*/14 */2 */3 * *',
        bind_to: $('.example7-input'),
        bind_method: {
            set: function($element, value) {
                $element.val(value);
            }
        },
        no_reset_button: false,
        lang: 'en'
    });
});
==========
<div class="example7"></div>
<input class="example7-input" />