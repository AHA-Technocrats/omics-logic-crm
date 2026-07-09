<?php

namespace AHATechnocrats\Admin\DataGrids\OmicsLogic;

use AHATechnocrats\Admin\DataGrids\Contact\PersonDataGrid;
use AHATechnocrats\OmicsLogic\Models\Segment;
use AHATechnocrats\OmicsLogic\Services\SegmentFilterCounter;
use Illuminate\Database\Query\Builder;

class SegmentPersonDataGrid extends PersonDataGrid
{
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = parent::prepareQueryBuilder();

        $segment = $this->resolveSegment();

        app(SegmentFilterCounter::class)->applyFiltersToQuery(
            $queryBuilder,
            $segment->filter_query ?? [],
            'persons',
        );

        return $queryBuilder;
    }

    protected function resolveSegment(): Segment
    {
        return Segment::query()->findOrFail((int) request()->input('segment_id'));
    }
}
