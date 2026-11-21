<?php

namespace App\Http\Controllers\Backend;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisteredRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if ($this->checkRole()) {
            return redirect('/backend');
        }

        $users = User::where('role', '!=', 'super_admin')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('backend.user.index', [
            'users' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if ($this->checkRole()) {
            return redirect('/backend');
        }

        $statusList = UserStatus::cases();
        $roleList = Role::cases();
        $permissionGroups = $this->getPermissionGroups();
        $roleModulesMap = $this->getRoleModulesMap();

        return view('backend.user.create', [
            'statusList' => $statusList,
            'roleList' => $roleList,
            'permissionGroups' => $permissionGroups,
            'roleModulesMap' => $roleModulesMap,
            'userPermissions' => [], // Mảng rỗng khi tạo mới
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RegisteredRequest $request)
    {
        // Gom nhóm role chính và mảng các quyền được tích chọn thành mảng dữ liệu
        $roleData = [
            'role' => $request->role,
            'permissions' => $request->input('permissions', [])
        ];

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => json_encode($roleData), // Lưu dưới dạng chuỗi JSON
            'status' => $request->status,
            'password' => Hash::make($request->password),
        ]);

        session()->flash('backend.toast.success', 'Lưu thông tin người dùng thành công!');

        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.user.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.user.edit', ['id' => $user->id]);
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
        if ($this->checkRole()) {
            return redirect('/backend');
        }

        $user = User::find($id);
        if ($user == null) {
            return redirect()->route('backend.user.index');
        }

        $statusList = UserStatus::cases();
        $roleList = Role::cases();
        $permissionGroups = $this->getPermissionGroups();
        $roleModulesMap = $this->getRoleModulesMap();

        // Giải mã cột role (JSON) để lấy ra role chính và danh sách permissions đã lưu
        $roleDecoded = json_decode($user->role, true);
        
        // Trường hợp tài khoản cũ lưu dạng chữ thuần "admin" hoặc JSON hợp lệ
        $currentRole = '';
        $userPermissions = [];

        if (is_array($roleDecoded)) {
            $currentRole = $roleDecoded['role'] ?? '';
            $userPermissions = $roleDecoded['permissions'] ?? [];
        } else {
            // Nếu cột role đang lưu chuỗi thuần như "admin"
            $currentRole = $user->role; 
        }

        return view('backend.user.edit', [
            'user' => $user,
            'statusList' => $statusList,
            'roleList' => $roleList,
            'permissionGroups' => $permissionGroups,
            'roleModulesMap' => $roleModulesMap,
            'currentRole' => $currentRole,         // <--- Truyền biến này ra view để chọn đúng option
            'userPermissions' => $userPermissions, 
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
        $user = User::find($id);
        if ($user == null) {
            return redirect()->route('backend.user.index');
        }

        // Nếu role chính là admin hoặc super_admin thì có thể reset mảng permissions nếu muốn
        $permissions = $request->input('permissions', []);
        if (in_array($request->role, ['admin', 'super_admin'])) {
            $permissions = [];
        }

        $roleData = [
            'role' => $request->role,
            'permissions' => $permissions
        ];

        $user->name = $request->name;
        $user->email = $request->email;
        $user->status = $request->status;
        $user->role = json_encode($roleData); // Lưu vào database
        $user->save();

        session()->flash('backend.toast.success', 'Lưu thông tin người dùng thành công!');

        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.user.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.user.edit', ['id' => $user->id]);
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
        if ($this->checkRole()) {
            return redirect('/backend');
        }

        try {
            $user = User::find($id);
            $user->delete();

            session()->flash('backend.toast.success', 'Xóa người dùng thành công!');
        } catch (\Exception $ex) {
            session()->flash('backend.toast.error', 'Xóa người dùng không thành công!');
        }

        return redirect()->route('backend.user.index');
    }

    private function checkRole() {
        $role = Auth::user()->role;
        
        if (is_object($role) && enum_exists(get_class($role))) {
            $roleValue = $role->value;
        } else {
            $decoded = json_decode($role, true);
            $roleValue = is_array($decoded) ? ($decoded['role'] ?? '') : $role;
        }

        return in_array($roleValue, ['content', 'seo']);
    }

    /**
     * Định nghĩa nhóm quyền dùng chung cho cả Create và Edit
     */
    private function getPermissionGroups()
    {
        return [
            // Cột 1
            [
                'Danh mục sản phẩm' => [
                    \App\Enums\Permission::CATEGORY_PRODUCT_EDIT_URL,
                    \App\Enums\Permission::CATEGORY_PRODUCT_DELETE,
                    \App\Enums\Permission::CATEGORY_PRODUCT_EDIT_ALL_EXCEPT_URL,
                    \App\Enums\Permission::CATEGORY_PRODUCT_CREATE,
                    \App\Enums\Permission::CATEGORY_PRODUCT_VIEW_DETAIL,
                    \App\Enums\Permission::CATEGORY_PRODUCT_VIEW_LIST,
                ],
                'Sản phẩm' => [
                    \App\Enums\Permission::PRODUCT_EDIT_PRICE,
                    \App\Enums\Permission::PRODUCT_EDIT_SPEC,
                    \App\Enums\Permission::PRODUCT_EDIT_URL,
                    \App\Enums\Permission::PRODUCT_DELETE,
                    \App\Enums\Permission::PRODUCT_EDIT_ALL_EXCEPT_URL,
                    \App\Enums\Permission::PRODUCT_CREATE,
                    \App\Enums\Permission::PRODUCT_VIEW_DETAIL,
                    \App\Enums\Permission::PRODUCT_VIEW_LIST,
                ],
                'Bình luận' => [
                    \App\Enums\Permission::COMMENT_MANAGEMENT,
                ],
                'Danh mục Bài viết' => [
                    \App\Enums\Permission::POST_CATEGORY_EDIT_URL,
                    \App\Enums\Permission::POST_CATEGORY_DELETE,
                    \App\Enums\Permission::POST_CATEGORY_EDIT_ALL_EXCEPT_URL,
                    \App\Enums\Permission::POST_CATEGORY_CREATE,
                    \App\Enums\Permission::POST_CATEGORY_VIEW_DETAIL,
                    \App\Enums\Permission::POST_CATEGORY_VIEW_LIST,
                ],
                'Bài viết' => [
                    \App\Enums\Permission::POST_EDIT_URL,
                    \App\Enums\Permission::POST_DELETE,
                    \App\Enums\Permission::POST_EDIT_ALL_EXCEPT_URL,
                    \App\Enums\Permission::POST_CREATE,
                    \App\Enums\Permission::POST_VIEW_DETAIL,
                    \App\Enums\Permission::POST_VIEW_LIST,
                ],
                'Trang tĩnh' => [
                    \App\Enums\Permission::PAGE_EDIT_URL,
                    \App\Enums\Permission::PAGE_DELETE,
                    \App\Enums\Permission::PAGE_EDIT_ALL_EXCEPT_URL,
                    \App\Enums\Permission::PAGE_CREATE,
                    \App\Enums\Permission::PAGE_VIEW_DETAIL,
                    \App\Enums\Permission::PAGE_VIEW_LIST,
                ],
                'Thông tin website' => [
                    \App\Enums\Permission::SYSTEM_INFO_EDIT,
                    \App\Enums\Permission::SYSTEM_INFO_VIEW,
                ],
                'Lịch sử chỉnh sửa' => [
                    \App\Enums\Permission::HISTORY_VIEW,
                ],
                'Chi tiết đơn hàng' => [
                    \App\Enums\Permission::ORDER_DETAIL_DELETE,
                    \App\Enums\Permission::ORDER_DETAIL_EDIT_EXCEPT_URL,
                    \App\Enums\Permission::ORDER_DETAIL_CREATE,
                    \App\Enums\Permission::ORDER_DETAIL_VIEW,
                    \App\Enums\Permission::ORDER_DETAIL_LIST,
                ],
            ],
            // Cột 2
            [
                'Đơn hàng' => [
                    \App\Enums\Permission::ORDER_DELETE,
                    \App\Enums\Permission::ORDER_EDIT_ALL_EXCEPT_URL,
                    \App\Enums\Permission::ORDER_CREATE,
                    \App\Enums\Permission::ORDER_VIEW_DETAIL,
                    \App\Enums\Permission::ORDER_VIEW_LIST,
                ],
                'Liên hệ' => [
                    \App\Enums\Permission::CONTACT_DELETE,
                    \App\Enums\Permission::CONTACT_EDIT_ALL_EXCEPT_URL,
                    \App\Enums\Permission::CONTACT_CREATE,
                    \App\Enums\Permission::CONTACT_VIEW_DETAIL,
                    \App\Enums\Permission::CONTACT_VIEW_LIST,
                ],
                'Thương hiệu' => [
                    \App\Enums\Permission::BRAND_DELETE,
                    \App\Enums\Permission::BRAND_EDIT_ALL_EXCEPT_URL,
                    \App\Enums\Permission::BRAND_CREATE,
                    \App\Enums\Permission::BRAND_VIEW_DETAIL,
                    \App\Enums\Permission::BRAND_VIEW_LIST,
                ],
                'Banner' => [
                    \App\Enums\Permission::BANNER_DELETE,
                    \App\Enums\Permission::BANNER_EDIT_ALL_EXCEPT_URL,
                    \App\Enums\Permission::BANNER_CREATE,
                    \App\Enums\Permission::BANNER_VIEW_DETAIL,
                    \App\Enums\Permission::BANNER_VIEW_LIST,
                ],
                'Giá trị Thuộc tính' => [
                    \App\Enums\Permission::ATTRIBUTE_VALUE_DELETE,
                    \App\Enums\Permission::ATTRIBUTE_VALUE_EDIT_ALL_EXCEPT_URL,
                    \App\Enums\Permission::ATTRIBUTE_VALUE_CREATE,
                    \App\Enums\Permission::ATTRIBUTE_VALUE_VIEW_DETAIL,
                    \App\Enums\Permission::ATTRIBUTE_VALUE_VIEW_LIST,
                ],
                'Cấu hình' => [
                    \App\Enums\Permission::SETTING_DELETE,
                    \App\Enums\Permission::SETTING_EDIT_ALL_EXCEPT_URL,
                    \App\Enums\Permission::SETTING_CREATE,
                    \App\Enums\Permission::SETTING_VIEW_DETAIL,
                    \App\Enums\Permission::SETTING_VIEW_LIST,
                ],
                'Đánh giá' => [
                    \App\Enums\Permission::REVIEW_DELETE,
                    \App\Enums\Permission::REVIEW_EDIT_ALL_EXCEPT_URL,
                    \App\Enums\Permission::REVIEW_CREATE,
                    \App\Enums\Permission::REVIEW_VIEW_DETAIL,
                    \App\Enums\Permission::REVIEW_VIEW_LIST,
                ],
                'Video' => [
                    \App\Enums\Permission::VIDEO_DELETE,
                    \App\Enums\Permission::VIDEO_EDIT_ALL_EXCEPT_URL,
                    \App\Enums\Permission::VIDEO_CREATE,
                    \App\Enums\Permission::VIDEO_VIEW_DETAIL,
                    \App\Enums\Permission::VIDEO_VIEW_LIST,
                ],
            ]
        ];
    }

    private function getRoleModulesMap()
    {
        return [
            'content' => [
                'Danh mục sản phẩm',
                'Sản phẩm',
                'Danh mục Bài viết',
                'Bài viết',
                'Trang tĩnh',
                'Thương hiệu',
                'Giá trị Thuộc tính',
            ],
            'seo' => [
                'Danh mục sản phẩm',
                'Sản phẩm',
                'Bình luận',
                'Danh mục Bài viết',
                'Bài viết',
                'Trang tĩnh',
                'Thông tin website',
                'Lịch sử chỉnh sửa',
                'Chi tiết đơn hàng',
                'Đơn hàng',
                'Liên hệ',
                'Thương hiệu',
                'Banner',
                'Giá trị Thuộc tính',
                'Cấu hình',
                'Đánh giá',
                'Video',
            ]
        ];
    }
}