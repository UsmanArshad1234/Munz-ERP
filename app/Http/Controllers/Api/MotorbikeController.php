<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Motorbike\CreateMotorbikeRequest;
use App\Http\Requests\Motorbike\UpdateMotorbikeRequest;
use App\Models\Motorbike;
use App\Models\MotorbikeDocument;
use App\Services\MotorbikeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MotorbikeController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly MotorbikeService $motorbikeService) {}

    public function index(Request $request): JsonResponse
    {
        $bikes = $this->motorbikeService->getAll(
            $request->only('search', 'status', 'emirate', 'zone', 'brand', 'bike_type'),
            $request->input('per_page', 20)
        );
        return $this->success($bikes);
    }

    public function stats(): JsonResponse
    {
        return $this->success($this->motorbikeService->getStats());
    }

    public function expiryAlerts(Request $request): JsonResponse
    {
        return $this->success($this->motorbikeService->getExpiryAlerts($request->input('days', 30)));
    }

    public function store(CreateMotorbikeRequest $request): JsonResponse
    {
        $bike = $this->motorbikeService->create($request->validated(), $request->user()->id);
        return $this->created($this->motorbikeService->formatBike($bike), 'Motorbike created successfully');
    }

    public function show(Motorbike $motorbike): JsonResponse
    {
        $motorbike->load(['currentRider:id,employee_id,name', 'documents']);
        return $this->success($this->motorbikeService->formatBike($motorbike));
    }

    public function update(UpdateMotorbikeRequest $request, Motorbike $motorbike): JsonResponse
    {
        $updated = $this->motorbikeService->update($motorbike, $request->validated());
        return $this->success($this->motorbikeService->formatBike($updated), 'Motorbike updated successfully');
    }

    public function destroy(Motorbike $motorbike): JsonResponse
    {
        try {
            $this->motorbikeService->delete($motorbike);
            return $this->success(null, 'Motorbike deleted successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    // ── Documents ─────────────────────────────────────────────────────────────

    public function uploadDocument(Request $request, Motorbike $motorbike): JsonResponse
    {
        $request->validate([
            'file'          => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
            'document_type' => 'required|in:' . implode(',', MotorbikeDocument::TYPES),
            'expiry_date'   => 'nullable|date',
        ]);

        $doc = $this->motorbikeService->uploadDocument(
            $motorbike,
            $request->file('file'),
            $request->document_type,
            $request->expiry_date,
            $request->user()->id
        );

        return $this->created([
            'id'            => $doc->id,
            'document_type' => $doc->document_type,
            'original_name' => $doc->original_name,
            'file_url'      => asset('storage/' . $doc->file_path),
            'file_size'     => $doc->file_size_human,
            'expiry_date'   => $doc->expiry_date?->toDateString(),
        ], 'Document uploaded');
    }

    public function documents(Motorbike $motorbike): JsonResponse
    {
        $docs = $motorbike->documents()->orderBy('document_type')->get()
            ->map(fn($d) => [
                'id'            => $d->id,
                'document_type' => $d->document_type,
                'original_name' => $d->original_name,
                'file_url'      => asset('storage/' . $d->file_path),
                'file_size'     => $d->file_size_human,
                'expiry_date'   => $d->expiry_date?->toDateString(),
                'uploaded_at'   => $d->created_at,
            ]);

        return $this->success($docs);
    }

    public function deleteDocument(Motorbike $motorbike, MotorbikeDocument $document): JsonResponse
    {
        if ($document->motorbike_id !== $motorbike->id) {
            return $this->error('Document does not belong to this motorbike', 400);
        }
        $this->motorbikeService->deleteDocument($document);
        return $this->success(null, 'Document deleted');
    }
}
