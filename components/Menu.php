<?php namespace Initbiz\Cumuluscore\Components;

use Cms\Classes\ComponentBase;
use Event;
use Initbiz\CumulusCore\Classes\Helpers;
use Initbiz\CumulusCore\Models\Cluster;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;

class Menu extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'initbiz.cumuluscore::lang.menu.name',
            'description' => 'initbiz.cumuluscore::lang.menu.description'
        ];
    }

    public function onRun()
    {
        //Building navigation
        $current_cluster_modules = Cluster::where('slug', $this->property('clusterSlug'))
            ->first()
            ->plan()
            ->first();

        if ($current_cluster_modules !== null) {
            $current_cluster_modules = $current_cluster_modules->modules()
            ->get()
            ->pluck('name')
            ->values()
            ->map(function ($item, $key) {
                return str_slug($item);
            })
            ->toArray();
        } else {
            $current_cluster_modules = [];
        }

        $menuEntries= [];
        $theme = Theme::getActiveTheme();
        $pages = CmsPage::listInTheme($theme, true);

        foreach ($pages as $page) {
            if ($page->hasComponent('menuItem')) {
                $component = '';
                foreach ($page['settings']['components'] as $componentName => $componentProperties) {
                    $exp_key = explode(' ', $componentName);
                    if ($exp_key[0] === 'menuItem') {
                        $component = $page['settings']['components'][$componentName];
                    }
                }
                if ($component['cumulusModule'] === "none"
                    || in_array($component['cumulusModule'], $current_cluster_modules, true)) {
                    $menuEntries[$component['menuItemTitle']] = CmsPage::url($page['fileName']);
                }
            }
        }
        $this->page['menuEntries'] =$menuEntries;
    }

    public function defineProperties()
    {
        return [
            'clusterSlug' => [
                'title' => 'initbiz.cumuluscore::lang.menu.cluster_slug',
                'description' => 'initbiz.cumuluscore::lang.menu.cluster_slug_desc',
                'type' => 'string',
                'default' => '{{ :cluster }}'
            ]
        ];
    }
}
