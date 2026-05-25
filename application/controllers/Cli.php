<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Command-line entry points. All actions refuse HTTP requests so
 * nobody can run them via a browser.
 *
 *   php index.php cli images_backfill
 *   php index.php cli images_backfill uploads
 *   php index.php cli images_backfill assets/images/listings
 */
class Cli extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->input->is_cli_request()) {
            show_404();
        }
        $this->load->helper('image');
    }

    /**
     * Walk an image directory and generate _thumb.jpg / _medium.jpg
     * variants for every image that does not already have them.
     *
     * Skips files that are themselves variants (`_thumb` / `_medium`
     * suffix) so re-runs are idempotent.
     */
    public function images_backfill($subdir = null)
    {
        $roots = $subdir
            ? array(rtrim($subdir, '/'))
            : array(
                'uploads',
                'assets/images/listings',
                'assets/images/news',
                'assets/images/events',
                'assets/images/blogs',
                'assets/images/products',
                'assets/images/house',
                'assets/images/rental',
                'assets/images/classifieds',
                'assets/images/user',
                'assets/images/coupons',
                'assets/images/ads',
            );

        $total = 0; $made = 0; $skipped = 0; $failed = 0;

        foreach ($roots as $root) {
            $abs_root = FCPATH . $root;
            if (!is_dir($abs_root)) {
                echo "skip (not a dir): $root\n";
                continue;
            }
            echo "==> $root\n";

            $iter = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($abs_root, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iter as $file) {
                if (!$file->isFile()) continue;
                $total++;

                $ext = strtolower($file->getExtension());
                if (!in_array($ext, array('jpg', 'jpeg', 'png', 'webp'), true)) continue;

                // Don't re-process generated variants.
                $base = $file->getBasename('.' . $ext);
                if (substr($base, -6) === '_thumb' || substr($base, -7) === '_medium') {
                    continue;
                }

                $dir   = $file->getPath();
                $thumb = $dir . '/' . $base . '_thumb.jpg';
                $med   = $dir . '/' . $base . '_medium.jpg';
                if (is_file($thumb) && is_file($med)) {
                    $skipped++;
                    continue;
                }

                $mime = @mime_content_type($file->getPathname()) ?: ('image/' . ($ext === 'jpg' ? 'jpeg' : $ext));
                if (pearland411_generate_variants($file->getPathname(), $mime)) {
                    $made++;
                    if ($made % 50 === 0) echo "  ... $made variants generated\n";
                } else {
                    $failed++;
                }
            }
        }

        echo "\nscanned: $total  generated: $made  already_had: $skipped  failed: $failed\n";
    }
}
