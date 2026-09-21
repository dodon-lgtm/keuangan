<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     */
    public function index(Request $request): View
    {
        $q = filled($request->get('q')) ? trim((string) $request->get('q')) : '';
        $sumber = filled($request->get('sumber')) ? (string) $request->get('sumber') : '';
        $status = filled($request->get('status')) ? (string) $request->get('status') : '';
        $tanggalFrom = filled($request->get('tanggal_from')) ? (string) $request->get('tanggal_from') : '';
        $tanggalTo = filled($request->get('tanggal_to')) ? (string) $request->get('tanggal_to') : '';

        $base = Customer::query()->withCount('orders');

        if ($q !== '') {
            $base->where(function ($sub) use ($q) {
                $sub->whereLike('nama_lengkap', "%$q%")
                    ->orWhereLike('nama_brand', "%$q%")
                    ->orWhereLike('no_whatsapp', "%$q%")
                    ->orWhereLike('email', "%$q%");
            });
        }

        if ($sumber !== '') {
            $base->where('sumber', $sumber);
        }

        if ($tanggalFrom !== '') {
            $base->where('tanggal_masuk_chat', '>=', $tanggalFrom);
        }

        if ($tanggalTo !== '') {
            $base->where('tanggal_masuk_chat', '<=', $tanggalTo);
        }

        if ($status === 'repeat' || $status === 'new') {
            $counts = [];

            foreach (DB::table('orders')->get(['customer_id']) as $row) {
                $id = (int) $row->customer_id;

                $counts[$id] = ($counts[$id] ?? 0) + 1;
            }

            $ids = [];

            foreach ($counts as $id => $count) {
                if (($status === 'repeat' && $count > 1) || ($status === 'new' && $count <= 1)) {
                    $ids[] = $id;
                }
            }

            $base->whereIn('id', $ids);
        }

        $customers = $base->orderByDesc('id')->paginate(10)->withQueryString();

        // Grafik: pelanggan per sumber (data gefilterd)
        $chartAll = $base->get();

        $sumberTotals = [];

        foreach ($chartAll as $customer) {
            $key = $customer->sumber ?? '';

            $sumberTotals[$key] = ($sumberTotals[$key] ?? 0) + 1;
        }

        $chartSumber = [];
        $chartSumberValue = [];

        foreach (Customer::SUMBERS as $label) {
            $chartSumber[] = $label;
            $chartSumberValue[] = (int) ($sumberTotals[$label] ?? 0);
        }

        // Grafik: new vs repeat pelanggan
        $repeat = 0;

        foreach ($chartAll as $customer) {
            if ((int) $customer->orders_count > 1) {
                $repeat++;
            }
        }

        $chartStatus = ['new', 'repeat'];
        $chartStatusValue = [max((int) $chartAll->count() - $repeat, 0), $repeat];

        return view('customers.index', compact(
            'customers',
            'q', 'sumber', 'status', 'tanggalFrom', 'tanggalTo',
            'chartSumber', 'chartSumberValue', 'chartStatus', 'chartStatusValue'
        ));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nama_brand' => ['required', 'string', 'max:255'],
            'no_whatsapp' => ['required', 'string', 'max:20'],
            'sumber' => ['required', Rule::in(Customer::SUMBERS)],
            'tanggal_masuk_chat' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        Customer::create($data);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nama_brand' => ['required', 'string', 'max:255'],
            'no_whatsapp' => ['required', 'string', 'max:20'],
            'sumber' => ['required', Rule::in(Customer::SUMBERS)],
            'tanggal_masuk_chat' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $customer->update($data);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Pelanggan berhasil diperbarui.');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }
}
