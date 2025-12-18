<?php
// stubs/dompdf.php - lightweight stub so IDEs (Intelephense) recognize Dompdf when vendor isn't installed
namespace Dompdf;

if (!class_exists('Dompdf\\Dompdf')) {
    class Dompdf {
        public function __construct() {}
        public function loadHtml($html) {}
        public function setPaper($size, $orientation = 'portrait') {}
        public function render() {}
        public function output() { return '';} 
    }
}
