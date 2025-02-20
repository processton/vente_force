<?php

namespace Crater\Traits;

trait GeneratesMenuTrait
{
    public function generateMenu($key, $user)
    {
        $menu = [];

        $m = config('crater.'.$key);

        foreach ($m as $data) {
            if ($user->checkAccess($data)) {
                $menu[] = [
                    'title' => $data['title'],
                    'link' => $data['link'],
                    'icon' => $data['icon'],
                    'name' => $data['name'],
                    'group' => $data['group'],
                ];
            }
        }

        return $menu;
    }
}
