<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TranslationService
{
    private const SUPPORTED_LANGUAGES = [
        'en' => 'English',
        'fr' => 'French',
        'es' => 'Spanish',
        'de' => 'German',
        'it' => 'Italian',
        'pt' => 'Portuguese'
    ];

    private $httpClient;
    private $cache;

    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    public function detectLanguage(string $text): string
    {
        if (empty(trim($text))) {
            return 'en';
        }

        $cacheKey = 'lang_' . md5($text);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($text) {
            $item->expiresAfter(3600);

            try {
                $response = $this->httpClient->request('GET', 'https://api.mymemory.translated.net/detect', [
                    'query' => [
                        'q' => substr($text, 0, 500),
                        'key' => '6a03354cff6ff0d76f34'
                    ],
                    'timeout' => 3
                ]);

                $data = $response->toArray();
                $detectedLang = strtolower($data['matches'][0]['language'] ?? 'en');
                return $this->validateLanguageCode($detectedLang);

            } catch (\Exception $e) {
                error_log("Detection failed: " . $e->getMessage());
                return 'en';
            }
        });
    }

    public function translateContent(string $text, string $sourceLang, string $targetLang): string
    {
        $sourceLang = $this->validateLanguageCode($sourceLang);
        $targetLang = $this->validateLanguageCode($targetLang);

        if (empty(trim($text)) || $sourceLang === $targetLang) {
            return $text;
        }

        $cacheKey = 'trans_' . md5($text . $sourceLang . $targetLang);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($text, $sourceLang, $targetLang) {
            $item->expiresAfter(3600);

            try {
                $response = $this->httpClient->request('GET', 'https://api.mymemory.translated.net/get', [
                    'query' => [
                        'q' => $text,
                        'langpair' => $sourceLang . '|' . $targetLang,
                        'key' => '6a03354cff6ff0d76f34'
                    ],
                    'timeout' => 3
                ]);

                $data = $response->toArray();
                return $data['responseData']['translatedText'] ?? $text;

            } catch (\Exception $e) {
                error_log("Translation failed: " . $e->getMessage());
                return $text;
            }
        });
    }

    private function validateLanguageCode(string $lang): string
    {
        $lang = substr(strtolower($lang), 0, 2);
        return array_key_exists($lang, self::SUPPORTED_LANGUAGES) ? $lang : 'en';
    }
}