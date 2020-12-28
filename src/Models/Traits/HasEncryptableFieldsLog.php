<?php

namespace Webqamdev\EncryptableFields\Models\Traits;

use Spatie\Activitylog\Models\Activity;

trait HasEncryptableFieldsLog
{
    public function tapActivity(Activity $activity, string $eventName)
    {
        if (method_exists($this, 'getEncryptableArray')) {
            // Encryption is enabled
            $activity->properties = $this->activityEncryptArray($activity->properties->toArray());
        }
    }

    protected function activityEncryptArray(array $properties): array
    {
        if (method_exists($this, 'getEncryptableArray')) {
            $mappingEncryptableFields = $this->getEncryptableArray();

            foreach ($properties as $key => $value) {
                if (is_array($value)) {
                    // Recursive encrypt
                    $properties[$key] = $this->activityEncryptArray($value);
                } elseif (isset($mappingEncryptableFields[$key])) {
                    // get value without use accessor (to get encrypted value)
                    $properties[$key] = $this->attributes[$key];
                }
            }
        }

        return $properties;
    }
}
