<?php


namespace Modules\Base\Traits;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait FilterCriteria
{
    protected $query;
    protected $term;
    protected $requestSet;
    protected $dateQueries;

    public function scopeFilter( $query = null, $pagify = true) {

        if(!$query)
        {
            $query = $this->model;
        }

        $this->query      = $query;
        $this->requestSet = Request::all();
        $limit            = $this->getPerPage();
        if ( isset( $this->requestSet['limit'] ) ) {
            $limit = $this->requestSet['limit'];
        }

        $this->applySort();

        // search filter
        if ( isset( $this->requestSet['q'] ) && ! empty( $this->requestSet['q'] ) ) {
            $this->term = $this->filterString( $this->requestSet['q'] );
            $this->search( $this->requestSet['q'] );
        }

        $this->filterCols();
        if($pagify)
        {
            return $this->query->paginate( $limit );
        }

        return $this->query;
    }


    /**
     * Get per page number
     * @return int
     */
    public function getPerPage()
    {
        if(property_exists($this, 'perPage'))
        {
            return $this->perPage;
        }
        return 20;
    }


    protected function applySort()
    {
        // data sorting
        if ( isset( $this->requestSet['sort'] ) && ! empty( $this->requestSet['sort'] ) ) {
            $sort    = explode( "|", $this->requestSet['sort'] );
            $sortCol = $sort[0];

            $sortDir = isset( $sort[1] ) ? $sort[1] : 'DESC';

            if ( strpos( $sortCol, '.' ) !== false ) {
                $sortDetails = explode( '.', $sortCol );

                $targetTable = Str::plural($sortDetails[0]);
                if(Schema::hasColumn( $targetTable, $sortDetails[1]) && Schema::hasColumn( $this->getCurrentObj()->getTable(), $sortDetails[0] . '_id')) {
                    $this->query->select($this->getCurrentObj()->getTable() . '.*', $targetTable . '.' . $sortDetails[1] . ' as sC');
                    $this->query->leftJoin($targetTable, $targetTable . '.id', '=', $this->getCurrentObj()->getTable() . '.' . $sortDetails[0] . '_id');
                    $this->query->orderBy( 'sC' , $sortDir );
                }

            } else {
                $sortCol = ( Schema::hasColumn( $this->getCurrentObj()->getTable(), $sortCol ) ) ? $sortCol : 'id';
                $sortCol = $this->getCurrentObj()->getTable().".". $sortCol;
                $this->query->orderBy( $sortCol, $sortDir );
            }
        }
    }


    /**
     * For Creating filters based on extra params from form generator
     * @param bool $filterSet
     */
    private function filterCols( $filterSet = false ) {
        $finalFilters = [];
        $filterSet    = $filterSet ? $filterSet : $this->getProperty('searchAble');

        if($filterSet)
        {
            foreach ( $filterSet as $name => $type ) {
                if ( is_array( $type ) ) {
                    $subFinalFilter = [];
                    foreach($type as $subName => $subType) {
                        $filSingle = $this->createFilters($subName, $subType, $name);
                        if($filSingle) $subFinalFilter[] = $filSingle;
                    }
                    if($subFinalFilter) {
                        $this->query->whereHas($name, function($q) use ($subFinalFilter)
                        {
                            $q->where($subFinalFilter);
                        });
                    }

                    continue;
                }

                $filSingle = $this->createFilters( $name, $type );
                if($filSingle) $finalFilters[] = $filSingle;
            }
        }



        if($finalFilters) $this->query->where($finalFilters);
    }

    private function createFilters( $name, $type, $relName = false) {
        $fieldName = $relName ? $relName . '_' . $name : $name;
        if ( ! isset( $this->requestSet[ $fieldName ] ) ) {
            return false;
        }

        $filterVal = $this->requestSet[ $fieldName ];

        switch ($type) {
            case 'free' :
                return [ $name, 'like', "%$filterVal%" ];
                break;

            case 'date' :
                $this->createDateFilter($name, $filterVal);
                return false;
                break;

            default :
                return [ $name, '=', $filterVal ];
                break;
        }
    }

    private function createDateFilter($name, $value) {
        $target = substr($name, strrpos($name, '_') + 1);
        $dateVal = Carbon::parse($value);
        switch ($target) {
            case "month":
                $col = str_replace('_month', '', $name);
                $this->query->whereMonth($col, $dateVal->month);
                $this->query->whereYear($col, $dateVal->year);
                break;

            case "year" :
                $col = str_replace('_year', '', $name);
                $this->query->whereYear($col, $dateVal->year);
                break;
        }
    }


    /**
     * Filtering query string
     *
     * @param $term
     *
     * @return string|string[]
     */
    public function filterString( $term ) {
        // removing symbols used by MySQL
        $reservedSymbols = [ '-', '+', '<', '>', '@', '(', ')', '~' ];
        return str_replace( $reservedSymbols, '', $term );
    }

    public function getProperty($property)
    {
        return  property_exists( $this->getCurrentObj(), $property )? $this->{$property}: false;
    }

    public function getCurrentObj()
    {
        return !empty($this->model)? $this->model: $this;
    }


    /**
     * Scope a query that matches a full text search of term.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $term
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function search( $term ) {

        if ( property_exists( $this->getCurrentObj(), 'searchAble' ) ) {
            $this->query->Where( function ( $q ) use ( $term ) {
                $term = "%{$term}%";
//                $searchableCols = isset($this->searchAble) ? $this->searchAble : $this->fillable;

                foreach ( $this->searchAble as $column => $type ) {
                    if(is_array($type) || !($type == 'free' || $type == 'fixed')) continue;
                    $q->orWhere( $this->getCurrentObj()->getTable() . "." . $column, 'like', $term );
                }
            } );

        }

        if ( property_exists( $this->getCurrentObj(), 'joinFilter' ) && ! empty( $this->getCurrentObj()->joinFilter ) ) {

            $this->joinFilter( $term );
        }
    }

    /**
     * Return filter joins with query
     *
     * @param $query
     * @param $term
     *
     * @return mixed
     */

    public function joinFilter( $term ) {
        //joins filter
        foreach ( $this->getCurrentObj()->joinFilter as $join => $cols ) {
            $this->query->orWhereHas( $join, function ( $q ) use ( $term, $cols ) {
                if ( is_array( $cols ) ) {
                    foreach ( $cols as $key => $col ) {
                        ( $key == 0 ) ? $q->where( $col, 'like', "%$term%" ) : $q->orWhere( $col, 'like', "%$term%" );
                    }
                } else {
                    $q->where( $cols, 'like', "%$term%" );
                }
            } );
        }
    }
}
