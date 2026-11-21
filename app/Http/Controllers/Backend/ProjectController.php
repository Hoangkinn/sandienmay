<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Enums\Status;
use App\Models\ProjectVideo;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Project::orderByDesc('order')->orderByDesc('created_at');

        $projects = $query->paginate(10);
        $statusList = Status::cases();

        return view('backend.project.index', [
            'projects' => $projects,
            'statusList' => $statusList,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $statusList = Status::cases();

        return view('backend.project.create', [
            'statusList' => $statusList
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProjectRequest $request)
    {
        $project = Project::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'status' => $request->status,
            'order' => $request->order,
            'seo_no_index' => $request->seo_no_index,
            'seo_title' => $request->seo_title,
            'seo_keywords' => $request->seo_keywords,
            'seo_description' => $request->seo_description,
            'seo_url_canonical' => $request->seo_url_canonical,
            'created_id' => Auth::user()->id,
        ]);

        if ($project) {
            $images = json_decode($request->images);
            foreach ($images as $image) {
                ProjectImage::create([
                    'project_id' => $project->id,
                    'name' => $image->name,
                    'url' => $image->url,
                    'thumb_url' => $image->thumb_url,
                    'has_primary' => isset($image->primary) ? $image->primary : false,
                ]);
            }
        }

        session()->flash('backend.toast.success', 'Lưu dự án thành công!');

        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.project.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.project.edit', ['id' => $project->id]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $statusList = Status::cases();
        $project = Project::find($id);
        $images = json_encode($project->images);

        if ($project == null) {
            return redirect()->route('backend.project.index');
        }

        return view('backend.project.edit', [
            'project' => $project,
            'images' => $images,
            'statusList' => $statusList
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $project = Project::find($id);
        $project->name = $request->name;
        $project->slug = $request->slug;
        $project->description = $request->description;
        $project->status = $request->status;
        $project->order = $request->order;
        $project->seo_no_index = $request->seo_no_index;
        $project->seo_title = $request->seo_title;
        $project->seo_keywords = $request->seo_keywords;
        $project->seo_description = $request->seo_description;
        $project->seo_url_canonical = $request->seo_url_canonical;
        $project->updated_id = Auth::user()->id;
        $project->save();

        ProjectImage::where('project_id', $project->id)->delete();
        $images = json_decode($request->images);
        foreach ($images as $image) {
            ProjectImage::create([
                'project_id' => $project->id,
                'name' => $image->name,
                'url' => $image->url,
                'thumb_url' => $image->thumb_url,
                'has_primary' => isset($image->primary) ? $image->primary : false,
            ]);
        }

        session()->flash('backend.toast.success', 'Lưu dự án thành công!');

        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.project.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.project.edit', ['id' => $id]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ProjectImage::where('project_id', $id)->delete();
        ProjectVideo::where('project_id', $id)->delete();
        Project::where('id', $id)->delete();

        session()->flash('backend.toast.success', 'Xóa dự án thành công!');
        return redirect()->route('backend.project.index');
    }
}
