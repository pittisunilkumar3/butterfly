<?php

namespace App\Http\Controllers\Api\Common;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;


class UpdateAppController extends ApiBaseController
{
    public function index()
    {
        $laravel = app();
        $updateVersionInfo['laravelVersion'] = $laravel::VERSION;
        $appVersion = $this->getAppVersion();

        $appDetails = [
            [
                'name' => 'app_details',
                'value' => '',
            ],
            [
                'name' => 'app_version',
                'value' => $appVersion,
            ],
            [
                'name' => 'php_version',
                'value' => phpversion(),
            ],
            [
                'name' => 'laravel_version',
                'value' => $laravel::VERSION,
            ],
            [
                'name' => 'vue_version',
                'value' => '',
            ],
            [
                'name' => 'mysql_version',
                'value' => mysqli_get_client_info(),
            ],
        ];

        return ApiResponse::make('Data fetched', [
            'app_details' => $appDetails,
            'app_version' => $appVersion
        ]);
    }

    public function updateApp(Request $request)
    {
        $modulesData = Common::moduleInformations();
        return ApiResponse::make('Success', [
            'modules' => $modulesData,
            'file_name' => '',
        ]);
    }

    public function extractZip(Request $request)
    {
        $moduleName = $request->verified_name;
        $fileName = $request->file_name;

        $tempPath = storage_path() . '/app';
        $tempFileName = $tempPath . '/' . $fileName;

        $extractPath = base_path();

        $zip = \Zip::open($tempFileName);
        $zip->extract($extractPath);

        // PermsSeed::seedMainPermissions();
        // LangTrans::seedMainTranslations();
        // sleep(3);
        // Artisan::call('migrate', ['--force' => true]);

        $this->configClear();

        // Delete Downloaded File

        $modulesData = Common::moduleInformations();

        return ApiResponse::make('Success', [
            'installed_modules' => $modulesData['installed_modules'],
            'enabled_modules' => Arr::pluck($modulesData['enabled_modules'], 'verified_name'),
            'verified_name' => $moduleName,
            'version'    => $this->getAppVersion()
        ]);
    }

    public function configClear()
    {
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('cache:clear');
    }


    public function getAppVersion()
    {
        $versionFileName = app_type() == 'saas' ? 'superadmin_version.txt' : 'version.txt';
        $appVersion = File::get($versionFileName);

        return preg_replace("/\r|\n/", "", $appVersion);
    }

    public function downloadPercent(Request $request)
    {
        $percentage = File::get(public_path() . '/download-percentage.txt');

        return ApiResponse::make('Success', [
            'percentage' => $percentage
        ]);
    }
}
