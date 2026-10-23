<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolProfile;

class SitemapController extends Controller
{
    /**
     * Render XML sitemap for search engine crawlers (Google, Bing).
     */
    public function index(Request $request)
    {
        $baseUrl = url('/');

        $staticPages = [
            ['url' => $baseUrl . '/', 'lastmod' => date('Y-m-d'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['url' => $baseUrl . '/login', 'lastmod' => date('Y-m-d'), 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['url' => $baseUrl . '/staff/activate', 'lastmod' => date('Y-m-d'), 'changefreq' => 'monthly', 'priority' => '0.6'],
            ['url' => $baseUrl . '/student/activate', 'lastmod' => date('Y-m-d'), 'changefreq' => 'monthly', 'priority' => '0.6'],
        ];

        // Include public school profiles if available
        $schools = SchoolProfile::select('school_code', 'updated_at')->get();
        $schoolPages = [];

        foreach ($schools as $school) {
            $schoolPages[] = [
                'url'        => $baseUrl . '/school/' . strtolower($school->school_code),
                'lastmod'    => $school->updated_at ? $school->updated_at->format('Y-m-d') : date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority'   => '0.7',
            ];
        }

        $allPages = array_merge($staticPages, $schoolPages);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($allPages as $page) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($page['url']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $page['lastmod'] . '</lastmod>' . "\n";
            $xml .= '    <changefreq>' . $page['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $page['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }
}
