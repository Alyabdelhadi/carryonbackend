<?php

namespace App\Support;

/**
 * Every page of the admin dashboard, in sidebar order. One registry feeds
 * the sidebar, the permission ticks on the group form and the `perm`
 * route middleware, so a new page is added here once.
 *
 * `actions` lists the permissions the page supports; `url` is where its
 * sidebar link goes (null = not in the sidebar); `children` are extra
 * sidebar links guarded by the same permission.
 */
final class AdminModules
{
    public const ACTIONS = ['view', 'create', 'edit', 'delete'];

    public const ACTION_LABELS = [
        'view' => 'View',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
    ];

    public static function sections(): array
    {
        return [
            'Overview' => [
                'dashboard' => ['label' => 'Dashboard', 'icon' => 'grid', 'url' => 'home', 'actions' => ['view']],
            ],
            'Operations' => [
                'orders' => ['label' => 'Parcel Orders', 'icon' => 'package', 'url' => 'parcel_order', 'actions' => ['view', 'edit', 'delete']],
                'trips' => ['label' => 'Trips', 'icon' => 'navigation', 'url' => 'trips', 'actions' => ['view'], 'children' => [
                    ['label' => 'All Trips', 'url' => 'trips'],
                    ['label' => 'One-time', 'url' => 'trips/one-time'],
                    ['label' => 'Frequent', 'url' => 'trips/frequent'],
                    ['label' => 'Upcoming routes', 'url' => 'trips/upcoming-routes'],
                    ['label' => 'Country routes', 'url' => 'trips/upcoming-country-routes'],
                ]],
                'users' => ['label' => 'App Users', 'icon' => 'users', 'url' => 'users', 'actions' => ['view', 'create', 'edit', 'delete'], 'children' => [
                    ['label' => 'All Users', 'url' => 'users'],
                    ['label' => 'Active', 'url' => 'users/active'],
                    ['label' => 'Inactive', 'url' => 'users/inactive'],
                    ['label' => 'Awaiting Review', 'url' => 'users/pending-verification', 'badge' => 'pending_identity'],
                ]],
            ],
            'Finance' => [
                'wallets' => ['label' => 'Wallets', 'icon' => 'credit-card', 'url' => 'wallets', 'actions' => ['view', 'edit']],
                'payouts' => ['label' => 'Payouts', 'icon' => 'dollar-sign', 'url' => 'payouts', 'actions' => ['view', 'edit'], 'badge' => 'pending_payouts'],
                'payment_methods' => ['label' => 'Payment Methods', 'icon' => 'shopping-bag', 'url' => 'payment-methods', 'actions' => ['view', 'edit']],
            ],
            'Communication' => [
                'push' => ['label' => 'Push Notification', 'icon' => 'send', 'url' => 'push', 'actions' => ['view', 'create']],
                'notifications' => ['label' => 'Notification Templates', 'icon' => 'bell', 'url' => 'notifications', 'actions' => self::ACTIONS],
                'emails' => ['label' => 'Email Templates', 'icon' => 'mail', 'url' => 'emails', 'actions' => self::ACTIONS],
            ],
            'App Content' => [
                'services' => ['label' => 'Services', 'icon' => 'layers', 'url' => 'services', 'actions' => self::ACTIONS],
                'sliders' => ['label' => 'Home Sliders', 'icon' => 'image', 'url' => 'sliders', 'actions' => self::ACTIONS],
                'sliders2' => ['label' => 'Second Sliders', 'icon' => 'image', 'url' => 'sliders2', 'actions' => self::ACTIONS],
                'categories' => ['label' => 'Parcel Categories', 'icon' => 'tag', 'url' => 'parcel_cate', 'actions' => self::ACTIONS],
                'weights' => ['label' => 'Weights', 'icon' => 'bar-chart-2', 'url' => 'weights', 'actions' => self::ACTIONS],
                'tips' => ['label' => 'Tips', 'icon' => 'award', 'url' => 'tips', 'actions' => self::ACTIONS],
                'statuses' => ['label' => 'Delivery Statuses', 'icon' => 'truck', 'url' => 'delivery_statuses', 'actions' => self::ACTIONS],
                'pages' => ['label' => 'Pages', 'icon' => 'file-text', 'url' => 'pages', 'actions' => self::ACTIONS],
                'texts' => ['label' => 'App Texts', 'icon' => 'type', 'url' => 'texts', 'actions' => ['view', 'edit']],
            ],
            'Locations' => [
                'countries' => ['label' => 'Countries', 'icon' => 'globe', 'url' => 'countries', 'actions' => self::ACTIONS],
                'cities' => ['label' => 'Cities', 'icon' => 'map-pin', 'url' => 'cities', 'actions' => self::ACTIONS],
            ],
            'Settings' => [
                'app_settings' => ['label' => 'App Settings', 'icon' => 'sliders', 'url' => 'app-settings', 'actions' => ['view', 'edit']],
                'versions' => ['label' => 'App Versions', 'icon' => 'smartphone', 'url' => 'versions', 'actions' => ['view', 'edit']],
            ],
            'Administration' => [
                'admins' => ['label' => 'Admins', 'icon' => 'user-check', 'url' => 'admins', 'actions' => self::ACTIONS],
                'admin_groups' => ['label' => 'Groups & Permissions', 'icon' => 'shield', 'url' => 'admin-groups', 'actions' => self::ACTIONS],
            ],
        ];
    }

    /** key => definition, all sections flattened. */
    public static function all(): array
    {
        return array_merge(...array_values(self::sections()));
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /**
     * Keeps only known modules and the actions each supports; `view` is
     * implied by any other action (you cannot edit a page you cannot open).
     */
    public static function sanitize(array $permissions): array
    {
        $clean = [];
        foreach (self::all() as $key => $module) {
            $actions = array_values(array_intersect($module['actions'], (array) ($permissions[$key] ?? [])));
            if ($actions && !in_array('view', $actions, true) && in_array('view', $module['actions'], true)) {
                array_unshift($actions, 'view');
            }
            if ($actions) {
                $clean[$key] = $actions;
            }
        }
        return $clean;
    }
}
