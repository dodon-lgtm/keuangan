<?php

namespace App\Http\Controllers;

use App\Models\OperationalExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OperationalExpenseController extends Controller
{
    /**
     * Display a listing of the operational expenses.
     */
    public function index(): View
    {
        $operationalExpenses = OperationalExpense::query()
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->orderBy('nama_pengeluaran')
            ->paginate(10);

        $months = $this->monthOptions();
        $totalFixCost = OperationalExpense::query()
            ->where('kategori', OperationalExpense::KATEGORI_FIX_COST)
            ->sum('nominal');
        $totalVariableCost = OperationalExpense::query()
            ->where('kategori', OperationalExpense::KATEGORI_VARIABLE_COST)
            ->sum('nominal');

        return view('operational-expenses.index', compact(
            'operationalExpenses', 'months', 'totalFixCost', 'totalVariableCost'
        ));
    }

    /**
     * Show the form for creating a new operational expense.
     */
    public function create(): View
    {
        $months = $this->monthOptions();
        $years = $this->yearOptions();

        return view('operational-expenses.create', compact('months', 'years'));
    }

    /**
     * Store a newly created operational expense in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        OperationalExpense::create($data);

        return redirect()
            ->route('operational-expenses.index')
            ->with('success', 'Pengeluaran operasional berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified operational expense.
     */
    public function edit(OperationalExpense $operationalExpense): View
    {
        $months = $this->monthOptions();
        $years = $this->yearOptions();

        return view('operational-expenses.edit', compact('operationalExpense', 'months', 'years'));
    }

    /**
     * Update the specified operational expense in storage.
     */
    public function update(Request $request, OperationalExpense $operationalExpense): RedirectResponse
    {
        $data = $this->validated($request);

        $operationalExpense->update($data);

        return redirect()
            ->route('operational-expenses.index')
            ->with('success', 'Pengeluaran operasional berhasil diperbarui.');
    }

    /**
     * Remove the specified operational expense from storage.
     */
    public function destroy(OperationalExpense $operationalExpense): RedirectResponse
    {
        $operationalExpense->delete();

        return redirect()
            ->route('operational-expenses.index')
            ->with('success', 'Pengeluaran operasional berhasil dihapus.');
    }

    /**
     * Validate the incoming operational expense payload.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'nama_pengeluaran' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::in(OperationalExpense::KATEGORIS)],
            'nominal' => ['required', 'integer', 'min:0'],
            'bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'tahun' => ['required', 'integer', 'min:2020', 'max:2100'],
        ]);
    }
}