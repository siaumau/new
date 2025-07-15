<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PosinUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // 根據實際需求調整授權邏輯
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $posinId = $this->route('id'); // 從路由參數獲取ID
        
        return [
            '_users_id' => [
                'required',
                'integer',
                'exists:users,id'
            ],
            'posin_sn' => [
                'required',
                'string',
                'max:200',
                Rule::unique('posin', 'posin_sn')->ignore($posinId, 'posin_id')
            ],
            'posin_user' => [
                'required',
                'string',
                'max:200'
            ],
            'posin_dt' => [
                'nullable',
                'date',
                'before_or_equal:today'
            ],
            'posin_note' => [
                'required',
                'string',
                'max:1000'
            ],
            'posin_items' => [
                'required',
                'array',
                'min:1'
            ],
            'posin_items.*.posinitem_id' => [
                'nullable',
                'integer',
                'exists:posinitem,posinitem_id'
            ],
            'posin_items.*.itemtype' => [
                'required',
                'integer',
                'min:1'
            ],
            'posin_items.*.item_id' => [
                'required',
                'integer',
                'exists:item,item_id'
            ],
            'posin_items.*.item_name' => [
                'required',
                'string',
                'max:200'
            ],
            'posin_items.*.item_sn' => [
                'required',
                'string',
                'max:200'
            ],
            'posin_items.*.item_spec' => [
                'required',
                'string',
                'max:200'
            ],
            'posin_items.*.item_batch' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9\-]+$/'
            ],
            'posin_items.*.item_count' => [
                'required',
                'integer',
                'min:1',
                'max:999999'
            ],
            'posin_items.*.item_price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999.99'
            ],
            'posin_items.*.item_expireday' => [
                'nullable',
                'date',
                'after:today'
            ],
            'posin_items.*.item_validyear' => [
                'nullable',
                'string',
                'max:10',
                'regex:/^[0-9]+$/'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            '_users_id.required' => '用戶ID是必填項',
            '_users_id.exists' => '指定的用戶不存在',
            
            'posin_sn.required' => '進貨單號是必填項',
            'posin_sn.unique' => '進貨單號已存在',
            'posin_sn.max' => '進貨單號不能超過200個字符',
            
            'posin_user.required' => '供應商名稱是必填項',
            'posin_user.max' => '供應商名稱不能超過200個字符',
            
            'posin_dt.date' => '進貨日期格式不正確',
            'posin_dt.before_or_equal' => '進貨日期不能超過今天',
            
            'posin_note.required' => '備註是必填項',
            'posin_note.max' => '備註不能超過1000個字符',
            
            'posin_items.required' => '進貨項目是必填項',
            'posin_items.array' => '進貨項目必須是陣列格式',
            'posin_items.min' => '至少需要一個進貨項目',
            
            'posin_items.*.posinitem_id.exists' => '指定的進貨項目不存在',
            
            'posin_items.*.itemtype.required' => '項目類型是必填項',
            'posin_items.*.itemtype.integer' => '項目類型必須是整數',
            
            'posin_items.*.item_id.required' => '商品ID是必填項',
            'posin_items.*.item_id.exists' => '指定的商品不存在',
            
            'posin_items.*.item_batch.required' => '批號是必填項',
            'posin_items.*.item_batch.max' => '批號不能超過20個字符',
            'posin_items.*.item_batch.regex' => '批號只能包含英文、數字和橫線',
            
            'posin_items.*.item_count.required' => '數量是必填項',
            'posin_items.*.item_count.integer' => '數量必須是整數',
            'posin_items.*.item_count.min' => '數量至少為1',
            'posin_items.*.item_count.max' => '數量不能超過999999',
            
            'posin_items.*.item_price.required' => '價格是必填項',
            'posin_items.*.item_price.numeric' => '價格必須是數字',
            'posin_items.*.item_price.min' => '價格不能小於0',
            
            'posin_items.*.item_expireday.date' => '到期日格式不正確',
            'posin_items.*.item_expireday.after' => '到期日必須在今天之後',
            
            'posin_items.*.item_validyear.regex' => '有效年數只能包含數字',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 檢查是否可以更新（例如：已生成QR碼的不能更新某些欄位）
            $this->validateUpdatePermissions($validator);
            
            // 檢查重複項目
            $this->validateUniqueItems($validator);
            
            // 檢查商品資訊一致性
            $this->validateItemConsistency($validator);
        });
    }

    /**
     * 驗證更新權限
     */
    private function validateUpdatePermissions($validator): void
    {
        $posinId = $this->route('id');
        
        // 檢查是否已生成QR碼
        $hasQrCodes = \Illuminate\Support\Facades\DB::table('qr_codes')
            ->where('posin_id', $posinId)
            ->exists();
            
        if ($hasQrCodes) {
            // 如果已生成QR碼，限制某些欄位的修改
            $restrictedFields = ['posin_items'];
            
            foreach ($restrictedFields as $field) {
                if ($this->has($field)) {
                    $validator->errors()->add(
                        $field,
                        '該進貨單已生成QR碼，無法修改商品項目'
                    );
                }
            }
        }
        
        // 檢查美國進貨單狀態
        $posin = \App\Models\Posin::find($posinId);
        if ($posin && in_array($posin->us_purchase_order_status, ['generated', 'reviewed', 'completed'])) {
            $validator->errors()->add(
                'posin_sn',
                '該進貨單已轉換為美國進貨單，無法修改'
            );
        }
    }

    /**
     * 驗證項目唯一性
     */
    private function validateUniqueItems($validator): void
    {
        $items = $this->input('posin_items', []);
        $seen = [];
        
        foreach ($items as $index => $item) {
            $key = sprintf('%d-%s-%s', 
                $item['item_id'] ?? 0, 
                $item['item_batch'] ?? '', 
                $item['item_expireday'] ?? ''
            );
            
            if (isset($seen[$key])) {
                $validator->errors()->add(
                    "posin_items.{$index}",
                    '存在重複的商品項目（相同商品、批號和到期日）'
                );
            }
            
            $seen[$key] = true;
        }
    }

    /**
     * 驗證商品與項目資訊的一致性
     */
    private function validateItemConsistency($validator): void
    {
        $items = $this->input('posin_items', []);
        
        foreach ($items as $index => $itemData) {
            if (!isset($itemData['item_id'])) {
                continue;
            }
            
            $item = \App\Models\Item::find($itemData['item_id']);
            if (!$item) {
                continue;
            }
            
            // 檢查商品名稱是否一致
            if ($item->item_name !== ($itemData['item_name'] ?? '')) {
                $validator->errors()->add(
                    "posin_items.{$index}.item_name",
                    '商品名稱與資料庫中的記錄不一致'
                );
            }
            
            // 檢查商品序號是否一致
            if ($item->item_sn !== ($itemData['item_sn'] ?? '')) {
                $validator->errors()->add(
                    "posin_items.{$index}.item_sn",
                    '商品序號與資料庫中的記錄不一致'
                );
            }
            
            // 檢查商品規格是否一致
            if ($item->item_spec !== ($itemData['item_spec'] ?? '')) {
                $validator->errors()->add(
                    "posin_items.{$index}.item_spec",
                    '商品規格與資料庫中的記錄不一致'
                );
            }
        }
    }
}