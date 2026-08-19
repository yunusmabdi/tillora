<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FetchProductImages extends Command
{
    protected $signature = 'products:fetch-images
                            {--force : Re-fetch even if the file already exists}
                            {--limit= : Only process the first N products}';

    protected $description = 'Fetch real product images from Pexels based on product name and save them to match the DB image path';

    public function handle()
    {
        $apiKey = config('services.pexels.key');

        if (! $apiKey) {
            $this->error('PEXELS_API_KEY is not set in your .env file.');
            return self::FAILURE;
        }

        $query = Product::query()->whereNotNull('image');

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            $this->warn('No products with an image path found.');
            return self::SUCCESS;
        }

        $this->info("Fetching images for {$products->count()} products...");
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        $success = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($products as $product) {
            $path = $product->image; // e.g. products/coca-cola-500ml.jpg

            if (! $this->option('force') && Storage::disk('public')->exists($path)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $searchTerm = $this->buildSearchTerm($product->name);

            $imageUrl = $this->searchPexels($searchTerm, $apiKey);

            if (! $imageUrl) {
                $failed++;
                $bar->advance();
                continue;
            }

            $imageContents = Http::timeout(15)->get($imageUrl)->body();

            if (empty($imageContents)) {
                $failed++;
                $bar->advance();
                continue;
            }

            Storage::disk('public')->put($path, $imageContents);

            $success++;
            $bar->advance();

            usleep(300000); // 0.3s, be polite to the free-tier rate limit
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Done. Saved: {$success}, Skipped (already existed): {$skipped}, Failed: {$failed}");

        if ($failed > 0) {
            $this->comment('Products that failed will show the SVG placeholder — re-run with --force to retry just those, or adjust their names for better search matches.');
        }

        return self::SUCCESS;
    }

    protected function buildSearchTerm(string $productName): string
    {
        $cleaned = preg_replace('/\s*\d+\s*(kg|g|ml|l|pcs|pack|pieces)\b.*$/i', '', $productName);

        return trim($cleaned) ?: $productName;
    }

    protected function searchPexels(string $query, string $apiKey): ?string
    {
        $response = Http::withHeaders([
            'Authorization' => $apiKey,
        ])->get('https://api.pexels.com/v1/search', [
            'query' => $query,
            'per_page' => 1,
            'orientation' => 'square',
        ]);

        if (! $response->successful()) {
            return null;
        }

        $photos = $response->json('photos', []);

        if (empty($photos)) {
            return null;
        }

        return $photos[0]['src']['medium'] ?? null;
    }
}