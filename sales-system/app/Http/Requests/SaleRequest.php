<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|exists:customers,id',
            'total_harga' => 'required|numeric|min:0',
            'total_diskon' => 'required|numeric|min:0',
            'total_bayar' => 'required|numeric|min:0',
            'bayar' => 'required|numeric|min:0',
            'kembalian' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:tunai,kartu,transfer',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.harga_jual' => 'required|numeric|min:0',
            'items.*.diskon' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'customer_id.exists' => 'Pelanggan tidak ditemukan.',
            'total_harga.required' => 'Total harga wajib diisi.',
            'total_harga.numeric' => 'Total harga harus berupa angka.',
            'total_diskon.required' => 'Total diskon wajib diisi.',
            'total_diskon.numeric' => 'Total diskon harus berupa angka.',
            'total_bayar.required' => 'Total bayar wajib diisi.',
            'total_bayar.numeric' => 'Total bayar harus berupa angka.',
            'bayar.required' => 'Bayar wajib diisi.',
            'bayar.numeric' => 'Bayar harus berupa angka.',
            'kembalian.required' => 'Kembalian wajib diisi.',
            'kembalian.numeric' => 'Kembalian harus berupa angka.',
            'metode_pembayaran.required' => 'Metode pembayaran wajib dipilih.',
            'metode_pembayaran.in' => 'Metode pembayaran tidak valid.',
            'items.required' => 'Item produk wajib diisi.',
            'items.array' => 'Item produk harus berupa array.',
            'items.min' => 'Minimal harus ada 1 item produk.',
            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.product_id.exists' => 'Produk tidak ditemukan.',
            'items.*.quantity.required' => 'Jumlah wajib diisi.',
            'items.*.quantity.integer' => 'Jumlah harus berupa angka bulat.',
            'items.*.quantity.min' => 'Jumlah minimal 1.',
            'items.*.harga_jual.required' => 'Harga jual wajib diisi.',
            'items.*.harga_jual.numeric' => 'Harga jual harus berupa angka.',
            'items.*.diskon.required' => 'Diskon wajib diisi.',
            'items.*.diskon.numeric' => 'Diskon harus berupa angka.',
            'items.*.subtotal.required' => 'Subtotal wajib diisi.',
            'items.*.subtotal.numeric' => 'Subtotal harus berupa angka.'
        ];
    }
}
