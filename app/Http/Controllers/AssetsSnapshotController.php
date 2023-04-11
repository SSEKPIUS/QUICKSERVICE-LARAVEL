<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storeassets_snapshotRequest;
use App\Http\Requests\Updateassets_snapshotRequest;
use App\Models\assets_snapshot;

class AssetsSnapshotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Storeassets_snapshotRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Storeassets_snapshotRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\assets_snapshot  $assets_snapshot
     * @return \Illuminate\Http\Response
     */
    public function show(assets_snapshot $assets_snapshot)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\assets_snapshot  $assets_snapshot
     * @return \Illuminate\Http\Response
     */
    public function edit(assets_snapshot $assets_snapshot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Updateassets_snapshotRequest  $request
     * @param  \App\Models\assets_snapshot  $assets_snapshot
     * @return \Illuminate\Http\Response
     */
    public function update(Updateassets_snapshotRequest $request, assets_snapshot $assets_snapshot)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\assets_snapshot  $assets_snapshot
     * @return \Illuminate\Http\Response
     */
    public function destroy(assets_snapshot $assets_snapshot)
    {
        //
    }
}
