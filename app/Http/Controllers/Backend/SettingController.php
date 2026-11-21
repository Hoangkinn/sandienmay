<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Models\Setting;
use App\Services\ActivityLogService;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = Setting::select('setting_name', 'setting_value')->get();
        $homepageSchema = Setting::where('setting_name', 'homepage_schema')->value('setting_value');

        return view('backend.setting.index', [
            'settings' => $settings,
            'homepage_schema' => $homepageSchema,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\SettingRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function save(SettingRequest $request)
    {
        // 1. Lấy dữ liệu cũ trước khi cập nhật
        $oldSettings = Setting::whereIn('setting_name', [
            'site_name', 'site_tite', 'site_keywords', 'site_description', 'homepage_schema'
        ])->pluck('setting_value', 'setting_name')->toArray();

        // 2. Tiến hành cập nhật
        Setting::where('setting_name', 'site_name')
            ->update(['setting_value' => $request->site_name]);

        Setting::where('setting_name', 'site_tite')
            ->update(['setting_value' => $request->site_tite]);

        Setting::where('setting_name', 'site_keywords')
            ->update(['setting_value' => $request->site_keywords]);

        Setting::where('setting_name', 'site_description')
            ->update(['setting_value' => $request->site_description]);

        // Dùng updateOrCreate để tự động tạo mới nếu chưa tồn tại trong DB
        Setting::updateOrCreate(
            ['setting_name' => 'homepage_schema'],
            ['setting_value' => $request->homepage_schema]
        );

        // 3. Lấy dữ liệu mới sau khi cập nhật
        $newSettings = Setting::whereIn('setting_name', [
            'site_name', 'site_tite', 'site_keywords', 'site_description', 'homepage_schema'
        ])->pluck('setting_value', 'setting_name')->toArray();

        // 4. Ghi log kèm theo dữ liệu Cũ và Mới để trang chi tiết hiển thị so sánh
        ActivityLogService::log(
            'update', 
            'Setting', 
            null, 
            "Đã cập nhật cấu hình thông tin chung website",
            $oldSettings, // Dữ liệu cũ
            $newSettings  // Dữ liệu mới
        );

        session()->flash('backend.toast.success', 'Cập nhật thông tin website thành công!');
        return redirect()->route('backend.setting.index');
    }
}