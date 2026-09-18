<?php

namespace App\Http\Controllers;

use App\Models\MarketingSpend;
use App\Models\OperationalExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Unifies every monthly expense on a single "Pengeluaran" page:
 *  - Marketing / ad budget (marketing_spends) for the MER & ROI reports.
 *  - Operational costs / Fix & Variable Cost (operational_expenses).
 */
class ExpenseController extends Controller
{
    /**
     * Display both expense sections on one page.
     */
    public function index(): View
    {
        $marketingSpends = MarketingSpend::query()
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get();

        $operationalExpenses = OperationalExpense::query()
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->orderBy('nama_pengeluaran')
            ->get();

        $totalMarketing = (int) MarketingSpend::query()->sum('nominal');
        $totalFixCost = (int) OperationalExpense::query()
            ->where('kategori', OperationalExpense::KATEGORI_FIX_COST)
            ->sum('nominal');
        $totalVariableCost = (int) OperationalExpense::query()
            ->where('kategori', OperationalExpense::KATEGORI_VARIABLE_COST)
            ->sum('nominal');

        $months = $this->monthOptions();
        $years = $this->yearOptions();

        return view('expenses.index', compact(
            'marketingSpends', 'operationalExpenses',
            'totalMarketing', 'totalFixCost', 'totalVariableCost',
            'months', 'years'
        ));
    }

    /**
     * Store a new monthly marketing / ad budget.
     */
    public function storeSpend(Request $request): RedirectResponse
    {
        $data = $this->validatedSpend($request);

        if (MarketingSpend::query()
            ->where('bulan', $data['bulan'])
            ->where('tahun', $data['tahun'])
            ->exists()) {
            return back()->withErrors(['bulan' => 'Budget iklan untuk bulan ini sudah ada.']);
        }

        MarketingSpend::create($data);

        return $this->backToExpenses('Budget iklan berhasil ditambahkan.');
    }

    /**
     * Update an existing marketing / ad budget.
     */
    public function updateSpend(Request $request, MarketingSpend $marketingSpend): RedirectResponse
    {
        $data = $this->validatedSpend($request);

        $duplicate = MarketingSpend::query()
            ->where('bulan', $data['bulan'])
            ->where('tahun', $data['tahun'])
            ->where('id', '!=', $marketingSpend->id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['bulan' => 'Budget iklan untuk bulan ini sudah ada.']);
        }

        $marketingSpend->update($data);

        return $this->backToExpenses('Budget iklan berhasil diperbarui.');
    }

    /**
     * Delete a marketing / ad budget.
     */
    public function destroySpend(MarketingSpend $marketingSpend): RedirectResponse
    {
        $marketingSpend->delete();

        return $this->backToExpenses('Budget iklan berhasil dihapus.');
    }

    /**
     * Store a new operational expense (Fix/Variable Cost).
     */
    public function storeOperational(Request $request): RedirectResponse
    {
        $data = $this->validatedOperational($request);

        OperationalExpense::create($data);

        return $this->backToExpenses('Pengeluaran operasional berhasil ditambahkan.');
    }

    /**
     * Update an existing operational expense.
     */
    public function updateOperational(Request $request, OperationalExpense $operationalExpense): RedirectResponse
    {
        $data = $this->validatedOperational($request);

        $operationalExpense->update($data);

        return $this->backToExpenses('Pengeluaran operasional berhasil diperbarui.');
    }

    /**
     * Delete an operational expense.
     */
    public function destroyOperational(OperationalExpense $operationalExpense): RedirectResponse
    {
        $operationalExpense->delete();

        return $this->backToExpenses('Pengeluaran operasional berhasil dihapus.');
    }

    /**
     * Redirect back to the unified expenses page with a flash message.
     */
    private function backToExpenses(string $message): RedirectResponse
    {
        return redirect()
            ->route('expenses.index')
            ->with('success', $message);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedSpend(Request $request): array
    {
        return $request->validate([
            'bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'tahun' => ['required', 'integer', 'min:2020', 'max:2100'],
            'nominal' => ['required', 'integer', 'min:0'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedOperational(Request $request): array
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