<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait PaginatesResources
{
    protected function perPage(Request $request, int $default = 20, int $max = 100): int
    {
        $limit = (int) $request->input('limit', $default);

        return max(1, min($limit, $max));
    }

    /**
     * Apply ?q= search across the given columns and ?sort=/-sort ordering.
     *
     * @param  array<int, string>  $searchable
     * @param  array<int, string>  $sortable
     */
    protected function applyListQuery(
        Builder $query,
        Request $request,
        array $searchable = [],
        array $sortable = [],
        string $defaultSort = '-created_at',
    ): Builder {
        $term = trim((string) $request->input('q', ''));

        if ($term !== '' && $searchable !== []) {
            $query->where(function (Builder $nested) use ($searchable, $term): void {
                foreach ($searchable as $column) {
                    $nested->orWhere($column, 'like', '%'.$term.'%');
                }
            });
        }

        [$column, $direction] = $this->resolveSort($request, $sortable, $defaultSort);

        return $query->orderBy($column, $direction);
    }

    /**
     * @param  array<int, string>  $sortable
     * @return array{0: string, 1: string}
     */
    private function resolveSort(Request $request, array $sortable, string $defaultSort): array
    {
        $raw = (string) $request->input('sort', $defaultSort);
        $direction = str_starts_with($raw, '-') ? 'desc' : 'asc';
        $column = ltrim($raw, '-');

        if ($sortable !== [] && ! in_array($column, $sortable, true)) {
            $raw = $defaultSort;
            $direction = str_starts_with($raw, '-') ? 'desc' : 'asc';
            $column = ltrim($raw, '-');
        }

        return [$column, $direction];
    }

    /**
     * Standard list envelope required by B6.
     *
     * @return array{data: mixed, meta: array<string, int>}
     */
    protected function paginated($paginator): array
    {
        return [
            'data' => $paginator->items(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'limit' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
            ],
        ];
    }
}
