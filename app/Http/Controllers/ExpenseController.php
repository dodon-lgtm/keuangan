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
    public function index(Request $request): View
    {
        $bulan = filled($request->get('bulan')) ? (int) $request->get('bulan') : null;
        $tahun = filled($request->get('tahun')) ? (int) $request->get('tahun') : null;
        $kategori = filled($request->get('kategori')) ? (string) $request->get('kategori') : '';

        $marketingQuery = MarketingSpend::query();

        if ($bulan !== null) {
            $marketingQuery->where('bulan', $bulan);
        }

        if ($tahun !== null) {
            $marketingQuery->where('tahun', $tahun);
        }

        $marketingSpends = $marketingQuery
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get();

        $operationalQuery = OperationalExpense::query();

        if ($bulan !== null) {
            $operationalQuery->where('bulan', $bulan);
        }

        if ($tahun !== null) {
            $operationalQuery->where('tahun', $tahun);
        }

        if ($kategori !== '') {
            $operationalQuery->where('kategori', $kategori);
        }

        $operationalExpenses = $operationalQuery
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->orderBy('nama_pengeluaran')
            ->get();

        $totalMarketing = (int) $marketingQuery->clone()->sum('nominal');
        $totalFixCost = (int) $operationalQuery->clone()
            ->where('kategori', OperationalExpense::KATEGORI_FIX_COST)
            ->sum('nominal');
        $totalVariableCost = (int) $operationalQuery->clone()
            ->where('kategori', OperationalExpense::KATEGORI_VARIABLE_COST)
            ->sum('nominal');

        // Grafik: budget iklan per bulan van geselecteerde jaar
        $chartYear = $tahun ?? (int) now()->year;

        $spendByMonth = [];

        foreach (MarketingSpend::query()->where('tahun', $chartYear)->get() as $spend) {
            $spendByMonth[(int) $spend->bulan] = ($spendByMonth[(int) $spend->bulan] ?? 0) + (int) $spend->nominal;
        }

        $chartMarketingLabels = [];
        $chartMarketingValue = [];

        foreach ($this->monthOptions() as $key => $label) {
            $chartMarketingLabels[] = $label;
            $chartMarketingValue[] = (int) ($spendByMonth[$key] ?? 0);
        }

        // Grafik: kompositie fix vs variable cost
        $chartKategoriLabels = ['Fix Cost', 'Variable Cost'];
        $chartKategoriValue = [
            (int) $operationalQuery->clone()->where('kategori', OperationalExpense::KATEGORI_FIX_COST)->sum('nominal'),
            (int) $operationalQuery->clone()->where('kategori', OperationalExpense::KATEGORI_VARIABLE_COST)->sum('nominal'),
        ];

        // Grafik: top 5 pengeluaran operasional
        $topOperational = $operationalQuery->clone()
            ->orderByDesc('nominal')
            ->limit(5)
            ->get();

        $chartTopNama = [];
        $chartTopValue = [];

        foreach ($topOperational as $expense) {
            $chartTopNama[] = $expense->nama_pengeluaran;
            $chartTopValue[] = (int) $expense->nominal;
        }

        $months = $this->monthOptions();
        $years = $this->yearOptions($tahun);

        return view('expenses.index', compact(
            'marketingSpends', 'operationalExpenses',
            'totalMarketing', 'totalFixCost', 'totalVariableCost',
            'bulan', 'tahun', 'kategori', 'chartYear',
            'chartMarketingLabels', 'chartMarketingValue',
            'chartKategoriLabels', 'chartKategoriValue',
            'chartTopNama', 'chartTopValue',
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