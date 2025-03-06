<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ProhibitedWord;

class WordListUpdater
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function fetchLatestList(): int
{
    // Temporary test data
    $testWords = [
        ['term' => 'badword1', 'category' => 'profanity'],
        ['term' => 'badword2', 'category' => 'hate_speech'],
        ['term' => 'phone', 'category' => 'personal_info']
    ];

    $count = 0;
    foreach($testWords as $wordData) {
        if(!$this->em->getRepository(ProhibitedWord::class)->findOneBy(['word' => $wordData['term']])) {
            $word = new ProhibitedWord();
            $word->setWord($wordData['term']);
            $word->setCategory($wordData['category']);
            $this->em->persist($word);
            $count++;
        }
    }
    
    $this->em->flush();
    return $count;
}
}