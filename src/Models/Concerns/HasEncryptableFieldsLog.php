<?php

namespace Webqamdev\EncryptableFields\Models\Concerns;

use Spatie\Activitylog\Models\Activity;

/**
 * Requires the EncryptableFields trait.
 */
trait HasEncryptableFieldsLog
{
    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->properties = $this->activityEncryptArray($activity->properties->toArray());
    }

    /**
     * @param  array<array-key, mixed>  $properties
     * @param  bool  $old  Whether the properties are the "old" values of the activity
     * @return array<array-key, mixed>
     */
    protected function activityEncryptArray(array $properties, bool $old = false): array
    {
        $mappingEncryptableFields = $this->getEncryptableArray();

        foreach ($properties as $key => $value) {
            if (is_array($value)) {
                // Recursive encrypt
                $properties[$key] = $this->activityEncryptArray($value, $old || $key === 'old');
            } elseif (array_key_exists($key, $mappingEncryptableFields)) {
                // Raw values, without accessor: original ones are not synced yet when the activity is logged
                $properties[$key] = $old ? $this->getRawOriginal($key) : ($this->attributes[$key] ?? null);
            }
        }

        return $properties;
    }
}
