<?php

return [
    [
        "key" => "dashboard",
        "label" => "Dashboard",
        "icon" => "home",
        "route" => "dashboard",
    ],
    [
        "key" => "infrastructure",
        "label" => "Infrastructure",
        "icon" => "server-stack",
        "permission" => "view infrastructure",
        "children" => [
            [
                "label" => "Routers",
                "route" => "router.index",
                "icon" => "wifi",
                "permission" => "view routers",
            ],
        ],
    ],
];
