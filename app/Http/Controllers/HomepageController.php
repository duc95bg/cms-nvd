<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\SetupController;
use App\Services\BlockRenderer;
use Illuminate\View\View;

class HomepageController extends Controller
{
    public function __invoke(string $locale = null): View
    {
        $site = SetupController::getMainSite();

        if (!$site) {
            abort(404);
        }

        if (!empty($site->blocks)) {
            $blocksHtml = BlockRenderer::render($site->blocks);
            return view('site.blocks-page', ['site' => $site, 'blocksHtml' => $blocksHtml]);
        }

        $site->load('template');
        return view($site->template->view, ['site' => $site]);
    }
}
