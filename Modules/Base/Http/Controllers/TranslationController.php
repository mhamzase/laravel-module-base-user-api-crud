<?php

namespace Modules\Base\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Modules\Base\Entities\TranslationCustom;
use Modules\Base\Transformers\TranslationResource;
use Waavi\Translation\Models\Translation;
use Waavi\Translation\Repositories\TranslationRepository;
use Modules\Base\Entities\Phrase;

class TranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return TranslationResource::collection( TranslationCustom::filter());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return Renderable
     */
    public function store( Request $request, TranslationRepository $transRepo) {
        $translation = $transRepo->create([
            'locale' => $request->get('lang'),
            'namespace'  => '*',
            'group'  => !empty($request->get('module_name'))? $request->get('module_name'): 'base',
            'item'   => $request->get('slug'),
            'text'   => $request->get('phrase'),
        ]);

        return new TranslationResource( $translation );
    }

    /**
     * Show the specified resource.
     *
     * @param int $id
     *
     * @return Renderable
     */
    public function show( $id, TranslationRepository $transRepo ) {

        return new TranslationResource( $transRepo->find($id) );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     *
     * @return Renderable
     */
    public function update( Request $request, $id, TranslationRepository $transRepo ) {
        $languageLine = $transRepo->update($id, $request->get( 'phrase' ));
        return new TranslationResource( $transRepo->find($id) );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Renderable
     */
    public function destroy( $id, TranslationRepository $transRepo) {
        $transRepo->find($id)->delete();

        return response()->json( [ 'success' => 'Phrase deleted successfully' ] );
    }

    /**
     * Return json of language strings those are needed to be loaded in front-end
     * @param $language
     * @param $category
     * @param TranslationRepository $transRepo
     *
     * @return array
     */
    public function getTrans($language, $category, TranslationRepository $transRepo) {
        $translations = $transRepo->loadSource($language, '*', $category);
        if(!$translations) {
            $translations = ['error' => 'nothing found'];
        }
        return $translations;
    }
}
