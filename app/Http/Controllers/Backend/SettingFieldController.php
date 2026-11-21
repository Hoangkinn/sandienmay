<?php

namespace App\Http\Controllers\Backend;

use App\Enums\FieldType;
use App\Helpers\UploadImageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingFieldRequest;
use App\Models\SettingField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Services\FeServices\SettingFieldService;

class SettingFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settingFields = SettingField::orderByDesc('created_at')->paginate(10);

        return view('backend.setting-field.index', [
            'settingFields' => $settingFields
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $fieldTypes = FieldType::cases();

        return view('backend.setting-field.create', [
            'fieldTypes' => $fieldTypes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\SettingFieldRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SettingFieldRequest $request)
    {
        $settingField = SettingField::create([
            'field_name' => $request->field_name,
            'field_key' => $request->field_key,
            'field_type' => $request->field_type,
            'user_id' => Auth::user()->id,
        ]);

        Cache::forget('allSettingFields');

        session()->flash('backend.toast.success', 'Lưu thông tin cài đặt thành công!');

        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.setting-field.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.setting-field.edit', ['id' => $settingField->id]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SettingField  $settingField
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $settingField = SettingField::find($id);
        if ($settingField == null) {
            return redirect()->route('backend.setting-field.index');
        }

        $fieldTypes = FieldType::cases();
        return view('backend.setting-field.edit', [
            'settingField' => $settingField,
            'fieldTypes' => $fieldTypes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SettingField  $settingField
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $settingField = SettingField::find($id);
        if ($settingField == null) {
            return redirect()->route('backend.setting-field.index');
        }

        $settingField->field_name = $request->field_name;
        $settingField->field_type = $request->field_type;

        if ($request->field_type == FieldType::IMAGE->value || $request->field_type == FieldType::FILE->value) {
            $oldImage = $settingField->field_value;
            if ($request->field_value) {
                $fileName = UploadImageHelper::store($request->field_value, 'storage', false)['name'];

                $settingField->field_value = $fileName;
            } else {
                $settingField->field_value = $oldImage;
            }
        } else {
            $settingField->field_value = $request->field_value;
        }
        $settingField->save();

        Cache::forget('allSettingFields');

        session()->flash('backend.toast.success', 'Lưu thông tin cài đặt thành công!');
        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.setting-field.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.setting-field.edit', ['id' => $id]);
        }
    }

    public function destroy($id)
    {
        if (!SettingFieldService::canDelete($id)) {
            session()->flash('backend.toast.error', 'Không thể xóa! Mục này đang được gọi ở một file Blade nào đó trong hệ thống.');
            return redirect()->back();
        }

        $setting = SettingField::findOrFail($id);
        $setting->delete();

        Cache::forget("allSettingFields");

        session()->flash('backend.toast.success', 'Xóa cấu hình thành công!');
        return redirect()->back();
    }
}
