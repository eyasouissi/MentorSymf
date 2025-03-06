<?php
namespace App\Service;

use Smalot\PdfParser\Parser;
use Smalot\PdfParser\Exception\PdfParserException;

class PdfParserService
{
    private $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    public function parsePdf($filePath): array
{
    try {
        $pdf = $this->parser->parseFile($filePath);
        $text = $pdf->getText() ?: '';
        
        $responses = [];
        $currentKey = null;

        foreach (preg_split('/\R/', $text) as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^([^:]+):\s*(.+)/', $line, $matches)) {
                $currentKey = strtolower(trim($matches[1])); // Fixed syntax
                $responses[$currentKey] = trim($matches[2]);
            } elseif ($currentKey && isset($responses[$currentKey])) {
                $responses[$currentKey] .= ' ' . $line;
            }
        }

        return $responses ?: ['info' => 'No structured data found'];
        
    } catch (PdfParserException $e) {
        return ['error' => 'PDF processing failed: ' . $e->getMessage()];
    }
}
}