<?php

namespace Modules\Base\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Modules\Base\Proxies\Repositories\TermRepository;
use Modules\Base\Transformers\TermResource;

class TermController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        $parent = TermRepository::getParentFromRequest();
        if($request->get('all'))
        {
            $children = TermRepository::allChild($parent->slug);
        }
        else
        {
            $children = TermRepository::getChildren($parent->slug);
        }

        return TermResource::collection($children);

        dd(TermRepository::bySlug('another-test')->toArray());

        dd(TermRepository::storeProductCategory('FormGenerator') );
        dd(TermRepository::createTree(['title' => 'Testing testing ', 'slug' => 'testing-testing', 'module_name' => 'Base'], $parent));
        dd(TermRepository::createTree([
            ['title' => 'Testing testing ', 'slug' => 'testing-testing', 'module_name' => 'Base'],
            ['title' => 'Testing testing ', 'slug' => 'testing-testing2', 'module_name' => 'Base'],
        ], $parent));
        return TermResource::collection(TermRepository::with('parent')->filter());
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $parent = false;
        if(empty($request->get('parent_id')))
        {
            $parent = TermRepository::getParentFromRequest();
        }

        $data = [
            'title' => $request->get('title'),
            'slug' => strtolower($request->get('slug')),
            'module_name' => !empty($request->get('module_name'))? $request->get('module_name'): 'Base',
            'parent_id' => !empty($parent)? $parent->id:$request->get('parent_id'),
            'extra' => $request->get('extra'),
        ];

        return new TermResource(TermRepository::createt($data));
    }



    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $parent = TermRepository::getParentFromRequest();
        return new TermResource(TermRepository::where('parent_id', $parent->id)->findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $parent = TermRepository::getParentFromRequest();
        $term = TermRepository::where('parent_id',$parent->id)->findOrFail($id);
        $term->updateT(
            [
                'title' => $request->get('title'),
                'slug'  => $request->get('slug'),
                'module_name' => !empty($request->get('module_name'))? $request->get('module_name'): 'Base',
                'parent_id' => $request->get('parent_id'),
                'extra' => $request->get('extra')
            ]
        );
        return new TermResource($term);
    }


    /**
     * Return repository with its children
     * @param $id
     * @return TermResource
     */
    public function children($parentId)
    {
        $terms = TermRepository::children($parentId);
        return TermResource::collection($terms);
    }


    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $term = TermRepository::findOrFail($id);
        $term->delete();
        return response()->json(['success' => 'Term deleted successfully']);
    }

    /**
     * Get Tree of parent node descendants
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getParentCategoryTree()
    {
        $parent = TermRepository::getParentFromRequest();
        return TermResource::collection(TermRepository::withDepth()->whereDescendantOf($parent)->get()->toTree());
    }
}
