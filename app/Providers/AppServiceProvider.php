<?php

namespace App\Providers;

use App\Setting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Tournaments;
use App\User;
use App\Project;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */

   

    public function boot()
    {
        //
        view()->composer('*', function ($view)
        {
            $siteSetting = cache()->rememberForever('siteSetting', function() {
                return Setting::pluck('value', 'key')->toArray();
            });

            // $totalCompanires = User::where('is_company',true)->count();

            // $verfiedCompanires = User::where('is_company',true)->where('is_company_verified',true)->count();
            // $verfiedCompaniresMonth = User::where('is_company',true)->where('is_company_verified',true)->whereMonth('company_verified_at',date('m'))->count();

            // $totalCompaniresMonth = User::where('is_company',true)->whereMonth('created_at',date('m'))->count();
            
            // //employees
            // $totalEmployee = User::where('is_employee',true)->count();
            // $totalEmployeeMonth = User::where('is_employee',true)->whereMonth('created_at',date('m'))->count();
            
            // //Projects
            // $totalProjects = Project::count();
            // $totalProjectsMonth = Project::whereMonth('created_at',date('m'))->count();

            // $Arraydata['Total Companies'] = [
            //     'count' => ($totalCompanires), 
            //     'this_month' => ($totalCompaniresMonth), 
            //     'icon' => '<i class="bx bxs-buildings"></i>', 
            //     'icon_bg' => 'bg-label-primary', 
            //     'url' => url('dashboard/company'), 
            // ];

            // $Arraydata['Verified Companies'] = [
            //     'count' => ($verfiedCompanires), 
            //     'this_month' => ($verfiedCompaniresMonth), 
            //     'icon' => '<i class="bx bxs-buildings"></i>', 
            //     'icon_bg' => 'bg-label-info', 
            //     'url' => url('dashboard/company'), 
            // ];

            // $Arraydata['Total Employees'] = [
            //     'count' => ($totalEmployee), 
            //     'this_month' => ($totalEmployeeMonth), 
            //     'icon' => '<i class="bx bxs-user-detail"></i>', 
            //     'icon_bg' => 'bg-label-success', 
            //     'url' => url('dashboard/employees'), 
            // ];

            // $Arraydata['Total Projects'] = [
            //     'count' => ($totalProjects), 
            //     'this_month' => ($totalProjectsMonth), 
            //     'icon' => '<i class="bx bx-file"></i>', 
            //     'icon_bg' => 'bg-label-warning', 
            //     'url' => url('dashboard/project'), 
            // ];

            
            $view->with(['siteSetting' => $siteSetting , 'paginateData' => [
                '10',
                '30',
                '50',
                '70',
                '100',
            ]]);
        });
        Paginator::useBootstrap();
    }
}
