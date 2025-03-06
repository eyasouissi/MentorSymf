<?php

namespace App\Controller;

use App\Service\TranslationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TranslationController extends AbstractController
{
    private TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    #[Route('/translate-post', name: 'app_translate_post', methods: ['POST'])]
    public function translatePost(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            // Validate request parameters
            if (!isset($data['text'], $data['sourceLang'], $data['targetLang'])) {
                throw new BadRequestHttpException('Missing required parameters');
            }

            $translatedText = $this->translationService->translateContent(
                $data['text'],
                $data['sourceLang'],
                $data['targetLang']
            );

            return $this->json([
                'success' => true,
                'translatedText' => $translatedText
            ]);

        } catch (BadRequestHttpException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Translation failed: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}