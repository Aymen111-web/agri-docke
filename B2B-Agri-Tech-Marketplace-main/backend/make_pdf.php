<?php
$dir = __DIR__ . '/storage/app/public/documents';
if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
}

// Valid minimal PDF 1.4 binary structure
function createValidPdf($title, $filename) {
    $stream = "BT\n/F1 16 Tf\n50 700 Td\n({$title}) Tj\n50 670 Td\n(AgriGate by Qelem Meda Technologies - Verified Official Record) Tj\nET";
    $streamLength = strlen($stream);

    $objects = [];
    $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
    $objects[2] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
    $objects[3] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>";
    $objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
    $objects[5] = "<< /Length {$streamLength} >>\nstream\n{$stream}\nendstream";

    $output = "%PDF-1.4\n";
    $offsets = [];

    foreach ($objects as $num => $obj) {
        $offsets[$num] = strlen($output);
        $output .= "{$num} 0 obj\n{$obj}\nendobj\n";
    }

    $xrefOffset = strlen($output);
    $output .= "xref\n0 " . (count($objects) + 1) . "\n";
    $output .= "0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $output .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }

    $output .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
    $output .= "startxref\n{$xrefOffset}\n%%EOF";

    file_put_contents($filename, $output);
    echo "Generated " . basename($filename) . " (" . strlen($output) . " bytes)\n";
}

createValidPdf("ALMAZ AYANA - NATIONAL ID CARD", $dir . '/almaz_national_id.pdf');
createValidPdf("ALMAZ AYANA - FARM LAND OWNERSHIP CERTIFICATE", $dir . '/almaz_farm_certificate.pdf');
