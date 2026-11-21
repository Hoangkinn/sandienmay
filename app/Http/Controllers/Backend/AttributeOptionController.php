<?php

namespace App\Http\Controllers\Backend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\AttributeOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttributeOptionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request, $attribute_id)
    {
        if ($request->option_id) {
            $attributeOption = AttributeOption::find($request->option_id);
            $attributeOption->name = $request->name;
            $attributeOption->position = $request->position;
            $attributeOption->status = $request->status;
            $attributeOption->attribute_id = $attribute_id;

            if ($attributeOption->save()) {
                return [
                    "error" => false,
                    "message" => "Cập nhật giá trị thành công!",
                ];
            } else {
                return [
                    "error" => true,
                    "message" => "Cập nhật giá trị không thành công!"
                ];
            }
        } else {
            $attributeOption = AttributeOption::create([
                'name' => $request->name,
                'position' => $request->position,
                'status' => $request->status,
                'attribute_id' => $attribute_id,
                'user_id' => Auth::user()->id,
            ]);

            if ($attributeOption) {
                return [
                    "error" => false,
                    "message" => "Thêm mới giá trị thành công!",
                    "data" => $attributeOption,
                ];
            } else {
                return [
                    "error" => true,
                    "message" => "Thêm mới giá trị không thành công!"
                ];
            }
        }
    }

    public function index($attribute_id)
    {
        $attributeOptions = AttributeOption::where('attribute_id', $attribute_id)->get();

        $html = '';
        $selectedActived = '';
        $selectedInactived = '';

        foreach($attributeOptions as $attributeOption) {
            if ($attributeOption->status == Status::ACTIVED->value) {
                $selectedActived = 'selected';
            } else {
                $selectedInactived = 'selected';
            }

            $html .= '<tr data-_token="'. csrf_token() .'" data-option_id="' . $attributeOption->id . '" data-position="' . $attributeOption->position . '" data-name="' . $attributeOption->name . '" data-status="' . $attributeOption->status . '">';
            $html .= '<td style="vertical-align: middle;">' . $attributeOption->id . '</td>';
            $html .= '<td><input type="text" class="form-control form-control-sm edit-input" data-field="name" value="' . $attributeOption->name . '" disabled=""></td>';
            $html .= '<td><input type="number" class="form-control form-control-sm edit-input" data-field="position" value="' . $attributeOption->position . '" disabled=""></td>';
            $html .= '<td>';
            $html .= '<select class="form-control form-control-sm edit-input" data-field="status" disabled="">';
            $html .= '<option value="actived" ' . $selectedActived . '>Hiển thị</option>';
            $html .= '<option value="inactived" ' . $selectedInactived . '>Ẩn</option>';
            $html .= '</select>';
            $html .= '</td>';
            $html .= '<td>';
            $html .= '<button type="button" class="btn btn-sm btn-default btn-disable edit-table-btn-edit">Sửa </button>';
            $html .= '<button type="button" class="btn btn-sm btn-primary btn-enable edit-table-btn-save" style="display: none;">Sửa </button>';
            $html .= '<button type="button" class="btn btn-sm btn-default btn-enable edit-table-btn-cancel" style="display: none;">Thôi</button>';
            $html .= '</td>';
            $html .= '</tr>';

            $selectedActived = '';
            $selectedInactived = '';
        }

        $html .= '<tr data-_token="'. csrf_token() .'" data-position="0" data-name="" data-status="actived">';
        $html .= '<td style="vertical-align: middle;">0</td>';
        $html .= '<td><input type="text" class="form-control form-control-sm edit-input" data-field="name" value="" disabled=""></td>';
        $html .= '<td><input type="number" class="form-control form-control-sm edit-input" data-field="position" value="0" disabled=""></td>';
        $html .= '<td>';
        $html .= '<select class="form-control form-control-sm edit-input" data-field="status" disabled="">';
        $html .= '<option value="actived" selected>Hiển thị</option>';
        $html .= '<option value="inactived">Ẩn</option>';
        $html .= '</select>';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<button type="button" class="btn btn-sm btn-default btn-disable edit-table-btn-edit">Thêm mới </button>';
        $html .= '<button type="button" class="btn btn-sm btn-primary btn-enable edit-table-btn-save" style="display: none;">Thêm mới </button>';
        $html .= '<button type="button" class="btn btn-sm btn-default btn-enable edit-table-btn-cancel" style="display: none;">Thôi</button>';
        $html .= '</td>';
        $html .= '</tr>';

        return [
            'view' => $html
        ];
    }
}
