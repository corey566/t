
<?php

namespace Modules\Gallface\Helpers;

class ClassHelper
{
    /**
     * Get the correct BusinessLocation class
     */
    public static function getBusinessLocationClass()
    {
        // Check various possible locations for BusinessLocation model
        $possibleClasses = [
            '\App\BusinessLocation',
            '\App\Models\BusinessLocation',
        ];

        foreach ($possibleClasses as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        throw new \Exception('BusinessLocation model not found in any expected namespace');
    }

    /**
     * Get BusinessLocation by ID
     */
    public static function getBusinessLocation($id)
    {
        $class = self::getBusinessLocationClass();
        return $class::find($id);
    }
}
