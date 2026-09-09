<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class NoticeController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Notice::where('tenant_id', $user->tenant_id)
            ->when($request->has('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where('title_bn', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%")
                    ->orWhere('content_bn', 'like', "%{$search}%");
            })
            ->when($request->has('is_pinned'), fn($q) => $q->where('is_pinned', filter_var($request->input('is_pinned'), FILTER_VALIDATE_BOOLEAN)))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->when($request->has('category'), fn($q) => $q->where('category', $request->input('category')))
            ->with('creator:id,name_bn,name_en')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc');

        $notices = $query->paginate($perPage);

        return $this->successResponse($notices);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $notice = Notice::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->with('creator:id,name_bn,name_en')
            ->first();

        if (!$notice) {
            return $this->errorResponse('বিজ্ঞপ্তি পাওয়া যায়নি', 404);
        }

        return $this->successResponse($notice);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title_bn' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'content_bn' => 'required|string',
            'content_en' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'is_pinned' => 'nullable|boolean',
            'is_scheduled' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'scheduled_at' => 'nullable|date',
            'expired_at' => 'nullable|date',
            'target_audience' => 'nullable|array',
            'attachments' => 'nullable|array',
            'channels' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['created_by_user_id'] = $request->user()->id;
        $data['is_active'] = $data['is_active'] ?? true;

        $notice = Notice::create($data);

        $notice->load('creator:id,name_bn,name_en');

        return $this->successResponse($notice, 'বিজ্ঞপ্তি তৈরি সফল', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $notice = Notice::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$notice) {
            return $this->errorResponse('বিজ্ঞপ্তি পাওয়া যায়নি', 404);
        }

        $validator = Validator::make($request->all(), [
            'title_bn' => 'nullable|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'content_bn' => 'nullable|string',
            'content_en' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'is_pinned' => 'nullable|boolean',
            'is_scheduled' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'scheduled_at' => 'nullable|date',
            'expired_at' => 'nullable|date',
            'target_audience' => 'nullable|array',
            'attachments' => 'nullable|array',
            'channels' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('বৈধতা ত্রুটি', 422, $validator->errors());
        }

        $notice->update($validator->validated());

        $notice->load('creator:id,name_bn,name_en');

        return $this->successResponse($notice->fresh(), 'বিজ্ঞপ্তি আপডেট সফল');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $notice = Notice::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->first();

        if (!$notice) {
            return $this->errorResponse('বিজ্ঞপ্তি পাওয়া যায়নি', 404);
        }

        $notice->delete();

        return $this->successResponse(null, 'বিজ্ঞপ্তি মুছে ফেলা সফল');
    }
}
