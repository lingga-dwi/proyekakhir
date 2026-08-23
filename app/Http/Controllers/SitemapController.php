<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SitemapController extends Controller
{
    public function __invoke()
    {
        $katalogs = collect();

        if (! Config::get('app.db_offline')) {
            try {
                $katalogs = Katalog::query()
                    ->published()
                    ->select(['id', 'updated_at'])
                    ->latest('updated_at')
                    ->get();
            } catch (\Throwable $exception) {
                Log::warning('Database unavailable when generating sitemap', [
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return response()
            ->view('sitemap', compact('katalogs'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
