<?php

namespace App\Http\Controllers\Api\ClientManagement;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientDocumentController extends Controller
{
    public function index(Client $client): JsonResponse
    {
        $documents = $client->documents()
            ->with('uploadedBy:id,name,email')
            ->latest()
            ->get();

        return response()->json([
            'data' => $documents,
        ]);
    }

    public function store(
        Request $request,
        Client $client
    ): JsonResponse {
        $validated = $request->validate([
            'document_type' => [
                'nullable',
                'string',
                'max:100',
            ],
            'document_name' => [
                'required',
                'string',
                'max:255',
            ],
            'remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'max:10240',
            ],
        ]);

        $file = $request->file('file');

        if (!$file) {
            return response()->json([
                'message' => 'No file was uploaded.',
            ], 422);
        }

        $path = $file->store(
            "client-documents/{$client->id}",
            'public'
        );

        $document = ClientDocument::create([
            'client_id' => $client->id,
            'document_type' => $validated['document_type'] ?? null,
            'document_name' => $validated['document_name'],
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'remarks' => $validated['remarks'] ?? null,
            'uploaded_by' => $request->user()?->id,
        ]);

        $document->load('uploadedBy:id,name,email');

        return response()->json([
            'message' => 'Client document uploaded successfully.',
            'data' => $document,
        ], 201);
    }

        public function download(
            Client $client,
            ClientDocument $document
        ) {
            $this->ensureDocumentBelongsToClient(
                $client,
                $document
            );

            if (
                !Storage::disk('public')->exists($document->file_path)
            ) {
                return response()->json([
                    'message' => 'File not found.',
                ], 404);
            }

            return response()->download(
                Storage::disk('public')->path($document->file_path),
                $document->file_name,
                [
                    'Content-Type' => $document->mime_type
                        ?? 'application/octet-stream',
                ]
            );
        }

    public function preview(
        Client $client,
        ClientDocument $document
    ) {
        $this->ensureDocumentBelongsToClient(
            $client,
            $document
        );

        if (
            !Storage::disk('public')
                ->exists($document->file_path)
        ) {
            return response()->json([
                'message' => 'File not found.',
            ], 404);
        }

        return response()->file(
            Storage::disk('public')->path(
                $document->file_path
            ),
            [
                'Content-Type' => $document->mime_type
                    ?? 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' .
                    $document->file_name .
                    '"',
            ]
        );
    }

    public function destroy(
        Client $client,
        ClientDocument $document
    ): JsonResponse {
        $this->ensureDocumentBelongsToClient(
            $client,
            $document
        );

        if (
            Storage::disk('public')
                ->exists($document->file_path)
        ) {
            Storage::disk('public')
                ->delete($document->file_path);
        }

        $document->delete();

        return response()->json([
            'message' => 'Client document deleted successfully.',
        ]);
    }

    private function ensureDocumentBelongsToClient(
        Client $client,
        ClientDocument $document
    ): void {
        abort_if(
            (int) $document->client_id !== (int) $client->id,
            404,
            'Document not found for this client.'
        );
    }
}
