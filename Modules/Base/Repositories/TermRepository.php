<?php

namespace Modules\Base\Repositories;

use Illuminate\Support\Facades\Route;
use Modules\Base\Proxies\Entities\Term;
use Modules\Base\Helpers\General;

class TermRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return Term::class;
    }


    public static function __callStatic($method, $parameters)
    {
        if(strpos($method,'store') !== false)
        {
            $children = isset($parameters[0]) && is_array($parameters[0])? $parameters[0]:[];
            $parent = substr($method, strlen('fetch'));
            $parent = lcfirst($parent);
            $parentSlug = General::camelToSnake($parent);



            $parentInput = [
                'title' => str_replace("-", " ", $parent),
                'slug'  => $parentSlug,
                'module_name' => $parameters[2] ?? 'Base',
            ];

            if(empty($children))
            {
                $parentInput['module_name'] = $parameters[0] ?? $parentInput['module_name'];
                $parentInput['extra'] = $parameters[1] ?? null;
                $parameters[0] = $parentInput;
                $parentInput = false;
            }
            else
            {

            }

            return (new static)->fetchCreateTree($parameters[0], $parentInput);
        }

        return parent::__callStatic($method, $parameters);
    }

    public function fetchAddOrCreate($query,$slug)
    {
        $input = [
            'slug' => $slug,
            'title' =>  ucfirst(str_replace("-"," ",$slug)),
            'module_name' => 'Base'
        ];

        $alreadyExist = self::fetchBySlug($query,$input['slug']);
        if(!$alreadyExist)
        {
            return $query->createT($input);
        }

        return $alreadyExist;
    }


    public function fetchGetParentFromRequest($query)
    {
        $prefix = Route::getCurrentRoute()->action['prefix'];
        $uri = Route::getCurrentRoute()->uri();
        $uri = str_replace(["api/v1/", "public/"],["", ""], $uri);
        $uri = explode("/",str_replace($prefix."/", '', $uri));
        $parent = str_replace("-","_",$uri[0]);
        $parent = self::fetchAddOrCreate($query,$parent);
        return $parent;
    }


    /**
     * Get by slug
     * @param $slug
     * @param bool $children
     * @return mixed
     */
    public function fetchBySlug($query,$slug, $children = false)
    {
        $with = ($children)? 'descendants':[];
//dd($query->whereSlug($slug)->toSql(), $slug);
        return $query->whereSlug($slug)->with($with)->first();
    }

    public function fetchGetChildren($query, $parentSlug)
    {
        $node = self::fetchBySlug($query, $parentSlug);
        return $node->children()->filter();
    }

    public function fetchAllChild($query, $parentSlug)
    {
        $node = self::fetchBySlug($query,$parentSlug);
        return $node->descendants()->with('parent')->filter();
    }


    /**
     * @param \Modules\Base\Entities\Term $term
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchFindChildren(\Modules\Base\Entities\Term $term)
    {
        return $term->children()->get();
    }

    public function fetchAdd($query, $input)
    {
        return $query->createT($input);
    }

    public function fetchAddMulti($query, $input, $parent = false)
    {
        foreach ($input as $termInput)
        {
            if($parent)
            {
                $termInput['parent_id'] = $parent->id;
            }

            if(isset($termInput['children']))
            {
                $children = $termInput['children'];
                unset($termInput['children']);
            }

            $term = $this->fetchAdd($query, $termInput);


            if(!empty($children))
            {
                foreach ($children as $child)
                {
                    $child['parent_id'] = $term->id;
                    $this->fetchAdd($query, $child);
                }
            }
        }
    }


    /**
     * Create Tree
     * @param $input
     * @param bool $parent
     */
    public function fetchCreateTree($input, $parent = false)
    {
        if(!General::isMultiArray($input))
        {
            $input = [$input];
        }

        if($parent)
        {
            if($parent instanceof Term)
            {
                foreach($input as $node)
                {
                    $child = $this->create($node);
                    $parent->appendNode($child);
                }

                return;
            }
            else
            {
                $input = [
                    'title' => $parent,
                    'slug' => 'another-test',
                    'module_name' => 'Base',
                    'children' => $input
                ];

                return $this->create($input);
            }
        }
        return $this->insert($input);
    }

    /**
     * @param $query
     * @param $parentSlug
     * @return mixed
     */
    public function fetchChildren($query, $parentId)
    {
        $parent = $this->findOrFail($parentId);
        return $parent->descendants()->filter();
    }

    /**
     * handle bulk insert , a method to be used for seeders
     * array input pattern
     * slug = [
     *    title = Term Title
     *    children = [
     *         slug   => child slug,
     *         title  => child title
     *    ]
     * ]
     *
     *
     * this will handle creation of parent with all of children along with parent relation
     * @param $query
     * @param $terms array
     */
    public function fetchBulkInsert($query, $terms)
    {
        if(sizeof($terms))
        {
            foreach($terms as $slug => $term)
            {
                if(! $parent = \Modules\Base\Proxies\Repositories\TermRepository::where(['slug' => $slug])->first())
                {
                    $parent = TermRepository::createT(['slug' => $slug, 'title' => $term['title']]);
                }

                if(isset($term['children']))
                {
                    TermRepository::where('parent_id', $parent->id)->delete();

                    foreach ($term['children'] as $child)
                    {
                        $child['parent_id'] = $parent->id;
                        TermRepository::createT($child);
                    }
                }
            }
        }
    }

}
