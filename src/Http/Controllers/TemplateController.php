<?php

namespace Idoneo\HumanoMailer\Http\Controllers;

use App\Http\Controllers\Controller;
use Idoneo\HumanoMailer\DataTables\TemplateDataTable;
use Idoneo\HumanoMailer\Models\Template;
use Dotlogics\Grapesjs\App\Traits\EditorTrait;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    use EditorTrait;

    public function index(TemplateDataTable $dataTable)
    {
        return $dataTable->render('humano-mailer::template.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('humano-mailer::template.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->except(['id', '_token']);

        $request->validate([
            'name' => 'required|string|min:3|max:25',
        ]);

        // Set status_id based on checkbox presence
        $status_id = $request->has('status_id') ? 1 : 0; // 1 = active, 0 = inactive

        Template::updateOrCreate(
            ['id' => $request->id],
            [
                'name' => $data['name'],
                'status_id' => $status_id,
                'gjs_data' => $data['gjs_data'] ?? null,
            ],
        );

        return redirect()->route('humano-mailer.template.index')->with('success', 'Record saved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $hashedId)
    {
        $page = Template::findByHash($hashedId);

        if (! $page)
        {
            return redirect()->route('humano-mailer.template.index')->with('error', 'Template not found.');
        }

        return view('humano-mailer::template.show', compact('page'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $hashedId)
    {
        $data = Template::findByHash($hashedId);

        if (! $data)
        {
            return redirect()->route('humano-mailer.template.index')->with('error', 'Template not found.');
        }

        return view('humano-mailer::template.form', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $hashedId)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $hashedId)
    {
        $model = Template::findByHash($hashedId);

        if (! $model)
        {
            return response()->json(['error' => 'Template not found.'], 404);
        }

        $model->delete();

        return response()->json(['success' => 'The record has been deleted.'], 200);
    }

    public function editor(Request $request, string $hashedId)
    {
        $page = Template::findByHash($hashedId);

        if (! $page)
        {
            return redirect()->route('humano-mailer.template.index')->with('error', 'Template not found.');
        }

        // Add team ID information to the editor context
        $teamId = auth()->user()->currentTeam->id ?? 'default';
        $request->merge(['team_id' => $teamId]);

        return $this->show_gjs_editor($request, $page);
    }
}
