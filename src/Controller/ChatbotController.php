<?php
namespace App\Controller;

use App\Service\PdfParserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatbotController extends AbstractController
{
    private $pdfParserService;
    private $responsesFilePath;
    private $pdfUploadDir;

    public function __construct(
        PdfParserService $pdfParserService,
        ParameterBagInterface $params
    ) {
        $this->pdfParserService = $pdfParserService;
        $this->responsesFilePath = $params->get('kernel.project_dir') . '/var/responses.json';
        $this->pdfUploadDir = $params->get('pdf_upload_directory');
        
        if (!file_exists($this->responsesFilePath)) {
            file_put_contents($this->responsesFilePath, json_encode(new \stdClass()));
        }
    }

    #[Route('/upload-pdf', name: 'upload_pdf', methods: ['POST'])]
    public function uploadPdf(Request $request): JsonResponse
    {
        $file = $request->files->get('file');

        if (!$file || !$file->isValid()) {
            return $this->jsonResponse('error', 'Invalid file upload', Response::HTTP_BAD_REQUEST);
        }

        try {
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move($this->pdfUploadDir, $fileName);
            
            $filePath = $this->pdfUploadDir . '/' . $fileName;
            $responses = $this->pdfParserService->parsePdf($filePath);

            // Force associative array conversion
            $responses = $this->ensureAssociativeArray($responses);
            
            file_put_contents($this->responsesFilePath, json_encode($responses, JSON_FORCE_OBJECT));

            return $this->jsonResponse('success', 'PDF processed!', Response::HTTP_OK, [
                'count' => count($responses),
                'data' => $responses
            ]);

        } catch (FileException $e) {
            return $this->jsonResponse('error', 'File error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return $this->jsonResponse('error', 'Processing error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/get-responses', name: 'get_responses', methods: ['GET'])]
    public function getResponses(): JsonResponse
    {
        try {
            if (!file_exists($this->responsesFilePath)) {
                throw new \RuntimeException('Responses file not found');
            }

            $fileContent = file_get_contents($this->responsesFilePath);
            $responses = json_decode($fileContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON format: ' . json_last_error_msg());
            }

            return $this->jsonResponse('success', 'Responses loaded', Response::HTTP_OK, [
                'data' => $this->ensureAssociativeArray($responses)
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse('error', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function jsonResponse(string $status, string $message, int $code, array $extra = []): JsonResponse
    {
        return new JsonResponse(array_merge([
            'status' => $status,
            'message' => $message,
            'data' => new \stdClass()
        ], $extra), $code);
    }

    private function ensureAssociativeArray($data): array
    {
        if (is_object($data)) {
            return (array)$data;
        }
        
        if (!is_array($data) || array_keys($data) === range(0, count($data) - 1)) {
            return ['_default' => 'No structured data found'];
        }
        
        return $data;
    }
}