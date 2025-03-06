<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ProhibitedWord;

class ContentModerator {
    private $em;
    private $leetMap = [
        'a' => ['@', '4', '^'],
        'b' => ['8', '|3'],
        'c' => ['(', '<'],
        'e' => ['3', '€'],
        'g' => ['6', '9'],
        'i' => ['1', '!', '|'],
        'o' => ['0'],
        's' => ['5', '$'],
        't' => ['7', '+']
    ];

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function checkContent(string $content): array {
        $violations = [];
        
        // 1. Get prohibited words from DB
        $wordList = $this->em->getRepository(ProhibitedWord::class)->findAll();
        
        // 2. Normalize content
        $normalized = $this->normalizeText($content);
        
        // 3. Check for word matches
        foreach($wordList as $word) {
            $pattern = $this->createPattern($word->getWord());
            if(preg_match($pattern, $normalized)) {
                $violations[] = [
                    'word' => $word->getWord(),
                    'category' => $word->getCategory(),
                    'severity' => $word->getSeverity(),
                    'message' => "Prohibited word detected: {$word->getWord()} (Category: {$word->getCategory()})"
                ];
            }
        }
        
        // 4. Check patterns
        $patternViolations = $this->checkPatterns($content);
        
        return array_merge($violations, $patternViolations);
    }

    private function normalizeText(string $text): string {
        $text = mb_strtolower($text);
        foreach($this->leetMap as $normal => $leetVariants) {
            $text = str_replace($leetVariants, $normal, $text);
        }
        return preg_replace('/[^a-z0-9]/', '', $text);
    }

    private function createPattern(string $word): string {
        $pattern = '/';
        foreach(str_split($word) as $char) {
            if(isset($this->leetMap[$char])) {
                $pattern .= '['.$char.implode('', $this->leetMap[$char]).']';
            } else {
                $pattern .= $char;
            }
        }
        return $pattern.'/i';
    }

    private function checkPatterns(string $text): array {
        $violations = [];
        $patterns = [
            'email' => '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i',
            'phone' => '/\b(\+\d{1,3}[- ]?)?\d{3}[-.]?\d{3}[-.]?\d{4}\b/',
            'url' => '/(https?:\/\/|www\.)[^\s]+/i'
        ];
        
        foreach($patterns as $type => $regex) {
            if(preg_match_all($regex, $text, $matches)) {
                foreach($matches[0] as $match) {
                    $violations[] = [
                        'type' => $type,
                        'value' => $match,
                        'category' => 'personal_info',
                        'severity' => 3,
                        'message' => "Prohibited {$type} detected: {$match}"
                    ];
                }
            }
        }
        return $violations;
    }
}