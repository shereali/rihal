<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\AbstractPaginator;

class ApiResource
{
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
        $data = $transformer ? $transformer($resource) : $resource;

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
            $transformedItems = $transformer ? $items->map($transformer)->values() : $items->values();

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
        $transformed = $transformer ? $collection->map($transformer)->values() : $collection->values();

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
