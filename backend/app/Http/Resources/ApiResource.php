<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Model;

class ResourceProxy extends JsonResource
{
    public function whenLoaded($relationship, $value = null, $default = null)
    {
        if ($this->resource instanceof Model && $this->resource->relationLoaded($relationship)) {
            return is_callable($value) ? $value() : ($value ?? $this->resource->getRelation($relationship));
        }
        return is_callable($default) ? $default() : $default;
    }
}

class ApiResource
{
    /**
     * Transform a single item, wrapping objects in ResourceProxy to support whenLoaded().
     */
    protected static function transformItem(mixed $item, ?callable $transformer = null): mixed
    {
        if (!$transformer) {
            return $item;
        }

        $wrapped = is_object($item) ? new ResourceProxy($item) : $item;
        return $transformer($wrapped);
    }

    /**
     * Return a standardized success response.
     */
    public static function success(mixed $data = [], int $code = 200): JsonResponse
    {
        if (is_array($data) && (isset($data['message']) || isset($data['data']))) {
            return response()->json(array_merge([
                'success' => true,
                'status'  => $code,
            ], $data), $code);
        }

        return response()->json([
            'success' => true,
            'status'  => $code,
            'data'    => $data,
        ], $code);
    }

    /**
     * Return a standardized single item response.
     */
    public static function item(mixed $resource, ?callable $transformer = null): JsonResponse
    {
        $data = static::transformItem($resource, $transformer);

        return response()->json([
            'success' => true,
            'status'  => 200,
            'data'    => $data,
        ], 200);
    }

    /**
     * Return a standardized paginated or array collection response.
     */
    public static function collection(mixed $resource, ?callable $transformer = null): JsonResponse
    {
        if ($resource instanceof AbstractPaginator) {
            $items = $resource->getCollection();
            $transformedItems = $items->map(fn($item) => static::transformItem($item, $transformer))->values();

            return response()->json([
                'success' => true,
                'status'  => 200,
                'data'    => [
                    'data'         => $transformedItems,
                    'current_page' => $resource->currentPage(),
                    'last_page'    => $resource->lastPage(),
                    'per_page'     => $resource->perPage(),
                    'total'        => $resource->total(),
                    'from'         => $resource->firstItem(),
                    'to'           => $resource->lastItem(),
                ],
                'meta'    => [
                    'current_page' => $resource->currentPage(),
                    'last_page'    => $resource->lastPage(),
                    'per_page'     => $resource->perPage(),
                    'total'        => $resource->total(),
                ]
            ], 200);
        }

        $collection = collect($resource);
        $transformed = $collection->map(fn($item) => static::transformItem($item, $transformer))->values();

        return response()->json([
            'success' => true,
            'status'  => 200,
            'data'    => $transformed,
            'meta'    => [
                'total' => $transformed->count(),
            ]
        ], 200);
    }

    /**
     * Return a standardized error response.
     */
    public static function error(string $message, int $code = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'status'  => $code,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
