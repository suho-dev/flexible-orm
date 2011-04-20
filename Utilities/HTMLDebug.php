<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM;

/**
 * Description of HTMLDebug
 *
 */
class HTMLDebug extends Debug {
    protected function _output( $string, $title = false ) {
        return $title ? $this->_outputWithTitle($string, $title) : $this->_outputSimple($string);
    }

    private function _outputWithTitle( $body, $title ) {
        echo '<div class="debug-dump"><h2>',htmlentities($title),'</h2><pre>',
             $body, "</pre></div>\n";
    }

    private function _outputSimple( $string ) {
        echo "<div class=\"debug-message\">$string</div>\n";
    }
}
?>
