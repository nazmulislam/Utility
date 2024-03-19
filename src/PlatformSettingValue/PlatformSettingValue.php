<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\PlatformSettingValue;
use App\Models\App\PlatformSetting;

class PlatformSettingValue
{

    public static function getPlatformSettingValue(string $platformVariableKey)
    {
        $platformStudiokey = PlatformSetting::select('platform_variable_value')->where('platform_variable_key', $platformVariableKey )->first();
        $platformStudioNameValue = isset($platformStudiokey) && isset($platformStudiokey->platform_variable_value)  ? json_decode($platformStudiokey->platform_variable_value) : "";

        return $platformStudioNameValue ;
    }

}
