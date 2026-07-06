<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Services\AgentActivityService;

class AgentDocumentController extends Controller
{
    public function index(Agent $agent): JsonResponse
    {
        $documents = $agent->documents()
            ->with('uploadedBy')
            ->latest()
            ->get();

        return response()->json([
            'data' => $documents,
        ]);
    }

public function store(Request $request, Agent $agent): JsonResponse
{
    $validated = $request->validate([
        'document_type' => [
            'required',
            'string',
            'max:100',
        ],
        'document_title' => [
            'required',
            'string',
            'max:255',
        ],
        'expires_at' => [
            'nullable',
            'date',
        ],
        'verification_status' => [
            'nullable',
            'in:pending,verified,expired',
        ],
        'remarks' => [
            'nullable',
            'string',
            'max:1000',
        ],
        'file' => [
            'required',
            'file',
            'max:102400',
        ],
    ]);

    $file = $request->file('file');

    if (! $file || ! $file->isValid()) {
        return response()->json([
            'message' => 'The uploaded file is invalid or exceeds the server upload limit.',
        ], 422);
    }

    $path = $file->store(
        'agent-documents/' . $agent->id,
        'public'
    );

    $document = AgentDocument::query()->create([
        'agent_id' => $agent->id,
        'document_type' => $validated['document_type'],
        'document_title' => $validated['document_title'],
        'file_name' => $file->getClientOriginalName(),
        'file_path' => $path,
        'mime_type' => $file->getClientMimeType(),
        'file_size' => $file->getSize(),
        'expires_at' => $validated['expires_at'] ?? null,
        'verification_status' => $validated['verification_status'] ?? 'pending',
        'remarks' => $validated['remarks'] ?? null,
        'uploaded_by' => Auth::id(),
    ]);

    AgentActivityService::log(
        $agent,
        'document_uploaded',
        'Document Uploaded',
        "{$document->document_title} uploaded.",
        null,
        [
            'document_id' => $document->id,
            'file_name' => $document->file_name,
            'document_type' => $document->document_type,
        ]
    );

    return response()->json([
        'message' => 'Agent document uploaded successfully.',
        'data' => $document->load('uploadedBy'),
    ], 201);
}

public function download(
        Agent $agent,
        AgentDocument $document
    ): BinaryFileResponse {
        abort_if(
            $document->agent_id !== $agent->id,
            404,
            'Document not found for this agent.'
        );

        abort_if(
            ! Storage::disk('public')->exists($document->file_path),
            404,
            'File not found.'
        );
        AgentActivityService::log(
            $agent,
            'document_downloaded',
            'Document Downloaded',
            "{$document->document_title} downloaded."
        );

        return response()->download(
            Storage::disk('public')->path($document->file_path),
            $document->file_name,
            [
                'Content-Type' => $document->mime_type,
            ]
        );
    }

        public function destroy(
            Agent $agent,
            AgentDocument $document
        ): JsonResponse {
            abort_if(
                $document->agent_id !== $agent->id,
                404,
                'Document not found for this agent.'
            );

            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            AgentActivityService::log(
                $agent,
                'document_deleted',
                'Document Deleted',
                "{$document->document_title} deleted.",
                [
                    'document_id' => $document->id,
                    'file_name' => $document->file_name,
                ],
                null
            );

            $document->delete();

            return response()->json([
                'message' => 'Agent document deleted successfully.',
            ]);
        }

        public function preview(
        Agent $agent,
        AgentDocument $document
    ) {
        abort_if(
            $document->agent_id !== $agent->id,
            404,
            'Document not found for this agent.'
        );

        abort_if(
            ! Storage::disk('public')->exists($document->file_path),
            404,
            'File not found.'
        );

        AgentActivityService::log(
                $agent,
                'document_previewed',
                'Document Previewed',
                "{$document->document_title} previewed."
            );
        return response()->file(
            Storage::disk('public')->path($document->file_path)
        );
    }


}
