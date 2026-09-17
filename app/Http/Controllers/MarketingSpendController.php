<?php

namespace App\Http\Controllers;

use App\Models\MarketingSpend;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketingSpendController extends Controller
{
    /**
     * Display a listing of the marketing spends.
     */
    public function index(): View
    {
        $marketingSpends = MarketingSpend::query()
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->paginate(10);

        $months = $this->monthOptions();

        return view('marketing-spends.index', compact('marketingSpends', 'months'));
    }

    /**
     * Show the form for creating a new marketing spend.
     */
    public function create(): View
    {
        $months = $this->monthOptions();
        $years = $this->yearOptions();

        return view('marketing-spends.create', compact('months', 'years'));
    }

    /**
     * Store a newly created marketing spend in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'tahun' => ['required', 'integer', 'min:2020', 'max:2100'],
            'nominal' => ['required', 'integer', 'min:0'],
        ]);

        if (self::periodExists((int) $data['bulan'], (int) $data['tahun'])) {
            return back()->withErrors(['bulan' => 'Marketing spend voor deze maand bestaat al.']);
        }

        MarketingSpend::create($data);

        return redirect()
            ->route('marketing-spends.index')
            ->with('success', 'Marketing spend berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified marketing spend.
     */
    public function edit(MarketingSpend $marketingSpend): View
    {
        $months = $this->monthOptions();
        $years = $this->yearOptions();

        return view('marketing-spends.edit', compact('marketingSpend', 'months', 'years'));
    }

    /**
     * Update the specified marketing spend in storage.
     */
    public function update(Request $request, MarketingSpend $marketingSpend): RedirectResponse
    {
        $data = $request->validate([
            'bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'tahun' => ['required', 'integer', 'min:2020', 'max:2100'],
            'nominal' => ['required', 'integer', 'min:0'],
        ]);

        $duplicate = MarketingSpend::query()
            ->where('bulan', $data['bulan'])
            ->where('tahun', $data['tahun'])
            ->where('id', '!=', $marketingSpend->id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['bulan' => 'Marketing spend voor deze maand bestaat al.']);
        }

        $marketingSpend->update($data);

        return redirect()
            ->route('marketing-spends.index')
            ->with('success', 'Marketing spend berhasil diperbarui.');
    }

    /**
     * Remove the specified marketing spend from storage.
     */
    public function destroy(MarketingSpend $marketingSpend): RedirectResponse
    {
        $marketingSpend->delete();

        return redirect()
            ->route('marketing-spends.index')
            ->with('success', 'Marketing spend berhasil dihapus.');
    }

    /**
     * Determine whether a marketing spend already exists for the given period.
     */
    private static function periodExists(int $month, int $year): bool
    {
        return MarketingSpend::query()
            ->where('bulan', $month)
            ->where('tahun', $year)
            ->exists();
    }
}